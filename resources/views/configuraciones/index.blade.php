@extends('adminlte::page')

@section('title', 'Configuraciones')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0 text-dark">Configuraciones</h1>
            <small class="text-muted">Credenciales de Microsoft Fabric / Power BI para App-Owns-Data</small>
        </div>
        <a href="{{ route('powerbi.import.run') }}" class="btn btn-outline-secondary">
            <i class="fas fa-cloud-download-alt mr-1"></i> Importar Ahora
        </a>
    </div>
@stop

@section('content')
    {{-- Mensajes --}}
    @if(session('success'))
        <x-adminlte-alert theme="success" title="Éxito" dismissable>
            {{ session('success') }}
        </x-adminlte-alert>
    @endif
    @if(session('error'))
        <x-adminlte-alert theme="danger" title="Error" dismissable>
            {{ session('error') }}
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
        <input type="hidden" name="return_to" value="{{ route('configuraciones.index') }}">

        <div class="row">
            {{-- COLUMNA IZQUIERDA: FORMULARIO PRINCIPAL --}}
            <div class="col-lg-6 d-flex">
                <x-adminlte-card title="Credenciales de Conexión (Service Principal)" icon="fas fa-key text-lightblue"
                                 theme="primary" theme-mode="outline" class="flex-grow-1">
                    
                    <p class="text-muted">
                        Configura una aplicación de Entra ID (Service Principal) con permisos de Power BI para la autenticación desatendida.
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <x-adminlte-input name="powerbi_client_id" label="Client ID (Application ID)" placeholder="ID de la aplicación" value="{{ old('powerbi_client_id', $config->powerbi_client_id) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-id-badge"></i></div></x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="powerbi_tenant_id" label="Tenant ID (Directory ID)" placeholder="ID del directorio" value="{{ old('powerbi_tenant_id', $config->powerbi_tenant_id) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-building"></i></div></x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="powerbi_client_secret" id="field_client_secret" type="password" label="Client Secret" placeholder="••••••••••••" value="{{ old('powerbi_client_secret', $config->powerbi_client_secret) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-key"></i></div></x-slot>
                            </x-adminlte-input>
                        </div>
                        {{-- Redirect URI y Scope removidos de la UI (scope via .env) --}}
                        <div class="col-md-4">
                            <x-adminlte-input name="powerbi_token_refresh_minutes" type="number" min="0" step="1" label="Refrescar (min)" placeholder="0 = auto" value="{{ old('powerbi_token_refresh_minutes', $config->powerbi_token_refresh_minutes ?? 0) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="far fa-clock"></i></div></x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Zona horaria por defecto</label>
                            @php
                                $tzList = \DateTimeZone::listIdentifiers();
                                $selTz = old('app_timezone_db', $config->app_timezone_db ?? config('app.timezone'));
                                $nowUTC = \Carbon\Carbon::now('UTC');
                                $label = function(string $id) use ($nowUTC) {
                                    try {
                                        $dt = $nowUTC->copy()->setTimezone($id);
                                        $offsetMin = $dt->offsetMinutes;
                                        $sign = $offsetMin >= 0 ? '+' : '-';
                                        $abs = abs($offsetMin);
                                        $hh = str_pad((string) intdiv($abs,60), 2, '0', STR_PAD_LEFT);
                                        $mm = str_pad((string) ($abs % 60), 2, '0', STR_PAD_LEFT);
                                        return sprintf('%s (UTC %s%s:%s)', $id, $sign, $hh, $mm);
                                    } catch (\Throwable $e) {
                                        return $id;
                                    }
                                };
                            @endphp
                            <select name="app_timezone_db" class="form-control">
                                @foreach($tzList as $tz)
                                    <option value="{{ $tz }}" {{ $selTz === $tz ? 'selected' : '' }}>{{ $label($tz) }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Se aplicará al scheduler y como valor por defecto en programación de datasets.</small>
                        </div>
                    </div>

                    <hr>

                    <div class="p-3 border rounded d-flex justify-content-between align-items-center bg-light">
                        <div>
                            <h6 class="mb-0 font-weight-bold">Inicio de Sesión con Microsoft</h6>
                            <small class="text-muted">Permite a los usuarios autenticarse usando sus cuentas de Microsoft.</small>
                        </div>
                        <div>
                            <label class="custom-toggle-switch">
                                <input type="checkbox" name="microsoft_login_enabled" value="1" {{ old('microsoft_login_enabled', $config->microsoft_login_enabled) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>

                    <div class="p-3 border rounded bg-light mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 font-weight-bold">Pre‑warm de capacidades</h6>
                                <small class="text-muted d-block">Si está activo, antes de una actualización programada reanudamos (encendemos) la capacidad asociada para evitar fallos por estar pausada. Desactívalo si prefieres controlar esto externamente.</small>
                            </div>
                            <div>
                                <label class="custom-toggle-switch">
                                    <input type="checkbox" name="prewarm_enabled" value="1" {{ old('prewarm_enabled', $config->prewarm_enabled) ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 border rounded d-flex justify-content-between align-items-center bg-light mt-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold">Inicio de Sesión Local</h6>
                            <small class="text-muted">Formulario tradicional de email y contraseña. En caso de emergencia, el entorno puede forzar habilitarlo con <code>AUTH_ALLOW_LOCAL_LOGIN=true</code>.</small>
                        </div>
                        <div>
                            <label class="custom-toggle-switch">
                                <input type="checkbox" name="local_login_enabled" value="1" {{ old('local_login_enabled', $config->local_login_enabled) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                </x-adminlte-card>
            </div>

            {{-- COLUMNA DERECHA: GUÍA Y AYUDA --}}
            <div class="col-lg-6 d-flex">
                <x-adminlte-card title="Guía y Buenas Prácticas" icon="fas fa-info-circle text-lightblue"
                                 theme="primary" theme-mode="outline" class="flex-grow-1">
                    <div class="d-flex flex-column h-100" style="gap: 1rem;">
                        <div class="callout callout-info flex-grow-1 mb-0">
                            <h5 class="mb-2"><i class="fas fa-shield-alt mr-1"></i> Permisos Requeridos</h5>
                            <ul class="mb-0 ps-3">
                                <li>Asignar la app al <em>tenant</em> (Admin Portal → Developer settings).</li>
                                <li>Roles típicos: <code>Tenant.Read.All</code> o <code>Tenant.ReadWrite.All</code>.</li>
                                <li>Conceder acceso a <em>workspaces</em> específicos.</li>
                            </ul>
                        </div>
                        <div class="callout callout-warning flex-grow-1 mb-0">
                            <h5 class="mb-2"><i class="fas fa-check-circle mr-1"></i> Recomendaciones</h5>
                            <ul class="mb-0 ps-3">
                                <li>Usa un secreto dedicado solo para este portal.</li>
                                <li>Rota los secretos periódicamente.</li>
                                <li>Restringe la app a los workspaces necesarios.</li>
                            </ul>
                        </div>
                    </div>
                </x-adminlte-card>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{--           NUEVA TARJETA DE ACCIONES PARA BOTONES           --}}
        {{-- ========================================================== --}}
        <x-adminlte-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="d-flex" style="gap: 0.75rem; width: 320px;">
                    <x-adminlte-button type="submit" theme="primary" label="Guardar" icon="fas fa-save" class="btn-eq"/>
                    <x-adminlte-button type="reset" theme="secondary" label="Cancelar" icon="fas fa-times" class="btn-eq"/>
                </div>
            </div>
        </x-adminlte-card>

    </form>
@stop

@push('css')
<style>
    /* Pequeño refinamiento visual */
    .input-group .btn i { pointer-events: none; }
    .callout h5 { font-weight: 600; }

    /* Estilo para el interruptor ON/OFF personalizado */
    .custom-toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        margin-bottom: 0;
    }
    .custom-toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }
    input:checked + .slider {
        background-color: #28a745;
    }
    input:focus + .slider {
        box-shadow: 0 0 1px #28a745;
    }
    input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }
    .slider.round {
        border-radius: 34px;
    }
    .slider.round:before {
        border-radius: 50%;
    }
</style>
@endpush

@push('js')
<script>
(function () {
    'use strict';
    
    // Scope recomendado
    const btnScope = document.getElementById('btn-scope-default');
    const fieldScope = document.getElementById('field_scope');
    if (btnScope && fieldScope) {
        btnScope.addEventListener('click', function () {
            fieldScope.value = 'https://analysis.windows.net/powerbi/api/.default';
        }, { passive: true });
    }
})();
</script>
@endpush
