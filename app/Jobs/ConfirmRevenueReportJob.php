<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class ConfirmRevenueReportJob
 *
 * This job is automatically dispatched by SendTotalRevenueReportJob.
 * It handles confirming the successful receipt of a revenue report
 * with the external reporting API.
 *
 * FEATURES:
 * - Independent queued job (runs asynchronously)
 * - Safe retry mechanism with backoff
 * - Detailed logging for monitoring and troubleshooting
 */
class ConfirmRevenueReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int Maximum retry attempts */
    public int $tries = 5;

    /** @var array Backoff intervals for retries (seconds) */
    public array $backoff = [30, 60, 120];

    /** @var string|null Report ID from previous report submission */
    protected ?string $reportId;

    /**
     * Constructor.
     *
     * @param string|null $reportId The report ID to confirm
     */
    public function __construct(?string $reportId)
    {
        $this->reportId = $reportId;
    }

    /**
     * Handle the confirmation process.
     *
     * @throws \Throwable If the HTTP request fails
     */
    public function handle(): void
    {
        if (!$this->reportId) {
            Log::warning("Revenue Report Confirmation Skipped", [
                'reason' => 'Missing report ID',
            ]);
            return;
        }

        try {
            Log::info("Confirming Revenue Report", [
                'report_id' => $this->reportId,
            ]);

            /** Make POST request to confirm report receipt */
            $response = Http::post('https://revenue-reporting.com/reports/confirm', [
                'report_id' => $this->reportId,
                'timestamp' => now()->timestamp,
            ])->throw()->json();

            Log::info("Revenue Report Confirmation Successful", [
                'report_id' => $this->reportId,
                'response' => $response,
            ]);
        } catch (\Throwable $e) {
            Log::error("Revenue Report Confirmation Failed", [
                'report_id' => $this->reportId,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Trigger retry
        }
    }
}
