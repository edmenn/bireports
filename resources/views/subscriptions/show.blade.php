@extends('adminlte::page')

@section('title', __('subscriptions.show.title', ['name' => $subscription->name]))

@section('content_header')
    <x-page-header :title="$subscription->name" icon="fas fa-calendar-check">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end">
            <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-secondary btn-sm mr-2">
                <i class="fas fa-arrow-left mr-1"></i> {{ __('subscriptions.actions.back') }}
            </a>
            <button type="button" class="btn btn-primary btn-sm mr-2" data-subscription-edit="{{ $subscription->id }}">
                <i class="fas fa-edit mr-1"></i> {{ __('subscriptions.actions.edit') }}
            </button>
            <form action="{{ route('subscriptions.send', $subscription) }}" method="post" class="d-inline-block mr-2">
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-paper-plane mr-1"></i> {{ __('subscriptions.actions.send_now') }}
                </button>
            </form>
            <form action="{{ route('subscriptions.destroy', $subscription) }}" method="post" class="d-inline-block" onsubmit="return confirm('{{ __('subscriptions.actions.delete_confirm') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-trash mr-1"></i> {{ __('subscriptions.actions.delete') }}
                </button>
            </form>
        </div>
        <x-slot name="subtitle">
            <small class="text-muted">{{ __('subscriptions.show.subtitle', ['report' => optional($subscription->report)->name ?? $subscription->report_id]) }}</small>
        </x-slot>
    </x-page-header>
@stop

