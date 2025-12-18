{{-- resources/views/capacities/audit.blade.php --}}
@extends('adminlte::page')

@section('title', __('capacities.audit.title'))

@section('content_header')
    <x-page-header :title="__('capacities.audit.header', ['name' => $capacity->display_name])" icon="fas fa-history">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <form action="{{ route('capacities.audit.refresh', $capacity) }}" method="POST" onsubmit="return confirm('{{ __('capacities.audit.refresh_confirm') }}');">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-sync-alt"></i> {{ __('common.refresh') }}
                </button>
            </form>
            <a href="{{ route('capacities.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-arrow-left"></i> {{ __('common.back') }}
            </a>
        </div>
        <x-slot name="subtitle">
            <small class="text-muted">
                {{ __('capacities.shared.sku_region', ['sku' => $capacity->sku, 'region' => $capacity->region]) }}
            </small>
        </x-slot>
    </x-page-header>
@stop

@section('content')
    <x-adminlte-card title="{{ __('capacities.audit.title') }}"
                     icon="fas fa-clipboard-list text-lightblue"
                     theme="primary" theme-mode="outline"
                     class="mt-3"
                     body-class="p-0">

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 audit-table">
                <thead class="thead-light">
                    <tr>
                        <th class="text-nowrap">{{ __('capacities.audit.table.date') }}</th>
                        <th>{{ __('capacities.audit.table.action') }}</th>
                        <th>{{ __('capacities.audit.table.result') }}</th>
                        <th>{{ __('capacities.audit.table.source') }}</th>
                        <th>{{ __('capacities.audit.table.detail') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $l)
                        @php
                            $actionKey = strtolower($l->action ?? 'other');
                            $actionLabelKey = 'capacities.audit.actions.' . ($actionKey !== '' ? $actionKey : 'other');
                            $actionLabel = app('translator')->has($actionLabelKey)
                                ? __($actionLabelKey)
                                : ($l->action ?? __('capacities.audit.actions.other'));

                            $resultKey = strtolower($l->result ?? '');
                            $resultLabelKey = 'capacities.audit.results.' . ($resultKey !== '' ? $resultKey : 'unknown');
                            $resultLabel = app('translator')->has($resultLabelKey)
                                ? __($resultLabelKey)
                                : ($l->result ?? __('capacities.audit.results.unknown'));

                            $src = strtolower($l->source ?? 'local');
                            $sourceLabelKey = 'capacities.audit.sources.' . $src;
                            $sourceLabel = app('translator')->has($sourceLabelKey)
                                ? __($sourceLabelKey)
                                : strtoupper($src);
                        @endphp

                        <tr>
                            <td class="text-nowrap text-monospace small text-muted">{{ optional($l->created_at)->format('d-m-Y H:i:s') }}</td>
                            <td class="fw-semibold">{{ $actionLabel }}</td>
                            <td>
                                <span class="badge status-badge
                                    {{ $resultKey==='success' ? 'bg-success' : ($resultKey==='error' ? 'bg-danger' : 'bg-secondary') }}">
                                    {{ $resultLabel }}
                                </span>
                            </td>
                            <td>
                                <span class="badge source-badge
                                    {{ $src==='api' ? 'bg-primary' : ($src==='system' ? 'bg-info' : 'bg-secondary') }}">
                                    {{ $sourceLabel }}
                                </span>
                            </td>
                            <td class="small text-break">{{ $l->message }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-lg mb-2 d-block"></i>
                                {{ __('capacities.audit.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-adminlte-card>
@stop

@push('css')
<style>
/* Header buttons */
.header-actions {
    display: flex;
    gap: .5rem;
    align-items: center;
}
.header-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    font-size: .875rem;
}
.header-actions i {
    margin-right: .35rem;
}

/* Table styling */
.audit-table {
    border-collapse: separate;
    border-spacing: 0;
}
.audit-table thead th {
    font-weight: 600;
    background-color: #f1f5f9;
    color: #334155;
    border-bottom: 2px solid #cbd5e1;
    font-size: .9rem;
}
.audit-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.15s ease-in-out;
}
.audit-table tbody tr:hover {
    background-color: #f9fafb;
}
.audit-table td {
    padding: .6rem .75rem;
    vertical-align: middle;
    font-size: .875rem;
}
.audit-table td:first-child {
    width: 150px;
}
.audit-table td:last-child {
    max-width: 420px;
}

/* Badges */
.status-badge, .source-badge {
    font-size: .75rem;
    padding: .35em .65em;
    border-radius: .4rem;
    font-weight: 600;
}

/* Responsive tweak */
@media (max-width: 768px) {
    .audit-table td:last-child {
        max-width: 260px;
        white-space: normal;
    }
}
</style>
@endpush
