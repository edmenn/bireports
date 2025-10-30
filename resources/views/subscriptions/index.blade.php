@extends('adminlte::page')

@section('title', __('subscriptions.index.title'))

@section('content_header')
    <x-page-header :title="__('subscriptions.index.title')" icon="fas fa-calendar-alt">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end">
            @can('report_subscriptions.manage')
                <button type="button" class="btn btn-primary btn-sm" data-open-subscription>
                    <i class="fas fa-plus-circle mr-1"></i> {{ __('subscriptions.actions.create') }}
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
    @endphp

    @if(empty($items) || count($items) === 0)
        <div class="card shadow-sm border-0">
            <div class="card-body py-5 text-center">
                <i class="fas fa-envelope-open-text fa-3x text-muted mb-3"></i>
                <h5 class="font-weight-bold mb-2">{{ __('subscriptions.index.empty.title') }}</h5>
                <p class="text-muted mb-4">{{ __('subscriptions.index.empty.hint') }}</p>
                @can('report_subscriptions.manage')
                    <button type="button" class="btn btn-primary" data-open-subscription>
                        <i class="fas fa-calendar-plus mr-1"></i> {{ __('subscriptions.actions.create') }}
                    </button>
                @endcan
            </div>
        </div>
    @else
        <div class="card shadow-sm border-0">
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-light">
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
                                <td>
                                    <strong>{{ $subscription->name }}</strong>
                                    <div class="text-muted small">#{{ $subscription->id }}</div>
                                </td>
                                <td>
                                    <span class="d-block">{{ optional($subscription->report)->name ?? __('subscriptions.labels.deleted_report') }}</span>
                                    <span class="badge badge-light text-muted">{{ $subscription->report_id }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-info text-uppercase">{{ strtoupper($subscription->format) }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        {{ __('subscriptions.recurrence_labels.' . $subscription->recurrence) }}
                                    </span>
                                </td>
                                <td>
                                        @if($subscription->is_enabled)
                                            <span class="badge badge-success">{{ __('subscriptions.status.enabled') }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ __('subscriptions.status.disabled') }}</span>
                                        @endif
                                </td>
                                <td>{{ optional($subscription->updated_at)->diffForHumans() }}</td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-sm btn-outline-secondary mr-1" data-subscription-edit="{{ $subscription->id }}">
                                        <i class="fas fa-edit"></i> {{ __('subscriptions.actions.edit') }}
                                    </button>
                                    <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> {{ __('subscriptions.actions.view') }}
                                    </a>
                                    <form action="{{ route('subscriptions.send', $subscription) }}" method="post" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-paper-plane"></i> {{ __('subscriptions.actions.send_now') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($isPaginator)
                <div class="card-footer">
                    {{ $subscriptions->withQueryString()->links() }}
                </div>
            @endif
        </div>
    @endif

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
