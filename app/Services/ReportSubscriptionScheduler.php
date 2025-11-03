<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ProcessReportSubscriptionRun;
use App\Models\ReportSubscription;
use App\Models\ReportSubscriptionRun;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Cron\CronExpression;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportSubscriptionScheduler
{
    /**
     * Resolve subscriptions that should run for the given instant.
     *
     * @return \Illuminate\Support\Collection<int, array{subscription: ReportSubscription, scheduled_at: CarbonImmutable}>
     */
    public function due(CarbonImmutable $now): Collection
    {
        $subscriptions = ReportSubscription::query()
            ->enabled()
            ->whereDate('start_date', '<=', $now->toDateString())
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $now->toDateString());
            })
            ->get();

        $due = collect();

        foreach ($subscriptions as $subscription) {
            $scheduledAt = $this->resolveScheduledTime($subscription, $now);
            if (! $scheduledAt) {
                continue;
            }

            if ($this->alreadyScheduled($subscription, $scheduledAt)) {
                continue;
            }

            $due->push([
                'subscription' => $subscription,
                'scheduled_at' => $scheduledAt,
            ]);
        }

        return $due;
    }

    public function reschedule(ReportSubscription $subscription, ?CarbonImmutable $reference = null): ?ReportSubscriptionRun
    {
        $reference ??= CarbonImmutable::now(config('app.timezone'));

        $this->cancelFutureRuns($subscription, $reference);

        if (! $subscription->is_enabled) {
            return null;
        }

        return $this->scheduleNextRun($subscription, $reference);
    }

    public function scheduleNextRun(ReportSubscription $subscription, ?CarbonImmutable $reference = null): ?ReportSubscriptionRun
    {
        $reference ??= CarbonImmutable::now(config('app.timezone'));

        if (! $subscription->is_enabled) {
            return null;
        }

        $next = $this->nextScheduledTime($subscription, $reference);
        if (! $next) {
            return null;
        }

        $appTimezone = config('app.timezone');
        $scheduledLocal = $next->setTimezone($appTimezone);
        $run = null;

        DB::transaction(function () use ($subscription, $scheduledLocal, $next, &$run): void {
            $existing = ReportSubscriptionRun::query()
                ->where('report_subscription_id', $subscription->id)
                ->where('scheduled_time', $scheduledLocal)
                ->lockForUpdate()
                ->first();

            $alreadyPending = $existing?->status === ReportSubscriptionRun::STATUS_PENDING;

            $run = ReportSubscriptionRun::query()->updateOrCreate(
                [
                    'report_subscription_id' => $subscription->id,
                    'scheduled_time' => $scheduledLocal,
                ],
                [
                    'status' => ReportSubscriptionRun::STATUS_PENDING,
                    'start_time' => null,
                    'finish_time' => null,
                    'error_message' => null,
                    'recipients_total' => 0,
                    'recipients_sent' => 0,
                    'recipients_failed' => 0,
                ]
            );

            if (! $alreadyPending) {
                ProcessReportSubscriptionRun::dispatch($run->id)
                    ->delay($scheduledLocal)
                    ->afterCommit();
            }
        });

        if ($run && $run->wasRecentlyCreated) {
            $this->pruneRuns($subscription);
        }

        return $run;
    }

    protected function resolveScheduledTime(ReportSubscription $subscription, CarbonImmutable $now): ?CarbonImmutable
    {
        [$current, $startDate, $endDate] = $this->buildContext($subscription, $now);

        if ($current->lt($startDate)) {
            return null;
        }
        if ($endDate && $current->greaterThan($endDate)) {
            return null;
        }

        return match ($subscription->recurrence) {
            ReportSubscription::RECURRENCE_ONCE => $this->resolveOneTime($subscription, $current, $startDate),
            ReportSubscription::RECURRENCE_DAILY => $this->resolveDaily($subscription, $current),
            ReportSubscription::RECURRENCE_WEEKLY => $this->resolveWeekly($subscription, $current, $startDate),
            ReportSubscription::RECURRENCE_MONTHLY => $this->resolveMonthly($subscription, $current, $startDate),
            ReportSubscription::RECURRENCE_CRON => $this->resolveCron($subscription, $current, $startDate, $endDate),
            default => null,
        };
    }

    protected function resolveDaily(ReportSubscription $subscription, CarbonImmutable $current): ?CarbonImmutable
    {
        $target = $current->setTime((int) $subscription->hour, (int) $subscription->minute, 0);
        return $this->isCurrentMinute($current, $target) ? $target : null;
    }

    protected function resolveWeekly(ReportSubscription $subscription, CarbonImmutable $current, CarbonImmutable $startDate): ?CarbonImmutable
    {
        if ($subscription->hour === null || $subscription->minute === null) {
            return null;
        }

        $candidate = $current->setTime((int) $subscription->hour, (int) $subscription->minute, 0);
        if (! $this->isCurrentMinute($current, $candidate)) {
            return null;
        }

        if ($candidate->lt($startDate)) {
            return null;
        }

        $days = $this->weeklyDays($subscription);
        if (! in_array($candidate->dayOfWeek, $days, true)) {
            return null;
        }

        $interval = max(1, (int) ($subscription->weekly_interval ?? 1));
        $anchorWeek = $startDate->startOfWeek(CarbonInterface::MONDAY);
        $candidateWeek = $candidate->startOfWeek(CarbonInterface::MONDAY);

        if ($candidateWeek->lt($anchorWeek)) {
            return null;
        }

        $weeksDiff = $anchorWeek->diffInWeeks($candidateWeek);
        if ($weeksDiff % $interval !== 0) {
            return null;
        }

        return $candidate;
    }

    protected function resolveMonthly(ReportSubscription $subscription, CarbonImmutable $current, CarbonImmutable $startDate): ?CarbonImmutable
    {
        if ($subscription->hour === null || $subscription->minute === null) {
            return null;
        }

        $candidate = $this->monthlyCandidateFor($subscription, $current, (int) $subscription->hour, (int) $subscription->minute);
        if (! $this->isCurrentMinute($current, $candidate)) {
            return null;
        }

        if ($candidate->lt($startDate)) {
            return null;
        }

        return $candidate;
    }

    protected function resolveCron(ReportSubscription $subscription, CarbonImmutable $current, CarbonImmutable $startDate, ?CarbonImmutable $endDate): ?CarbonImmutable
    {
        $expression = trim((string) $subscription->cron_expression);
        if ($expression === '') {
            return null;
        }

        try {
            $cron = new CronExpression($expression);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $cron->isDue($current)) {
            return null;
        }

        if ($current->lt($startDate)) {
            return null;
        }

        if ($endDate && $current->greaterThan($endDate)) {
            return null;
        }

        return $current;
    }

    protected function resolveOneTime(ReportSubscription $subscription, CarbonImmutable $current, CarbonImmutable $startDate): ?CarbonImmutable
    {
        if ($subscription->hour === null || $subscription->minute === null) {
            return null;
        }

        $target = $startDate->setTime((int) $subscription->hour, (int) $subscription->minute, 0);
        return $this->isCurrentMinute($current, $target) ? $target : null;
    }

    protected function isCurrentMinute(CarbonInterface $current, CarbonInterface $target): bool
    {
        return $current->format('Y-m-d H:i') === $target->format('Y-m-d H:i');
    }

    protected function alreadyScheduled(ReportSubscription $subscription, CarbonImmutable $scheduledAt): bool
    {
        $appTimezone = config('app.timezone');

        $localTime = $scheduledAt->setTimezone($appTimezone);
        $legacyUtc = $scheduledAt->setTimezone('UTC');

        return ReportSubscriptionRun::query()
            ->where('report_subscription_id', $subscription->id)
            ->whereIn('scheduled_time', [$localTime, $legacyUtc])
            ->exists();
    }

    protected function nextScheduledTime(ReportSubscription $subscription, CarbonImmutable $reference): ?CarbonImmutable
    {
        [$current, $startDate, $endDate] = $this->buildContext($subscription, $reference);

        if ($current->lt($startDate)) {
            $current = $startDate;
        }

        return match ($subscription->recurrence) {
            ReportSubscription::RECURRENCE_ONCE => $this->nextOneTime($subscription, $current, $startDate, $endDate),
            ReportSubscription::RECURRENCE_DAILY => $this->nextDaily($subscription, $current, $startDate, $endDate),
            ReportSubscription::RECURRENCE_WEEKLY => $this->nextWeekly($subscription, $current, $startDate, $endDate),
            ReportSubscription::RECURRENCE_MONTHLY => $this->nextMonthly($subscription, $current, $startDate, $endDate),
            ReportSubscription::RECURRENCE_CRON => $this->nextCron($subscription, $current, $startDate, $endDate),
            default => null,
        };
    }

    protected function nextDaily(ReportSubscription $subscription, CarbonImmutable $current, CarbonImmutable $startDate, ?CarbonImmutable $endDate): ?CarbonImmutable
    {
        if ($subscription->hour === null || $subscription->minute === null) {
            return null;
        }

        $target = $current->setTime((int) $subscription->hour, (int) $subscription->minute, 0);

        if ($target->lt($current)) {
            $target = $target->addDay();
        }

        $startTarget = $startDate->setTime((int) $subscription->hour, (int) $subscription->minute, 0);
        if ($target->lt($startTarget)) {
            $target = $startTarget;
        }

        if ($endDate && $target->greaterThan($endDate)) {
            return null;
        }

        return $target;
    }

    protected function nextWeekly(ReportSubscription $subscription, CarbonImmutable $current, CarbonImmutable $startDate, ?CarbonImmutable $endDate): ?CarbonImmutable
    {
        if ($subscription->hour === null || $subscription->minute === null) {
            return null;
        }

        $interval = max(1, (int) ($subscription->weekly_interval ?? 1));
        $days = $this->weeklyDays($subscription);

        $hour = (int) $subscription->hour;
        $minute = (int) $subscription->minute;

        $anchorWeek = $startDate->startOfWeek(CarbonInterface::MONDAY);
        $weekCursor = $current->startOfWeek(CarbonInterface::MONDAY);
        if ($weekCursor->lt($anchorWeek)) {
            $weekCursor = $anchorWeek;
        }

        for ($iterations = 0; $iterations < 104; $iterations++) {
            $weeksDiff = $anchorWeek->diffInWeeks($weekCursor);
            $matchesInterval = ($weeksDiff % $interval) === 0;

            if ($matchesInterval) {
                foreach ($days as $dayOfWeek) {
                    $offset = ($dayOfWeek - $weekCursor->dayOfWeek + 7) % 7;
                    $candidate = $weekCursor->addDays($offset)->setTime($hour, $minute, 0);

                    if ($candidate->lt($startDate)) {
                        continue;
                    }

                    if ($candidate->lt($current)) {
                        continue;
                    }

                    if ($endDate && $candidate->greaterThan($endDate)) {
                        return null;
                    }

                    return $candidate;
                }
            }

            $weekCursor = $weekCursor->addWeek();
        }

        return null;
    }

    protected function nextMonthly(ReportSubscription $subscription, CarbonImmutable $current, CarbonImmutable $startDate, ?CarbonImmutable $endDate): ?CarbonImmutable
    {
        if ($subscription->hour === null || $subscription->minute === null) {
            return null;
        }

        $hour = (int) $subscription->hour;
        $minute = (int) $subscription->minute;

        $cursor = $startDate;
        if ($cursor->lt($current)) {
            $cursor = $current;
        }

        $cursor = $cursor->startOfMonth();

        for ($iterations = 0; $iterations < 60; $iterations++) {
            $candidate = $this->monthlyCandidateFor($subscription, $cursor, $hour, $minute);

            if ($candidate->lt($startDate)) {
                $cursor = $cursor->addMonth();
                continue;
            }

            if ($candidate->lt($current)) {
                $cursor = $cursor->addMonth();
                continue;
            }

            if ($endDate && $candidate->greaterThan($endDate)) {
                return null;
            }

            return $candidate;
        }

        return null;
    }

    protected function nextCron(ReportSubscription $subscription, CarbonImmutable $current, CarbonImmutable $startDate, ?CarbonImmutable $endDate): ?CarbonImmutable
    {
        $expression = trim((string) $subscription->cron_expression);
        if ($expression === '') {
            return null;
        }

        try {
            $cron = new CronExpression($expression);
        } catch (\Throwable $e) {
            return null;
        }

        if ($current->lt($startDate)) {
            $current = $startDate;
        }

        $next = CarbonImmutable::createFromInterface(
            $cron->getNextRunDate($current, 0, true, $current->getTimezone()->getName())
        );

        if ($next->lt($startDate)) {
            $next = CarbonImmutable::createFromInterface(
                $cron->getNextRunDate($startDate, 0, true, $current->getTimezone()->getName())
            );
        }

        if ($endDate && $next->greaterThan($endDate)) {
            return null;
        }

        return $next;
    }

    protected function nextOneTime(ReportSubscription $subscription, CarbonImmutable $current, CarbonImmutable $startDate, ?CarbonImmutable $endDate): ?CarbonImmutable
    {
        if ($subscription->hour === null || $subscription->minute === null) {
            return null;
        }

        $target = $startDate->setTime((int) $subscription->hour, (int) $subscription->minute, 0);

        if ($current->gt($target)) {
            return null;
        }

        if ($endDate && $target->greaterThan($endDate)) {
            return null;
        }

        return $target;
    }

    protected function cancelFutureRuns(ReportSubscription $subscription, CarbonImmutable $reference): void
    {
        $appTimezone = config('app.timezone');
        $cutoff = $reference->setTimezone($appTimezone);

        ReportSubscriptionRun::query()
            ->where('report_subscription_id', $subscription->id)
            ->where('status', ReportSubscriptionRun::STATUS_PENDING)
            ->where('scheduled_time', '>=', $cutoff)
            ->update([
                'status' => ReportSubscriptionRun::STATUS_SKIPPED,
                'finish_time' => now(),
                'error_message' => null,
            ]);

        $legacyCutoff = $reference->setTimezone('UTC');

        ReportSubscriptionRun::query()
            ->where('report_subscription_id', $subscription->id)
            ->where('status', ReportSubscriptionRun::STATUS_PENDING)
            ->where('scheduled_time', '>=', $legacyCutoff)
            ->update([
                'status' => ReportSubscriptionRun::STATUS_SKIPPED,
                'finish_time' => now(),
                'error_message' => null,
            ]);
    }

    public function cancelPendingRuns(ReportSubscription $subscription, ?CarbonImmutable $reference = null): void
    {
        $reference ??= CarbonImmutable::now(config('app.timezone'));
        $this->cancelFutureRuns($subscription, $reference);
    }

    public function pruneRuns(ReportSubscription $subscription): void
    {
        $ids = ReportSubscriptionRun::query()
            ->where('report_subscription_id', $subscription->id)
            ->orderByDesc('scheduled_time')
            ->orderByDesc('id')
            ->skip(100)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        ReportSubscriptionRun::query()
            ->whereIn('id', $ids)
            ->forceDelete();
    }

    /**
     * @return array<int, int>
     */
    protected function weeklyDays(ReportSubscription $subscription): array
    {
        $map = [
            'mon' => CarbonInterface::MONDAY,
            'tue' => CarbonInterface::TUESDAY,
            'wed' => CarbonInterface::WEDNESDAY,
            'thu' => CarbonInterface::THURSDAY,
            'fri' => CarbonInterface::FRIDAY,
            'sat' => CarbonInterface::SATURDAY,
            'sun' => CarbonInterface::SUNDAY,
        ];

        $raw = $subscription->weekly_days;
        $days = [];

        if (is_string($raw) && trim($raw) !== '') {
            foreach (explode(',', $raw) as $value) {
                $key = strtolower(trim($value));
                if (isset($map[$key])) {
                    $days[] = $map[$key];
                }
            }
        }

        if (empty($days) && $subscription->start_date) {
            $days[] = $subscription->start_date->dayOfWeek ?? CarbonInterface::MONDAY;
        }

        if (empty($days)) {
            $days[] = CarbonInterface::MONDAY;
        }

        $days = array_values(array_unique($days));
        sort($days);

        return $days;
    }

    protected function monthlyCandidateFor(ReportSubscription $subscription, CarbonImmutable $reference, int $hour, int $minute): CarbonImmutable
    {
        $monthStart = $reference->startOfMonth();

        if ($subscription->monthly_use_last_day) {
            return $monthStart->endOfMonth()->setTime($hour, $minute, 0);
        }

        $day = $subscription->monthly_day;
        if (! is_int($day) || $day < 1 || $day > 31) {
            $day = $subscription->start_date?->day ?? 1;
        }

        $day = max(1, min(31, (int) $day));
        $day = min($day, $monthStart->endOfMonth()->day);

        return $monthStart->setDay($day)->setTime($hour, $minute, 0);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: ?CarbonImmutable}
     */
    protected function buildContext(ReportSubscription $subscription, CarbonImmutable $point): array
    {
        $tz = $subscription->timezone ?: config('app.timezone');
        $current = $point->setTimezone($tz)->setSecond(0);

        $startDate = CarbonImmutable::parse(
            $subscription->start_date?->format('Y-m-d') ?? (string) $subscription->start_date,
            $tz
        )->startOfDay();

        $endDate = $subscription->end_date
            ? CarbonImmutable::parse($subscription->end_date->format('Y-m-d'), $tz)->endOfDay()
            : null;

        return [$current, $startDate, $endDate];
    }
}

