{{-- resources/views/capacities/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Capacidades')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="h4 mb-0">Capacidades</h1>

        <form action="{{ route('capacities.refresh') }}" method="POST" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-sync-alt mr-1"></i> Recargar
            </button>
        </form>
    </div>
@stop

@section('content')
    {{-- Mensajes flash --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('capacities.index') }}" class="mb-0">
                <div class="input-group input-group-sm" style="max-width: 380px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" name="q" value="{{ request('q','') }}" class="form-control"
                           placeholder="Buscar por nombre, SKU o tipo…">
                    <div class="input-group-append">
                        <button class="btn btn-primary">Buscar</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sm mb-0">
                    <thead class="thead-light">
                        <tr class="text-nowrap">
                            <th>Nombre de Capacidad</th>
                            <th>SKU</th>
                            <th>Tipo</th>
                            <th>Optimización</th>
                            <th>Tiempo de espera</th>
                            <th>Horarios</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Controlar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($caps as $cap)
                            @php
                                $estado   = $cap->is_active ? 'Activo' : 'Pausado';
                                $badge    = $cap->is_active ? 'badge-success' : 'badge-secondary';
                                $btnText  = $cap->is_active ? 'Pausar' : 'Reanudar';
                                $btnClass = $cap->is_active ? 'btn-outline-danger' : 'btn-outline-success';
                                $btnIcon  = $cap->is_active ? 'fa-pause' : 'fa-play';
                                $opt      = $cap->cost_optimization_enabled ? 'Sí' : 'No';
                                $timeout  = $cap->inactivity_timeout_minutes ?? 0;
                                $hasSched = $cap->schedules->count() > 0;
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $cap->display_name }}</div>
                                    <div class="text-muted small">UUID: {{ $cap->capacity_uuid }}</div>
                                </td>
                                <td>{{ $cap->sku }}</td>
                                <td>{{ $cap->type }}</td>
                                <td>
                                    <span class="badge {{ $cap->cost_optimization_enabled ? 'badge-info' : 'badge-light border' }}">
                                        {{ $opt }}
                                    </span>
                                </td>
                                <td>{{ $timeout }} min</td>
                                <td style="min-width:220px;">
                                    @if ($hasSched)
                                        <ul class="mb-0 pl-3 small">
                                            @foreach ($cap->schedules as $s)
                                                <li>
                                                    <span class="badge badge-light border mr-1">
                                                        {{ ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'][$s->weekday] ?? $s->weekday }}
                                                    </span>
                                                    {{ $s->start_time->format('H:i') }}–{{ $s->end_time->format('H:i') }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">Sin horarios</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $badge }}">{{ $estado }}</span>
                                    @unless ($cap->isPausable())
                                        <div class="small text-muted">No pausable</div>
                                    @endunless
                                </td>
                                <td class="text-center" style="width:120px;">
                                    <form method="POST" action="{{ route('capacities.toggle', $cap) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $btnClass }}" {{ $cap->isPausable() ? '' : 'disabled' }}>
                                            <i class="fas {{ $btnIcon }} mr-1"></i>{{ $btnText }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="far fa-folder-open fa-lg mb-2 d-block"></i>
                                    No hay capacidades para mostrar.
                                    <form action="{{ route('capacities.refresh') }}" method="POST" class="d-inline-block ml-2">
                                        @csrf
                                        <button class="btn btn-link p-0 align-baseline">Recargar desde Power BI</button>
                                    </form>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($caps->hasPages())
            <div class="card-footer">
                {{-- Si usas Bootstrap para la paginación: --}}
                {{ $caps->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
                {{-- O default: {{ $caps->links() }} --}}
            </div>
        @endif
    </div>
@stop

@section('css')
    {{-- CSS extra opcional --}}
@stop

@section('js')
    {{-- JS extra opcional --}}
@stop
