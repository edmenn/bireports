@extends('adminlte::page')

@section('title', 'Auditoría de capacidad')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">Auditoría: {{ $capacity->display_name }}</h1>
            <small class="text-muted">SKU {{ $capacity->sku }} — {{ $capacity->region }}</small>
        </div>
        <div class="d-flex" style="gap: .5rem;">
            <form action="{{ route('capacities.audit.refresh', $capacity) }}" method="POST" onsubmit="return confirm('¿Actualizar metadatos de esta capacidad?');">
                @csrf
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-sync-alt"></i> Actualizar</button>
            </form>
            <a href="{{ route('capacities.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Acción</th>
                        <th>Resultado</th>
                        <th>Fuente</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $l)
                        <tr>
                            <td class="text-nowrap text-monospace small">{{ optional($l->created_at)->format('d-m-Y H:i:s') }}</td>
                            <td>{{ $l->action }}</td>
                            <td><span class="badge {{ ($l->result==='success') ? 'bg-success' : (($l->result==='error')?'bg-danger':'bg-secondary') }}">{{ $l->result ?? '-' }}</span></td>
                            <td>
                                @php $src = strtolower($l->source ?? 'local'); @endphp
                                <span class="badge {{ $src==='api' ? 'bg-primary' : 'bg-secondary' }}">{{ strtoupper($src) }}</span>
                            </td>
                            <td class="small">{{ $l->message }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Sin registros</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
