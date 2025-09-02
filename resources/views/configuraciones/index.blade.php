@extends('adminlte::page')

@section('title', 'Configuraciones')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">Configuraciones</h1>
            <small class="text-muted">Credenciales de Microsoft Fabric / Power BI para App-Owns-Data</small>
        </div>
        <a href="{{ route('powerbi.import.run') }}" class="btn btn-outline-secondary">
            <i class="fas fa-cloud-download-alt"></i> Importar ahora
        </a>
    </div>
@stop

@section('content')
    {{-- Mensajes --}}
    @if(session('success'))
        <x-adminlte-alert theme="success" title="OK" dismissable>
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

        {{-- CARD: Conexión a Microsoft Fabric / Power BI --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Conexión a Microsoft Fabric / Power BI</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Colapsar">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>

            <div class="card-body">
                <p class="text-muted mb-4">
                    Usa una aplicación de Entra ID (Service Principal) con permisos de Power BI.
                </p>

                <div class="row">
                    {{-- Client ID --}}
                    <div class="col-md-6">
                        <x-adminlte-input
                            name="powerbi_client_id"
                            label="Client ID (Application ID)"
                            placeholder="e.g. 11111111-2222-3333-4444-555555555555"
                            value="{{ old('powerbi_client_id', $config->powerbi_client_id) }}"
                            igroup-size="md"
                            enable-old-support
                            >
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-id-badge"></i>
                                </div>
                            </x-slot>
                            <x-slot name="appendSlot">
                                <button class="btn btn-outline-secondary js-copy" type="button" data-copy-target="powerbi_client_id" title="Copiar">
                                    <i class="far fa-copy"></i>
                                </button>
                            </x-slot>
                        </x-adminlte-input>
                        @error('powerbi_client_id')<span class="text-danger small">{{ $message }}</span>@enderror
                        <small class="text-muted d-block">ID de la app registrada en Microsoft Entra ID.</small>
                    </div>

                    {{-- Tenant ID --}}
                    <div class="col-md-6">
                        <x-adminlte-input
                            name="powerbi_tenant_id"
                            label="Tenant ID"
                            placeholder="e.g. 6c9c6032-1b20-4ce3-b8b8-3172fc887c2c"
                            value="{{ old('powerbi_tenant_id', $config->powerbi_tenant_id) }}"
                            igroup-size="md"
                            enable-old-support
                            >
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-building"></i>
                                </div>
                            </x-slot>
                            <x-slot name="appendSlot">
                                <button class="btn btn-outline-secondary js-copy" type="button" data-copy-target="powerbi_tenant_id" title="Copiar">
                                    <i class="far fa-copy"></i>
                                </button>
                            </x-slot>
                        </x-adminlte-input>
                        @error('powerbi_tenant_id')<span class="text-danger small">{{ $message }}</span>@enderror
                        <small class="text-muted d-block">ID del directorio (tenant) de tu organización.</small>
                    </div>

                    {{-- Client Secret --}}
                    <div class="col-md-6">
                        <x-adminlte-input
                            name="powerbi_client_secret"
                            id="field_client_secret"
                            type="password"
                            label="Client Secret"
                            placeholder="••••••••••••••••••••••••••••"
                            value="{{ old('powerbi_client_secret', $config->powerbi_client_secret) }}"
                            igroup-size="md"
                            >
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </div>
                            </x-slot>
                            <x-slot name="appendSlot">
                                <button class="btn btn-outline-secondary" type="button" id="btn-toggle-secret" title="Mostrar/Ocultar">
                                    <i class="far fa-eye"></i>
                                </button>
                            </x-slot>
                        </x-adminlte-input>
                        @error('powerbi_client_secret')<span class="text-danger small">{{ $message }}</span>@enderror
                        <small class="text-muted d-block">Se recomienda rotar este secreto periódicamente.</small>
                    </div>

                    {{-- Redirect URI --}}
                    <div class="col-md-6">
                        <x-adminlte-input
                            name="powerbi_redirect_uri"
                            label="Redirect URI"
                            placeholder="https://tu-dominio.com/oauth/callback"
                            value="{{ old('powerbi_redirect_uri', $config->powerbi_redirect_uri) }}"
                            igroup-size="md"
                            >
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-link"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                        @error('powerbi_redirect_uri')<span class="text-danger small">{{ $message }}</span>@enderror
                        <small class="text-muted d-block">Solo para flujos interactivos; no es necesario para Client Credentials.</small>
                    </div>

                    {{-- Scope --}}
                    <div class="col-md-8">
                        <x-adminlte-input
                            name="powerbi_scope"
                            id="field_scope"
                            label="Scope"
                            placeholder="https://analysis.windows.net/powerbi/api/.default"
                            value="{{ old('powerbi_scope', $config->powerbi_scope ?? 'https://analysis.windows.net/powerbi/api/.default') }}"
                            igroup-size="md"
                            >
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                            </x-slot>
                            <x-slot name="appendSlot">
                                <button class="btn btn-outline-secondary" type="button" id="btn-scope-default" title="Usar scope recomendado">
                                    <i class="fas fa-magic"></i>
                                </button>
                            </x-slot>
                        </x-adminlte-input>
                        @error('powerbi_scope')<span class="text-danger small">{{ $message }}</span>@enderror
                        <small class="text-muted d-block">
                            Para Service Principal usa <code>https://analysis.windows.net/powerbi/api/.default</code>. Los permisos se asignan en el Admin Portal.
                        </small>
                    </div>

                    {{-- Refresh minutes --}}
                    <div class="col-md-4">
                        <x-adminlte-input
                            name="powerbi_token_refresh_minutes"
                            type="number"
                            min="0"
                            step="1"
                            label="Refrescar token cada (minutos)"
                            placeholder="0 = auto"
                            value="{{ old('powerbi_token_refresh_minutes', $config->powerbi_token_refresh_minutes ?? 0) }}"
                            igroup-size="md"
                            >
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="far fa-clock"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>
                        @error('powerbi_token_refresh_minutes')<span class="text-danger small">{{ $message }}</span>@enderror
                        <small class="text-muted d-block">Deja <strong>0</strong> para usar la expiración real del token.</small>
                    </div>

                    {{-- Habilitar login con Microsoft --}}
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="custom-control custom-switch mt-4">
                            <input type="checkbox" class="custom-control-input" id="ms_login_enabled" name="microsoft_login_enabled" value="1" {{ old('microsoft_login_enabled', $config->microsoft_login_enabled) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="ms_login_enabled">Habilitar inicio de sesión con Microsoft</label>
                        </div>
                    </div>
                </div>

                {{-- Tips / Requisitos --}}
                <div class="row">
                    <div class="col-lg-6">
                        <div class="callout callout-info">
                            <h5 class="mb-2"><i class="fas fa-shield-alt mr-1"></i> Permisos requeridos (Service Principal)</h5>
                            <ul class="mb-0 ps-3">
                                <li>Asignar la app al <em>tenant</em> (Admin Portal → Developer settings).</li>
                                <li>App roles típicos: <code>Tenant.Read.All</code> o <code>Tenant.ReadWrite.All</code>.</li>
                                <li>Conceder acceso a <em>workspaces</em> específicos si no usás permisos globales.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="callout callout-warning">
                            <h5 class="mb-2"><i class="fas fa-info-circle mr-1"></i> Buenas prácticas</h5>
                            <ul class="mb-0 ps-3">
                                <li>Secreto dedicado solo para este portal.</li>
                                <li>Rotar secretos y revocar los antiguos.</li>
                                <li>Restringir la app a los workspaces necesarios.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </form>
@stop

@push('css')
<style>
    /* Pequeño refinamiento visual */
    .input-group .btn i { pointer-events: none; }
    .callout h5 { font-weight: 600; }
</style>
@endpush

@push('js')
<script>
(function () {
    'use strict';

    // Mostrar / ocultar secreto
    const btnToggle = document.getElementById('btn-toggle-secret');
    const secret = document.getElementById('field_client_secret');
    if (btnToggle && secret) {
        btnToggle.addEventListener('click', function () {
            const isPwd = secret.getAttribute('type') === 'password';
            secret.setAttribute('type', isPwd ? 'text' : 'password');
            this.innerHTML = isPwd
                ? '<i class="far fa-eye-slash"></i>'
                : '<i class="far fa-eye"></i>';
        }, { passive: true });
    }

    // Scope recomendado
    const btnScope = document.getElementById('btn-scope-default');
    const fieldScope = document.getElementById('field_scope');
    if (btnScope && fieldScope) {
        btnScope.addEventListener('click', function () {
            const recommended = 'https://analysis.windows.net/powerbi/api/.default';
            fieldScope.value = recommended;
            fieldScope.dispatchEvent(new Event('input', { bubbles: true }));
        }, { passive: true });
    }

    // Copiar valor (por name) con feedback
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-copy');
        if (!btn) return;

        const targetName = btn.getAttribute('data-copy-target');
        const input = document.querySelector(`[name="${targetName}"]`);
        if (!input) return;

        const value = input.value || '';
        if (!value) return;

        const onSuccess = () => {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            setTimeout(() => {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 900);
        };

        if (navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(value).then(onSuccess).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }

        function fallbackCopy() {
            try {
                const tmp = document.createElement('textarea');
                tmp.value = value;
                tmp.setAttribute('readonly', '');
                tmp.style.position = 'absolute';
                tmp.style.left = '-9999px';
                document.body.appendChild(tmp);
                tmp.select();
                document.execCommand('copy');
                document.body.removeChild(tmp);
                onSuccess();
            } catch {
                alert('No se pudo copiar al portapapeles.');
            }
        }
    }, { passive: true });
})();
</script>
@endpush
