<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ReportSubscriptionRun;
use App\Services\ReportSubscriptionScheduler;
use App\Jobs\ProcessReportSubscriptionRun;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchReportSubscriptions extends Command
{
    protected $signature = 'subscriptions:dispatch';

    protected $description = 'Dispatch scheduled report subscriptions due for execution.';

    public function handle(ReportSubscriptionScheduler $scheduler): int
    {
        $now = CarbonImmutable::now();
        $due = $scheduler->due($now);

        if ($due->isEmpty()) {
            $this->line('No due subscriptions at ' . $now->toDateTimeString());
            return self::SUCCESS;
        }

        foreach ($due as $item) {
            /** @var \App\Models\ReportSubscription $subscription */
            $subscription = $item['subscription'];
            /** @var \Carbon\CarbonImmutable $scheduledAt */
            $scheduledAt = $item['scheduled_at'];

            DB::transaction(function () use ($subscription, $scheduledAt): void {
                $run = ReportSubscriptionRun::query()->create([
                    'report_subscription_id' => $subscription->id,
                    'scheduled_time' => $scheduledAt->setTimezone('UTC'),
                    'status' => ReportSubscriptionRun::STATUS_PENDING,
                ]);

                ProcessReportSubscriptionRun::dispatch($run->id);
            });

            $this->info(sprintf(
                'Dispatched subscription %s for %s.',
                $subscription->id,
                $scheduledAt->toIso8601String()
            ));
        }

        return self::SUCCESS;
    }
}

