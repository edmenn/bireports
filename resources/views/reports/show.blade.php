{{-- resources/views/reports/show.blade.php --}}
@extends('adminlte::page')

@section('title', $report->name ?? 'Reporte')

@section('css')
<style>
    html, body, .wrapper, .content-wrapper, .content, .container-fluid {
        height: 100%;
        min-height: 100%;
        margin: 0;
        padding: 0;
    }

    .content-header { padding: .75rem 1rem; }

    #reportContainer {
        position: relative;
        width: 100%;
        background: transparent;
        overflow: hidden;
    }

    #reportLoading {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
        background: transparent;
        z-index: 2;
    }

    #reportError {
        position: absolute;
        left: 1rem; right: 1rem; top: 1rem;
        z-index: 3;
    }

    .content { padding: 0 1rem 1rem; }
</style>
@endsection

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h5 mb-0">
            {{ $report->name ?? 'Reporte' }}
            @if(!empty($workspace?->name))
                <small class="text-muted">/ {{ $workspace->name }}</small>
            @endif
        </h1>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
@stop

@section('content')
    {{-- Banner de error corto (si el controller capturó una excepción) --}}
    @if(!empty($error))
        <div class="alert alert-danger">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-triangle mr-2 mt-1"></i>
                <div>
                    <strong>{{ $error }}</strong>
                    @if(!empty($error_debug))
                        <details class="mt-2">
                            <summary>Ver detalle técnico</summary>
                            <pre class="mt-2 bg-light p-2 border rounded" style="white-space: pre-wrap; max-height: 45vh; overflow:auto;">{{ $error_debug }}</pre>
                        </details>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Diagnóstico de embed si faltan campos críticos --}}
    @php
        $embedUrl    = $embed['embedUrl']    ?? null;
        $accessToken = $embed['accessToken'] ?? null;
        $reportId    = $embed['reportId']    ?? ($report->id ?? null);
        $tokenType   = $embed['tokenType']   ?? 'Embed';
        $hasCritical = $embedUrl && $accessToken && $reportId;
    @endphp

    @if (!$hasCritical)
        <div class="alert alert-warning">
            <h5 class="mb-2"><i class="fas fa-info-circle mr-2"></i>No se pudo preparar el embed del reporte.</h5>
            <ul class="mb-2">
                <li>embedUrl: {!! $embedUrl ? '<span class="text-success">OK</span>' : '<span class="text-danger">FALTA</span>' !!}</li>
                <li>accessToken: {!! $accessToken ? '<span class="text-success">OK</span>' : '<span class="text-danger">FALTA</span>' !!}</li>
                <li>reportId: {!! $reportId ? '<span class="text-success">OK</span>' : '<span class="text-danger">FALTA</span>' !!}</li>
            </ul>
            <details>
                <summary>Ver payload recibido</summary>
                <pre class="mt-2 bg-light p-2 border rounded" style="white-space: pre-wrap;">{{ json_encode($embed ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        </div>
        @php return; @endphp
    @endif

    <div id="reportContainer">
        <div id="reportLoading">
            <div class="text-center">
                <div class="spinner-border" role="status"></div>
                <div class="mt-2">Cargando reporte...</div>
            </div>
        </div>
        <div id="reportError" class="alert alert-danger d-none"></div>
    </div>

    @if(!empty($report->description))
        <div class="mt-3">
            <small class="text-muted">{{ $report->description }}</small>
        </div>
    @endif

    @if (! request()->hasCookie('XSRF-TOKEN'))
        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endif
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/powerbi-client@2/dist/powerbi.js"></script>
    <script>
    (function () {
        'use strict';

        // ====== Elementos UI ======
        const container = document.getElementById('reportContainer');
        const loading   = document.getElementById('reportLoading');
        const errorBox  = document.getElementById('reportError');

        // ====== Altura dinámica para que se vea todo (incl. navegación) ======
        const NAV_RESERVED_PX = 64; // ajusta si fuera necesario

        function h(el){ return el ? (el.offsetHeight||0) : 0; }
        function setReportHeight() {
            const vh = window.innerHeight || document.documentElement.clientHeight;
            const headerH = h(document.querySelector('.content-header'));
            const footerH = h(document.querySelector('.main-footer'));
            const paddingB = 16;
            const target = Math.max(240, vh - headerH - footerH - paddingB - NAV_RESERVED_PX);
            container.style.height = target + 'px';
        }
        setReportHeight();
        window.addEventListener('resize', setReportHeight);
        setTimeout(setReportHeight, 0);
        setTimeout(setReportHeight, 250);
        setTimeout(setReportHeight, 1000);

        // ====== Config embed desde servidor ======
        const EMBED = {
            embedUrl:    @json($embedUrl),
            accessToken: @json($accessToken),
            reportId:    @json($reportId),
            tokenType:   @json($tokenType),
        };
        const REPORT_UUID    = @json($report->id);
        const WORKSPACE_UUID = @json($workspace->id ?? null);

        function showError(msg) {
            if (!errorBox) return;
            errorBox.classList.remove('d-none');
            errorBox.innerText = typeof msg === 'string' ? msg : (msg?.message ?? JSON.stringify(msg));
            if (loading) loading.remove();
        }

        if (!EMBED.embedUrl || !EMBED.accessToken || !EMBED.reportId) {
            return showError('Faltan datos de embedUrl / accessToken / reportId.');
        }

        const models = window['powerbi-client']?.models;
        if (!window.powerbi || !models) {
            return showError('No se pudo cargar powerbi-client.');
        }

        try { powerbi.reset(container); } catch (_) {}

        const config = {
            type: 'report',
            id: EMBED.reportId,
            embedUrl: EMBED.embedUrl,
            accessToken: EMBED.accessToken,
            tokenType: (EMBED.tokenType && models.TokenType[EMBED.tokenType]) ? models.TokenType[EMBED.tokenType] : models.TokenType.Embed,
            permissions: models.Permissions.All,
            settings: {
                background: models.BackgroundType.Default,
                panes: { filters: { visible: true, expanded: false }, pageNavigation: { visible: true } },
                displayOption: models.DisplayOption.FitToPage
            }
        };

        // ====== Embebido + watchdog (10s) ======
        const report = powerbi.embed(container, config);
        window.report = report;

        let loaded = false;
        const LOAD_TIMEOUT_MS = 10000;
        const loadTimer = setTimeout(() => {
            if (loaded) return;
            try { powerbi.reset(container); } catch (_) {}
            showError('El reporte tardó demasiado en cargar (timeout 10s). Verifica la red, el token o la capacidad.');
        }, LOAD_TIMEOUT_MS);

        report.on('loaded', () => {
            loaded = true;
            clearTimeout(loadTimer);
            if (loading) loading.remove();
            setReportHeight();
        });

        report.on('rendered', () => setReportHeight());

        report.on('error', (event) => {
            clearTimeout(loadTimer);
            const detail = event?.detail;
            const msg = (typeof detail === 'string') ? detail : (detail?.message || 'Error al renderizar el reporte');
            showError(msg);
        });

        // ====== Auditoría mínima con timeout 5s ======
        async function postAudit(action, extra = {}) {
            const controller = new AbortController();
            const t = setTimeout(() => controller.abort(), 5000);
            try {
                await fetch(@json(route('api.audit.store')), {
                    method: 'POST',
                    signal: controller.signal,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token())),
                    },
                    body: JSON.stringify({
                        report_uuid:    REPORT_UUID,
                        workspace_uuid: WORKSPACE_UUID,
                        action,
                        success: action !== 'error',
                        occurred_at: new Date().toISOString(),
                        ...extra
                    })
                });
            } catch (_) {
                // no bloquear UI
            } finally {
                clearTimeout(t);
            }
        }

        window.addEventListener('load', () => postAudit('open'), { once: true });
        window.addEventListener('beforeunload', () => {
            try {
                const payload = {
                    report_uuid: REPORT_UUID,
                    workspace_uuid: WORKSPACE_UUID,
                    action: 'close',
                    success: true,
                    occurred_at: new Date().toISOString()
                };
                if (navigator.sendBeacon) {
                    const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
                    navigator.sendBeacon(@json(route('api.audit.store')), blob);
                } else {
                    postAudit('close');
                }
            } catch (_) {}
        });
    })();
    </script>
@stop
