<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ReportSubscription;
use App\Models\User;
use App\Services\ReportAccessService;

class ReportSubscriptionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('report_subscriptions.manage');
    }

    public function view(User $user, ReportSubscription $subscription): bool
    {
        if (! $user->can('report_subscriptions.manage')) {
            return false;
        }

        return $this->userCanAccessReport($user, $subscription->report);
    }

    public function create(User $user): bool
    {
        return $user->can('report_subscriptions.manage');
    }

    public function update(User $user, ReportSubscription $subscription): bool
    {
        if (! $user->can('report_subscriptions.manage')) {
            return false;
        }

        return $this->userCanAccessReport($user, $subscription->report);
    }

    public function delete(User $user, ReportSubscription $subscription): bool
    {
        return $user->can('report_subscriptions.manage');
    }

    public function run(User $user, ReportSubscription $subscription): bool
    {
        if (! $user->can('report_subscriptions.manage')) {
            return false;
        }

        return $this->userCanAccessReport($user, $subscription->report);
    }

    protected function userCanAccessReport(User $user, ?\App\Models\Report $report): bool
    {
        if (! $report) {
            return false;
        }

        if ($user->can('reports.edit')) {
            return true;
        }

        if (! $user->can('reports.view')) {
            return false;
        }

        return app(ReportAccessService::class)->userCanAccessReport($user, $report);
    }
}
