<?php

namespace App\Services;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueManager
{
    /**
     * Calculate total revenue for all orders.
     *
     * @return float
     */
    public static function calculateTotalRevenue(): float
    {
        return (float) OrderItem::sum(DB::raw('quantity * price'));
    }

    /**
     * Calculate total revenue for a specific date range.
     *
     * @param string $from Start date (Y-m-d)
     * @param string $to End date (Y-m-d)
     * @return float
     */
    public static function calculateRevenueByDateRange(string $from, string $to): float
    {
        return (float) OrderItem::whereHas('order', function ($query) use ($from, $to) {
            $query->whereBetween('created_at', [$from, $to]);
        })->sum(DB::raw('quantity * price'));
    }

    /**
     * Calculate daily revenue for today or a specific day.
     *
     * @param string|null $date (Y-m-d), default today
     * @return float
     */
    public static function calculateDailyRevenue(?string $date = null): float
    {
        $date = $date ?: Carbon::today()->toDateString();

        return static::calculateRevenueByDateRange($date, $date);
    }

    /**
     * Calculate weekly revenue for the current week or a given start date.
     *
     * @param string|null $startOfWeek (Y-m-d), default start of current week
     * @return float
     */
    public static function calculateWeeklyRevenue(?string $startOfWeek = null): float
    {
        $start = $startOfWeek ? Carbon::parse($startOfWeek)->startOfWeek() : Carbon::now()->startOfWeek();
        $end = (clone $start)->endOfWeek();

        return static::calculateRevenueByDateRange($start->toDateString(), $end->toDateString());
    }

    /**
     * Calculate monthly revenue for the current month or a given month.
     *
     * @param string|null $month (Y-m), default current month
     * @return float
     */
    public static function calculateMonthlyRevenue(?string $month = null): float
    {
        $start = $month ? Carbon::parse($month . '-01')->startOfMonth() : Carbon::now()->startOfMonth();
        $end = (clone $start)->endOfMonth();

        return static::calculateRevenueByDateRange($start->toDateString(), $end->toDateString());
    }
}
