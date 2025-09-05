@extends('adminlte::page')

@section('title', 'Programaciones de datasets')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">Programaciones de datasets</h1>
            <small class="text-muted">Locales y de Fabric (solo lectura)</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('datasets.schedules.index', array_merge(request()->query(), ['type'=>'local'])) }}" class="btn btn-outline-secondary">
                <i class="fas fa-filter"></i> Mostrar solo locales
            </a>
            <a href="{{ route('datasets.schedules.index', array_merge(request()->query(), ['type'=>'all'])) }}" class="btn btn-outline-secondary">
                <i class="fas fa-database"></i> Cargar Fabric también
            </a>
            <a href="{{ route('datasets.schedules.export', request()->query()) }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-export"></i> Exportar CSV
            </a>
        </div>
    </div>
@stop

@section('content')
    @php
        $type = $type ?? 'all';
        $q    = $q ?? '';
        $day  = $day ?? '';
        $workspace = $workspace ?? '';
    @endphp

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('datasets.schedules.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Tipo</label>
                    <select name="type" class="form-control">
                        <option value="all" {{ $type==='all'?'selected':'' }}>Todos</option>
                        <option value="local" {{ $type==='local'?'selected':'' }}>Locales</option>
                        <option value="fabric" {{ $type==='fabric'?'selected':'' }}>Fabric</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Workspace</label>
                    <select name="workspace" class="form-control">
                        <option value="">Todos</option>
                        @foreach($workspaces as $ws)
                            <option value="{{ $ws->id }}" {{ $workspace===$ws->id?'selected':'' }}>{{ $ws->name ?? $ws->id }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Día</label>
                    @php $dayOpts = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; @endphp
                    <select name="day" class="form-control">
                        <option value="">Todos</option>
                        @foreach($dayOpts as $d)
                            <option value="{{ $d }}" {{ $day===$d?'selected':'' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Buscar</label>
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Dataset, ID o Workspace">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="{{ route('datasets.schedules.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fuente</th>
                        <th>Workspace</th>
                        <th>Dataset</th>
                        <th class="text-nowrap">Estado</th>
                        <th>TZ</th>
                        <th>Días</th>
                        <th>Horarios</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        @php
                            $wsId = $r['workspace_id'];
                            $dsId = $r['dataset_id'];
                        @endphp
                        <tr>
                            <td>
                                <span class="badge {{ $r['source']==='local' ? 'bg-primary' : 'bg-secondary' }}">
                                    {{ $r['source']==='local' ? 'Local' : 'Fabric' }}
                                </span>
                            </td>
                            <td class="small">{{ $r['workspace'] }}</td>
                            <td class="small">
                                <div class="fw-semibold">{{ $r['dataset_name'] }}</div>
                                <div class="text-muted text-monospace">{{ $r['dataset_id'] }}</div>
                            </td>
                            <td class="small">
                                <span class="badge {{ ($r['enabled']??false) ? 'bg-success':'bg-secondary' }}">{{ ($r['enabled']??false) ? 'Habilitado':'Deshabilitado' }}</span>
                                @if(!empty($r['last_status']))
                                    <div class="text-muted">Último: {{ $r['last_status'] }} {{ $r['last_at'] ? '(' . $r['last_at'] . ')' : '' }}</div>
                                @endif
                            </td>
                            <td class="small text-monospace">{{ $r['tz'] }}</td>
                            <td class="small">{{ implode(', ', (array)$r['days']) }}</td>
                            <td class="small">{{ implode(', ', (array)$r['times']) }}</td>
                            <td class="text-end">
                                <form action="{{ route('datasets.refresh', [$wsId, $dsId]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Refrescar ahora este dataset?')">
                                    @csrf
                                    <button class="btn btn-xs btn-outline-primary"><i class="fas fa-sync"></i> Actualizar ahora</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Sin resultados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($datasets ?? null, 'links'))
            <div class="card-footer">
                {{ $datasets->links() }}
            </div>
        @endif
    </div>
@stop
