@extends('adminlte::page')

@section('title', 'Auditoría de Configuraciones')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">Auditoría de Configuraciones</h1>
            <small class="text-muted">Cambios agrupados por evento</small>
        </div>
        <a href="{{ route('configuraciones.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Configuraciones
        </a>
    </div>
@stop

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label mb-1">Buscar</label>
                    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Campo/valor…">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Usuario (ID)</label>
                    <input type="number" name="user" value="{{ $user ?? '' }}" class="form-control" min="1">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary"><i class="fas fa-filter"></i> Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>IP</th>
                    <th>Cambios</th>
                </tr>
                </thead>
                <tbody>
                @forelse($audits as $a)
                    <tr>
                        <td class="text-nowrap text-monospace small">{{ optional($a->created_at)->format('d-m-Y H:i:s') }}</td>
                        <td>{{ $a->user_id ?? '-' }}</td>
                        <td class="text-monospace small">{{ $a->ip ?? '-' }}</td>
                        <td>
                            <details>
                                <summary class="small text-muted">Ver {{ is_countable($a->changes ?? []) ? count($a->changes) : 0 }} cambio(s)</summary>
                                <div class="mt-2">
                                    <table class="table table-sm">
                                        <thead><tr><th>Campo</th><th>Antes</th><th>Después</th></tr></thead>
                                        <tbody>
                                        @foreach(($a->changes ?? []) as $ch)
                                            <tr>
                                                <td class="text-monospace small">{{ $ch['field'] ?? '' }}</td>
                                                <td class="small">{{ $ch['old'] ?? '' }}</td>
                                                <td class="small">{{ $ch['new'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Sin registros</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($audits,'links'))
            <div class="card-footer">{{ $audits->links() }}</div>
        @endif
    </div>
@stop

