@extends('layouts.app')

@section('title', __('datasets.page.title'))

{{-- =========================
      HEADER
     ========================= --}}
@section('content_header')
    <x-page-header :title="__('datasets.page.title')" :subtitle="__('datasets.page.subtitle')" icon="fas fa-database">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('datasets.schedules.index') }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-clock"></i>
                <span>{{ __('datasets.actions.view_all_schedules') }}</span>
            </a>
            <a href="{{ url()->current() }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-sync"></i>
                <span>{{ __('datasets.actions.reload_list') }}</span>
            </a>
            <a href="{{ route('powerbi.import.datasets') }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-cloud-download-alt"></i>
                <span>{{ __('datasets.actions.import_metadata') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

{{-- =========================
      CONTENT
     ========================= --}}
@section('content')
    {{-- Flash alerts --}}
    @if(session('status'))
        <x-ui-alert theme="success" icon="fas fa-check-circle">
            {{ session('status') }}
        </x-ui-alert>
    @endif
    @if(session('error'))
        <x-ui-alert theme="danger" icon="fas fa-times-circle">
            {{ session('error') }}
        </x-ui-alert>
    @endif

    <p class="text-muted small mb-3">{{ __('datasets.index.tip_reload_metadata') }}</p>

    @php
        $total = method_exists($datasets ?? null, 'count') ? $datasets->count() : (is_countable($datasets ?? []) ? count($datasets) : 0);
    @endphp

    @if($total === 0)
        {{-- Empty state --}}
        <x-ui-card theme="light" class="text-center py-5">
            <div class="mb-3"><i class="fas fa-inbox fa-3x text-muted"></i></div>
            <h5 class="fw-semibold mb-2">{{ __('datasets.index.empty.title') }}</h5>
            <p class="text-muted mb-0">{{ __('datasets.index.empty.hint') }}</p>
        </x-ui-card>
    @else
        {{-- Dataset list --}}
        <x-ui-card theme="primary" theme-mode="outline" class="shadow-sm datasets-card-wrap">
            
            {{-- 
               SOLUCIÓN: 'datasets-table-wrap' controlará el overflow mediante CSS
            --}}
            <div class="table-responsive datasets-table-wrap">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
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
                                $wsId = $d->workspace_id;
                                $dsId = $d->id;
                                $webView = "https://app.powerbi.com/groups/$wsId/datasets/$dsId/details?experience=power-bi";
                                $webSettings = "https://app.powerbi.com/groups/$wsId/settings/datasets/$dsId?experience=power-bi";
                                $webLineage = "https://app.powerbi.com/groups/$wsId/lineage?experience=power-bi";
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $d->name }}</td>
                                <td>{{ $d->workspace?->name ?: $d->workspace_id }}</td>
                                <td class="text-monospace small">{{ $d->id }}</td>
                                <td class="text-end">
                                    {{-- Agregamos position-static para ayudar al posicionamiento relativo --}}
                                    <div class="dropdown position-static">
                                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                                id="dd-{{ $dsId }}"
                                                data-toggle="dropdown"
                                                aria-haspopup="true"
                                                aria-expanded="false"
                                                {{-- Usamos boundary window como respaldo --}}
                                                data-boundary="window">
                                            {{ __('datasets.index.table.actions') }}
                                        </button>
                                        
                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dd-{{ $dsId }}">
                                            {{-- Refresh --}}
                                            <li>
                                                <form action="{{ route('datasets.refresh', [$wsId, $dsId]) }}" method="POST"
                                                      onsubmit="return confirm('{{ __('datasets.index.confirm_refresh') }}')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-sync me-2"></i>{{ __('datasets.actions.refresh_now') }}
                                                    </button>
                                                </form>
                                            </li>

                                            {{-- History / Schedule --}}
                                            <li><a class="dropdown-item" href="{{ route('datasets.history', [$wsId, $dsId]) }}"><i class="fas fa-history me-2"></i>{{ __('datasets.actions.history') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ route('datasets.schedule', [$wsId, $dsId]) }}"><i class="fas fa-clock me-2"></i>{{ __('datasets.actions.schedule') }}</a></li>

                                            <li><hr class="dropdown-divider"></li>

                                            {{-- Metadata reload --}}
                                            <li>
                                                <form action="{{ route('datasets.metadata.refresh', [$wsId, $dsId]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-database me-2"></i>{{ __('datasets.actions.reload_metadata') }}
                                                    </button>
                                                </form>
                                            </li>

                                            <li><hr class="dropdown-divider"></li>

                                            {{-- Power BI external links --}}
                                            <li><a class="dropdown-item" href="{{ $webSettings }}" target="_blank"><i class="fas fa-tools me-2"></i>{{ __('datasets.actions.manage_in_pbi') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ $webView }}" target="_blank"><i class="fas fa-external-link-alt me-2"></i>{{ __('datasets.actions.view_in_pbi') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ $webView }}" target="_blank"><i class="fas fa-edit me-2"></i>{{ __('datasets.actions.edit_in_pbi') }}</a></li>
                                            <li><a class="dropdown-item" href="{{ $webLineage }}" target="_blank"><i class="fas fa-project-diagram me-2"></i>{{ __('datasets.actions.view_lineage') }}</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($datasets,'links'))
                <div class="card-footer d-flex justify-content-end">
                    {{ $datasets->links() }}
                </div>
            @endif
        </x-ui-card>
    @endif
@stop

{{-- =========================
      STYLES
     ========================= --}}
@push('css')
<style>
.header-actions {
    display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:.5rem;
}
.header-btn {
    display:inline-flex;align-items:center;justify-content:center;
    height:38px;line-height:1;padding:0 .9rem;font-size:.875rem;
}
.header-btn i {font-size:.9rem;margin-right:.4rem;}
.fw-semibold {font-weight:600;}
.text-monospace {
    font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
}

/* === CORRECCIÓN DEFINITIVA DE DROPDOWN === */

/* 1. Z-Index alto para asegurar que flote sobre todo */
.dropdown-menu { z-index: 9999 !important; }

/* 1.b Evitar recorte por card/tabla */
.datasets-card-wrap { overflow: visible; }
.datasets-card-wrap .card-body { overflow: visible; }
.datasets-card-wrap .dropdown,
.datasets-card-wrap .btn-group { position: static; }

/* 2. En pantallas de escritorio (min-width: 992px), permitimos que la tabla
      desborde su contenedor. Esto hace que el menú NO se corte. */
@media (min-width: 992px) {
    .datasets-table-wrap {
        overflow: visible !important;
    }
}

/* 3. En móviles, mantenemos el scroll para no romper el diseño, 
      aunque el menú podría cortarse si es muy largo, es preferible a romper la tabla */
@media (max-width: 991.98px) {
    .datasets-table-wrap {
        overflow-x: auto !important;
    }
}
</style>
@endpush
