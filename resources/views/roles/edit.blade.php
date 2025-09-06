@extends('adminlte::page')

@section('title', 'Editar perfil')

@section('content_header')
    <h1 class="m-0">Editar perfil</h1>
@stop

@section('content')
    @if (session('success')) <x-adminlte-alert theme="success" title="OK" dismissable class="mb-2">{{ session('success') }}</x-adminlte-alert> @endif
    @if (session('warning')) <x-adminlte-alert theme="warning" title="Atención" dismissable class="mb-2">{{ session('warning') }}</x-adminlte-alert> @endif
    @if (session('error'))   <x-adminlte-alert theme="danger"  title="Error"    dismissable class="mb-2">{{ session('error') }}</x-adminlte-alert>   @endif

    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Error" dismissable>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PUT')

        <x-adminlte-card title="Datos del Perfil" icon="fas fa-id-badge text-lightblue" theme="primary" theme-mode="outline">
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $role->name) }}" required maxlength="150">
            </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="badge_bg">Color de fondo</label>
                    <input type="color" name="badge_bg" id="badge_bg" class="form-control" value="{{ old('badge_bg', $role->badge_bg ?: '#eef2ff') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="badge_text">Color de texto/borde</label>
                    <input type="color" name="badge_text" id="badge_text" class="form-control" value="{{ old('badge_text', $role->badge_text ?: '#4338ca') }}">
                </div>
            </div>
        </x-adminlte-card>

        @php $sel = old('permissions', $current ?? []); @endphp
        @if(!empty($isAdmin))
            <x-adminlte-alert theme="info" title="Perfil protegido" class="mb-2">
                El perfil <strong>admin</strong> tiene acceso a <strong>todas</strong> las opciones y no permite modificar sus accesos.
            </x-adminlte-alert>
            @include('roles._permissions', ['sections' => $sections, 'current' => $sel, 'disableInputs' => true])
        @else
            @include('roles._permissions', ['sections' => $sections, 'current' => $sel])
        @endif

        <x-adminlte-card theme="primary" theme-mode="outline">
            <x-adminlte-button type="submit" theme="primary" label="Guardar" icon="fas fa-save"/>
            <x-adminlte-button :href="route('roles.index')" theme="secondary" label="Cancelar" icon="fas fa-times"/>
            @if (strtolower($role->name) !== 'admin')
                <x-adminlte-button theme="danger" label="Eliminar" icon="fas fa-trash" type="submit" form="roleDeleteForm"/>
            @endif
        </x-adminlte-card>
    </form>

    @if (strtolower($role->name) !== 'admin')
        <form id="roleDeleteForm" action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar el perfil {{ $role->name }}?');">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
