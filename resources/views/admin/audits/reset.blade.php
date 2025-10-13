@extends('adminlte::page')

@section('title', 'Resetear Auditoría')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1 class="m-0 text-dark">Resetear Auditoría</h1>
        <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-chart-line mr-1"></i> Métricas de Uso
        </a>
    </div>
@stop

@section('content')
    {{-- Alertas con el componente de AdminLTE --}}
    @if (session('status'))
        <x-adminlte-alert theme="success" title="Éxito" dismissable>
            {{ session('status') }}
        </x-adminlte-alert>
    @endif
    @if (session('error'))
        <x-adminlte-alert theme="danger" title="Error" dismissable>
            {{ session('error') }}
        </x-adminlte-alert>
    @endif

    <form method="post" 
          action="{{ route('admin.audit.reset.run') }}" 
          onsubmit="return confirm('¿Seguro que deseas BORRAR TODO? Esta acción no se puede deshacer.');">
        @csrf

        <x-adminlte-card title="⚠️ Confirmación de Borrado Total" icon="fas fa-exclamation-triangle"
                         theme="danger" theme-mode="outline" class="mt-3">
            
            <p class="lead">
                Esta acción <strong>eliminará todos los registros</strong> de la tabla <code>audits</code> y
                <strong>reiniciará</strong> el contador de IDs. <span class="text-danger font-weight-bold">No se puede deshacer.</span>
            </p>

            {{-- Eliminamos la clase "small" para que el texto sea más grande --}}
            <ul class="list-unstyled text-muted my-4">
                <li><strong>Registros actuales:</strong> {{ number_format($total) }}</li>
                <li><strong>Primer evento:</strong> {{ $first_at ?? '—' }}</li>
                <li><strong>Último evento:</strong> {{ $last_at ?? '—' }}</li>
            </ul>

            <x-adminlte-input name="confirm" label="Escribe RESET para confirmar" placeholder="RESET" required>
                <x-slot name="prependSlot"><div class="input-group-text text-danger"><i class="fas fa-keyboard"></i></div></x-slot>
                <x-slot name="bottom"><small class="text-muted">Debes escribir la palabra exactamente como se muestra.</small></x-slot>
            </x-adminlte-input>

        </x-adminlte-card>

        {{-- Tarjeta de acciones con los botones centrados y de igual tamaño --}}
        <x-adminlte-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="d-flex" style="gap: 0.75rem; width: 320px;">
                    <x-adminlte-button type="submit" theme="danger" label="Borrar Todo" icon="fas fa-trash-alt" class="btn-eq"/>
                    <x-adminlte-button :href="route('audit.index')" theme="secondary" label="Cancelar" icon="fas fa-times" class="btn-eq"/>
                </div>
            </div>
        </x-adminlte-card>

    </form>
@stop

@push('css')
<style>
    /* ================================== */
    /* CSS PARA TEXTOS MÁS GRANDES      */
    /* ================================== */
    .card-body .lead {
        font-size: 1.15rem; /* Párrafo principal */
        font-weight: 400;
    }
    .card-body ul li {
        font-size: 1rem; /* Lista de estadísticas */
        padding-bottom: 0.25rem;
    }
    #confirm-group label,
    #confirm-group .form-text {
        font-size: 1rem; /* Etiqueta y texto de ayuda del input */
    }
</style>
@endpush