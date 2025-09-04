@extends('adminlte::page')

@section('title', 'Grupos')

@section('content_header')
    {{-- Título y botón Crear --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="m-0 text-dark">Grupos</h1>
        <a href="{{ route('grupos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nuevo Grupo
        </a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <x-adminlte-alert theme="success" title="Éxito" dismissable>
            {{ session('success') }}
        </x-adminlte-alert>
    @endif

    @php
        $q = $q ?? request('q', '');
        $hasQuery = strlen(trim($q)) > 0;
    @endphp

    <x-adminlte-card title="Listado de Grupos" icon="fas fa-users text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3" body-class="p-0">

        <div class="p-3">
            {{-- Buscador con input + botones alineados PERFECTO --}}
            <form method="GET" action="{{ route('grupos.index') }}" class="w-100">
                <div class="searchbar">
                    {{-- Input con icono a la izquierda --}}
                    <div class="field flex-grow-1">
                        <i class="fas fa-search text-muted"></i>
                        <input type="text"
                               name="q"
                               value="{{ $q }}"
                               class="form-control field-input"
                               placeholder="Buscar por nombre...">
                    </div>

                    {{-- Botones con misma altura y línea que el input --}}
                    <button type="submit" class="btn btn-dark btn-ctl">Buscar</button>
                    @if($hasQuery)
                        <a href="{{ route('grupos.index') }}" class="btn btn-outline-secondary btn-ctl">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 65%;">Nombre y Descripción</th>
                        <th class="text-center">Miembros</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse(($grupos ?? collect()) as $g)
                    <tr>
                        <td class="align-middle">
                            <div class="font-weight-bold">{{ $g->name }}</div>
                            @if(!empty($g->description))
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($g->description, 120) }}</small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @php
                                $membersCount = isset($g->users_count) ? (int) $g->users_count : (int) $g->users()->count();
                            @endphp
                            <span class="badge badge-primary">{{ $membersCount }}</span>
                        </td>
                        <td class="text-right align-middle">
                            <a href="{{ route('grupos.edit', $g) }}" class="btn btn-sm btn-default btn-action-text-eq" title="Editar">
                                <i class="fas fa-edit mr-1"></i> Editar
                            </a>
                            <form action="{{ route('grupos.destroy', $g) }}" method="POST" class="d-inline ml-1"
                                  onsubmit="return confirm('¿Eliminar el grupo \"{{ $g->name }}\"? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-default btn-action-text-eq" title="Eliminar">
                                    <i class="fas fa-trash mr-1"></i> Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center p-4 text-muted">
                            <i class="fas fa-users-slash fa-2x mb-2"></i>
                            <p class="mb-0">Aún no hay grupos creados.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(($grupos ?? collect())->hasPages())
            <x-slot name="footerSlot">
                <div class="d-flex justify-content-center">
                    {{ ($grupos ?? collect())->appends(['q' => $q])->links() }}
                </div>
            </x-slot>
        @endif
    </x-adminlte-card>
@stop

@push('css')
<style>
    /* ===== Normalización global para este bloque ===== */
    :root {
        --ctl-h: 44px;           /* altura única para input + botones */
        --ctl-r: .5rem;          /* radio consistente */
        --gap: .5rem;            /* separación entre elementos */
    }

    /* Contenedor principal: MISMOS ALTOS, sin “saltos” */
    .searchbar{
        display: flex;
        align-items: stretch;    /* <<< clave para igualar altura */
        gap: var(--gap);
        max-width: 760px;
        width: 100%;
    }

    /* Caja del input con icono */
    .field{
        display: flex;
        align-items: center;             /* centra el icono e input verticalmente */
        box-sizing: border-box;          /* asegura cálculo de altura correcto */
        height: var(--ctl-h);            /* misma altura que botones */
        padding-left: .75rem;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: var(--ctl-r);
    }
    .field i{ margin-right: .4rem; }

    /* Input interno: sin bordes, line-height estable y 100% alto */
    .field-input{
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        height: 100%;
        line-height: normal;             /* evita “subir” texto */
        padding-left: .25rem;
    }

    /* Botones: misma altura, caja y línea que el input */
    .btn-ctl{
        display: inline-flex;            /* centra el texto dentro del botón */
        align-items: center;
        justify-content: center;
        height: var(--ctl-h);
        line-height: 1;                  /* neutraliza line-height de Bootstrap */
        box-sizing: border-box;
        border-radius: var(--ctl-r);
        padding: 0 1rem;                 /* vertical = 0 para no alterar altura */
        margin: 0;                       /* quita cualquier “saltito” */
        white-space: nowrap;
    }

    /* Acciones tabla: ancho uniforme */
    .btn-action-text-eq {
        width: 100px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }

    /* Badges más legibles */
    .table .badge {
        font-size: 0.85rem;
        padding: 0.4em 0.65em;
        font-weight: 600;
    }
</style>
@endpush
