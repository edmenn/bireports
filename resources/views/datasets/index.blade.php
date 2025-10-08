@extends('adminlte::page')

@section('title', __('datasets.page.title'))

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">{{ __('datasets.page.title') }}</h1>
            <small class="text-muted">{{ __('datasets.page.subtitle') }}</small>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <a href="{{ route('datasets.schedules.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-clock"></i> {{ __('datasets.actions.view_all_schedules') }}
            </a>
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary">
                <i class="fas fa-sync"></i> {{ __('datasets.actions.reload_list') }}
            </a>
            <a href="{{ route('powerbi.import.datasets') }}" class="btn btn-outline-secondary">
                <i class="fas fa-cloud-download-alt"></i> {{ __('datasets.actions.import_metadata') }}
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('status'))
        <x-adminlte-alert theme="success" title="{{ __('datasets.alerts.success') }}">{{ session('status') }}</x-adminlte-alert>
    @endif
    @if(session('error'))
        <x-adminlte-alert theme="danger" title="{{ __('datasets.alerts.error') }}">{{ session('error') }}</x-adminlte-alert>
    @endif

    <div class="mb-2 small text-muted">{{ __('datasets.index.tip_reload_metadata') }}</div>

    @php $total = method_exists($datasets ?? null, 'count') ? $datasets->count() : (is_countable($datasets ?? []) ? count($datasets) : 0); @endphp

    @if($total === 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-inbox fa-3x text-muted"></i></div>
                <h5 class="mb-2">{{ __('datasets.index.empty.title') }}</h5>
                <p class="text-muted mb-0">{{ __('datasets.index.empty.hint') }}</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body table-responsive datasets-table-wrap p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('datasets.index.table.name') }}</th>
                            <th>{{ __('datasets.index.table.workspace') }}</th>
                            <th class="text-nowrap">{{ __('datasets.index.table.dataset_id') }}</th>
                            <th class="text-end">{{ __('datasets.index.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($datasets as $d)
                            @php
                                $wsId   = $d->workspace_id;
                                $dsId   = $d->id;
                                $webView = 'https://app.powerbi.com/groups/'.$wsId.'/datasets/'.$dsId.'/details?experience=power-bi';
                                $webSettings = 'https://app.powerbi.com/groups/'.$wsId.'/settings/datasets/'.$dsId.'?experience=power-bi';
                                $webLineage = 'https://app.powerbi.com/groups/'.$wsId.'/lineage?experience=power-bi';
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $d->name }}</td>
                                <td>{{ $d->workspace?->name ?: $d->workspace_id }}</td>
                                <td class="text-monospace small">{{ $d->id }}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" data-toggle="dropdown" aria-expanded="false">
                                            {{ __('datasets.index.table.actions') }}
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-right">
                                            <li>
                                                <form action="{{ route('datasets.refresh', [$wsId, $dsId]) }}" method="POST" onsubmit="return confirm('{{ __('datasets.index.confirm_refresh') }}')">
                                                    @csrf
                                                    <button class="dropdown-item" type="submit"><i class="fas fa-sync me-2"></i>{{ __('datasets.actions.refresh_now') }}</button>
                                                </form>
                                            </li>
                                            <li><a class="dropdown-item" href="{{ route('datasets.history', [$wsId, $dsId]) }}"><i class="fas fa-history me-2"></i>{{ __('datasets.actions.history') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ route('datasets.schedule', [$wsId, $dsId]) }}"><i class="fas fa-clock me-2"></i>{{ __('datasets.actions.schedule') }}</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('datasets.metadata.refresh', [$wsId, $dsId]) }}" method="POST">
                                                    @csrf
                                                    <button class="dropdown-item" type="submit"><i class="fas fa-database me-2"></i>{{ __('datasets.actions.reload_metadata') }}</button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="{{ $webSettings }}" target="_blank" rel="noopener"><i class="fas fa-tools me-2"></i>{{ __('datasets.actions.manage_in_pbi') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ $webView }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt me-2"></i>{{ __('datasets.actions.view_in_pbi') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ $webView }}" target="_blank" rel="noopener"><i class="fas fa-edit me-2"></i>{{ __('datasets.actions.edit_in_pbi') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ $webLineage }}" target="_blank" rel="noopener"><i class="fas fa-project-diagram me-2"></i>{{ __('datasets.actions.view_lineage') }}</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($datasets,'links'))
                <div class="card-footer">
                    {{ $datasets->links() }}
                </div>
            @endif
        </div>
    @endif
@stop

@push('css')
<style>
    /* Evitar que el dropdown quede oculto por overflow en tablas responsivas */
    .datasets-table-wrap { overflow: visible !important; }
    .text-monospace { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    .fw-semibold { font-weight: 600; }
</style>
@endpush
