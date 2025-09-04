@extends('adminlte::page')

@section('title', 'Carpetas')

@section('content_header')
    {{-- Título y botón Crear --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="m-0 text-dark">Carpetas</h1>
        <a href="{{ route('folders.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nueva Carpeta
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

    <x-adminlte-card title="Listado de Carpetas" icon="fas fa-folder text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3" body-class="p-0">

        <div class="p-3">
            {{-- Buscador con input + botones alineados --}}
            <form method="GET" action="{{ route('folders.index') }}" class="w-100">
                <div class="searchbar">
                    {{-- Input con icono --}}
                    <div class="field flex-grow-1">
                        <i class="fas fa-search text-muted"></i>
                        <input type="text"
                               name="q"
                               value="{{ $q }}"
                               class="form-control field-input"
                               placeholder="Buscar por nombre...">
                    </div>

                    <button type="submit" class="btn btn-dark btn-ctl">Buscar</button>
                    @if($hasQuery)
                        <a href="{{ route('folders.index') }}" class="btn btn-outline-secondary btn-ctl">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 25%;">Nombre</th>
                        <th style="width: 40%;">Descripción</th>
                        <th class="text-center">Usuarios</th>
                        <th class="text-center">Grupos</th>
                        <th class="text-center">Reportes</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse(($folders ?? collect()) as $f)
                    <tr>
                        <td class="font-weight-bold align-middle">{{ $f->name }}</td>
                        <td class="align-middle" title="{{ $f->description }}">
                            <span class="text-muted">{{ Str::limit($f->description, 70, '...') ?: '—' }}</span>
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge badge-secondary">{{ $f->users()->count() }}</span>
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge badge-info">{{ $f->groups()->count() }}</span>
                        </td>
                        <td class="text-center align-middle">
                            <span class="badge badge-success">{{ $f->reports()->count() }}</span>
                        </td>
                        <td class="text-right align-middle">
                            <a href="{{ route('folders.edit', $f) }}" class="btn btn-sm btn-default btn-action-text-eq" title="Editar">
                                <i class="fas fa-edit mr-1"></i> Editar
                            </a>
                            <form action="{{ route('folders.destroy', $f) }}" method="POST" class="d-inline ml-1"
                                  onsubmit="return confirm('¿Eliminar esta carpeta? Esta acción no se puede deshacer.');">
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
                        <td colspan="6" class="text-center p-4 text-muted">
                            <i class="fas fa-folder-open fa-2x mb-2"></i>
                            <p class="mb-0">Aún no hay carpetas creadas.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(($folders ?? collect())->hasPages())
            <x-slot name="footerSlot">
                <div class="d-flex justify-content-center">
                    {{ ($folders ?? collect())->appends(['q' => $q])->links() }}
                </div>
            </x-slot>
        @endif
    </x-adminlte-card>
@stop

@push('css')
<style>
    :root {
        --ctl-h: 44px;
        --ctl-r: .5rem;
        --gap: .5rem;
    }

    .searchbar {
        display: flex;
        align-items: stretch;
        gap: var(--gap);
        max-width: 760px;
        width: 100%;
    }

    .field {
        display: flex;
        align-items: center;
        height: var(--ctl-h);
        padding-left: .75rem;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: var(--ctl-r);
        box-sizing: border-box;
    }
    .field i { margin-right: .4rem; }

    .field-input {
        border: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        flex: 1;
        height: 100%;
        line-height: normal;
        padding-left: .25rem;
    }

    .btn-ctl {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: var(--ctl-h);
        line-height: 1;
        box-sizing: border-box;
        border-radius: var(--ctl-r);
        padding: 0 1rem;
        margin: 0;
        white-space: nowrap;
    }

    .btn-action-text-eq {
        width: 100px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }

    .table .badge {
        font-size: 0.85rem;
        padding: 0.4em 0.65em;
        font-weight: 600;
    }
</style>
@endpush
