{{-- resources/views/reports/show.blade.php --}}
@extends('adminlte::page')

@section('title', $report->name ?? 'Reporte')

@section('css')
<style>
    @if($embedded)
    body.sidebar-mini .main-header,
    body.sidebar-mini .main-sidebar,
    body.sidebar-mini .content-header { display: none !important; }
    body.sidebar-mini .content-wrapper { margin-left: 0 !important; }
    .preloader { display: none !important; }
    @endif
    :root{
        --pbi-min-height: 460px; /* Mínimo para que se vean tabs cómodamente */
    }
    html, body, .content-wrapper, .content, .container-fluid{
        height: 100%;
    }
    .pbi-wrapper{
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 120px);
        overflow: hidden;
    }
    #reportContainer{
        position: relative;
        width: 100%;
        height: var(--pbi-min-height);
        min-height: var(--pbi-min-height);
        background: transparent;
        overflow: hidden;
    }
    #reportLoading{
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        color: #666; background: transparent; z-index: 2;
    }
    #reportError{
        position: absolute; left: 1rem; right: 1rem; top: 1rem; z-index: 3;
    }
</style>
@endsection

@section('content_header')
    @if (! $embedded)
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h5 mb-0">
                {{ $report->name ?? 'Reporte' }}
                @if(!empty($workspace?->name))
                    <small class="text-muted">/ {{ $workspace->name }}</small>
                @endif
            </h1>
            <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('common.back') }}
            </a>
        </div>
    @endif
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @php
        $embedUrl    = $embed['embedUrl']    ?? null;
        $accessToken = $embed['accessToken'] ?? null;
        $reportId    = $embed['reportId']    ?? ($report->id ?? null);
        $tokenType   = $embed['tokenType']   ?? 'Embed';
        $hasCritical = $embedUrl && $accessToken && $reportId;
    @endphp

    @if (!$hasCritical)
        <div class="alert alert-danger" role="alert">
            <h5 class="mb-2"><i class="fas fa-times-circle me-2"></i>No se pudo preparar el embed del reporte.</h5>
            <ul class="mb-2">
                <li>embedUrl: {!! $embedUrl ? '<span class="text-success">OK</span>' : '<span class="text-danger">FALTA</span>' !!}</li>
                <li>accessToken: {!! $accessToken ? '<span class="text-success">OK</span>' : '<span class="text-danger">FALTA</span>' !!}</li>
                <li>reportId: {!! $reportId ? '<span class="text-success">OK</span>' : '<span class="text-danger">FALTA</span>' !!}</li>
            </ul>
            <p class="mb-2">
                Si faltan credenciales o permisos, revisa la configuración de Power BI en
                <a href="{{ route('configuraciones.index') }}" class="alert-link">Configuraciones</a>.
            </p>
            <details>
                <summary>Payload de <code>PowerBiService::generateReportEmbedToken()</code></summary>
                <pre class="mt-2 bg-light p-2 border rounded" style="white-space: pre-wrap;">{{ json_encode($embed ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        </div>
        @php return; @endphp
    @endif

    <div class="pbi-wrapper">
        <div id="reportContainer" aria-live="polite" aria-busy="true">
            <div id="reportLoading">
                <div class="text-center">
                    <div class="spinner-border" role="status" aria-label="{{ __("reports.show.loading") }}"></div>
                    <div class="mt-2">{{ __("reports.show.loading") }}</div>
                </div>
            </div>
            <div id="reportError" class="alert alert-danger d-none" role="alert"></div>
        </div>

        @if(!empty($report->description))
            <div class="mt-3">
                <small class="text-muted">{{ $report->description }}</small>
            </div>
        @endif
    </div>

    @if (! request()->hasCookie('XSRF-TOKEN'))
        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endif
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/powerbi-client@2/dist/powerbi.js"></script>
<script>
(function () {
    'use strict';

    const container   = document.getElementById('reportContainer');
    const loading     = document.getElementById('reportLoading');
    const errorBox    = document.getElementById('reportError');

    const EMBED = {
        embedUrl:    @json($embed['embedUrl'] ?? null),
        accessToken: @json($embed['accessToken'] ?? null),
        reportId:    @json($embed['reportId'] ?? ($report->id ?? null)),
        tokenType:   @json(($embed['tokenType'] ?? 'Embed')),
    };
    const REPORT_UUID    = @json($report->id);
    const WORKSPACE_UUID = @json($workspace->id ?? null);
    // [Minimalista] IDs de capacidad para warmup on-demand desde la vista
    const CAPACITY_DB_ID = @json($capacityModelId ?? null); // PK local para /api/capacities/{id}/ping
    const CAPACITY_UUID  = @json($capacityUuid ?? null);     // UUID informativo (no se usa en el ping)
    // [Minimalista] Parámetros on-demand desde Configuraciones
    const ON_DEMAND_ENABLE = @json($capacityOnDemandEnable ?? true);
    const START_TIMEOUT_MS = @json(($capacityStartTimeoutSec ?? 120) * 1000);
    const START_POLL_MS    = @json(($capacityStartPollSec ?? 5) * 1000);
    const STARTING_MSG     = @json($capacityOnDemandMessage ?? 'Encendiendo la capacidad, aguarde unos segundos…');

    function showError(msg) {
        if (!errorBox) return;
        errorBox.classList.remove('d-none');
        errorBox.textContent = (typeof msg === 'string') ? msg : (msg?.message ?? JSON.stringify(msg));
        if (loading) loading.remove();
        container?.setAttribute('aria-busy', 'false');
    }

    if (!EMBED.embedUrl || !EMBED.accessToken || !EMBED.reportId) {
        return showError('Faltan datos de embedUrl / accessToken / reportId.');
    }
    const models = window['powerbi-client']?.models;
    if (!window.powerbi || !models) {
        return showError('No se pudo cargar powerbi-client.');
    }

    let currentHeight = 0;
    const px = (n) => (typeof n === 'number' && isFinite(n)) ? n : 0;
    const queryH = (sel) => {
        const el = document.querySelector(sel);
        if (!el) return 0;
        const r = el.getBoundingClientRect();
        return px(r.height);
    };
    function fitContainerHeight() {
        const vh = window.innerHeight || document.documentElement.clientHeight || 800;
        const mainHeaderH    = queryH('.main-header');
        const contentHeaderH = queryH('.content-header');
        const mainFooterH    = queryH('.main-footer');
        const contentTopPad = 8, contentBotPad = 8;
        let h = vh - mainHeaderH - contentHeaderH - mainFooterH - contentTopPad - contentBotPad;
        const MIN = 460; if (!isFinite(h) || h < MIN) h = MIN;
        if (Math.abs(h - currentHeight) > 2) {
            currentHeight = h;
            container.style.height = h + 'px';
            try { if (window.report?.resize) window.report.resize(); } catch (_) {}
        }
    }
    const scheduleFit = () => requestAnimationFrame(fitContainerHeight);

    if ('ResizeObserver' in window) {
        const ro = new ResizeObserver(scheduleFit);
        ['.main-header', '.content-header', '.main-footer'].forEach(sel=>{
            const el = document.querySelector(sel);
            if (el) ro.observe(el);
        });
    }
    window.addEventListener('load', scheduleFit, { once: true });
    window.addEventListener('resize', scheduleFit);
    window.addEventListener('orientationchange', scheduleFit);
    document.addEventListener('transitionend', (e) => {
        const c = e.target?.classList;
        if (c && (c.contains('main-sidebar') || c.contains('content-wrapper'))) scheduleFit();
    });

    // [Minimalista] Warmup de capacidad on-demand antes de embeder
    async function waitForCapacityActive(timeoutMs = START_TIMEOUT_MS, intervalMs = START_POLL_MS) {
        if (!ON_DEMAND_ENABLE) return true; // Desactivado en Configuraciones
        if (!CAPACITY_DB_ID) return true; // Nada que hacer si no hay capacidad asociada
        const start = Date.now();
        const url = `/api/capacities/${CAPACITY_DB_ID}/ping`;
        const payload = { resume: true, report_uuid: REPORT_UUID };

        // Chequeo inicial: si ya está activa no mostramos mensaje de encendido
        try {
            const res0 = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token())),
                },
                body: JSON.stringify({ resume: false, report_uuid: REPORT_UUID }),
            });
            if (res0.ok) {
                const j0 = await res0.json().catch(() => ({}));
                if (j0?.capacity?.is_active) {
                    return true;
                }
            }
        } catch (_) { /* continuar y mostrar mensaje */ }

        // Mensaje de encendido
        if (loading) {
            loading.innerHTML = `<div class="text-center"><div class=\"spinner-border\" role=\"status\" aria-label=\"Encendiendo\"></div><div class=\"mt-2\">${STARTING_MSG}</div></div>`;
        }

        while (Date.now() - start < timeoutMs) {
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token())),
                    },
                    body: JSON.stringify(payload),
                });
                if (res.ok) {
                    const json = await res.json().catch(() => ({}));
                    const isActive = !!json?.capacity?.is_active;
                    if (isActive) return true;
                    if (json?.reason === 'outside_schedule_window') {
                        showError('La capacidad está pausada por fuera del horario operativo.');
                        return false;
                    }
                }
            } catch (_) { /* ignorar y reintentar */ }
            await new Promise(r => setTimeout(r, intervalMs));
        }
        showError('No fue posible activar la capacidad a tiempo (timeout).');
        return false;
    }

    async function prewarmThenEmbed() {
        const ok = await waitForCapacityActive();
        if (!ok) return null; // error ya mostrado
        if (loading) {
            loading.innerHTML = `<div class=\"text-center\"><div class=\"spinner-border\" role=\"status\" aria-label=\"Cargando\"></div><div class=\"mt-2\">{{ __("reports.show.loading") }}</div></div>`;
        }
        try { powerbi.reset(container); } catch (_) {}
        const rpt = doEmbed();
        // Arrancamos el timeout de carga recién cuando comenzamos a embeber
        loaded = false;
        if (loadTimer) { try { clearTimeout(loadTimer); } catch (_) {} loadTimer = null; }
        loadTimer = setTimeout(() => {
            if (loaded) return;
            try { powerbi.reset(container); } catch (_) {}
            showError('El reporte tardó demasiado en cargar (timeout 10s). Verifica la red, el token o la capacidad.');
            postAudit('error', { error_message: 'load_timeout_10s' });
        }, LOAD_TIMEOUT_MS);
        return rpt;
    }

    // Encapsulamos el embed en función reutilizable
    function doEmbed() {
        try { powerbi.reset(container); } catch (_) {}

        const config = {
        type: 'report',
        id: EMBED.reportId,
        embedUrl: EMBED.embedUrl,
        accessToken: EMBED.accessToken,
        tokenType: (EMBED.tokenType && models.TokenType[EMBED.tokenType]) ? models.TokenType[EMBED.tokenType] : models.TokenType.Embed,
        permissions: models.Permissions.All,
        settings: {
            layoutType: models.LayoutType.Responsive,
            displayOption: models.DisplayOption.FitToWidth,
            background: models.BackgroundType.Default,
            panes: {
                filters: { visible: true, expanded: false },
                pageNavigation: { visible: true }
            }
        }
        };

        const report = powerbi.embed(container, config);
        window.report = report; // deja referencia global
        return report;
    }

    scheduleFit();
    setTimeout(scheduleFit, 60);

    let loaded = false;
    const LOAD_TIMEOUT_MS = @json(($reportLoadTimeoutSec ?? 10) * 1000);
    let loadTimer = null; // Inicia al embeber, no durante warmup

    // ====== Solo 1 "view" al cargar ======
    function attachListeners(rpt) {
    rpt.on('loaded', async () => {
        loaded = true;
        clearTimeout(loadTimer);
        if (loading) loading.remove();
        container?.setAttribute('aria-busy', 'false');
        scheduleFit();

        postAudit('view', await snapshotState('after_loaded'));
    });

    // Render (telemetría auxiliar)
    rpt.on('rendered', async () => {
        scheduleFit();
        postAudit('render', await snapshotState('after_rendered'));
    });

    rpt.on('error', (event) => {
        if (loadTimer) { try { clearTimeout(loadTimer); } catch (_) {} loadTimer = null; }
        const detail = event?.detail;
        const msg = (typeof detail === 'string') ? detail : (detail?.message || 'Error al renderizar el reporte');
        showError(msg);
        postAudit('error', { error_message: String(msg) });
    });

    // Otros eventos
    rpt.on('pageChanged', async (ev) => {
        const page = ev?.detail?.newPage || ev?.detail;
        const pageName = page?.name || page?.displayName || null;
        postAudit('page_change', await snapshotState('page_changed', { page_name: pageName }));
    });

    rpt.on('filtersApplied', async () => {
        postAudit('filter_change', await snapshotState('filters_applied'));
    });

    rpt.on('bookmarkApplied', async (ev) => {
        const bookmark = ev?.detail?.name || ev?.detail?.displayName || null;
        postAudit('bookmark', await snapshotState('bookmark_applied', { bookmark }));
    });
    }

    // [Minimalista] Orquestación principal: warmup (si aplica) y embed con listeners
    (async () => {
        const rpt = await prewarmThenEmbed();
        if (rpt) attachListeners(rpt);
    })();

    try {
        if (window.report && typeof window.report.on === 'function') {
            window.report.on('dataSelected', async (ev) => {
                const visual = ev?.detail?.visual || {};
                const visualInfo = { title: visual?.title || null, type: visual?.type || null, name: visual?.name || null };
                postAudit('click', await snapshotState('data_selected', { visual: visualInfo }));
            });
        }
    } catch (_) {}

    // ====== Auditoría helpers ======
    const debouncers = new Map();
    function debounceByKey(key, fn, wait = 800) {
        if (debouncers.has(key)) clearTimeout(debouncers.get(key));
        const t = setTimeout(fn, wait);
        debouncers.set(key, t);
    }
    async function safePostJson(url, payload, timeoutMs = 5000) {
        const controller = new AbortController();
        const t = setTimeout(() => controller.abort(), timeoutMs);
        try {
            const res = await fetch(url, {
                method: 'POST',
                signal: controller.signal,
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token())),
                },
                body: JSON.stringify(payload),
            });
            clearTimeout(t);
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return await res.json().catch(() => ({}));
        } catch (e) {
            clearTimeout(t);
            await new Promise(r => setTimeout(r, 500));
            const controller2 = new AbortController();
            const t2 = setTimeout(() => controller2.abort(), timeoutMs);
            try {
                const res2 = await fetch(url, {
                    method: 'POST',
                    signal: controller2.signal,
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]')?.content || @json(csrf_token())),
                    },
                    body: JSON.stringify(payload),
                });
                clearTimeout(t2);
                if (!res2.ok) throw new Error('HTTP ' + res2.status);
                return await res2.json().catch(() => ({}));
            } catch (_) {
                clearTimeout(t2);
                return { ok: false };
            }
        }
    }
    function postAudit(action, extra = {}) {
        const payload = {
            report_uuid:    REPORT_UUID,
            workspace_uuid: WORKSPACE_UUID,
            action,
            success: action !== 'error',
            occurred_at: new Date().toISOString(),
            ...extra
        };
        debounceByKey('audit:'+action, () => {
            safePostJson(@json(route('api.audit.store')), payload);
        }, 600);
    }
    async function snapshotState(reason = null, patch = {}) {
        try {
            const page   = await activePageSafe();
            const filtersReport = await report.getFilters().catch(() => []);
            let filtersVisuals = [];
            try {
                const visuals = await page.getVisuals();
                const take = visuals.slice(0, 6);
                const arr = await Promise.all(take.map(async v => {
                    try {
                        const vf = await v.getFilters();
                        if (!vf || !vf.length) return null;
                        return { visual: { name: v.name, title: v.title, type: v.type }, filters: serializeFilters(vf) };
                    } catch (_) { return null; }
                }));
                filtersVisuals = arr.filter(Boolean);
            } catch (_) {}

            return {
                page_name: page?.name || null,
                bookmark: null,
                filters: { report: serializeFilters(filtersReport), visuals: filtersVisuals },
                reason,
                ...patch
            };
        } catch (_) {
            return { reason, ...patch };
        }
    }
    async function activePageSafe() {
        try {
            const pgs = await report.getPages();
            const active = pgs.find(p => p.isActive);
            return active || pgs[0] || null;
        } catch (_) { return null; }
    }
    function serializeFilters(filters) {
        return (filters || []).map(f => {
            const base = {
                $schema: f && f.$schema ? 'powerbi.schema' : undefined,
                target: f?.target ? {
                    table: f.target.table, column: f.target.column,
                    hierarchy: f.target.hierarchy, level: f.target.level, measure: f.target.measure
                } : undefined,
                filterType: f?.filterType, operator: f?.operator, isAdvanced: f?.isAdvanced || false
            };
            if (Array.isArray(f?.values)) base.values = sampleValues(f.values, 30);
            if (Array.isArray(f?.whereItems)) base.whereItems = f.whereItems.slice(0, 10);
            if (f?.lower && f?.upper) base.range = { lower: f.lower, upper: f.upper };
            return JSON.parse(JSON.stringify(base));
        });
    }
    function sampleValues(values, max = 30) {
        if (!Array.isArray(values)) return [];
        if (values.length <= max) return values;
        const step = Math.max(1, Math.floor(values.length / max));
        const out = [];
        for (let i = 0; i < values.length && out.length < max; i += step) out.push(values[i]);
        return out;
    }

    // No mandamos 'open' (evita doble acceso)
    // window.addEventListener('load', () => postAudit('open'), { once: true });

    // 'close' informativo
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
            } else { postAudit('close'); }
        } catch (_) {}
    });
})();
</script>
@endsection
