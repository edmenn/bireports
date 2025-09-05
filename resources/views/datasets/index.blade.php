@extends('adminlte::page')

@section('title', 'Conjuntos de datos')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">Conjuntos de datos</h1>
            <small class="text-muted">Listado desde BD; los metadatos vienen de Fabric</small>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <a href="{{ route('datasets.schedules.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-clock"></i> Ver todas las programaciones
            </a>
            <a href="{{ url()->current() }}" class="btn btn-outline-secondary">
                <i class="fas fa-sync"></i> Recargar lista
            </a>
            <a href="{{ route('powerbi.import.datasets') }}" class="btn btn-outline-secondary">
                <i class="fas fa-cloud-download-alt"></i> Importar/recargar metadatos (todos)
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('status'))
        <x-adminlte-alert theme="success" title="Listo">{{ session('status') }}</x-adminlte-alert>
    @endif
    @if(session('error'))
        <x-adminlte-alert theme="danger" title="Error">{{ session('error') }}</x-adminlte-alert>
    @endif

    <div class="mb-2 small text-muted">Consejo: usa “Acciones → Recargar metadatos” por fila para evitar demoras.</div>

    @php $total = method_exists($datasets ?? null, 'count') ? $datasets->count() : (is_countable($datasets ?? []) ? count($datasets) : 0); @endphp

    @if($total === 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-inbox fa-3x text-muted"></i></div>
                <h5 class="mb-2">No se encontraron datasets</h5>
                <p class="text-muted mb-0">Asegúrate de haber importado y tener acceso a los workspaces.</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body table-responsive datasets-table-wrap p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Workspace</th>
                            <th class="text-nowrap">Dataset ID</th>
                            <th class="text-end">Acciones</th>
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
                                            Acciones
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-right">
                                            <li>
                                                <form action="{{ route('datasets.refresh', [$wsId, $dsId]) }}" method="POST" onsubmit="return confirm('¿Iniciar actualización ahora?')">
                                                    @csrf
                                                    <button class="dropdown-item" type="submit"><i class="fas fa-sync me-2"></i>Actualizar datos ahora</button>
                                                </form>
                                            </li>
                                            <li><a class="dropdown-item" href="{{ route('datasets.history', [$wsId, $dsId]) }}"><i class="fas fa-history me-2"></i>Historial de actualización</a></li>
                                            <li><a class="dropdown-item" href="{{ route('datasets.schedule', [$wsId, $dsId]) }}"><i class="fas fa-clock me-2"></i>Programar actualización</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('datasets.metadata.refresh', [$wsId, $dsId]) }}" method="POST">
                                                    @csrf
                                                    <button class="dropdown-item" type="submit"><i class="fas fa-database me-2"></i>Recargar metadatos</button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="{{ $webSettings }}" target="_blank" rel="noopener"><i class="fas fa-tools me-2"></i>Administrar en Power BI</a></li>
                                            <li><a class="dropdown-item" href="{{ $webView }}" target="_blank" rel="noopener"><i class="fas fa-external-link-alt me-2"></i>Ver en Power BI</a></li>
                                            <li><a class="dropdown-item" href="{{ $webView }}" target="_blank" rel="noopener"><i class="fas fa-edit me-2"></i>Editar en Power BI</a></li>
                                            <li><a class="dropdown-item" href="{{ $webLineage }}" target="_blank" rel="noopener"><i class="fas fa-project-diagram me-2"></i>Ver linaje</a></li>
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
</style>
@endpush
