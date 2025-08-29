@extends('adminlte::page')

@section('title', 'Resetear auditoría')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h5 mb-0">Resetear auditoría</h1>
        <a href="{{ route('auditoria.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-chart-line mr-1"></i> Métricas de uso
        </a>
    </div>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-times-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <strong>Confirmación de borrado total</strong>
        </div>
        <div class="card-body">
            <p class="mb-3">
                Esta acción <strong>eliminará todos los registros</strong> de la tabla <code>audits</code> y
                <strong>reiniciará</strong> el contador de IDs. No se puede deshacer.
            </p>

            <ul class="list-unstyled mb-3">
                <li><strong>Registros actuales:</strong> {{ number_format($total) }}</li>
                <li><strong>Primer evento:</strong> {{ $first_at ?? '—' }}</li>
                <li><strong>Último evento:</strong> {{ $last_at ?? '—' }}</li>
            </ul>

            <form method="post" action="{{ route('admin.audit.reset.run') }}" onsubmit="return confirm('¿Seguro que deseas BORRAR TODO? Esta acción no se puede deshacer.');">
                @csrf

                <div class="form-group">
                    <label for="confirm">Escribe <code>RESET</code> para confirmar</label>
                    <input id="confirm" name="confirm" type="text" class="form-control" placeholder="RESET" required>
                    <small class="form-text text-muted">
                        Debes escribir exactamente <strong>RESET</strong>.
                    </small>
                </div>

                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt mr-1"></i> Borrar todos los datos de auditoría
                </button>
            </form>
        </div>
    </div>
@stop
