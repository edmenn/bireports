<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\AttachmentTooLargeException;
use App\Mail\SubscriptionDeliveryMail;
use App\Models\Capacity;
use App\Models\Configuration;
use App\Models\Report;
use App\Models\ReportSubscription;
use App\Models\ReportSubscriptionNotification;
use App\Models\ReportSubscriptionRun;
use App\Models\User;
use App\Notifications\SubscriptionFailedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Throwable;

class ReportSubscriptionRunner
{
    /**
     * Cache of report page metadata indexed by "workspace|report" keys.
     *
     * @var array<string, array<int, array{name:string, displayName:?string}>>
     */
    private array $pageMetadataCache = [];

    public function __construct(
        private readonly ReportAccessService $accessService,
        private readonly PowerBiService $powerBiService,
        private readonly ReportAttachmentCompressor $compressor,
        private readonly ReportSubscriptionScheduler $scheduler,
        private readonly PowerBiCapacityService $capacityService,
    ) {
    }

    public function handle(int $runId): void
    {
        $run = ReportSubscriptionRun::query()
            ->with([
                'subscription.report.workspace',
                'subscription.pages',
                'subscription.recipientGroups.users',
                'subscription.recipientUsers',
                'subscription.notificationLinks.user',
                'subscription.notificationLinks.group.users',
            ])
            ->find($runId);

        if (! $run || $run->status !== ReportSubscriptionRun::STATUS_PENDING) {
            return;
        }

        $subscription = $run->subscription;
        if (! $subscription || (method_exists($subscription, 'trashed') && $subscription->trashed())) {
            $run->forceFill([
                'status' => ReportSubscriptionRun::STATUS_SKIPPED,
                'finish_time' => now(),
            ])->save();
            return;
        }

        if (! $subscription->is_enabled) {
            $run->forceFill([
                'status' => ReportSubscriptionRun::STATUS_SKIPPED,
                'finish_time' => now(),
            ])->save();
            return;
        }

        $scheduledMoment = $run->scheduled_time
            ? CarbonImmutable::make($run->scheduled_time)->setTimezone(config('app.timezone'))
            : CarbonImmutable::now(config('app.timezone'));
        $nextReference = $scheduledMoment->addMinute();

        if (! $subscription->inEffectAt($scheduledMoment)) {
            $run->forceFill([
                'status' => ReportSubscriptionRun::STATUS_SKIPPED,
                'finish_time' => now(),
            ])->save();
            $this->scheduler->scheduleNextRun($subscription, $nextReference);
            return;
        }

        $run->forceFill([
            'status' => ReportSubscriptionRun::STATUS_RUNNING,
            'start_time' => now(),
            'recipients_total' => 0,
            'recipients_sent' => 0,
            'recipients_failed' => 0,
            'error_message' => null,
        ])->save();

        $recipients = $this->resolveRecipients($subscription);
        $run->forceFill(['recipients_total' => $recipients->count()])->save();

        if ($recipients->isEmpty()) {
            $message = __('subscriptions.errors.no_recipients');

            $run->forceFill([
                'status' => ReportSubscriptionRun::STATUS_FAILED,
                'finish_time' => now(),
                'error_message' => $message,
            ])->save();

            $this->notifyFailure($subscription, $run, $message);

            return;
        }

        if ($subscription->ensure_capacity_active && $subscription->report) {
            $this->ensureCapacityReady($subscription, $subscription->report);
        }

        $orderedPageNames = $this->orderedPageNames($subscription);
        $errors = [];

        foreach ($recipients as $recipient) {
            try {
                $this->deliverToRecipient($run, $subscription, $recipient, $orderedPageNames);
                $run->increment('recipients_sent');
            } catch (AttachmentTooLargeException $e) {
                $run->increment('recipients_failed');
                $message = __('subscriptions.errors.attachment_too_large') . ' (' . $recipient->email . ')';
                $errors[] = $message;
                $this->notifyFailure($subscription, $run, $message);
            } catch (Throwable $e) {
                $run->increment('recipients_failed');
                $message = sprintf(
                    'Delivery failed for %s (%s): %s',
                    $recipient->name,
                    $recipient->email,
                    $e->getMessage()
                );
                $errors[] = $message;
                Log::error('Report subscription delivery failure', [
                    'subscription_id' => $subscription->id,
                    'run_id' => $run->id,
                    'user_id' => $recipient->id,
                    'exception' => $e,
                ]);
                $this->notifyFailure($subscription, $run, $message);
            }
        }

        $status = empty($errors)
            ? ReportSubscriptionRun::STATUS_SUCCESS
            : ReportSubscriptionRun::STATUS_FAILED;

        $run->forceFill([
            'status' => $status,
            'finish_time' => now(),
            'error_message' => empty($errors) ? null : implode("\n", $errors),
        ])->save();

        if (! empty($errors)) {
            $this->notifyFailure($subscription, $run, implode("\n", $errors));
        }

        $this->scheduler->scheduleNextRun($subscription, $nextReference);
    }

