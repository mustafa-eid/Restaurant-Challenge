<?php

namespace App\Jobs;

use App\Services\RevenueManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Pool;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class SendTotalRevenueReportJob
 *
 * This job is responsible for calculating total revenue (daily, weekly, monthly, or custom),
 * performing asynchronous external API calls to verify and submit a revenue report,
 * and dispatching a confirmation job once the report has been successfully submitted.
 *
 * PERFORMANCE FEATURES:
 * - Uses Http::pool() for concurrent API calls (asynchronous)
 * - Implements caching to avoid duplicate calculations
 * - Includes retry logic with exponential backoff
 * - Dispatches chained confirmation job for modular workflow
 *
 * RELIABILITY FEATURES:
 * - Full logging for debugging and audit trail
 * - Graceful exception handling and queue auto-retry
 * - Configurable retries, delays, and exception limits
 */
class SendTotalRevenueReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int Maximum number of job attempts before failing */
    public int $tries = 10;

    /** @var int Maximum number of exceptions allowed before job is marked as failed */
    public int $maxExceptions = 3;

    /** @var array Exponential backoff delays (in seconds) for retries */
    public array $backoff = [60, 120, 300];

    /** @var string The type of revenue report ('daily', 'weekly', 'monthly', 'custom') */
    protected string $type;

    /** @var string|null The start date (used for 'custom' reports only) */
    protected ?string $from;

    /** @var string|null The end date (used for 'custom' reports only) */
    protected ?string $to;

    /**
     * Constructor.
     *
     * @param string $type Report type (default: 'daily')
     * @param string|null $from Optional start date for 'custom' type
     * @param string|null $to Optional end date for 'custom' type
     */
    public function __construct(string $type = 'daily', ?string $from = null, ?string $to = null)
    {
        $this->type = $type;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Execute the job.
     *
     * Handles the entire workflow:
     * 1. Calculates total revenue (with caching)
     * 2. Sends verification and report API requests concurrently
     * 3. Dispatches a follow-up job to confirm report submission
     *
     * @throws \Throwable If any HTTP request or revenue calculation fails
     */
    public function handle(): void
    {
        Log::info("Revenue Report Job Started", [
            'type' => $this->type,
            'from' => $this->from,
            'to' => $this->to,
        ]);

        try {
            /** STEP 1: Calculate total revenue and cache result for 10 minutes */
            $totalRevenue = cache()->remember(
                "revenue:{$this->type}:{$this->from}:{$this->to}",
                now()->addMinutes(10),
                fn() => $this->calculateRevenue()
            );

            /** STEP 2: Perform asynchronous HTTP requests using Http::pool() */
            $responses = Http::pool(fn(Pool $pool) => [
                // 2.1. Verification request
                $pool->as('verify')->post('https://revenue-verifier.com'),

                // 2.2. Report submission request
                $pool->as('report')->post('https://revenue-reporting.com/reports', [
                    'type' => $this->type,
                    'from' => $this->from,
                    'to' => $this->to,
                    'total_revenue' => $totalRevenue,
                ]),
            ]);

            /** STEP 3: Extract JSON responses and validate results */
            $verificationResponse = $responses['verify']->throw()->json();
            $reportResponse = $responses['report']->throw()->json();

            Log::info("Revenue Report Submitted Successfully", [
                'type' => $this->type,
                'verification_id' => $verificationResponse['id'] ?? null,
                'report_id' => $reportResponse['id'] ?? null,
            ]);

            /** STEP 4: Chain confirmation job with a 10-second delay */
            ConfirmRevenueReportJob::dispatch($reportResponse['id'] ?? null)
                ->delay(now()->addSeconds(10));

        } catch (\Throwable $e) {
            /** Log error details for debugging */
            Log::error("Revenue Report Job Failed", [
                'type' => $this->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Rethrow to trigger queue retry mechanism
            throw $e;
        }
    }

    /**
     * Calculates total revenue based on the selected report type.
     *
     * @return float The computed total revenue
     * @throws \InvalidArgumentException If 'custom' type is missing dates
     */
    private function calculateRevenue(): float
    {
        return match ($this->type) {
            'weekly' => RevenueManager::calculateWeeklyRevenue(),
            'monthly' => RevenueManager::calculateMonthlyRevenue(),
            'custom' => $this->from && $this->to
                ? RevenueManager::calculateRevenueByDateRange($this->from, $this->to)
                : throw new \InvalidArgumentException("Custom report requires both 'from' and 'to' dates."),
            default => RevenueManager::calculateDailyRevenue(),
        };
    }
}
