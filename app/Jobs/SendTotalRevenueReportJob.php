<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\RevenueManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Pool;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class SendTotalRevenueReportJob
 *
 * This job is responsible for generating, caching, and sending total revenue reports
 * to external APIs. It supports multiple report types (daily, weekly, monthly, custom)
 * and ensures reliability through retries, exponential backoff, and queued confirmation.
 *
 * The job performs the following sequence:
 * 1. Retrieves or calculates the total revenue (cached for efficiency).
 * 2. Sends verification and reporting requests concurrently.
 * 3. Dispatches a confirmation job upon success.
 *
 * @package App\Jobs
 */
class SendTotalRevenueReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The maximum number of attempts before the job is considered failed.
     *
     * @var int
     */
    public int $tries = 10;

    /**
     * The maximum number of exceptions allowed before the job fails.
     *
     * @var int
     */
    public int $maxExceptions = 3;

    /**
     * Exponential backoff intervals (in seconds) for retry attempts.
     *
     * @var array<int>
     */
    public array $backoff = [60, 120, 300, 600];

    /**
     * The report type (e.g. daily, weekly, monthly, custom).
     *
     * @var string
     */
    protected string $type;

    /**
     * Optional start date for custom report ranges.
     *
     * @var string|null
     */
    protected ?string $from;

    /**
     * Optional end date for custom report ranges.
     *
     * @var string|null
     */
    protected ?string $to;

    /**
     * Create a new job instance.
     *
     * @param string $type The type of report (default: daily).
     * @param string|null $from Optional start date for custom reports.
     * @param string|null $to Optional end date for custom reports.
     */
    public function __construct(string $type = 'daily', ?string $from = null, ?string $to = null)
    {
        $this->type = $type;
        $this->from = $from;
        $this->to   = $to;
    }

    /**
     * Execute the main job logic.
     *
     * This method coordinates revenue calculation, caching, verification,
     * report submission, and confirmation dispatch.
     *
     * @throws Throwable
     */
    public function handle(): void
    {
        Log::info('[SendTotalRevenueReportJob] Started', [
            'type' => $this->type,
            'from' => $this->from,
            'to'   => $this->to,
        ]);

        try {
            /** Retrieve or compute total revenue, cached for 15 minutes. */
            $cacheKey = "revenue:{$this->type}:{$this->from}:{$this->to}";

            $totalRevenue = Cache::remember($cacheKey, now()->addMinutes(15), function (): float {
                return $this->calculateRevenue();
            });

            /** Define endpoints and send concurrent requests (verification + reporting). */
            $verifyUrl = config('services.revenue.verify_url', 'https://revenue-verifier.com');
            $reportUrl = config('services.revenue.report_url', 'https://revenue-reporting.com/reports');

            $responses = Http::pool(fn(Pool $pool) => [
                $pool->as('verify')->timeout(8)->post($verifyUrl),
                $pool->as('report')->timeout(8)->post($reportUrl, [
                    'type'          => $this->type,
                    'from'          => $this->from,
                    'to'            => $this->to,
                    'total_revenue' => $totalRevenue,
                ]),
            ]);

            // Decode and validate both responses
            $verify = $responses['verify']->throw()->json();
            $report = $responses['report']->throw()->json();

            /** Log successful submission and queue confirmation job. */
            Log::info('[SendTotalRevenueReportJob] Report submitted successfully.', [
                'type'            => $this->type,
                'total'           => $totalRevenue,
                'verification_id' => $verify['id'] ?? null,
                'report_id'       => $report['id'] ?? null,
            ]);

            // Dispatch a confirmation job with a 10-second delay
            ConfirmRevenueReportJob::dispatch($report['id'] ?? null)->delay(now()->addSeconds(10));
        } catch (Throwable $e) {
            /** Handle any unexpected errors and trigger retry. */
            Log::error('[SendTotalRevenueReportJob] Failed.', [
                'type'  => $this->type,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Allow Laravel to handle retries automatically
        }
    }

    /**
     * Calculate total revenue based on the specified report type.
     *
     * @return float The calculated total revenue amount.
     *
     * @throws \InvalidArgumentException If date range is missing for custom reports.
     */
    private function calculateRevenue(): float
    {
        return match ($this->type) {
            'weekly'  => RevenueManager::calculateWeeklyRevenue(),
            'monthly' => RevenueManager::calculateMonthlyRevenue(),
            'custom'  => $this->from && $this->to
                ? RevenueManager::calculateRevenueByDateRange($this->from, $this->to)
                : throw new \InvalidArgumentException("[SendTotalRevenueReportJob] Missing date range for custom report."),
            default   => RevenueManager::calculateDailyRevenue(),
        };
    }
}
