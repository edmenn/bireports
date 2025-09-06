@extends('adminlte::page')

@section('title', 'Editar Grupo')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Grupo</h1>
@stop

@section('content')
    <form action="{{ route('grupos.update', $grupo) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- TARJETA 1: DATOS DEL GRUPO --}}
        <x-adminlte-card title="Datos del Grupo" icon="fas fa-users text-lightblue"
                         theme="primary" theme-mode="outline">
            <div class="row">
                <div class="col-12">
                    <x-adminlte-input name="name" label="Nombre del Grupo" placeholder="Ej: Equipo Comercial"
                        value="{{ old('name', $grupo->name) }}" required
                        class="@error('name') is-invalid @enderror"/>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <x-adminlte-textarea name="description" label="Descripción (Opcional)" rows="3"
                        placeholder="Describe el propósito del grupo."
                        class="@error('description') is-invalid @enderror">{{ old('description', $grupo->description) }}</x-adminlte-textarea>
                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </x-adminlte-card>

        {{-- TARJETA 2: ASIGNACIONES (solo Usuarios) --}}
        <x-adminlte-card title="Asignaciones" icon="fas fa-user-check text-lightblue"
                         theme="primary" theme-mode="outline">
            <div class="row">
                <div class="col-md-12 d-flex flex-column">
                    <label>Usuarios del Grupo</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($users as $user)
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox"
                                       name="users[]" value="{{ $user->id }}" id="user_{{ $user->id }}"
                                       @checked(in_array($user->id, old('users', $grupo->users->pluck('id')->toArray())))>
                                <label class="custom-control-label" for="user_{{ $user->id }}">
                                    {{ $user->name }} <small class="text-muted">&lt;{{ $user->email }}&gt;</small>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No hay usuarios disponibles.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-adminlte-card>

        {{-- TARJETA DE ACCIONES: BOTONES --}}
        <x-adminlte-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="d-flex" style="gap: 0.75rem; width: 320px;">
                    <x-adminlte-button type="submit" theme="primary" label="Guardar" icon="fas fa-save" class="btn-eq"/>

                    <x-adminlte-button :href="route('grupos.index')" theme="secondary" label="Cancelar" icon="fas fa-times" class="btn-eq"/>
                </div>
            </div>
        </x-adminlte-card>
    </form>
@stop

@push('css')
<style>
    /* Lista de checkboxes con scroll y espaciado uniforme */
    .checkbox-list-container {
        max-height: 300px;
        overflow-y: auto;
        background-color: #f8f9fa;
    }
    .custom-control { margin-bottom: 0.5rem; }
</style>
@endpush
