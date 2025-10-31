@extends('adminlte::page')

@section('title', __('subscriptions.index.title'))

@section('css')
    @include('portal.partials.base-styles')
    @include('portal.partials.home-card-styles')
    <style>
        .portal-subscriptions-card .card-header {
            position: relative;
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
            padding: .85rem 1.15rem;
        }

        .portal-table tbody td {
            padding: 1rem 1.15rem;
            border-bottom: 1px solid #eef2f7;
            vertical-align: middle;
            background-color: #ffffff;
        }

        .portal-table tbody tr:hover td {
            background-color: #f8fafd;
        }

        .portal-table tbody tr:last-child td {
            border-bottom: none;
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

        .badge-soft-muted {
            background-color: rgba(108, 117, 125, .12);
            color: #6c757d;
        }

        .portal-action-group {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .portal-action-group form {
            margin: 0 !important;
        }

        .portal-action-btn {
            border-radius: .65rem;
            padding: .45rem .85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            transition: all .2s ease-in-out;
            background-color: #ffffff;
            min-height: 2.2rem;
            margin: 0 !important;
        }

        .portal-action-btn i {
            font-size: .85rem;
        }

        .portal-action-btn:hover,
        .portal-action-btn:focus {
            transform: translateY(-1px);
            box-shadow: 0 .35rem .9rem rgba(15, 34, 58, .12);
        }

        .portal-empty-state i {
            font-size: 2.5rem;
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

            .portal-action-group {
                justify-content: flex-start;
            }
        }
    </style>
@endsection

@section('content_header')
    <x-page-header :title="__('subscriptions.index.title')" icon="fas fa-calendar-alt">
        <div class="header-actions">
            @can('report_subscriptions.manage')
                <button type="button" class="btn btn-primary btn-sm" data-open-subscription>
                    <i class="fas fa-plus-circle"></i> {{ __('subscriptions.actions.create') }}
                </button>
            @endcan
        </div>
        <x-slot name="subtitle">
            <small class="text-muted">{{ __('subscriptions.index.subtitle') }}</small>
        </x-slot>
    </x-page-header>
@stop

@section('content')
    @php
        $subscriptions = $subscriptions ?? collect();
        $isPaginator = $subscriptions instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
        $items = $isPaginator ? $subscriptions->items() : $subscriptions;
        $totalSubscriptions = $isPaginator ? $subscriptions->total() : collect($items)->count();
    @endphp

    <div class="portal-card portal-subscriptions-card mt-3">
        <div class="card-header">
            <span class="portal-icon">
                <i class="fas fa-calendar-check"></i>
            </span>
            <div>
                <h5>{{ __('subscriptions.index.title') }}</h5>
                <small>{{ __('subscriptions.index.subtitle') }}</small>
            </div>
            <div class="ms-auto d-none d-md-inline-flex align-items-center text-muted">
                <i class="fas fa-list-ul mr-2"></i>
                <span>{{ number_format($totalSubscriptions) }}</span>
            </div>
        </div>

        <div class="card-body p-0">
            @if(empty($items) || count($items) === 0)
                <div class="portal-empty-state">
                    <i class="fas fa-envelope-open-text"></i>
                    <h5 class="font-weight-bold mb-2">{{ __('subscriptions.index.empty.title') }}</h5>
                    <p class="mb-4">{{ __('subscriptions.index.empty.hint') }}</p>
                    @can('report_subscriptions.manage')
                        <button type="button" class="btn btn-primary" data-open-subscription>
                            <i class="fas fa-calendar-plus mr-1"></i> {{ __('subscriptions.actions.create') }}
                        </button>
                    @endcan
                </div>
            @else
                <div class="portal-table-wrapper">
                    <div class="table-responsive">
                        <table class="portal-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('subscriptions.table.name') }}</th>
                                    <th>{{ __('subscriptions.table.report') }}</th>
                                    <th>{{ __('subscriptions.table.format') }}</th>
                                    <th>{{ __('subscriptions.table.recurrence') }}</th>
                                    <th>{{ __('subscriptions.table.enabled') }}</th>
                                    <th>{{ __('subscriptions.table.updated_at') }}</th>
                                    <th class="text-right">{{ __('subscriptions.table.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $subscription)
                                    <tr>
                                        <td data-label="{{ __('subscriptions.table.name') }}">
                                            <strong>{{ $subscription->name }}</strong>
                                            <div class="text-muted small">#{{ $subscription->id }}</div>
                                        </td>
                                        <td data-label="{{ __('subscriptions.table.report') }}">
                                            <span class="d-block font-weight-medium">{{ optional($subscription->report)->name ?? __('subscriptions.labels.deleted_report') }}</span>
                                            <span class="text-muted small">{{ $subscription->report_id }}</span>
                                        </td>
                                        <td data-label="{{ __('subscriptions.table.format') }}">
                                            <span class="portal-badge badge-soft-primary text-uppercase">{{ strtoupper($subscription->format) }}</span>
                                        </td>
                                        <td data-label="{{ __('subscriptions.table.recurrence') }}">
                                            <span class="portal-badge badge-soft-secondary">
                                                {{ __('subscriptions.recurrence_labels.' . $subscription->recurrence) }}
                                            </span>
                                        </td>
                                        <td data-label="{{ __('subscriptions.table.enabled') }}">
                                            @if($subscription->is_enabled)
                                                <span class="portal-badge badge-soft-success">{{ __('subscriptions.status.enabled') }}</span>
                                            @else
                                                <span class="portal-badge badge-soft-muted">{{ __('subscriptions.status.disabled') }}</span>
                                            @endif
                                        </td>
                                        <td data-label="{{ __('subscriptions.table.updated_at') }}">
                                            <span class="text-muted">{{ optional($subscription->updated_at)->diffForHumans() }}</span>
                                        </td>
                                        <td data-label="{{ __('subscriptions.table.actions') }}" class="text-right text-nowrap">
                                            <div class="portal-action-group">
                                                <button type="button" class="btn btn-outline-secondary btn-sm portal-action-btn" data-subscription-edit="{{ $subscription->id }}">
                                                    <i class="fas fa-edit"></i> {{ __('subscriptions.actions.edit') }}
                                                </button>
                                                <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-outline-primary btn-sm portal-action-btn">
                                                    <i class="fas fa-eye"></i> {{ __('subscriptions.actions.view') }}
                                                </a>
                                                <form action="{{ route('subscriptions.send', $subscription) }}" method="post">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success btn-sm portal-action-btn">
                                                        <i class="fas fa-paper-plane"></i> {{ __('subscriptions.actions.send_now') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        @if($isPaginator)
            <div class="card-footer bg-white border-top-0 portal-pagination">
                {{ $subscriptions->withQueryString()->links() }}
            </div>
        @endif
    </div>

    @can('report_subscriptions.manage')
        @include('subscriptions.partials.create-modal', [
            'reports' => $reports,
            'subscriptionSnippetOptions' => $subscriptionSnippetOptions ?? [],
        ])
    @endcan
@stop

@include('subscriptions.partials.script', [
    'subscriptionSnippetOptions' => $subscriptionSnippetOptions ?? [],
])









