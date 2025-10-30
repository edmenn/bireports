<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ReportSubscriptionRun;
use App\Services\ReportSubscriptionRunner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessReportSubscriptionRun implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $runId
    ) {
    }

    public function handle(ReportSubscriptionRunner $runner): void
    {
        $runner->handle($this->runId);
    }

    public function failed(Throwable $exception): void
    {
        $run = ReportSubscriptionRun::find($this->runId);
        if (! $run) {
            return;
        }

        $run->forceFill([
            'status' => ReportSubscriptionRun::STATUS_FAILED,
            'error_message' => $exception->getMessage(),
            'finish_time' => now(),
        ])->save();

        Log::error('Report subscription run failed', [
            'run_id' => $this->runId,
            'exception' => $exception,
        ]);
    }
}

