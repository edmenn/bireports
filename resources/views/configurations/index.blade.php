@extends('adminlte::page')

@section('title', __('settings.page.title'))

@section('css')
    @include('portal.partials.base-styles')
    @include('portal.partials.home-card-styles')
@endsection

@section('content_header')
    <x-page-header :title="__('settings.menu.parameters')" :subtitle="__('settings.page.subtitle')" icon="fas fa-sliders-h">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end gap-2 header-actions">
            <a href="{{ route('audit.changes.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-list"></i>
                <span>{{ __('settings.actions.view_audit') }}</span>
            </a>
            <a href="{{ route('powerbi.import.run') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-cloud-download-alt"></i>
                <span>{{ __('settings.actions.import_now') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    {{-- Mensajes --}}
    @if(session('success'))
        <x-adminlte-alert theme="success" title="{{ __('settings.alerts.success') }}" dismissable>
            {{ session('success') }}
        </x-adminlte-alert>
    @endif
    @if(session('error'))
        <x-adminlte-alert theme="danger" title="{{ __('settings.alerts.error') }}" dismissable>
            {{ session('error') }}
        </x-adminlte-alert>
    @endif
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="{{ __('settings.alerts.validation') }}" dismissable>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <div class="portal-card mt-3">
        <div class="card-body">
            <form method="POST" action="{{ route('configurations.update', $config->id) }}" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="return_to" value="{{ route('configurations.index') }}">

        <div class="row">
            {{-- COLUMNA IZQUIERDA: FORMULARIO PRINCIPAL --}}
            <div class="col-lg-6 d-flex">
                <x-adminlte-card title="{{ __('settings.service_principal.card_title') }}" icon="fas fa-key text-lightblue"
                                 theme="primary" theme-mode="outline" class="flex-grow-1">
                    
                    <p class="text-muted">
                        {{ __('settings.service_principal.intro') }}
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <x-adminlte-input name="powerbi_client_id" label="{{ __('settings.service_principal.fields.client_id.label') }}" placeholder="{{ __('settings.service_principal.fields.client_id.placeholder') }}" value="{{ old('powerbi_client_id', $config->powerbi_client_id) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-id-badge"></i></div></x-slot>
                                {{-- Tooltip: dónde encontrar el Client ID en Entra ID --}}
                                <x-slot name="bottom"><small class="text-muted">{{ __('settings.service_principal.fields.client_id.help') }} <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="{{ __('settings.service_principal.fields.client_id.tooltip') }}"></i></small></x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="powerbi_tenant_id" label="{{ __('settings.service_principal.fields.tenant_id.label') }}" placeholder="{{ __('settings.service_principal.fields.tenant_id.placeholder') }}" value="{{ old('powerbi_tenant_id', $config->powerbi_tenant_id) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-building"></i></div></x-slot>
                                {{-- Tooltip: dónde ver el Tenant ID --}}
                                <x-slot name="bottom"><small class="text-muted">{{ __('settings.service_principal.fields.tenant_id.help') }} <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="{{ __('settings.service_principal.fields.tenant_id.tooltip') }}"></i></small></x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="powerbi_client_secret" id="field_client_secret" type="password" label="{{ __('settings.service_principal.fields.client_secret.label') }}" placeholder="{{ __('settings.service_principal.fields.client_secret.placeholder') }}" value="{{ old('powerbi_client_secret', $config->powerbi_client_secret) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-key"></i></div></x-slot>
                                {{-- Tooltip: cómo gestionar el secreto --}}
                                <x-slot name="bottom"><small class="text-muted">{{ __('settings.service_principal.fields.client_secret.help') }} <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="Genera el secreto en Azure Entra ID → Certificates & secrets. Evita pegar valores enmascarados."></i></small></x-slot>
                            </x-adminlte-input>
                        </div>
                        {{-- Redirect URI y Scope removidos de la UI (scope via .env) --}}
                        <div class="col-md-4">
                            <x-adminlte-input name="powerbi_token_refresh_minutes" type="number" min="0" step="1" label="{{ __('settings.service_principal.fields.refresh_minutes.label') }}" placeholder="{{ __('settings.service_principal.fields.refresh_minutes.placeholder') }}" value="{{ old('powerbi_token_refresh_minutes', $config->powerbi_token_refresh_minutes ?? 0) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="far fa-clock"></i></div></x-slot>
                                {{-- Tooltip: política de refresco del token --}}
                                <x-slot name="bottom"><small class="text-muted">{{ __('settings.service_principal.fields.refresh_minutes.help') }} <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="{{ __('settings.service_principal.fields.refresh_minutes.tooltip') }}"></i></small></x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">{{ __('settings.service_principal.fields.timezone.label') }} <i class="far fa-question-circle ms-1" data-bs-toggle="tooltip" title="{{ __('settings.service_principal.fields.timezone.tooltip') }}"></i></label>
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
                            <small class="text-muted">{{ __('settings.service_principal.fields.timezone.help') }}</small>
                        </div>
                    </div>

                    <hr>

                    <div class="p-3 border rounded d-flex justify-content-between align-items-center bg-light">
                        <div>
                            <h6 class="mb-0 font-weight-bold">{{ __('settings.auth.microsoft.title') }}</h6>
                            <small class="text-muted">{{ __('settings.auth.microsoft.description') }}</small>
                        </div>
                        <div>
                            <label class="custom-toggle-switch">
                                <input type="hidden" name="microsoft_login_enabled" value="0">
                                <input type="checkbox" name="microsoft_login_enabled" value="1" {{ old('microsoft_login_enabled', $config->microsoft_login_enabled) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>


                    <div class="p-3 border rounded d-flex justify-content-between align-items-center bg-light mt-3">
                        <div>
                            <h6 class="mb-0 font-weight-bold">{{ __('settings.auth.local.title') }}</h6>
                            <small class="text-muted">{!! __('settings.auth.local.description', ['env' => '<code>AUTH_ALLOW_LOCAL_LOGIN=true</code>']) !!}</small>
                        </div>
                        <div>
                            <label class="custom-toggle-switch">
                                <input type="hidden" name="local_login_enabled" value="0">
                                <input type="checkbox" name="local_login_enabled" value="1" {{ old('local_login_enabled', $config->local_login_enabled) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>



                    <div class="p-3 border rounded bg-light mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 font-weight-bold">{{ __('settings.prewarm.title') }}</h6>
                                <small class="text-muted d-block">{{ __('settings.prewarm.description') }}</small>
                            </div>
                            <div>
                                <label class="custom-toggle-switch">
                                    <input type="hidden" name="prewarm_enabled" value="0">
                                    <input type="checkbox" name="prewarm_enabled" value="1" {{ old('prewarm_enabled', $config->prewarm_enabled) ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                    </div>


                </x-adminlte-card>
            </div>

            {{-- COLUMNA DERECHA: GUÍA Y AYUDA --}}
            <div class="col-lg-6 d-flex">
                <x-adminlte-card title="{{ __('settings.guide.title') }}" icon="fas fa-info-circle text-lightblue"
                                 theme="primary" theme-mode="outline" class="flex-grow-1">
                    <div class="d-flex flex-column h-100 gap-3">
                        <div class="callout callout-info flex-grow-1 mb-0">
                            <h5 class="mb-2"><i class="fas fa-shield-alt mr-1"></i> {{ __('settings.guide.permissions.title') }}</h5>
                            <ul class="mb-0 ps-3">
                                <li>{!! __('settings.guide.permissions.items.assign') !!}</li>
                                <li>{{ __('settings.guide.permissions.items.roles') }}</li>
                                <li>{!! __('settings.guide.permissions.items.access') !!}</li>
                            </ul>
                        </div>
                        <div class="callout callout-warning flex-grow-1 mb-0">
                            <h5 class="mb-2"><i class="fas fa-check-circle mr-1"></i> {{ __('settings.guide.recommendations.title') }}</h5>
                            <ul class="mb-0 ps-3">
                                <li>{{ __('settings.guide.recommendations.items.secret') }}</li>
                                <li>{{ __('settings.guide.recommendations.items.rotate') }}</li>
                                <li>{!! __('settings.guide.recommendations.items.restrict') !!}</li>
                            </ul>
                        </div>
                    </div>
                </x-adminlte-card>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{--         CAPACIDAD ON‑DEMAND (Parámetros del Viewer)         --}}
        {{-- ========================================================== --}}
        <div class="row mt-3">
            <div class="col-12 d-flex">
                <x-adminlte-card title="Capacidad on‑demand (Viewer)" icon="fas fa-bolt text-warning"
                                 theme="warning" theme-mode="outline" class="flex-grow-1">
                    {{-- [Minimalista] Estos parámetros controlan el comportamiento del viewer cuando la capacidad está en pausa.
                         - Encendido on‑demand: si está en true, el viewer intenta encender la capacidad automáticamente
                           cuando un usuario abre un reporte fuera de horario o con la capacidad pausada.
                         - Mensaje: texto mostrado mientras se enciende la capacidad.
                         - Timeout (seg): tiempo máximo de espera antes de mostrar error si no enciende.
                         - Intervalo (seg): cada cuántos segundos se consulta el estado mientras enciende. --}}
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('settings.viewer.fields.enable.label') }}
                                {{-- Tooltip: explica qué hace el encendido on-demand --}}
                                <i class="far fa-question-circle ms-1 text-muted" tabindex="0"
                                   data-bs-toggle="tooltip"
                                   title="{{ __('settings.viewer.fields.enable.tooltip') }}"></i>
                            </label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="capacity_on_demand_enable" value="0">
                                <input class="form-check-input" type="checkbox" id="capacity_on_demand_enable"
                                       name="capacity_on_demand_enable" value="1"
                                       {{ old('capacity_on_demand_enable', $config->capacity_on_demand_enable ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="capacity_on_demand_enable">{{ __('settings.viewer.fields.enable.toggle') }}</label>
                            </div>
                            <small class="text-muted">{{ __('settings.viewer.fields.enable.help') }}
                                {{-- Tooltip de ayuda extra --}}
                                <i class="far fa-question-circle ms-1 text-muted" tabindex="0"
                                   data-bs-toggle="tooltip"
                                   title="{{ __('settings.viewer.fields.enable.tooltip_help') }}"></i>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="capacity_on_demand_message" label="{{ __('settings.viewer.fields.message.label') }}"
                                              placeholder="{{ __('settings.viewer.fields.message.placeholder') }}"
                                              value="{{ old('capacity_on_demand_message', $config->capacity_on_demand_message ?? __('settings.viewer.fields.message.default')) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="far fa-comment"></i></div></x-slot>
                            </x-adminlte-input>
                            <small class="text-muted">{{ __('settings.viewer.fields.message.help') }}
                                {{-- Tooltip: alcance del mensaje --}}
                                <i class="far fa-question-circle ms-1 text-muted" tabindex="0"
                                   data-bs-toggle="tooltip"
                                   title="{{ __('settings.viewer.fields.message.tooltip') }}"></i>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="capacity_start_timeout_sec" type="number" min="10" max="600" step="5"
                                              label="{{ __('settings.viewer.fields.start_timeout.label') }}"
                                              value="{{ old('capacity_start_timeout_sec', $config->capacity_start_timeout_sec ?? 120) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="far fa-clock"></i></div></x-slot>
                            </x-adminlte-input>
                            <small class="text-muted">{{ __('settings.viewer.fields.start_timeout.help') }}
                                {{-- Tooltip: qué pasa al vencer el timeout --}}
                                <i class="far fa-question-circle ms-1 text-muted" tabindex="0"
                                   data-bs-toggle="tooltip"
                                   title="{{ __('settings.viewer.fields.start_timeout.tooltip') }}"></i>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="capacity_start_poll_interval_sec" type="number" min="1" max="60" step="1"
                                              label="{{ __('settings.viewer.fields.poll_interval.label') }}"
                                              value="{{ old('capacity_start_poll_interval_sec', $config->capacity_start_poll_interval_sec ?? 5) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-sync"></i></div></x-slot>
                            </x-adminlte-input>
                            <small class="text-muted">{{ __('settings.viewer.fields.poll_interval.help') }}
                                {{-- Tooltip: impacto del intervalo --}}
                                <i class="far fa-question-circle ms-1 text-muted" tabindex="0"
                                   data-bs-toggle="tooltip"
                                   title="{{ __('settings.viewer.fields.poll_interval.tooltip') }}"></i>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <x-adminlte-input name="report_load_timeout_sec" type="number" min="5" max="300" step="1"
                                              label="{{ __('settings.viewer.fields.report_timeout.label') }}"
                                              value="{{ old('report_load_timeout_sec', $config->report_load_timeout_sec ?? 10) }}">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="far fa-clock"></i></div></x-slot>
                            </x-adminlte-input>
                            <small class="text-muted">{{ __('settings.viewer.fields.report_timeout.help') }}
                                <i class="far fa-question-circle ms-1 text-muted" tabindex="0"
                                   data-bs-toggle="tooltip"
                                   title="{{ __('settings.viewer.fields.report_timeout.tooltip') }}"></i>
                            </small>
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
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <x-adminlte-button type="submit" theme="primary" label="{{ __('settings.actions.save') }}" icon="fas fa-save" class="btn-eq"/>
                    <x-adminlte-button type="button" theme="secondary" label="{{ __('settings.actions.cancel') }}" icon="fas fa-times" class="btn-eq" onclick="window.history.back()"/>
                </div>
            </div>
        </x-adminlte-card>

            </form>
        </div>
    </div>
@stop

@push('css')
<style>
    /* ======================================== */
    /* Uniform form controls                    */
    /* ======================================== */

    .form-control {
        height: calc(2.25rem + 2px) !important;
        padding: .375rem .75rem !important;
        line-height: 1.5 !important;
        font-size: .875rem !important;
        color: #212529 !important;
        background-color: #fff !important;
        border: 1px solid #ced4da !important;
        border-radius: .25rem !important;
        box-shadow: none !important;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }

    select.form-control {
        padding-top: .4rem !important;
        padding-bottom: .4rem !important;
    }

    .form-control:focus {
        color: #212529;
        background-color: #fff;
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 .15rem rgba(0,123,255,.25);
    }

    .form-label {
        font-weight: 600;
        font-size: .875rem;
        margin-bottom: .25rem;
    }

    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        border-right: 0;
        height: calc(2.25rem + 2px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .875rem;
        color: #6c757d;
    }

    .input-group > .form-control {
        height: calc(2.25rem + 2px);
        border-left: 0;
    }

    small.text-muted {
        display: block;
        margin-top: .25rem;
        font-size: .75rem;
    }

    .x-adminlte-card .card-body label,
    .card-body .form-label {
        margin-top: .5rem;
    }

    /* ======================================== */
    /* Custom toggle switch (unchanged)         */
    /* ======================================== */

    .custom-toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
        margin-bottom: 0;
        vertical-align: middle;
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
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #28a745;
    }

    input:focus + .slider {
        box-shadow: 0 0 1px #28a745;
    }

    input:checked + .slider:before {
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

    // [Tooltips] Inicializa tooltips Bootstrap en esta pantalla (si están disponibles)
    try {
        if (window.bootstrap && typeof window.bootstrap.Tooltip === 'function') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new window.bootstrap.Tooltip(el);
            });
        }
    } catch (_) { /* silencioso si no hay bootstrap */ }
})();
</script>
@endpush
