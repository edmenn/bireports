@extends('adminlte::page')

@section('title', 'Login')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1 class="m-0">Configuración de Login</h1>
        <a href="{{ route('configuraciones.tema') }}" class="btn btn-outline-secondary">
            <i class="fas fa-palette"></i> Ir a Tema
        </a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <x-adminlte-alert theme="success" title="OK" dismissable>
            {{ session('success') }}
        </x-adminlte-alert>
    @endif
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Errores de validación" dismissable>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('configuraciones.update', $config->id) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="return_to" value="{{ route('configuraciones.login') }}">

        <div class="card">
            <div class="card-header">Logos e imagen de fondo</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Logo de login (PNG/JPG/SVG)</label>
                        <input type="file" name="login_logo" accept="image/png,image/jpeg,image/svg+xml" class="form-control @error('login_logo') is-invalid @enderror">
                        @error('login_logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @if($config->login_logo_path)
                            <div class="mt-2">
                                <small class="text-muted d-block">Actual:</small>
                                <img src="{{ Storage::disk('public')->url($config->login_logo_path) }}" alt="Login Logo" style="max-height:60px">
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Imagen de fondo (pantalla completa)</label>
                        <input type="file" name="login_bg" accept="image/png,image/jpeg" class="form-control @error('login_bg') is-invalid @enderror">
                        @error('login_bg')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        @if($config->login_bg_path)
                            <div class="mt-2">
                                <small class="text-muted d-block">Vista previa:</small>
                                <img class="border" src="{{ Storage::disk('public')->url($config->login_bg_path) }}" alt="Login BG" style="max-width:100%; max-height:120px; object-fit:cover;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </div>
    </form>
@stop