    protected function resolveRecipients(ReportSubscription $subscription): Collection
    {
        $recipients = collect();

        foreach ($subscription->recipientGroups as $group) {
            $recipients = $recipients->merge($group->users);
        }

        $recipients = $recipients->merge($subscription->recipientUsers);

        return $recipients
            ->filter(fn (User $user) => filled($user->email))
            ->unique('id')
            ->values();
    }

    /**
     * @param array<int, string> $pageNames
     */
    protected function deliverToRecipient(ReportSubscriptionRun $run, ReportSubscription $subscription, User $recipient, array $pageNames): void
    {
        $report = $subscription->report;
        if (! $report) {
            throw new \RuntimeException('Report not found for subscription.');
        }

        if (empty($pageNames)) {
            throw new \RuntimeException('No report pages selected.');
        }

        $exportPath = $this->powerBiService->exportReportToFile(
            workspaceId: $report->workspace_id,
            reportId: $report->id,
            pages: $pageNames,
            format: $subscription->format,
            datasetId: $report->dataset_id,
            rlsUser: $recipient->email,
            rlsRoles: []
        );

        $attachmentPath = $exportPath;

        try {
            if ($subscription->max_attachment_size_mb) {
                $attachmentPath = $this->compressor->ensureUnderLimit(
                    $attachmentPath,
                    $subscription->format,
                    (int) $subscription->max_attachment_size_mb
                );
            }

            $this->sendMail($subscription, $run, $recipient, $attachmentPath);
        } finally {
            if (is_file($attachmentPath)) {
                @unlink($attachmentPath);
            }

            if ($attachmentPath !== $exportPath && is_file($exportPath)) {
                @unlink($exportPath);
            }
        }
    }

