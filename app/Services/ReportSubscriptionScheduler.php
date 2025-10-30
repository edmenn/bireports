<?php

declare(strict_types=1);

namespace App\Services;

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

    protected function resolveScheduledTime(ReportSubscription $subscription, CarbonImmutable $now): ?CarbonImmutable
    {
        $tz = $subscription->timezone ?: config('app.timezone');
        $current = $now->setTimezone($tz)->setSecond(0);

        $startDate = CarbonImmutable::parse($subscription->start_date?->format('Y-m-d') ?? $subscription->start_date, $tz)->startOfDay();
        $endDate = $subscription->end_date
            ? CarbonImmutable::parse($subscription->end_date->format('Y-m-d'), $tz)->endOfDay()
            : null;

        if ($current->lt($startDate)) {
            return null;
        }
        if ($endDate && $current->greaterThan($endDate)) {
            return null;
        }

        return match ($subscription->recurrence) {
            ReportSubscription::RECURRENCE_DAILY => $this->resolveDaily($subscription, $current),
            ReportSubscription::RECURRENCE_WEEKLY => $this->resolveWeekly($subscription, $current, $startDate),
            ReportSubscription::RECURRENCE_MONTHLY => $this->resolveMonthly($subscription, $current, $startDate),
            ReportSubscription::RECURRENCE_CRON => $this->resolveCron($subscription, $current),
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
        $target = $current->setTime((int) $subscription->hour, (int) $subscription->minute, 0);
        if (! $this->isCurrentMinute($current, $target)) {
            return null;
        }

        if ($target->dayOfWeek !== $startDate->dayOfWeek) {
            return null;
        }

        $weeksSinceStart = (int) floor($startDate->startOfDay()->diffInDays($target->startOfDay()) / 7);
        if ($weeksSinceStart < 0) {
            return null;
        }

        return $target;
    }

    protected function resolveMonthly(ReportSubscription $subscription, CarbonImmutable $current, CarbonImmutable $startDate): ?CarbonImmutable
    {
        $target = $current->setTime((int) $subscription->hour, (int) $subscription->minute, 0);
        if (! $this->isCurrentMinute($current, $target)) {
            return null;
        }

        $day = min($startDate->day, $target->endOfMonth()->day);
        if ($target->day !== $day) {
            return null;
        }

        if ($target < $startDate) {
            return null;
        }

        return $target;
    }

    protected function resolveCron(ReportSubscription $subscription, CarbonImmutable $current): ?CarbonImmutable
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

        return $current;
    }

    protected function isCurrentMinute(CarbonInterface $current, CarbonInterface $target): bool
    {
        return $current->format('Y-m-d H:i') === $target->format('Y-m-d H:i');
    }

    protected function alreadyScheduled(ReportSubscription $subscription, CarbonImmutable $scheduledAt): bool
    {
        return ReportSubscriptionRun::query()
            ->where('report_subscription_id', $subscription->id)
            ->where('scheduled_time', $scheduledAt->setTimezone('UTC'))
            ->exists();
    }
}

