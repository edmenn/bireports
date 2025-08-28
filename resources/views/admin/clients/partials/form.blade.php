@php($client = $client ?? null)
@if ($errors->any())
    <x-adminlte-alert theme="danger" title="Error">
        <ul class="mb-0">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </x-adminlte-alert>
@endif

<form action="{{ $action }}" method="POST">
    @csrf
    @if(($method ?? '') === 'PUT') @method('PUT') @endif

    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label>Nombre</label>
                <input name="name" class="form-control" required
                       value="{{ old('name', $client->name ?? '') }}">
            </div>
            <div class="form-group">
                <label>Slug (opcional)</label>
                <input name="slug" class="form-control"
                       value="{{ old('slug', $client->slug ?? '') }}">
            </div>
            <div class="form-group">
                <label>Dominio personalizado</label>
                <input name="custom_domain" class="form-control"
                       value="{{ old('custom_domain', $client->custom_domain ?? '') }}">
            </div>
            <div class="form-group">
                <label>Código Google Analytics</label>
                <input name="ga_tracking_code" class="form-control"
                       value="{{ old('ga_tracking_code', $client->ga_tracking_code ?? '') }}">
            </div>
            <div class="form-group">
                <label>Tipo de visualización por defecto</label>
                <select name="default_view_type" class="form-control" required>
                    @foreach(['folders'=>'Pastas','icons'=>'Íconos','list'=>'Lista'] as $v=>$label)
                        <option value="{{ $v }}" @selected(old('default_view_type', $client->default_view_type ?? 'folders')===$v)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Idioma por defecto de emails</label>
                <input name="default_email_lang" class="form-control" value="{{ old('default_email_lang', $client->default_email_lang ?? 'es') }}">
            </div>

            <hr>
            <h5>Power BI / Fabric</h5>
            <div class="form-group">
                <label>Client ID</label>
                <input name="powerbi_client_id" class="form-control" value="{{ old('powerbi_client_id', $client->powerbi_client_id ?? '') }}">
            </div>
            <div class="form-group">
                <label>Tenant ID</label>
                <input name="powerbi_tenant_id" class="form-control" value="{{ old('powerbi_tenant_id', $client->powerbi_tenant_id ?? '') }}">
            </div>
            <div class="form-group">
                <label>Hint del Client Secret (no guardar el secreto aquí)</label>
                <input name="powerbi_client_secret_hint" class="form-control" value="{{ old('powerbi_client_secret_hint', $client->powerbi_client_secret_hint ?? '') }}">
            </div>

            {{-- Más adelante agregamos tabs para Branding, Login y Horarios (JSON/UI) --}}
        </div>
        <div class="card-footer">
            <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">Volver</a>
            <button class="btn btn-success">Guardar</button>
        </div>
    </div>
</form>
