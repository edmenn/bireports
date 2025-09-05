@extends('adminlte::page')

@section('title', 'Programación de capacidad')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">Programación: {{ $capacity->display_name }}</h1>
            <small class="text-muted">SKU {{ $capacity->sku }} — {{ $capacity->region }}</small>
        </div>
        <a href="{{ route('capacities.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <x-adminlte-alert theme="success" title="Listo">{{ session('success') }}</x-adminlte-alert>
    @endif
    @if(session('error'))
        <x-adminlte-alert theme="danger" title="Error">{{ session('error') }}</x-adminlte-alert>
    @endif

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Horarios</h3></div>
                <div class="card-body">
                    @if($schedules->isEmpty())
                        <div class="text-muted">No hay horarios configurados.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Desde</th>
                                        <th>Hasta</th>
                                        <th>Estado</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $s)
                                        <tr>
                                            <td>{{ $dayNames[$s->weekday] ?? $s->weekday }}</td>
                                            <td>{{ $s->start_time->format('H:i') }}</td>
                                            <td>{{ $s->end_time->format('H:i') }}</td>
                                            <td><span class="badge {{ $s->enabled ? 'bg-success':'bg-secondary' }}">{{ $s->enabled ? 'Activo':'Inactivo' }}</span></td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('capacities.schedule.toggle', [$capacity->id, $s->id]) }}" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-xs btn-outline-primary">{{ $s->enabled ? 'Desactivar':'Activar' }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('capacities.schedule.delete', [$capacity->id, $s->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar horario?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-xs btn-outline-danger">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <hr>
                    <h5 class="mb-2">Agregar horario</h5>
                    <form method="POST" action="{{ route('capacities.schedule.store', $capacity->id) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Días</label>
                            <div class="d-flex flex-wrap" style="gap:.5rem">
                                @foreach($dayNames as $idx=>$name)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="days[]" value="{{ $idx }}" id="d{{ $idx }}">
                                        <label for="d{{ $idx }}" class="form-check-label">{{ $name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Desde</label>
                            <input type="time" class="form-control" name="start_time" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Hasta</label>
                            <input type="time" class="form-control" name="end_time" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary"><i class="fas fa-plus"></i> Agregar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Opciones</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('capacities.settings.update', $capacity->id) }}">
                        @csrf @method('PUT')
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="force_sched" name="force_shutdown_on_schedule" value="1" {{ $capacity->force_shutdown_on_schedule ? 'checked' : '' }}>
                            <label class="form-check-label" for="force_sched">Forzar encendido/pausado por horario</label>
                        </div>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="opt_inact" name="cost_optimization_enabled" value="1" {{ $capacity->cost_optimization_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="opt_inact">Pausar por inactividad</label>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Tiempo de inactividad (min)</label>
                            <input type="number" class="form-control" name="inactivity_timeout_minutes" min="5" max="120" step="5" value="{{ old('inactivity_timeout_minutes', $capacity->inactivity_timeout_minutes) }}" placeholder="ej. 30">
                            <small class="text-muted">Mínimo 5, incrementos de 5, máximo 120.</small>
                        </div>
                        <button class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