@section('content')
    <div class="row">
        <div class="col-xl-6 col-lg-6 mb-4 d-flex">
            <div class="card border-0 shadow-sm w-100 h-100">
                <div class="card-header bg-primary text-white">
                    <strong>{{ __('subscriptions.show.overview') }}</strong>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">{{ __('subscriptions.fields.report_id') }}</dt>
                        <dd class="col-sm-8">
                            <span class="font-weight-semibold">{{ optional($subscription->report)->name ?? __('subscriptions.labels.deleted_report') }}</span>
                            <div class="text-muted small">{{ $subscription->report_id }}</div>
                        </dd>

                        <dt class="col-sm-4">{{ __('subscriptions.fields.format') }}</dt>
                        <dd class="col-sm-8 text-uppercase">{{ strtoupper($subscription->format) }}</dd>

                        <dt class="col-sm-4">{{ __('subscriptions.fields.recurrence') }}</dt>
                        <dd class="col-sm-8">
                            {{ __('subscriptions.recurrence_labels.' . $subscription->recurrence) }}
                            @if($subscription->recurrence === 'cron' && $subscription->cron_expression)
                                <div class="text-muted small">{{ $subscription->cron_expression }}</div>
                            @endif
                        </dd>

                        <dt class="col-sm-4">{{ __('subscriptions.fields.schedule_window') }}</dt>
                        <dd class="col-sm-8">
                            {{ optional($subscription->start_date)->toFormattedDateString() }}
                            &mdash;
                            {{ $subscription->end_date ? $subscription->end_date->toFormattedDateString() : __('subscriptions.labels.no_end_date') }}
                            @if($subscription->recurrence !== 'cron')
                                <div class="text-muted small">
                                    {{ __('subscriptions.labels.time_at', ['time' => sprintf('%02d:%02d', $subscription->hour, $subscription->minute)]) }}
                                    @if($subscription->timezone)
                                        ({{ $subscription->timezone }})
                                    @endif
                                </div>
                            @endif
                        </dd>

                        <dt class="col-sm-4">{{ __('subscriptions.fields.max_attachment_size_mb') }}</dt>
                        <dd class="col-sm-8">
                            {{ $subscription->max_attachment_size_mb ?? __('subscriptions.labels.no_limit') }}
                        </dd>

                        <dt class="col-sm-4">{{ __('subscriptions.fields.is_enabled') }}</dt>
                        <dd class="col-sm-8">
                            @if($subscription->is_enabled)
                                <span class="badge badge-success">{{ __('subscriptions.status.enabled') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ __('subscriptions.status.disabled') }}</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">{{ __('subscriptions.fields.created_by') }}</dt>
                        <dd class="col-sm-8">
                            <span>{{ optional($subscription->creator)->name ?? __('subscriptions.labels.unknown_user') }}</span>
                            <div class="text-muted small">{{ optional($subscription->created_at)->diffForHumans() }}</div>
                        </dd>

                        <dt class="col-sm-4">{{ __('subscriptions.fields.updated_by') }}</dt>
                        <dd class="col-sm-8">
                            <span>{{ optional($subscription->updater)->name ?? __('subscriptions.labels.unknown_user') }}</span>
                            <div class="text-muted small">{{ optional($subscription->updated_at)->diffForHumans() }}</div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6 mb-4 d-flex">
            <div class="card border-0 shadow-sm w-100 h-100">
                <div class="card-header bg-light">
                    <strong>{{ __('subscriptions.sections.pages') }}</strong>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @forelse($subscription->pages as $page)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $page->display_name ?? $page->page_name }}</span>
                                <span class="badge badge-light text-muted">{{ $page->page_name }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">{{ __('subscriptions.labels.no_pages') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <strong>{{ __('subscriptions.sections.recipients') }}</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small">{{ __('subscriptions.fields.groups') }}</h6>
                            <ul class="list-unstyled mb-0">
                                @forelse($subscription->recipientGroups as $group)
                                    <li class="mb-1"><i class="fas fa-users text-muted mr-1"></i> {{ $group->name }}</li>
                                @empty
                                    <li class="text-muted small">{{ __('subscriptions.labels.no_groups_selected') }}</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small">{{ __('subscriptions.fields.users') }}</h6>
                            <ul class="list-unstyled mb-0">
                                @forelse($subscription->recipientUsers as $userRecipient)
                                    <li class="mb-1"><i class="fas fa-user text-muted mr-1"></i> {{ $userRecipient->name }} <span class="text-muted small">&lt;{{ $userRecipient->email }}&gt;</span></li>
                                @empty
                                    <li class="text-muted small">{{ __('subscriptions.labels.no_users_selected') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <strong>{{ __('subscriptions.sections.email') }}</strong>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <span class="font-weight-semibold">{{ __('subscriptions.fields.mail_subject') }}:</span>
                        <span class="text-muted">{{ $subscription->mail_subject ?: __('subscriptions.labels.subject_default') }}</span>
                    </p>
                    <div class="border rounded p-3 bg-light overflow-auto" style="max-height: 320px;">
                        {!! $subscription->mail_body ?: '<span class="text-muted">'.__('subscriptions.labels.body_default').'</span>' !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <strong>{{ __('subscriptions.sections.notifications') }}</strong>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-2">
                        <li>
                            <i class="fas fa-bell text-muted mr-1"></i>
                            @if($subscription->notify_workspace_admins)
                                <span class="badge badge-success">{{ __('subscriptions.labels.notify_workspace_admins_yes') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ __('subscriptions.labels.notify_workspace_admins_no') }}</span>
                            @endif
                        </li>
                    </ul>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small">{{ __('subscriptions.fields.notification_users') }}</h6>
                            <ul class="list-unstyled mb-0">
                                @php
                                    $notificationUsers = $subscription->notificationLinks->where('notifiable_type', \App\Models\ReportSubscriptionNotification::TYPE_USER);
                                @endphp
                                @forelse($notificationUsers as $link)
                                    <li><i class="fas fa-user-shield text-muted mr-1"></i> {{ optional($link->user)->name }}</li>
                                @empty
                                    <li class="text-muted small">{{ __('subscriptions.labels.no_notification_users') }}</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small">{{ __('subscriptions.fields.notification_groups') }}</h6>
                            <ul class="list-unstyled mb-0">
                                @php
                                    $notificationGroups = $subscription->notificationLinks->where('notifiable_type', \App\Models\ReportSubscriptionNotification::TYPE_GROUP);
                                @endphp
                                @forelse($notificationGroups as $link)
                                    <li><i class="fas fa-users-cog text-muted mr-1"></i> {{ optional($link->group)->name }}</li>
                                @empty
                                    <li class="text-muted small">{{ __('subscriptions.labels.no_notification_groups') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <strong>{{ __('subscriptions.sections.runs') }}</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('subscriptions.runs.scheduled_time') }}</th>
                                <th>{{ __('subscriptions.runs.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($runs as $run)
                                <tr>
                                    <td>
                                        <div>{{ optional($run->scheduled_time)->format('Y-m-d H:i') ?? '-' }}</div>
                                        <div class="text-muted small">{{ optional($run->finish_time)->diffForHumans() }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match ($run->status) {
                                                \App\Models\ReportSubscriptionRun::STATUS_SUCCESS => 'badge-success',
                                                \App\Models\ReportSubscriptionRun::STATUS_FAILED => 'badge-danger',
                                                \App\Models\ReportSubscriptionRun::STATUS_RUNNING => 'badge-warning',
                                                default => 'badge-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            {{ __('subscriptions.statuses.' . $run->status) }}
                                        </span>
                                        @if($run->error_message)
                                            <div class="text-muted small mt-1">{{ $run->error_message }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-3">{{ __('subscriptions.runs.empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($runs instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                    <div class="card-footer">
                        {{ $runs->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @can('report_subscriptions.manage')
        @include('subscriptions.partials.create-modal', [
            'reports' => $reports ?? collect(),
            'subscriptionSnippetOptions' => $subscriptionSnippetOptions ?? [],
        ])
    @endcan
@stop

@include('subscriptions.partials.script', [
    'subscriptionSnippetOptions' => $subscriptionSnippetOptions ?? [],
])
