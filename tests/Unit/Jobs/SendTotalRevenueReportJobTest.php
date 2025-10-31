<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendTotalRevenueReportJob;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SendTotalRevenueReportJobTest extends TestCase
{
    public function test_handle()
    {
        Http::fake([
            '*revenue-verifier.com' => Http::response(['id' => 1]),
            '*revenue-reporting.com/reports' => Http::response(['id' => 2]),
            '*revenue-reporting.com/reports/confirm' => Http::response([]),
        ]);

        $job = new SendTotalRevenueReportJob();
        $job->handle();

        Http::assertSentCount(3);
    }
}
