<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Report;
use App\Models\ReportSubscription;
use App\Services\ReportAccessService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class ReportSubscriptionRequest extends FormRequest
{
    protected ?Report $resolvedReport = null;

    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->can('report_subscriptions.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'is_enabled' => ['sometimes', 'boolean'],
            'format' => ['required', Rule::in(['pdf', 'ppt'])],
            'recurrence' => ['required', Rule::in([
                ReportSubscription::RECURRENCE_ONCE,
                ReportSubscription::RECURRENCE_DAILY,
                ReportSubscription::RECURRENCE_WEEKLY,
                ReportSubscription::RECURRENCE_MONTHLY,
                ReportSubscription::RECURRENCE_CRON,
            ])],
            'cron_expression' => ['nullable', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'hour' => ['nullable', 'integer', 'between:0,23'],
            'minute' => ['nullable', 'integer', 'between:0,59'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'report_id' => ['required', 'exists:reports,id'],
            'pages' => ['required', 'array', 'min:1'],
            'pages.*.name' => ['required', 'string', 'max:255'],
            'pages.*.display_name' => ['nullable', 'string', 'max:255'],
            'groups' => ['array'],
            'groups.*' => ['integer', 'exists:groups,id'],
            'users' => ['array'],
            'users.*' => ['integer', 'exists:users,id'],
            'mail_subject' => ['nullable', 'string', 'max:255'],
            'mail_body' => ['nullable', 'string'],
            'max_attachment_size_mb' => ['nullable', 'integer', 'between:1,100'],
            'notify_workspace_admins' => ['sometimes', 'boolean'],
            'ensure_capacity_active' => ['sometimes', 'boolean'],
            'weekly_days' => ['array'],
            'weekly_days.*' => [Rule::in($this->allowedWeekDays())],
            'weekly_interval' => ['nullable', 'integer', 'between:1,6'],
            'monthly_day' => ['nullable', 'integer', 'between:1,31'],
            'monthly_use_last_day' => ['sometimes', 'boolean'],
            'notification_users' => ['array'],
            'notification_users.*' => ['integer', 'exists:users,id'],
            'notification_groups' => ['array'],
            'notification_groups.*' => ['integer', 'exists:groups,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_enabled' => $this->boolean('is_enabled', true),
            'notify_workspace_admins' => $this->boolean('notify_workspace_admins', false),
            'ensure_capacity_active' => $this->boolean('ensure_capacity_active', false),
            'monthly_use_last_day' => $this->boolean('monthly_use_last_day', false),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->passesCronRules()) {
                $validator->errors()->add('cron_expression', __('subscriptions.validation.invalid_cron'));
            }

            if (! $this->passesTimeRules()) {
                $validator->errors()->add('hour', __('subscriptions.validation.hour_required'));
            }

            if (! $this->passesWeeklyRules()) {
                $validator->errors()->add('weekly_days', __('subscriptions.validation.weekly_required'));
            }

            if (! $this->passesMonthlyRules()) {
                $validator->errors()->add('monthly_day', __('subscriptions.validation.monthly_required'));
            }

            if (! $this->hasRecipients()) {
                $validator->errors()->add('groups', __('subscriptions.validation.recipient_required'));
            }

            $report = $this->resolveReport();
            if (! $report) {
                $validator->errors()->add('report_id', __('subscriptions.validation.report_missing'));
                return;
            }

            $user = $this->user();
            if ($user && ! $user->hasRole('admin')) {
                $access = app(ReportAccessService::class);
                if (! $access->userCanAccessReport($user, $report)) {
                    $validator->errors()->add('report_id', __('subscriptions.validation.report_forbidden'));
                }
            }
        });
    }

    protected function passesCronRules(): bool
    {
        if ($this->input('recurrence') !== ReportSubscription::RECURRENCE_CRON) {
            return true;
        }

        $expr = trim((string) $this->input('cron_expression', ''));
        if ($expr === '') {
            return false;
        }

        $parts = preg_split('/\s+/', $expr);
        return count($parts) >= 5 && count($parts) <= 6;
    }

    protected function passesTimeRules(): bool
    {
        $recurrence = $this->input('recurrence');

        if ($recurrence === ReportSubscription::RECURRENCE_CRON) {
            return true;
        }

        if (in_array($recurrence, [
            ReportSubscription::RECURRENCE_ONCE,
            ReportSubscription::RECURRENCE_DAILY,
            ReportSubscription::RECURRENCE_WEEKLY,
            ReportSubscription::RECURRENCE_MONTHLY,
        ], true)) {
            return $this->filled('hour') && $this->filled('minute');
        }

        return true;
    }

    protected function passesWeeklyRules(): bool
    {
        if ($this->input('recurrence') !== ReportSubscription::RECURRENCE_WEEKLY) {
            return true;
        }

        return count($this->normalizedWeeklyDays()) > 0;
    }

    protected function passesMonthlyRules(): bool
    {
        if ($this->input('recurrence') !== ReportSubscription::RECURRENCE_MONTHLY) {
            return true;
        }

        $day = (int) $this->input('monthly_day');
        $useLast = (bool) $this->input('monthly_use_last_day');

        return $useLast || ($day >= 1 && $day <= 31);
    }

    protected function hasRecipients(): bool
    {
        $groups = $this->input('groups', []);
        $users = $this->input('users', []);

        return is_array($groups) && count($groups) > 0
            || is_array($users) && count($users) > 0;
    }

    public function resolveReport(): ?Report
    {
        if ($this->resolvedReport) {
            return $this->resolvedReport;
        }

        $reportId = $this->input('report_id');
        if (! $reportId) {
            return null;
        }

        $this->resolvedReport = Report::find($reportId);
        return $this->resolvedReport;
    }

    /**
     * Data payload for creating/updating the subscription (excluding relations).
     *
     * @return array<string, mixed>
     */
    public function subscriptionAttributes(): array
    {
        $validated = $this->validated();

        $weeklyDays = $this->normalizedWeeklyDays();
        $weeklyDaysString = empty($weeklyDays) ? null : implode(',', $weeklyDays);
        $weeklyInterval = (int) ($validated['weekly_interval'] ?? 1);
        if ($weeklyInterval <= 0) {
            $weeklyInterval = 1;
        }

        $monthlyDay = isset($validated['monthly_day']) ? (int) $validated['monthly_day'] : null;
        $monthlyUseLast = (bool) ($validated['monthly_use_last_day'] ?? false);

        $recurrence = $validated['recurrence'];

        if ($recurrence !== ReportSubscription::RECURRENCE_WEEKLY) {
            $weeklyDaysString = null;
            $weeklyInterval = 1;
        }

        if ($recurrence !== ReportSubscription::RECURRENCE_MONTHLY) {
            $monthlyDay = null;
            $monthlyUseLast = false;
        } elseif (! $monthlyUseLast && $monthlyDay !== null) {
            $monthlyDay = max(1, min(31, $monthlyDay));
        }

        $validated['weekly_days'] = $weeklyDaysString;
        $validated['weekly_interval'] = $weeklyInterval;
        $validated['monthly_day'] = $monthlyDay;
        $validated['monthly_use_last_day'] = $monthlyUseLast;
        $validated['cron_expression'] = $this->generateCronExpression($validated);

        if ($recurrence === ReportSubscription::RECURRENCE_CRON) {
            $validated['hour'] = null;
            $validated['minute'] = null;
        }

        return Arr::only($validated, [
            'name',
            'is_enabled',
            'format',
            'recurrence',
            'weekly_days',
            'weekly_interval',
            'monthly_day',
            'monthly_use_last_day',
            'cron_expression',
            'start_date',
            'end_date',
            'hour',
            'minute',
            'timezone',
            'report_id',
            'mail_subject',
            'mail_body',
            'max_attachment_size_mb',
            'notify_workspace_admins',
            'ensure_capacity_active',
        ]);
    }

    /**
     * @return array<int, array{name:string,display_name:?string}>
     */
    public function selectedPages(): array
    {
        return array_map(
            fn ($row) => [
                'name' => (string) ($row['name'] ?? ''),
                'display_name' => $row['display_name'] ?? null,
            ],
            $this->input('pages', [])
        );
    }

    /**
     * @return array<int, string>
     */
    protected function normalizedWeeklyDays(): array
    {
        $days = $this->input('weekly_days', []);
        if (! is_array($days)) {
            return [];
        }

        $allowed = $this->allowedWeekDays();
        $filtered = [];

        foreach ($days as $day) {
            $normalized = Str::lower(trim((string) $day));
            if (in_array($normalized, $allowed, true)) {
                $filtered[] = $normalized;
            }
        }

        return array_values(array_unique($filtered));
    }

    /**
     * @return array<int, string>
     */
    protected function allowedWeekDays(): array
    {
        return ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function generateCronExpression(array $data): ?string
    {
        $hour = isset($data['hour']) ? (int) $data['hour'] : null;
        $minute = isset($data['minute']) ? (int) $data['minute'] : null;

        if ($hour === null || $minute === null) {
            return null;
        }

        $minute = max(0, min(59, $minute));
        $hour = max(0, min(23, $hour));

        $recurrence = $data['recurrence'] ?? ReportSubscription::RECURRENCE_ONCE;

        return match ($recurrence) {
            ReportSubscription::RECURRENCE_ONCE => null,
            ReportSubscription::RECURRENCE_DAILY => sprintf('%d %d * * *', $minute, $hour),
            ReportSubscription::RECURRENCE_WEEKLY => $this->cronForWeekly($data, $minute, $hour),
            ReportSubscription::RECURRENCE_MONTHLY => $this->cronForMonthly($data, $minute, $hour),
            ReportSubscription::RECURRENCE_CRON => $data['cron_expression'] ?? null,
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function cronForWeekly(array $data, int $minute, int $hour): string
    {
        $days = array_map(static fn (string $day) => strtoupper($day), $this->normalizedWeeklyDays());
        if (empty($days)) {
            $startDate = $this->startDateFromData($data);
            $fallback = $startDate ? strtoupper($startDate->format('D')) : 'MON';
            $days[] = $fallback;
        }

        return sprintf('%d %d * * %s', $minute, $hour, implode(',', $days));
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function cronForMonthly(array $data, int $minute, int $hour): string
    {
        $useLast = ! empty($data['monthly_use_last_day']);
        $day = isset($data['monthly_day']) ? max(1, min(31, (int) $data['monthly_day'])) : null;

        if ($useLast) {
            // Approximate last-day behaviour for reference purposes.
            return sprintf('%d %d 28-31 * *', $minute, $hour);
        }

        $startDate = $this->startDateFromData($data);
        $day ??= (int) ($startDate?->day ?? 1);

        return sprintf('%d %d %d * *', $minute, $hour, $day);
    }
}