    protected function ensureCapacityReady(ReportSubscription $subscription, Report $report): void
    {
        $workspace = $report->workspace ?? $report->loadMissing('workspace')->workspace;
        if (! $workspace || blank($workspace->capacity_id)) {
            return;
        }

        $capacity = Capacity::query()
            ->where('capacity_uuid', $workspace->capacity_id)
            ->first();

        if (! $capacity) {
            Log::warning('Report subscription capacity not found', [
                'subscription_id' => $subscription->id,
                'workspace_id' => $workspace->id,
                'capacity_uuid' => $workspace->capacity_id,
            ]);
            return;
        }

        try {
            $isActive = $this->capacityService->isCapacityActive($capacity);
        } catch (Throwable $e) {
            Log::warning('Unable to determine capacity state before subscription run', [
                'subscription_id' => $subscription->id,
                'capacity_uuid' => $capacity->capacity_uuid,
                'error' => $e->getMessage(),
            ]);
            $isActive = null;
        }

        if ($isActive === true) {
            return;
        }

        if ($isActive === null) {
            Log::info('Capacity state undetermined before subscription run, attempting resume', [
                'subscription_id' => $subscription->id,
                'capacity_uuid' => $capacity->capacity_uuid,
            ]);
        }

        try {
            $this->capacityService->resume($capacity);
            Log::info('Capacity resume requested for subscription', [
                'subscription_id' => $subscription->id,
                'capacity_uuid' => $capacity->capacity_uuid,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to resume capacity before subscription run', [
                'subscription_id' => $subscription->id,
                'capacity_uuid' => $capacity->capacity_uuid,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function sendMail(ReportSubscription $subscription, ReportSubscriptionRun $run, User $recipient, string $filePath): void
    {
        $subject = $this->renderTemplate(
            $subscription->mail_subject ?: __('subscriptions.mail.subject.default'),
            $subscription,
            $run
        );

        $body = $this->renderTemplate(
            $subscription->mail_body ?: __('subscriptions.mail.body_default'),
            $subscription,
            $run
        );

        $this->applyMailConfiguration();

        Mail::to($recipient->email)->send(new SubscriptionDeliveryMail(
            subscription: $subscription,
            run: $run,
            recipient: $recipient,
            subjectLine: $subject,
            bodyHtml: $body,
            attachmentPath: $filePath
        ));
    }

    protected function renderTemplate(string $template, ReportSubscription $subscription, ReportSubscriptionRun $run): string
    {
        $reportName = $subscription->report?->name ?: $subscription->name;
        $date = CarbonImmutable::parse($run->scheduled_time)->setTimezone($subscription->timezone ?: config('app.timezone'));

        $replacements = [
            '{{report_name}}' => $reportName,
            '{{date}}' => $date->toFormattedDateString(),
            '{{datetime}}' => $date->toDayDateTimeString(),
            '{{subscription_name}}' => $subscription->name,
        ];

        if ($subscription->report) {
            $replacements['{{report_link}}'] = route('reports.show', [
                $subscription->report->workspace_id,
                $subscription->report_id,
            ]);
        }

        return strtr($template, $replacements);
    }

    protected function applyMailConfiguration(): void
    {
        static $configured = false;

        if ($configured) {
            return;
        }

        $cfg = Configuration::query()->first();
        if (! $cfg) {
            $configured = true;
            return;
        }

        $driver = trim((string) ($cfg->mail_driver ?? ''));
        if ($driver !== '') {
            config(['mail.default' => $driver]);
        }

        $host = trim((string) ($cfg->mail_host ?? ''));
        $isUrlHost = $host !== '' && str_contains($host, '://');
        if ($host !== '' && ! $isUrlHost) {
            config([
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $host,
            ]);
        }

        $port = (int) ($cfg->mail_port ?? 0);
        if ($port > 0) {
            config(['mail.mailers.smtp.port' => $port]);
        }

        $encryption = trim((string) ($cfg->mail_encryption ?? ''));
        if ($encryption !== '') {
            config(['mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption]);
        }

        $username = trim((string) ($cfg->mail_username ?? ''));
        $password = (string) ($cfg->mail_password ?? '');
        if ($username !== '') {
            config(['mail.mailers.smtp.username' => $username]);
        }

        if ($password !== '') {
            config(['mail.mailers.smtp.password' => $password]);
        }

        if ($username !== '') {
            config(['mail.mailers.mailjet.api_key' => $username]);
        }

        if ($password !== '') {
            config(['mail.mailers.mailjet.api_secret' => $password]);
        }

        if ($isUrlHost) {
            config(['mail.mailers.mailjet.endpoint' => rtrim($host, '/')]);
        }

        $fromAddress = trim((string) ($cfg->mail_from_address ?? ''));
        if ($fromAddress !== '') {
            config(['mail.from.address' => $fromAddress]);
        }

        $fromName = trim((string) ($cfg->mail_from_name ?? ''));
        if ($fromName !== '') {
            config(['mail.from.name' => $fromName]);
        }

        $configured = true;
    }

    protected function notifyFailure(ReportSubscription $subscription, ReportSubscriptionRun $run, string $message): void
    {
        $notifiables = $this->resolveNotificationRecipients($subscription);
        if ($notifiables->isEmpty()) {
            return;
        }

        Notification::send(
            $notifiables,
            new SubscriptionFailedNotification($subscription, $run, $message)
        );
    }

    protected function resolveNotificationRecipients(ReportSubscription $subscription): Collection
    {
        $users = collect();

        if ($subscription->notify_workspace_admins) {
            $users = $users->merge($this->workspaceAdministrators($subscription));
        }

        foreach ($subscription->notificationLinks as $link) {
            if ($link->notifiable_type === ReportSubscriptionNotification::TYPE_USER && $link->user) {
                $users->push($link->user);
            }

            if ($link->notifiable_type === ReportSubscriptionNotification::TYPE_GROUP && $link->group) {
                $users = $users->merge($link->group->users);
            }
        }

        return $users
            ->filter(fn (User $user) => filled($user->email))
            ->unique('id')
            ->values();
    }

    /**
     * @return array<int, string>
     */
    protected function orderedPageNames(ReportSubscription $subscription): array
    {
        $pages = $subscription->pages->pluck('page_name')->filter()->values()->all();
        if (empty($pages)) {
            return [];
        }

        $report = $subscription->report;
        if (! $report) {
            return $pages;
        }

        $cacheKey = $report->workspace_id . '|' . $report->id;

        $metadata = $this->reportPageMetadata($subscription, $report);
        $labels = [];

        foreach ($metadata as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $label = $row['displayName'] ?? $name;
            $labels[$name] = (string) $label;
        }

        $indexMap = [];
        foreach ($pages as $idx => $name) {
            $indexMap[$name] = $idx;
        }

        usort($pages, function (string $left, string $right) use ($labels, $indexMap): int {
            $leftLabel = $labels[$left] ?? $left;
            $rightLabel = $labels[$right] ?? $right;

            $comparison = strnatcasecmp($leftLabel, $rightLabel);
            if ($comparison !== 0) {
                return $comparison;
            }

            return ($indexMap[$left] ?? 0) <=> ($indexMap[$right] ?? 0);
        });

        return $pages;
    }

    /**
     * @return array<int, array{name:string, displayName:?string}>
     */
    protected function reportPageMetadata(ReportSubscription $subscription, \App\Models\Report $report): array
    {
        $cacheKey = $report->workspace_id . '|' . $report->id;

        if (array_key_exists($cacheKey, $this->pageMetadataCache)) {
            return $this->pageMetadataCache[$cacheKey];
        }

        try {
            $metadata = $this->powerBiService->listReportPages($report->workspace_id, $report->id);
        } catch (Throwable $e) {
            Log::warning('Unable to fetch report page metadata for ordering', [
                'subscription_id' => $subscription->id,
                'report_id' => $report->id,
                'workspace_id' => $report->workspace_id,
                'error' => $e->getMessage(),
            ]);
            $metadata = [];
        }

        $this->pageMetadataCache[$cacheKey] = $metadata;

        return $metadata;
    }

    protected function workspaceAdministrators(ReportSubscription $subscription): Collection
    {
        if (! $subscription->report) {
            return collect();
        }

        $admins = User::role('admin')->get();

        return $admins->filter(function (User $user) use ($subscription) {
            return $this->accessService->userCanAccessReport($user, $subscription->report);
        });
    }
}
