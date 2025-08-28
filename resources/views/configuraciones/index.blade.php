@extends('adminlte::page')

@section('title', 'Configuraciones')

@section('content_header')
    <h1>Configuraciones</h1>
@stop

@section('content')
    @if(session('success'))
        <x-adminlte-alert theme="success" title="OK" dismissable>
            {{ session('success') }}
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('configuraciones.update', $config->id) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header"><strong>Organización</strong></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Nombre</label>
                    <input name="nombre" class="form-control" value="{{ old('nombre', $config->nombre) }}">
                </div>

                <div class="form-group">
                    <label>Dominio personalizado</label>
                    <input name="dominio_personalizado" class="form-control"
                           value="{{ old('dominio_personalizado', $config->dominio_personalizado) }}">
                </div>

                <div class="form-group">
                    <label>Código Google Analytics</label>
                    <input name="google_analytics_code" class="form-control"
                           value="{{ old('google_analytics_code', $config->google_analytics_code) }}">
                </div>

                <div class="form-group">
                    <label>Tipo de visualización</label>
                    <select name="tipo_visualizacion" class="form-control">
                        @foreach(['pastas'=>'Pastas','iconos'=>'Íconos','lista'=>'Lista'] as $k=>$v)
                            <option value="{{ $k }}" @selected(old('tipo_visualizacion',$config->tipo_visualizacion)==$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Idioma por defecto de los emails</label>
                    <select name="email_locale" class="form-control">
                        @foreach(['es'=>'Español','pt'=>'Portugués','en'=>'Inglés'] as $k=>$v)
                            <option value="{{ $k }}" @selected(old('email_locale',$config->email_locale)==$k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><strong>Conexión a Microsoft Fabric / Power BI</strong></div>
            <div class="card-body">
                <div class="form-group">
                    <label>Client ID (Application ID)</label>
                    <input name="powerbi_client_id" class="form-control"
                           value="{{ old('powerbi_client_id', $config->powerbi_client_id) }}">
                </div>

                <div class="form-group">
                    <label>Client Secret</label>
                    <input name="powerbi_client_secret" type="password" class="form-control"
                           value="{{ old('powerbi_client_secret', $config->powerbi_client_secret) }}">
                </div>

                <div class="form-group">
                    <label>Tenant ID</label>
                    <input name="powerbi_tenant_id" class="form-control"
                           value="{{ old('powerbi_tenant_id', $config->powerbi_tenant_id) }}">
                </div>

                <div class="form-group">
                    <label>Redirect URI</label>
                    <input name="powerbi_redirect_uri" class="form-control"
                           value="{{ old('powerbi_redirect_uri', $config->powerbi_redirect_uri) }}">
                </div>

                <div class="form-group">
                    <label>Scope</label>
                    <input name="powerbi_scope" class="form-control"
                           value="{{ old('powerbi_scope', $config->powerbi_scope ?? 'https://analysis.windows.net/powerbi/api/.default') }}">
                </div>

                <div class="form-group">
                    <label>Refrescar token cada (minutos)</label>
                    <input name="powerbi_token_refresh_minutes" type="number" min="0" class="form-control"
                           value="{{ old('powerbi_token_refresh_minutes', $config->powerbi_token_refresh_minutes ?? 0) }}">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Guardar
            </button>
        </div>
    </form>
@stop
