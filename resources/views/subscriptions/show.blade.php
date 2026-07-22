@extends('adminlte::page')

@section('title', __('subscriptions.show.title', ['name' => $subscription->name]))

@section('css')
    @include('portal.partials.base-styles')
    @include('portal.partials.home-card-styles')
    <style>
        .portal-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .portal-summary-item {
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            padding: 1rem 1.1rem;
            background: #f8fafc;
        }

        .portal-summary-item .summary-label {
            display: block;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #6c7a91;
            margin-bottom: .4rem;
            font-weight: 600;
        }

        .portal-summary-item .summary-value {
            font-weight: 600;
            color: #1f2d3d;
        }

        .portal-summary-item .summary-value .text-muted {
            font-weight: 400;
        }

        .portal-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.5rem;
            padding: .35rem .75rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
        }

        .badge-soft-primary {
            background-color: rgba(13, 110, 253, .12);
            color: #0d6efd;
        }

        .badge-soft-secondary {
            background-color: rgba(73, 80, 87, .12);
            color: #495057;
        }

        .badge-soft-success {
            background-color: rgba(25, 135, 84, .12);
            color: #198754;
        }

        .badge-soft-warning {
            background-color: rgba(255, 193, 7, .18);
            color: #b38300;
        }

        .badge-soft-danger {
            background-color: rgba(220, 53, 69, .15);
            color: #dc3545;
        }

        .badge-soft-muted {
            background-color: rgba(108, 117, 125, .15);
            color: #6c757d;
        }

        .portal-section-heading {
            text-transform: uppercase;
            font-size: .72rem;
            letter-spacing: .08em;
            font-weight: 600;
            color: #6c7a91;
            margin-bottom: .65rem;
        }

        .portal-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
            border: 1px solid #e2e8f0;
            border-radius: .75rem;
            background: #ffffff;
            overflow: hidden;
        }

        .portal-list li {
            display: flex;
            align-items: center;
            gap: .55rem;
            padding: .65rem .85rem;
            border-bottom: 1px solid #eef2f7;
        }

        .portal-list li:last-child {
            border-bottom: none;
        }

        .portal-list li.list-empty {
            justify-content: center;
            color: #6c7a91;
            font-style: italic;
        }

        .portal-email-preview {
            border: 1px solid #e4ebf5;
            border-radius: .75rem;
            background: #fbfdff;
            padding: 1.1rem 1.25rem;
            max-height: 320px;
            overflow: auto;
        }

        .portal-table-wrapper {
            border-radius: .75rem;
            overflow: hidden;
        }

        .portal-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .portal-table thead th {
            text-transform: uppercase;
            font-size: .7rem;
            letter-spacing: .05em;
            font-weight: 600;
            color: #6c757d;
            background: #f3f5f9;
            border-bottom: 1px solid #e2e8f0;
            padding: .85rem 1.1rem;
        }

        .portal-table tbody td {
            padding: 1rem 1.1rem;
            border-bottom: 1px solid #eef2f7;
            background: #ffffff;
            vertical-align: middle;
        }

        .portal-table tbody tr:hover td {
            background-color: #f8fafd;
        }

        .portal-table tbody tr:last-child td {
            border-bottom: none;
        }

        .portal-empty-state {
            text-align: center;
            color: #6b7280;
            padding: 3rem 1rem;
        }

        .portal-empty-state i {
            display: block;
            font-size: 2rem;
            margin-bottom: .5rem;
            opacity: .6;
        }

        .portal-per-page-form {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
        }

        .portal-per-page-form label {
            margin: 0;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6c7a91;
            font-weight: 600;
        }

        .portal-per-page-form select {
            border-radius: .65rem;
            border: 1px solid #d0d7e2;
            padding: .3rem .75rem;
            font-size: .85rem;
            min-height: 2.2rem;
        }

        .portal-pagination {
            display: flex;
            justify-content: center;
        }

        .portal-pagination .pagination {
            margin-bottom: 0;
            gap: .35rem;
        }

        .portal-pagination .page-link {
            border-radius: .65rem;
            border: 1px solid #d0d7e2;
            padding: .35rem .65rem;
            min-width: 2.25rem;
            min-height: 2.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-weight: 500;
            box-shadow: none;
        }

        .portal-pagination .page-item.active .page-link {
            background-color: rgba(13, 110, 253, .12);
            color: #0d6efd;
            border-color: rgba(13, 110, 253, .35);
        }

        .portal-pagination .page-link:hover {
            color: #0d6efd;
            border-color: rgba(13, 110, 253, .35);
        }

        .portal-pagination .page-link:focus {
            box-shadow: none;
        }

        @media (max-width: 768px) {
            .portal-table thead {
                display: none;
            }

            .portal-table tbody tr {
                display: block;
                margin-bottom: 1.25rem;
                border-radius: .75rem;
                box-shadow: 0 .65rem 1.6rem rgba(15, 34, 58, .08);
                overflow: hidden;
            }

            .portal-table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: .85rem 1rem;
                border-bottom: 1px solid #eef2f7;
            }

            .portal-table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #6b7280;
                margin-right: .75rem;
                text-transform: uppercase;
                font-size: .7rem;
                letter-spacing: .05em;
            }
        }

        @media (max-width: 768px) {
            .portal-per-page-form {
                width: 100%;
                margin-top: .75rem;
                justify-content: flex-start;
            }
        }
    </style>
