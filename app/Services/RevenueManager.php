<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OrderItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class RevenueManager
 *
 * Utility class for revenue calculation. Stateless static methods.
 *
 * Adds lightweight caching to avoid repeated heavy aggregation queries.
 */
class RevenueManager
{
    private const CACHE_TTL_SECONDS = 3600; // 1 hour cache for computed ranges

    /**
     * Calculate total revenue for all order items.
     *
     * @return float
     */
    public static function calculateTotalRevenue(): float
    {
        $cacheKey = 'revenue:total';

        return (float) Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () {
            return (float) OrderItem::sum(DB::raw('quantity * price'));
        });
    }

    /**
     * Calculate total revenue for a specific date range (inclusive).
     *
     * @param string $from Date string (Y-m-d)
     * @param string $to Date string (Y-m-d)
     * @return float
     */
    public static function calculateRevenueByDateRange(string $from, string $to): float
    {
        // Normalize and validate dates using Carbon
        $start = Carbon::parse($from)->startOfDay()->toDateTimeString();
        $end = Carbon::parse($to)->endOfDay()->toDateTimeString();

        $cacheKey = sprintf('revenue:%s:%s', $from, $to);

        return (float) Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($start, $end) {
            return (float) OrderItem::whereHas('order', function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            })->sum(DB::raw('quantity * price'));
        });
    }

    /**
     * Calculate revenue for a specific day (default: today).
     *
     * @param string|null $date Y-m-d
     * @return float
     */
    public static function calculateDailyRevenue(?string $date = null): float
    {
        $date = $date ?? Carbon::today()->toDateString();

        return static::calculateRevenueByDateRange($date, $date);
    }

    /**
     * Calculate revenue for the week that contains the given start date or current week.
     *
     * @param string|null $startOfWeek Date Y-m-d or null for current week
     * @return float
     */
    public static function calculateWeeklyRevenue(?string $startOfWeek = null): float
    {
        $start = $startOfWeek ? Carbon::parse($startOfWeek)->startOfWeek() : Carbon::now()->startOfWeek();
        $end = (clone $start)->endOfWeek();

        return static::calculateRevenueByDateRange($start->toDateString(), $end->toDateString());
    }

    /**
     * Calculate revenue for a month (format Y-m). Defaults to current month.
     *
     * @param string|null $month e.g. "2025-11" or null for current month
     * @return float
     */
    public static function calculateMonthlyRevenue(?string $month = null): float
    {
        $start = $month ? Carbon::parse($month . '-01')->startOfMonth() : Carbon::now()->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return static::calculateRevenueByDateRange($start->toDateString(), $end->toDateString());
    }
}
