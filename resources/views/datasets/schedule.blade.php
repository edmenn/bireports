@extends('adminlte::page')

@section('title', 'Programación de actualización')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">Programación de actualización</h1>
            <small class="text-muted">Workspace: {{ $workspace->name ?? $workspace->id }} — Dataset: {{ $datasetId }}</small>
        </div>
        <a href="{{ route('datasets.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
@stop

@section('content')
    @if(session('status'))
        <x-adminlte-alert theme="success" title="Listo">{{ session('status') }}</x-adminlte-alert>
    @endif
    @if(session('error'))
        <x-adminlte-alert theme="danger" title="Error">{{ session('error') }}</x-adminlte-alert>
    @endif

    @php
        $value = (array)($schedule['value'] ?? []);
        $enabled = (bool)($value['enabled'] ?? false);
        $tz = (string)($value['localTimeZoneId'] ?? ($defaultTz ?? 'UTC'));
        $days = (array)($value['days'] ?? []);
        $times = (array)($value['times'] ?? []);
        $notify = (string)($value['notifyOption'] ?? 'MailOnFailure');
        $timesStr = implode(',', $times);
        $dayOpts = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        $notifyOpts = ['NoNotification','MailOnFailure','MailOnCompletion','MailOnFailureOrCompletion'];
    @endphp

    {{-- Resumen claro de programación en Fabric --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Programación en Fabric (Power BI) — Solo lectura</h3>
            
        </div>
        <div class="card-body">
            @php
                $val = (array)($schedule['value'] ?? []);
                $sumEnabled = (bool)($val['enabled'] ?? false);
                $sumTz = (string)($val['localTimeZoneId'] ?? ($defaultTz ?? 'UTC'));
                $sumDays = implode(', ', (array)($val['days'] ?? []));
                $sumTimes = implode(', ', (array)($val['times'] ?? []));
                $sumNotify = (string)($val['notifyOption'] ?? '');
            @endphp
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="small text-muted">Estado</div>
                    <div>
                        <span class="badge {{ $sumEnabled ? 'bg-success' : 'bg-secondary' }}">{{ $sumEnabled ? 'Habilitado' : 'Deshabilitado' }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Zona horaria</div>
                    <div class="text-monospace small">{{ $sumTz }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Días</div>
                    <div class="small">{{ $sumDays ?: '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">Horarios</div>
                    <div class="small">{{ $sumTimes ?: '-' }}</div>
                </div>
                @if($sumNotify !== '')
                <div class="col-md-3">
                    <div class="small text-muted">Notificación</div>
                    <div class="small">{{ $sumNotify }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Programaciones locales --}}
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Programaciones locales</h3>
        </div>
        <div class="card-body">
            @if(session('warning'))
                <x-adminlte-alert theme="warning" title="Aviso">{{ session('warning') }}</x-adminlte-alert>
            @endif

            @php $localSchedules = $localSchedules ?? collect(); @endphp
            @if($localSchedules->isEmpty())
                <div class="text-muted">No hay programaciones locales.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Último resultado</th>
                                <th>Días</th>
                                <th>Horarios</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localSchedules as $s)
                                <tr>
                                    <td>
                                        <span class="badge {{ $s->enabled ? 'bg-success' : 'bg-secondary' }}">{{ $s->enabled ? 'Activo' : 'Inactivo' }}</span>
                                    </td>
                                    <td class="small">
                                        @php $st = strtolower((string)$s->last_run_status); @endphp
                                        @if($s->last_run_at)
                                            <div>
                                                <span class="badge {{ $st==='success' ? 'bg-success' : ($st==='error' ? 'bg-danger' : 'bg-secondary') }}">{{ $s->last_run_status ?? 'n/a' }}</span>
                                                <span class="text-muted">{{ optional($s->last_run_at)->format('Y-m-d H:i') }}</span>
                                            </div>
                                            @if(!empty($s->last_run_message))
                                                <div class="text-muted" style="max-width:360px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $s->last_run_message }}">{{ $s->last_run_message }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">sin ejecuciones</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ implode(', ', (array)$s->days) }}</td>
                                    <td class="small">{{ implode(', ', (array)$s->times) }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('datasets.local.toggle', [$workspace->id, $datasetId, $s->id]) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-xs btn-outline-primary">{{ $s->enabled ? 'Desactivar' : 'Activar' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('datasets.local.delete', [$workspace->id, $datasetId, $s->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar programación?')">
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
            <h5 class="mb-2">Nueva programación</h5>
            <form method="POST" action="{{ route('datasets.local.store', [$workspace->id, $datasetId]) }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Días</label>
                    @php $dayOpts = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; @endphp
                    <div class="form-check mb-1">
                        <input id="days-all" class="form-check-input" type="checkbox">
                        <label class="form-check-label" for="days-all">Seleccionar todos</label>
                    </div>
                    <select id="days-select" name="days[]" class="form-control" multiple size="7">
                        @foreach($dayOpts as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Horarios (HH:mm)</label>
                    <input type="text" name="times" class="form-control" placeholder="08:00,12:30,18:00">
                    <small class="text-muted">Múltiples horarios separados por coma.</small>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary"><i class="fas fa-plus"></i> Agregar</button>
                </div>
            </form>
        </div>
    </div>
@stop

@push('js')
<script>
(function(){
  const input = document.getElementById('ds-times');
  const warn  = document.getElementById('times-warning');
  if(!input || !warn) return;
  function checkDup(){
    const raw = (input.value||'').split(',').map(s=>s.trim()).filter(Boolean);
    const norm = raw.map(s=> /^\d{2}:\d{2}$/.test(s) ? s : s);
    const seen = new Set(); let dup = false;
    for(const t of norm){ if(seen.has(t)){ dup = true; break; } seen.add(t); }
    warn.style.display = dup ? '' : 'none';
  }
  input.addEventListener('input', checkDup);
  checkDup();
})();
</script>
@endpush

@push('js')
<script>
(function(){
    const all = document.getElementById('days-all');
    const sel = document.getElementById('days-select');
    if(!all || !sel) return;
    all.addEventListener('change', function(){
        Array.from(sel.options).forEach(o => o.selected = all.checked);
    });
})();
</script>
@endpush


