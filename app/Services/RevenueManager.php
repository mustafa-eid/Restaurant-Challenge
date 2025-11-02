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
 * Provides utility methods for calculating revenue metrics over different time ranges.
 * Uses static methods for convenience and applies caching to reduce database load
 * during repeated analytics or dashboard updates.
 *
 * @package App\Services
 */
class RevenueManager
{
    /**
     * Cache duration in seconds for all computed revenue values.
     *
     * @var int
     */
    private const CACHE_TTL_SECONDS = 3600; // 1 hour cache for computed ranges

    /**
     * Calculate total revenue for all order items in the system.
     *
     * Uses cached results to avoid re-querying large datasets repeatedly.
     *
     * @return float Total revenue across all orders.
     */
    public static function calculateTotalRevenue(): float
    {
        $cacheKey = 'revenue:total';

        return (float) Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function (): float {
            return (float) OrderItem::sum(DB::raw('quantity * price'));
        });
    }

    /**
     * Calculate total revenue for a specific date range (inclusive).
     *
     * Internally, this method:
     * - Parses and normalizes input dates using Carbon.
     * - Filters related orders created within the date range.
     * - Aggregates revenue using quantity * price.
     * - Caches the computed total for performance.
     *
     * @param string $from The start date (Y-m-d format, inclusive).
     * @param string $to The end date (Y-m-d format, inclusive).
     *
     * @return float Total revenue generated within the given range.
     */
    public static function calculateRevenueByDateRange(string $from, string $to): float
    {
        $start = Carbon::parse($from)->startOfDay()->toDateTimeString();
        $end = Carbon::parse($to)->endOfDay()->toDateTimeString();

        $cacheKey = sprintf('revenue:%s:%s', $from, $to);

        return (float) Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($start, $end): float {
            return (float) OrderItem::whereHas('order', function ($query) use ($start, $end): void {
                $query->whereBetween('created_at', [$start, $end]);
            })->sum(DB::raw('quantity * price'));
        });
    }

    /**
     * Calculate total revenue for a specific day (defaults to today).
     *
     * @param string|null $date Optional date in Y-m-d format. Defaults to current date.
     *
     * @return float Revenue generated on the specified or current date.
     */
    public static function calculateDailyRevenue(?string $date = null): float
    {
        $date = $date ?? Carbon::today()->toDateString();

        return static::calculateRevenueByDateRange($date, $date);
    }

    /**
     * Calculate revenue for a specific week or the current week.
     *
     * Determines the start and end of the week using Carbon and sums all revenue
     * between those dates (inclusive).
     *
     * @param string|null $startOfWeek Optional start date (Y-m-d). Defaults to the current week.
     *
     * @return float Total revenue for the determined week.
     */
    public static function calculateWeeklyRevenue(?string $startOfWeek = null): float
    {
        $start = $startOfWeek ? Carbon::parse($startOfWeek)->startOfWeek() : Carbon::now()->startOfWeek();
        $end = (clone $start)->endOfWeek();

        return static::calculateRevenueByDateRange($start->toDateString(), $end->toDateString());
    }

    /**
     * Calculate revenue for a specific month or the current month.
     *
     * Parses the input month, identifies the first and last day of that month,
     * and sums total revenue within that range.
     *
     * @param string|null $month Month in "YYYY-MM" format, or null for the current month.
     *
     * @return float Total revenue for the month.
     */
    public static function calculateMonthlyRevenue(?string $month = null): float
    {
        $start = $month
            ? Carbon::parse($month . '-01')->startOfMonth()
            : Carbon::now()->startOfMonth();

        $end = (clone $start)->endOfMonth();

        return static::calculateRevenueByDateRange($start->toDateString(), $end->toDateString());
    }
}
