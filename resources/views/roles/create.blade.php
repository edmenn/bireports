@extends('adminlte::page')

@section('title', 'Nuevo perfil')

@section('content_header')
    <h1 class="m-0">Nuevo perfil</h1>
@stop

@section('content')
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Error" dismissable>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf

        <x-adminlte-card title="Datos del Perfil" icon="fas fa-id-badge text-lightblue" theme="primary" theme-mode="outline">
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required maxlength="150" placeholder="Ej: auditor, gestor, analista">
            </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="badge_bg">Color de fondo</label>
                    <input type="color" name="badge_bg" id="badge_bg" class="form-control" value="{{ old('badge_bg', '#eef2ff') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="badge_text">Color de texto/borde</label>
                    <input type="color" name="badge_text" id="badge_text" class="form-control" value="{{ old('badge_text', '#4338ca') }}">
                </div>
            </div>
        </x-adminlte-card>

        @include('roles._permissions', ['sections' => $sections, 'current' => old('permissions', [])])

        <x-adminlte-card theme="primary" theme-mode="outline">
            <x-adminlte-button type="submit" theme="primary" label="Guardar" icon="fas fa-save"/>
            <x-adminlte-button :href="route('roles.index')" theme="secondary" label="Cancelar" icon="fas fa-times"/>
        </x-adminlte-card>
    </form>
@endsection
