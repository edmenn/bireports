<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Report;
use App\Services\ReportAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
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
            'recurrence' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'cron'])],
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
        if ($this->input('recurrence') !== 'cron') {
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
        if ($this->input('recurrence') === 'cron') {
            return true;
        }

        return $this->filled('hour') && $this->filled('minute');
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
        $data = $this->validated();

        if ($data['recurrence'] !== 'cron') {
            $data['cron_expression'] = null;
        }

        if ($data['recurrence'] === 'cron') {
            $data['hour'] = null;
            $data['minute'] = null;
        }

        return Arr::only($data, [
            'name',
            'is_enabled',
            'format',
            'recurrence',
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
}
