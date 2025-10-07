@extends('adminlte::page')

@section('title', __('folders.page.create'))

@section('content_header')
    <h1 class="m-0 text-dark">{{ __('folders.page.create') }}</h1>
@stop

@section('content')
    <form action="{{ route('folders.store') }}" method="POST">
        @csrf

        {{-- TARJETA 1: DATOS DE LA CARPETA --}}
        <x-adminlte-card title="{{ __('folders.cards.details.title') }}" icon="fas fa-folder text-lightblue"
                         theme="primary" theme-mode="outline">
            
            {{-- CAMBIO: Se elimina la estructura de dos columnas para un mejor flujo vertical --}}
            <div class="row">
                <div class="col-12">
                    <x-adminlte-input name="name" label="{{ __('folders.form.name_label') }}" placeholder="{{ __('folders.form.name_placeholder') }}"
                                      value="{{ old('name') }}" required/>
                </div>
                <div class="col-12">
                    <x-adminlte-textarea name="description" label="{{ __('folders.form.description_label') }}" rows="3"
                                         placeholder="{{ __('folders.form.description_placeholder') }}">{{ old('description') }}</x-adminlte-textarea>
                </div>
            </div>
        </x-adminlte-card>

        {{-- TARJETA 2: ASIGNACIONES --}}
        <x-adminlte-card title="{{ __('folders.cards.assignments.title') }}" icon="fas fa-users text-lightblue"
                         theme="primary" theme-mode="outline">
            <div class="row">

                {{-- Columna de Usuarios --}}
                <div class="col-md-4 d-flex flex-column">
                    <label>{{ __('folders.form.users_label') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($users as $user)
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="users[]" value="{{ $user->id }}" id="user_{{ $user->id }}">
                                <label class="custom-control-label" for="user_{{ $user->id }}">
                                    {{ $user->name }} <small class="text-muted">&lt;{{ $user->email }}&gt;</small>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('folders.empty.users') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Columna de Grupos --}}
                <div class="col-md-4 d-flex flex-column">
                    <label>{{ __('folders.form.groups_label') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($groups as $group)
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="groups[]" value="{{ $group->id }}" id="group_{{ $group->id }}">
                                <label class="custom-control-label" for="group_{{ $group->id }}">
                                    {{ $group->name }}
                                </label>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('folders.empty.groups') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Columna de Reportes --}}
                <div class="col-md-4 d-flex flex-column">
                    <label>{{ __('folders.form.reports_label') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($reports as $report)
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="reports[]" value="{{ $report->id }}" id="report_{{ $report->id }}">
                                <label class="custom-control-label" for="report_{{ $report->id }}">
                                    {{ $report->name }}
                                </label>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('folders.empty.reports') }}</p>
                        @endforelse
                    </div>
                </div>

            </div>
        </x-adminlte-card>

        {{-- TARJETA DE ACCIONES: BOTONES --}}
        <x-adminlte-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="d-flex" style="gap: 0.75rem; width: 320px;">
                    <x-adminlte-button type="submit" theme="primary" label="{{ __('folders.form.buttons.save') }}" icon="fas fa-save" class="btn-eq"/>
                    <x-adminlte-button :href="route('folders.index')" theme="secondary" label="{{ __('folders.form.buttons.cancel') }}" icon="fas fa-times" class="btn-eq"/>
                </div>
            </div>
        </x-adminlte-card>

    </form>
@stop

@push('css')
<style>
    /* Estilo para los contenedores de checkboxes con scroll */
    .checkbox-list-container {
        max-height: 300px; /* Altura máxima antes de mostrar el scroll */
        overflow-y: auto;  /* Scroll vertical automático */
        background-color: #f8f9fa; /* Fondo ligero para destacar */
    }
    .custom-control {
        margin-bottom: 0.5rem; /* Espacio entre cada checkbox */
    }
</style>
@endpush
