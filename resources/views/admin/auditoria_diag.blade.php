@extends('adminlte::page')

@section('title', 'Diagnóstico de Auditoría')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h5 mb-0">Diagnóstico de Auditoría</h1>
        <a class="btn btn-secondary btn-sm" href="{{ route('auditoria.index') }}">
            <i class="fas fa-arrow-left mr-1"></i> Volver a Auditoría
        </a>
    </div>
@stop

@section('content')
<form method="get" class="mb-3">
    <div class="card">
        <div class="card-body row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Desde</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Hasta</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Aplicar</button>
                <a href="{{ route('admin.auditoria.diag') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </div>
    </div>
</form>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong>Resumen general</strong></div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Total registros: <strong>{{ number_format($totalAll) }}</strong></li>
                    <li>Primera fecha (global): <strong>{{ $globalMin ?? '—' }}</strong></li>
                    <li>Última fecha (global): <strong>{{ $globalMax ?? '—' }}</strong></li>
                    <li>Registros en el futuro (&gt; hoy+1d): <strong>{{ $futureCount }}</strong></li>
                    <li>Registros muy antiguos (&lt; 5 años): <strong>{{ $oldCount }}</strong></li>
                </ul>
                <small class="text-muted">Si el rango no coincide con tus datos, aquí lo verás.</small>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Acciones en el rango ({{ $from }} → {{ $to }})</strong>
                <span class="text-muted small">Group by action</span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Acción</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                    @forelse($byAction as $r)
                        <tr>
                            <td><code>{{ $r->action ?? 'NULL' }}</code></td>
                            <td class="text-right">{{ number_format((int)$r->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted">Sin datos en el rango</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="mt-2">
                    <strong>Acciones distintas (global):</strong>
                    @if(count($distinctActions))
                        <code>{{ implode(', ', $distinctActions) }}</code>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><strong>Candidatas a “acceso” en el rango</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-bordered w-auto">
            <thead>
                <tr>
                    <th>open</th><th>loaded</th><th>rendered</th>
                    <th>page_changed</th><th>filters_applied</th>
                    <th>bookmark_applied</th><th>data_selected</th><th>error</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $candidateCounts['open'] ?? 0 }}</td>
                    <td>{{ $candidateCounts['loaded'] ?? 0 }}</td>
                    <td>{{ $candidateCounts['rendered'] ?? 0 }}</td>
                    <td>{{ $candidateCounts['page_changed'] ?? 0 }}</td>
                    <td>{{ $candidateCounts['filters_applied'] ?? 0 }}</td>
                    <td>{{ $candidateCounts['bookmark_applied'] ?? 0 }}</td>
                    <td>{{ $candidateCounts['data_selected'] ?? 0 }}</td>
                    <td>{{ $candidateCounts['error'] ?? 0 }}</td>
                </tr>
            </tbody>
        </table>
        <small class="text-muted">Con este cuadro decidimos qué acción usar para “accesos”.</small>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header"><strong>Últimos 100 eventos en el rango</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>ID</th><th>Fecha (occurred_at)</th><th>User</th><th>Action</th>
                    <th>Report</th><th>Page</th><th>ms</th><th>OK</th><th>IP</th><th>Referer</th>
                </tr>
            </thead>
            <tbody>
            @forelse($latest as $e)
                <tr>
                    <td>{{ $e->id }}</td>
                    <td>{{ $e->occurred_at }}</td>
                    <td>{{ $e->user_id ?? '—' }}</td>
                    <td><code>{{ $e->action }}</code></td>
                    <td><small><code>{{ $e->report_uuid }}</code></small></td>
                    <td>{{ $e->page_name ?? '—' }}</td>
                    <td class="text-right">{{ $e->duration_ms ?? '—' }}</td>
                    <td>{{ $e->success ? '✔' : '✖' }}</td>
                    <td>{{ $e->ip ?? '—' }}</td>
                    <td><small class="text-muted">{{ $e->referer ? \Illuminate\Support\Str::limit($e->referer, 60) : '—' }}</small></td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted">Sin eventos en el rango</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
