<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class ConfirmRevenueReportJob
 *
 * Handles asynchronous confirmation of revenue reports to an external service.
 * This job ensures that the confirmation process is retried automatically on failure,
 * improving system resilience and reliability.
 *
 * @package App\Jobs
 */
class ConfirmRevenueReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The maximum number of retry attempts before the job fails permanently.
     *
     * @var int
     */
    public int $tries = 5;

    /**
     * The delay (in seconds) between retry attempts.
     * This exponential backoff strategy helps prevent system overload.
     *
     * @var array<int>
     */
    public array $backoff = [30, 60, 120, 300];

    /**
     * The unique report identifier to confirm.
     *
     * @var string|null
     */
    protected ?string $reportId;

    /**
     * Create a new job instance.
     *
     * @param string|null $reportId The unique ID of the report to confirm.
     */
    public function __construct(?string $reportId)
    {
        $this->reportId = $reportId;
    }

    /**
     * Execute the job logic.
     *
     * This method attempts to confirm the specified revenue report
     * by sending a POST request to the configured external API endpoint.
     *
     * @return void
     */
    public function handle(): void
    {
        // Skip execution if the report ID is not provided
        if (!$this->reportId) {
            Log::warning('[ConfirmRevenueReportJob] Skipped: missing report ID.');
            return;
        }

        try {
            Log::info('[ConfirmRevenueReportJob] Confirming report...', [
                'report_id' => $this->reportId,
            ]);

            // External confirmation endpoint (loaded from config/services.php)
            $endpoint = config('services.revenue.confirm_url', 'https://revenue-reporting.com/reports/confirm');

            // Send HTTP POST request with retries and timeout
            $response = Http::timeout(10)
                ->retry(3, 200)
                ->post($endpoint, [
                    'report_id' => $this->reportId,
                    'timestamp' => now()->timestamp,
                ])
                ->throw()
                ->json();

            Log::info('[ConfirmRevenueReportJob] Confirmation successful.', [
                'report_id' => $this->reportId,
                'response' => $response,
            ]);
        } catch (Throwable $e) {
            // Log the failure and allow Laravel’s queue system to retry automatically
            Log::error('[ConfirmRevenueReportJob] Failed to confirm report.', [
                'report_id' => $this->reportId,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw the exception to trigger job retry
        }
    }
}
