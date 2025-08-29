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
    {{-- Mensajes de estado --}}
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

    {{-- Errores de validación --}}
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Errores de validación" dismissable>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('configuraciones.update', $config->id) }}" novalidate>
        @csrf
        @method('PUT')

        {{-- CARD: Conexión a Microsoft Fabric / Power BI --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <strong>Conexión a Microsoft Fabric / Power BI</strong>
                <span class="text-muted small">
                    Usa una aplicación de Entra ID (Service Principal) con permisos de Power BI
                </span>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    {{-- Client ID --}}
                    <div class="col-md-6">
                        <label class="form-label">Client ID (Application ID)</label>
                        <div class="input-group">
                            <input
                                name="powerbi_client_id"
                                class="form-control @error('powerbi_client_id') is-invalid @enderror"
                                value="{{ old('powerbi_client_id', $config->powerbi_client_id) }}"
                                placeholder="e.g. 11111111-2222-3333-4444-555555555555"
                                autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary js-copy" data-copy-target="powerbi_client_id">
                                <i class="far fa-copy"></i>
                            </button>
                            @error('powerbi_client_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">ID de la app registrada en Microsoft Entra ID.</small>
                    </div>

                    {{-- Tenant ID --}}
                    <div class="col-md-6">
                        <label class="form-label">Tenant ID</label>
                        <div class="input-group">
                            <input
                                name="powerbi_tenant_id"
                                class="form-control @error('powerbi_tenant_id') is-invalid @enderror"
                                value="{{ old('powerbi_tenant_id', $config->powerbi_tenant_id) }}"
                                placeholder="e.g. 6c9c6032-1b20-4ce3-b8b8-3172fc887c2c"
                                autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary js-copy" data-copy-target="powerbi_tenant_id">
                                <i class="far fa-copy"></i>
                            </button>
                            @error('powerbi_tenant_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">ID del directorio (tenant) de tu organización.</small>
                    </div>

                    {{-- Client Secret (con reveal) --}}
                    <div class="col-md-6">
                        <label class="form-label">Client Secret</label>
                        <div class="input-group">
                            <input
                                name="powerbi_client_secret"
                                id="field_client_secret"
                                type="password"
                                class="form-control @error('powerbi_client_secret') is-invalid @enderror"
                                value="{{ old('powerbi_client_secret', $config->powerbi_client_secret) }}"
                                placeholder="••••••••••••••••••••••••••••"
                                autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary" id="btn-toggle-secret" title="Mostrar/Ocultar">
                                <i class="far fa-eye"></i>
                            </button>
                            @error('powerbi_client_secret')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">Se recomienda rotar este secreto periódicamente.</small>
                    </div>

                    {{-- Redirect URI --}}
                    <div class="col-md-6">
                        <label class="form-label">Redirect URI</label>
                        <input
                            name="powerbi_redirect_uri"
                            class="form-control @error('powerbi_redirect_uri') is-invalid @enderror"
                            value="{{ old('powerbi_redirect_uri', $config->powerbi_redirect_uri) }}"
                            placeholder="https://tu-dominio.com/oauth/callback">
                        @error('powerbi_redirect_uri')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Usada solo si aplicás flujos interactivos (no estrictamente necesario para Client Credentials).</small>
                    </div>

                    {{-- Scope --}}
                    <div class="col-md-8">
                        <label class="form-label">Scope</label>
                        <div class="input-group">
                            @php
                                $defaultScope = 'https://analysis.windows.net/powerbi/api/.default';
                            @endphp
                            <input
                                name="powerbi_scope"
                                id="field_scope"
                                class="form-control @error('powerbi_scope') is-invalid @enderror"
                                value="{{ old('powerbi_scope', $config->powerbi_scope ?? $defaultScope) }}"
                                placeholder="{{ $defaultScope }}">
                            <button type="button" class="btn btn-outline-secondary" id="btn-scope-default" title="Usar scope recomendado">
                                <i class="fas fa-magic"></i>
                            </button>
                            @error('powerbi_scope')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">
                            Para Service Principal, usa <code>{{ $defaultScope }}</code>. Los permisos (app roles) se definen en Power BI (Admin Portal).
                        </small>
                    </div>

                    {{-- Refresh minutes --}}
                    <div class="col-md-4">
                        <label class="form-label">Refrescar token cada (minutos)</label>
                        <input
                            name="powerbi_token_refresh_minutes"
                            type="number"
                            min="0"
                            step="1"
                            class="form-control @error('powerbi_token_refresh_minutes') is-invalid @enderror"
                            value="{{ old('powerbi_token_refresh_minutes', $config->powerbi_token_refresh_minutes ?? 0) }}"
                            placeholder="0 = auto">
                        @error('powerbi_token_refresh_minutes')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Deja <strong>0</strong> para usar la expiración real del token.</small>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Tips de seguridad / requisitos --}}
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="alert alert-light border">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-shield-alt mt-1"></i>
                                <div>
                                    <strong>Permisos requeridos en Power BI (Service Principal):</strong>
                                    <ul class="mb-0 mt-2 ps-3">
                                        <li>Asignar la app al <em>tenant</em> (Admin Portal &rarr; Developer settings).</li>
                                        <li>App roles típicos: <code>Tenant.Read.All</code> o <code>Tenant.ReadWrite.All</code> según el uso.</li>
                                        <li>Conceder acceso a <em>workspaces</em> específicos si no usás permisos globales.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="alert alert-light border">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-info-circle mt-1"></i>
                                <div>
                                    <strong>Buenas prácticas:</strong>
                                    <ul class="mb-0 mt-2 ps-3">
                                        <li>Usar un secreto dedicado solo para este portal.</li>
                                        <li>Rotar secretos y revocar los antiguos.</li>
                                        <li>Restringir el acceso de la app a los workspaces necesarios.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> {{-- card-body --}}

            <div class="card-footer d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </form>
@stop

@push('css')
<style>
    .input-group .btn i{ pointer-events: none; }
</style>
@endpush

@push('js')
<script>
(function(){
    // Mostrar/ocultar secreto
    const btnToggle = document.getElementById('btn-toggle-secret');
    const secret = document.getElementById('field_client_secret');
    if (btnToggle && secret) {
        btnToggle.addEventListener('click', function(){
            const type = secret.getAttribute('type') === 'password' ? 'text' : 'password';
            secret.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class="far fa-eye"></i>' : '<i class="far fa-eye-slash"></i>';
        });
    }

    // Poner scope recomendado
    const btnScope = document.getElementById('btn-scope-default');
    const fieldScope = document.getElementById('field_scope');
    if (btnScope && fieldScope) {
        btnScope.addEventListener('click', function(){
            const recommended = 'https://analysis.windows.net/powerbi/api/.default';
            fieldScope.value = recommended;
        });
    }

    // Copiar por target name (data-copy-target)
    document.addEventListener('click', function(e){
        const btn = e.target.closest('.js-copy');
        if(!btn) return;
        const name = btn.getAttribute('data-copy-target');
        if(!name) return;
        const input = document.querySelector(`[name="${name}"]`);
        if(!input) return;
        const value = input.value || '';
        if(!value) return;

        navigator.clipboard.writeText(value).then(()=>{
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            setTimeout(()=>{
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 900);
        }).catch(()=> alert('No se pudo copiar al portapapeles.'));
    }, {passive:true});
})();
</script>
@endpush