@endsection

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
                <button type="submit" class="btn btn-primary btn-sm">
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
    @php
        $reportName = optional($subscription->report)->name ?? __('subscriptions.labels.deleted_report');
        $reportId = $subscription->report_id;
        $formatLabel = strtoupper($subscription->format);
        $recurrenceLabel = __('subscriptions.recurrence_labels.' . $subscription->recurrence);
        $startWindow = optional($subscription->start_date)->toFormattedDateString();
        $endWindow = $subscription->end_date ? $subscription->end_date->toFormattedDateString() : __('subscriptions.labels.no_end_date');
        $timeLabel = ($subscription->recurrence !== 'cron' && $subscription->hour !== null && $subscription->minute !== null)
            ? sprintf('%02d:%02d', $subscription->hour, $subscription->minute)
            : null;
        $timeCaption = $timeLabel ? __('subscriptions.labels.time_at', ['time' => $timeLabel]) : null;
        $timezoneLabel = $subscription->timezone ?: config('app.timezone');
        $attachmentLabel = $subscription->max_attachment_size_mb
            ? $subscription->max_attachment_size_mb . ' MB'
            : __('subscriptions.labels.no_limit');
        $capacityWarmupLabel = $subscription->ensure_capacity_active
            ? __('subscriptions.labels.ensure_capacity_active_yes')
            : __('subscriptions.labels.ensure_capacity_active_no');
        $weeklyDaysSummary = collect(explode(',', (string) $subscription->weekly_days))
            ->filter()
            ->map(fn ($day) => __('subscriptions.schedule.weekly.day_labels.' . strtolower($day)))
            ->implode(', ');
        $weeklyInterval = max(1, (int) ($subscription->weekly_interval ?? 1));
        $weeklySummary = __('subscriptions.schedule.weekly.summary', [
            'interval' => $weeklyInterval,
            'days' => $weeklyDaysSummary !== '' ? $weeklyDaysSummary : __('subscriptions.schedule.weekly.default_day'),
        ]);
        $monthlySummary = $subscription->monthly_use_last_day
            ? __('subscriptions.schedule.monthly.summary_last')
            : __('subscriptions.schedule.monthly.summary_day', [
                'day' => $subscription->monthly_day ?? optional($subscription->start_date)->day ?? 1,
            ]);
        $statusBadgeClass = $subscription->is_enabled ? 'badge-soft-success' : 'badge-soft-muted';
        $statusLabel = $subscription->is_enabled ? __('subscriptions.status.enabled') : __('subscriptions.status.disabled');
    @endphp

    <div class="portal-card portal-subscription-summary mt-3">
        <div class="card-header">
            <span class="portal-icon"><i class="fas fa-clipboard-list"></i></span>
            <div>
                <h5>{{ __('subscriptions.show.overview') }}</h5>
                <small class="text-muted">{{ __('subscriptions.show.subtitle', ['report' => $reportName]) }}</small>
            </div>
        </div>
        <div class="card-body">
            <div class="portal-summary-grid">
                <div class="portal-summary-item">
                    <span class="summary-label">{{ __('subscriptions.fields.report_id') }}</span>
                    <div class="summary-value">
                        <strong>{{ $reportName }}</strong>
                        <div class="text-muted small">{{ $reportId }}</div>
                    </div>
                </div>
                <div class="portal-summary-item">
                    <span class="summary-label">{{ __('subscriptions.fields.format') }}</span>
                    <div class="summary-value">
                        <span class="portal-badge badge-soft-primary text-uppercase">{{ $formatLabel }}</span>
                    </div>
                </div>
                <div class="portal-summary-item">
                    <span class="summary-label">{{ __('subscriptions.fields.recurrence') }}</span>
                    <div class="summary-value">
                        <span class="portal-badge badge-soft-secondary">{{ $recurrenceLabel }}</span>
                        @if($subscription->recurrence === \App\Models\ReportSubscription::RECURRENCE_WEEKLY)
                            <div class="text-muted small mt-1">{{ $weeklySummary }}</div>
                        @endif
                        @if($subscription->recurrence === \App\Models\ReportSubscription::RECURRENCE_MONTHLY)
                            <div class="text-muted small mt-1">{{ $monthlySummary }}</div>
                        @endif
                        @if($subscription->recurrence === \App\Models\ReportSubscription::RECURRENCE_CRON && $subscription->cron_expression)
                            <div class="text-muted small mt-1">{{ $subscription->cron_expression }}</div>
                        @endif
                    </div>
                </div>
                <div class="portal-summary-item">
                    <span class="summary-label">{{ __('subscriptions.fields.schedule_window') }}</span>
                    <div class="summary-value">
                        {{ $startWindow }} &mdash; {{ $endWindow }}
                        @if($timeCaption)
                            <div class="text-muted small">{{ $timeCaption }} ({{ $timezoneLabel }})</div>
                        @elseif($subscription->timezone)
                            <div class="text-muted small">{{ $timezoneLabel }}</div>
                        @endif
                    </div>
                </div>
                <div class="portal-summary-item">
                    <span class="summary-label">{{ __('subscriptions.fields.max_attachment_size_mb') }}</span>
                    <div class="summary-value">{{ $attachmentLabel }}</div>
                </div>
                <div class="portal-summary-item">
                    <span class="summary-label">{{ __('subscriptions.fields.ensure_capacity_active') }}</span>
                    <div class="summary-value">{{ $capacityWarmupLabel }}</div>
                </div>
                <div class="portal-summary-item">
                    <span class="summary-label">{{ __('subscriptions.fields.is_enabled') }}</span>
                    <div class="summary-value">
                        <span class="portal-badge {{ $statusBadgeClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-6 mb-3 d-flex">
            <div class="portal-card portal-subscription-recipients w-100">
                <div class="card-header">
                    <span class="portal-icon"><i class="fas fa-users"></i></span>
                    <div>
                        <h5>{{ __('subscriptions.sections.recipients') }}</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="portal-section-heading">{{ __('subscriptions.fields.groups') }}</h6>
                            <ul class="portal-list">
                                @forelse($subscription->recipientGroups as $group)
                                    <li>
                                        <i class="fas fa-users text-muted"></i>
                                        <span>{{ $group->name }}</span>
                                    </li>
                                @empty
                                    <li class="list-empty">{{ __('subscriptions.labels.no_groups_selected') }}</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="portal-section-heading">{{ __('subscriptions.fields.users') }}</h6>
                            <ul class="portal-list">
                                @forelse($subscription->recipientUsers as $userRecipient)
                                    <li>
                                        <i class="fas fa-user text-muted"></i>
                                        <div>
                                            <div>{{ $userRecipient->name }}</div>
                                            <div class="text-muted small">{{ $userRecipient->email }}</div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-empty">{{ __('subscriptions.labels.no_users_selected') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3 d-flex">
            <div class="portal-card portal-subscription-notifications w-100">
                <div class="card-header">
                    <span class="portal-icon"><i class="fas fa-bell"></i></span>
                    <div>
                        <h5>{{ __('subscriptions.sections.notifications') }}</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="portal-section-heading mb-2">{{ __('subscriptions.fields.notify_workspace_admins') }}</span>
                        <span class="portal-badge {{ $subscription->notify_workspace_admins ? 'badge-soft-success' : 'badge-soft-muted' }}">
                            {{ $subscription->notify_workspace_admins ? __('subscriptions.labels.notify_workspace_admins_yes') : __('subscriptions.labels.notify_workspace_admins_no') }}
                        </span>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="portal-section-heading">{{ __('subscriptions.fields.notification_users') }}</h6>
                            @php
                                $notificationUsers = $subscription->notificationLinks->where('notifiable_type', \App\Models\ReportSubscriptionNotification::TYPE_USER);
                            @endphp
                            <ul class="portal-list">
                                @forelse($notificationUsers as $link)
                                    <li>
                                        <i class="fas fa-user-shield text-muted"></i>
                                        <div>
                                            <div>{{ optional($link->user)->name }}</div>
                                            <div class="text-muted small">{{ optional($link->user)->email }}</div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-empty">{{ __('subscriptions.labels.no_notification_users') }}</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="portal-section-heading">{{ __('subscriptions.fields.notification_groups') }}</h6>
                            @php
                                $notificationGroups = $subscription->notificationLinks->where('notifiable_type', \App\Models\ReportSubscriptionNotification::TYPE_GROUP);
                            @endphp
                            <ul class="portal-list">
                                @forelse($notificationGroups as $link)
                                    <li>
                                        <i class="fas fa-users-cog text-muted"></i>
                                        <span>{{ optional($link->group)->name }}</span>
                                    </li>
                                @empty
                                    <li class="list-empty">{{ __('subscriptions.labels.no_notification_groups') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="portal-card portal-subscription-email mb-3">
        <div class="card-header">
            <span class="portal-icon"><i class="fas fa-envelope"></i></span>
            <div>
                <h5>{{ __('subscriptions.sections.email') }}</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <span class="portal-section-heading mb-2">{{ __('subscriptions.fields.mail_subject') }}</span>
                <div class="summary-value text-muted">
                    {{ $subscription->mail_subject ?: __('subscriptions.labels.subject_default') }}
                </div>
            </div>
            <div class="portal-email-preview">
                @if($subscription->mail_body)
                    {!! nl2br(e($subscription->mail_body)) !!}
                @else
                    <span class="text-muted">{{ __('subscriptions.labels.body_default') }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="portal-card portal-subscription-runs mb-4">
        @php
            $queryParams = request()->except(['per_page', 'page']);
        @endphp
        <div class="card-header">
            <span class="portal-icon"><i class="fas fa-history"></i></span>
            <div>
                <h5>{{ __('subscriptions.sections.runs') }}</h5>
            </div>
            <form method="GET" class="portal-per-page-form">
                @foreach($queryParams as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $item)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label for="runs-per-page">{{ __('subscriptions.runs.per_page') }}</label>
                <select id="runs-per-page" name="per_page" class="custom-select custom-select-sm" onchange="this.form.submit()">
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        @if(count($runs) === 0)
            <div class="portal-empty-state">
                <i class="fas fa-tasks"></i>
                <span>{{ __('subscriptions.runs.empty') }}</span>
            </div>
        @else
            <div class="portal-table-wrapper">
                <div class="table-responsive">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>{{ __('subscriptions.runs.scheduled_time') }}</th>
                                <th>{{ __('subscriptions.runs.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($runs as $run)
                                @php
                                    $statusBadge = match ($run->status) {
                                        \App\Models\ReportSubscriptionRun::STATUS_SUCCESS => 'badge-soft-success',
                                        \App\Models\ReportSubscriptionRun::STATUS_FAILED => 'badge-soft-danger',
                                        \App\Models\ReportSubscriptionRun::STATUS_RUNNING => 'badge-soft-warning',
                                        default => 'badge-soft-muted',
                                    };
                                @endphp
                                <tr>
                                    <td data-label="{{ __('subscriptions.runs.scheduled_time') }}">
                                        <div>{{ optional($run->scheduled_time)->format('Y-m-d H:i') ?? '-' }}</div>
                                        <div class="text-muted small">{{ optional($run->finish_time)->diffForHumans() }}</div>
                                    </td>
                                    <td data-label="{{ __('subscriptions.runs.status') }}">
                                        <span class="portal-badge {{ $statusBadge }}">
                                            {{ __('subscriptions.statuses.' . $run->status) }}
                                        </span>
                                        @if($run->error_message)
                                            <div class="text-muted small mt-1">{{ $run->error_message }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        

     @if($runs instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="card-footer bg-white border-top-0 portal-pagination">
            <nav aria-label="Paginación de ejecuciones">
                <ul class="pagination justify-content-center mb-0">
                    {{-- Botón Anterior --}}
                    @if ($runs->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link" aria-hidden="true">&laquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $runs->previousPageUrl() }}" rel="prev" aria-label="Anterior">&laquo;</a>
                        </li>
                    @endif

                    {{-- Números de página --}}
                    @foreach ($runs->links()->elements[0] ?? [] as $page => $url)
                        @if ($page == $runs->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach

                    {{-- Botón Siguiente --}}
                    @if ($runs->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $runs->nextPageUrl() }}" rel="next" aria-label="Siguiente">&raquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link" aria-hidden="true">&raquo;</span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    @endif


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
