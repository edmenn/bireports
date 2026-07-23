@extends('layouts.app')

@section('title', $currentReport?->name ?? $application->name)

@section('css')
    @include('portal.partials.base-styles')
    @include('portal.partials.home-card-styles')
<style>
    :root {
        --pbi-min-height: 460px; /* Minimum height for Power BI tabs visibility */
    }

    html, body, .content-wrapper, .content, .container-fluid {
        height: 100%;
    }

    .pbi-wrapper {
        position: relative;
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 120px);
        overflow: hidden;
    }

    .pbi-wrapper.fullscreen-active,
    .pbi-wrapper:fullscreen,
    .pbi-wrapper:-webkit-full-screen {
        position: fixed;
        inset: 0;
        z-index: 1050;
        background-color: #000;
        padding: 0;
        box-sizing: border-box;
    }

    .pbi-wrapper.fullscreen-active #reportContainer,
    .pbi-wrapper:fullscreen #reportContainer,
    .pbi-wrapper:-webkit-full-screen #reportContainer {
        height: 100%;
        min-height: 100%;
    }

    body.fullscreen-scroll-lock {
        overflow: hidden !important;
    }

    .fullscreen-gesture-hint {
        position: absolute;
        top: 1rem;
        left: 50%;
        transform: translate(-50%, -0.5rem);
        padding: .5rem .9rem;
        border-radius: .75rem;
        background: rgba(0, 0, 0, 0.8);
        color: #fff;
        font-size: .875rem;
        line-height: 1.2;
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease, transform .2s ease;
        z-index: 1060;
        max-width: 90%;
        text-align: center;
    }

    .fullscreen-gesture-hint.visible {
        opacity: 1;
        transform: translate(-50%, 0);
    }

    #reportContainer {
        position: relative;
        width: 100%;
        height: var(--pbi-min-height);
        min-height: var(--pbi-min-height);
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
        left: 1rem;
        right: 1rem;
        top: 1rem;
        z-index: 3;
    }

    /* ======================================== */
    /* Portal runtime layout tweaks              */
    /* ======================================== */
    .portal-application-runtime .content-wrapper,
    .portal-application-runtime .content,
    .portal-application-runtime .container-fluid {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    .portal-application-runtime .content-header {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }

    .portal-application-runtime .content {
        padding-top: 0 !important;
    }

    .portal-application-runtime .container-fluid {
        padding-top: 0 !important;
    }

    .portal-app-card .card-body {
        padding: 1.25rem;
    }

    .portal-app-card .pbi-wrapper {
        border: 1px solid rgba(15, 34, 58, .08);
        border-radius: .75rem;
        background-color: #fff;
        padding: 1.25rem;
        box-shadow: inset 0 0 0 rgba(0, 0, 0, 0);
    }

    .portal-app-card .pbi-wrapper.fullscreen-active,
    .portal-app-card .pbi-wrapper:fullscreen,
    .portal-app-card .pbi-wrapper:-webkit-full-screen {
        border-radius: 0;
        border-color: transparent;
        padding: 0;
    }

    /* ======================================== */
    /* Sidebar navigation adjustments            */
    /* ======================================== */
    .nav-sidebar[data-portal-app-nav="1"] .nav-header {
        font-size: 0.75rem;
        letter-spacing: .05em;
    }

    .nav-sidebar[data-portal-app-nav="1"] .nav-header.app-title {
        font-size: 1rem;
        font-weight: 700;
        text-transform: none;
        letter-spacing: 0;
    }

    .nav-sidebar[data-portal-app-nav="1"] .nav-treeview > .nav-item > .nav-link {
        padding-left: 2.75rem;
    }

    .nav-sidebar[data-portal-app-nav="1"] .nav-treeview > .nav-item > .nav-link p {
        font-size: 0.8rem;
        margin: 0;
    }
</style>
@endsection


@section('content_header')
    <x-page-header :title="$currentReport?->name ?? $application->name" icon="fas fa-chart-line">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            @if($currentReport)
                <button type="button"
                        id="reportFullscreenBtn"
                        class="btn btn-primary btn-sm header-btn"
                        data-label-enter="{{ __('reports.show.fullscreen_enter') }}"
                        data-label-exit="{{ __('reports.show.fullscreen_exit') }}"
                        title="{{ __('reports.show.fullscreen_enter') }}"
                        aria-label="{{ __('reports.show.fullscreen_enter') }}">
                    <i class="fas fa-expand"></i>
                    <span>{{ __('reports.show.fullscreen_enter') }}</span>
                </button>
            @endif
        </div>
    </x-page-header>
@stop

@section('content')
    @php
        $sidebarNav = [
            'applicationName' => $application->name,
            'activeReportId' => optional($currentReport)->id,
            'sections' => $sections->map(function ($section) use ($application) {
                $reports = $section->reports->map(function ($report) use ($application) {
                    return [
                        'id' => $report->id,
                        'name' => $report->name,
                        'url' => route('portal.applications.show', ['application' => $application, 'report' => $report->id]),
                    ];
                })->values()->all();

                return [
                    'id' => $section->id,
                    'name' => $section->name,
                    'reports' => $reports,
                ];
            })->values()->all(),
            'looseReports' => $looseReports->map(function ($report) use ($application) {
                return [
                    'id' => $report->id,
                    'name' => $report->name,
                    'url' => route('portal.applications.show', ['application' => $application, 'report' => $report->id]),
                ];
            })->values()->all(),
            'labels' => [
                'otherReports' => __('applications.runtime.other_reports'),
            ],
        ];
    @endphp

    <div class="portal-card portal-app-card">
        <div class="card-header">
            <span class="portal-icon">
                <i class="fas fa-chart-line"></i>
            </span>
            <div>
                <h5 class="mb-0">{{ $application->name }}</h5>
                @if($currentReport)
                    <small class="text-muted">{{ $currentReport->name }}</small>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if (! $currentReport)
                <div class="portal-empty-state">
                    <i class="fas fa-info-circle"></i>
                    <div>{{ __('applications.runtime.no_reports') }}</div>
                </div>
            @else
                <div class="pbi-wrapper" data-gesture-hint="{{ __('reports.show.fullscreen_triple_tap_hint') }}">
                    @if($error)
                        <div class="alert alert-danger" role="alert">
                            <h5 class="mb-2"><i class="fas fa-times-circle me-2"></i>{{ $error }}</h5>
                            @if($error_debug)
                                <details class="mt-2">
                                    <summary>{{ __('applications.runtime.technical_details') }}</summary>
                                    <pre class="mt-2 bg-light p-2 border rounded" style="white-space: pre-wrap;">{{ $error_debug }}</pre>
                                </details>
                            @endif
                        </div>
                    @endif

                    <div id="reportContainer" aria-live="polite" aria-busy="true">
                        <div id="reportLoading">
                            <div class="text-center">
                                <div class="spinner-border" role="status" aria-label="{{ __('reports.show.loading') }}"></div>
                                <div class="mt-2">{{ __('reports.show.loading') }}</div>
                            </div>
                        </div>
                        <div id="reportError" class="alert alert-danger d-none" role="alert"></div>
                    </div>

                    @if(!empty($currentReport->description))
                        <div class="mt-3">
                            <small class="text-muted">{{ $currentReport->description }}</small>
                        </div>
                    @endif
                </div>
            @endif
        </div>
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

    document.documentElement.classList.add('portal-application-runtime');
    document.body.classList.add('portal-application-runtime');
    document.querySelector('.content-wrapper')?.classList.add('application-content-wrapper');
    document.querySelector('.content')?.classList.add('application-content');

    const sidebarData = @json($sidebarNav);

    (function initDynamicSidebar(){
        const nav = document.querySelector('.main-sidebar .nav-sidebar');
        const hasSections = Array.isArray(sidebarData.sections) && sidebarData.sections.some(s => Array.isArray(s.reports) && s.reports.length > 0);
        const hasLoose = Array.isArray(sidebarData.looseReports) && sidebarData.looseReports.length > 0;
        if (!(nav && (hasSections || hasLoose))) return;

        const activeId = sidebarData.activeReportId ? String(sidebarData.activeReportId) : null;
        nav.innerHTML = '';
        nav.setAttribute('data-portal-app-nav', '1');
        if (!nav.hasAttribute('data-widget')) nav.setAttribute('data-widget', 'treeview');
        nav.setAttribute('data-accordion', 'false');
        nav.classList.add('nav-child-indent');

        const makeHeader = (text) => {
            const li = document.createElement('li');
            li.className = 'nav-header text-uppercase';
            li.textContent = text;
            return li;
        };
        const makeReportItem = (report) => {
            const li = document.createElement('li'); li.className = 'nav-item';
            const link = document.createElement('a'); link.className = 'nav-link'; link.href = report.url;
            const label = document.createElement('p'); label.textContent = report.name;
            link.appendChild(label);
            if (activeId && String(report.id) === activeId) link.classList.add('active');
            li.appendChild(link); return li;
        };
        const makeSectionNode = (section) => {
            const li = document.createElement('li'); li.className = 'nav-item has-treeview';
            const link = document.createElement('a'); link.className = 'nav-link'; link.href = '#'; link.setAttribute('role', 'button'); link.setAttribute('tabindex', '0');
            const icon = document.createElement('i'); icon.className = 'nav-icon fas fa-folder';
            const angle = document.createElement('i'); angle.className = 'right fas fa-angle-left';
            const label = document.createElement('p'); label.textContent = section.name; label.appendChild(angle);
            link.appendChild(icon); link.appendChild(label);
            const list = document.createElement('ul'); list.className = 'nav nav-treeview';
            (section.reports || []).forEach(r => list.appendChild(makeReportItem(r)));

            const isActive = section.reports?.some(r => activeId && String(r.id) === activeId) ?? false;
            const initiallyOpen = isActive || section.forceOpen;
            if (initiallyOpen) { li.classList.add('menu-open'); link.classList.add('active'); link.setAttribute('aria-expanded', 'true'); list.style.display = 'block'; }
            else { link.setAttribute('aria-expanded', 'false'); list.style.display = 'none'; }

            li.appendChild(link); li.appendChild(list); return li;
        };

        if (sidebarData.applicationName) {
            const title = makeHeader(sidebarData.applicationName);
            title.classList.add('app-title');
            nav.appendChild(title);
        }
        (sidebarData.sections || []).forEach((section) => {
            if (Array.isArray(section.reports) && section.reports.length) nav.appendChild(makeSectionNode(section));
        });
        if (hasLoose) {
            const label = sidebarData.labels?.otherReports || @json(__('applications.runtime.other_reports'));
            nav.appendChild(makeSectionNode({
                name: label,
                reports: sidebarData.looseReports,
                forceOpen: sidebarData.looseReports?.some(r => activeId && String(r.id) === activeId),
            }));
        }

        const toggles = nav.querySelectorAll('li.nav-item.has-treeview > a.nav-link');
        toggles.forEach((toggle) => {
            const parent = toggle.parentElement;
            const submenu = parent.querySelector(':scope > .nav-treeview');
            const onToggle = (ev) => {
                ev.preventDefault(); ev.stopImmediatePropagation();
                const willOpen = !parent.classList.contains('menu-open');
                parent.classList.toggle('menu-open', willOpen);
                toggle.classList.toggle('active', willOpen);
                toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                if (submenu) submenu.style.display = willOpen ? 'block' : 'none';
            };
            toggle.addEventListener('click', onToggle);
            toggle.addEventListener('keydown', (ev) => {
                if (ev.key === 'Enter' || ev.key === ' ') onToggle(ev);
            });
        });
    })();

    const hasReport = @json((bool) $currentReport);
    if (!hasReport) {
        return;
    }

    const wrapper     = document.querySelector('.pbi-wrapper');
    const fullscreenBtn = document.getElementById('reportFullscreenBtn');

    if (wrapper && fullscreenBtn) {
        const labelEnter = fullscreenBtn.dataset.labelEnter || fullscreenBtn.getAttribute('title') || '';
        const labelExit  = fullscreenBtn.dataset.labelExit || labelEnter;
        const gestureHint = wrapper.dataset.gestureHint || '';
        const getFsElement = () => document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement || null;
        const isWrapperActive = () => {
            const fsEl = getFsElement();
            return fsEl === wrapper || wrapper.classList.contains('fullscreen-active');
        };
        let gestureHintEl = null;
        let gestureHintTimeout = null;
        let gestureTapTimer = null;
        let gestureTapCount = 0;
        let gestureLastTap = 0;
        let wasFullscreenActive = false;
        const TRIPLE_TAP_WINDOW = 550;
        const ensureGestureHintEl = () => {
            if (!gestureHintEl) {
                gestureHintEl = document.createElement('div');
                gestureHintEl.className = 'fullscreen-gesture-hint';
                gestureHintEl.setAttribute('role', 'status');
                gestureHintEl.setAttribute('aria-live', 'polite');
                wrapper.appendChild(gestureHintEl);
            }
            return gestureHintEl;
        };
        const hideGestureHint = () => {
            if (gestureHintEl) {
                gestureHintEl.classList.remove('visible');
            }
            if (gestureHintTimeout) {
                clearTimeout(gestureHintTimeout);
                gestureHintTimeout = null;
            }
        };
        const showGestureHint = () => {
            if (!gestureHint) return;
            const el = ensureGestureHintEl();
            el.textContent = gestureHint;
            el.classList.add('visible');
            if (gestureHintTimeout) clearTimeout(gestureHintTimeout);
            gestureHintTimeout = setTimeout(() => {
                hideGestureHint();
            }, 3000);
        };
        const resetTapState = () => {
            gestureTapCount = 0;
            gestureLastTap = 0;
            if (gestureTapTimer) {
                clearTimeout(gestureTapTimer);
                gestureTapTimer = null;
            }
        };
        const exitFs = () => {
            if (document.exitFullscreen) return document.exitFullscreen();
            if (document.webkitExitFullscreen) return document.webkitExitFullscreen();
            if (document.mozCancelFullScreen) return document.mozCancelFullScreen();
            if (document.msExitFullscreen) return document.msExitFullscreen();
            return Promise.resolve();
        };
        const setFallback = (active) => {
            wrapper.classList.toggle('fullscreen-active', active);
            document.body.classList.toggle('fullscreen-scroll-lock', active);
        };
        const syncFsState = () => {
            const fsEl = getFsElement();
            const isApiActive = fsEl === wrapper;
            const isFallback = wrapper.classList.contains('fullscreen-active');
            const active = isApiActive || isFallback;
            const nextLabel = active ? labelExit : labelEnter;
            fullscreenBtn.setAttribute('title', nextLabel);
            fullscreenBtn.setAttribute('aria-label', nextLabel);
            fullscreenBtn.classList.toggle('d-none', active);
            if (active) {
                if (!wasFullscreenActive) {
                    showGestureHint();
                }
            } else {
                hideGestureHint();
                resetTapState();
            }
            wasFullscreenActive = active;
        };
        const enterFs = async () => {
            const request = wrapper.requestFullscreen || wrapper.webkitRequestFullscreen || wrapper.mozRequestFullScreen || wrapper.msRequestFullscreen;
            if (!request) {
                setFallback(true);
                syncFsState();
                return;
            }
            try {
                const result = request.call(wrapper, { navigationUI: 'hide' });
                if (result && typeof result.then === 'function') {
                    await result;
                }
            } catch (_) {
                setFallback(true);
            }
            syncFsState();
        };
        const leaveFs = async () => {
            try { await exitFs(); }
            catch (_) {}
            setFallback(false);
            syncFsState();
        };
        const registerTap = () => {
            if (!isWrapperActive()) return;
            const now = Date.now();
            if (now - gestureLastTap > TRIPLE_TAP_WINDOW) {
                gestureTapCount = 0;
            }
            gestureTapCount += 1;
            gestureLastTap = now;
            if (gestureTapTimer) clearTimeout(gestureTapTimer);
            gestureTapTimer = setTimeout(() => resetTapState(), TRIPLE_TAP_WINDOW);
            if (gestureTapCount >= 3) {
                resetTapState();
                leaveFs();
            }
        };
        if (window.PointerEvent) {
            wrapper.addEventListener('pointerdown', (ev) => {
                if (ev.pointerType !== 'touch') return;
                registerTap();
            });
        } else {
            wrapper.addEventListener('touchend', () => {
                registerTap();
            });
        }
        fullscreenBtn.addEventListener('click', async () => {
            const fsEl = getFsElement();
            const isFallback = wrapper.classList.contains('fullscreen-active');
            if (fsEl === wrapper || isFallback) {
                await leaveFs();
                return;
            }
            if (fsEl && fsEl !== wrapper) {
                await exitFs().catch(() => {});
            }
            await enterFs();
        });
        const onFsChange = () => {
            const fsEl = getFsElement();
            if (fsEl !== wrapper) {
                setFallback(wrapper.classList.contains('fullscreen-active'));
            }
            syncFsState();
        };
        ['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange', 'MSFullscreenChange'].forEach((evt) => {
            document.addEventListener(evt, onFsChange);
        });
        document.addEventListener('keydown', (ev) => {
            if (ev.key === 'Escape' && wrapper.classList.contains('fullscreen-active')) {
                setFallback(false);
                syncFsState();
            }
        });
        syncFsState();
    }

    const container   = document.getElementById('reportContainer');
    const loading     = document.getElementById('reportLoading');
    const errorBox    = document.getElementById('reportError');

    if (!container) {
        return;
    }

    const EMBED = {
        embedUrl:    @json($embed['embedUrl'] ?? null),
        accessToken: @json($embed['accessToken'] ?? null),
        reportId:    @json($embed['reportId'] ?? ($currentReport->id ?? null)),
        tokenType:   @json($embed['tokenType'] ?? 'Embed'),
    };
    const REPORT_UUID    = @json($currentReport->id ?? null);
    const WORKSPACE_UUID = @json($workspace->id ?? null);
    const CAPACITY_DB_ID = @json($capacityModelId ?? null);
    const CAPACITY_UUID  = @json($capacityUuid ?? null);
    const ON_DEMAND_ENABLE = @json($capacityOnDemandEnable ?? true);
    const START_TIMEOUT_MS = @json(($capacityStartTimeoutSec ?? 120) * 1000);
    const START_POLL_MS    = @json(($capacityStartPollSec ?? 5) * 1000);
    const STARTING_MSG     = @json($capacityOnDemandMessage ?? __('reports.show.on_demand.starting'));

    function showError(msg) {
        if (!errorBox) return;
        errorBox.classList.remove('d-none');
        errorBox.textContent = (typeof msg === 'string') ? msg : (msg?.message ?? JSON.stringify(msg));
        if (loading) loading.remove();
        container?.setAttribute('aria-busy', 'false');
    }

    if (!EMBED.embedUrl || !EMBED.accessToken || !EMBED.reportId) {
        return showError(@json(__('reports.show.errors.missing_fields')));
    }
    const models = window['powerbi-client']?.models;
    if (!window.powerbi || !models) {
        return showError(@json(__('reports.show.errors.client_library')));
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

    scheduleFit();
    setTimeout(scheduleFit, 60);

    let loaded = false;
    const LOAD_TIMEOUT_MS = @json(($reportLoadTimeoutSec ?? 10) * 1000);
    let loadTimer = null;

    async function waitForCapacityActive(timeoutMs = START_TIMEOUT_MS, intervalMs = START_POLL_MS) {
        if (!ON_DEMAND_ENABLE) return true;
        if (!CAPACITY_DB_ID) return true;
        const start = Date.now();
        const url = `/api/capacities/${CAPACITY_DB_ID}/ping`;
        const payload = { resume: true, report_uuid: REPORT_UUID };

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
        } catch (_) {}

        if (loading) {
            loading.innerHTML = `<div class="text-center"><div class="spinner-border" role="status" aria-label="${STARTING_MSG}"></div><div class="mt-2">${STARTING_MSG}</div></div>`;
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
                        showError(@json(__('reports.show.errors.capacity_paused')));
                        return false;
                    }
                }
            } catch (_) {}
            await new Promise(r => setTimeout(r, intervalMs));
        }
        showError(@json(__('reports.show.errors.capacity_timeout')));
        return false;
    }

    async function prewarmThenEmbed() {
        const ok = await waitForCapacityActive();
        if (!ok) return null;
        if (loading) {
            loading.innerHTML = `<div class="text-center"><div class="spinner-border" role="status" aria-label="{{ __('reports.show.loading') }}"></div><div class="mt-2">{{ __('reports.show.loading') }}</div></div>`;
        }
        try { powerbi.reset(container); } catch (_) {}
        const rpt = doEmbed();
        loaded = false;
        if (loadTimer) { try { clearTimeout(loadTimer); } catch (_) {} loadTimer = null; }
        loadTimer = setTimeout(() => {
            if (loaded) return;
            try { powerbi.reset(container); } catch (_) {}
            showError(@json(__('reports.show.errors.load_timeout')));
            postAudit('error', { error_message: 'load_timeout_10s' });
        }, LOAD_TIMEOUT_MS);
        return rpt;
    }

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
        window.report = report;
        return report;
    }

    function attachListeners(rpt) {
        rpt.on('loaded', async () => {
            loaded = true;
            clearTimeout(loadTimer);
            if (loading) loading.remove();
            container?.setAttribute('aria-busy', 'false');
            scheduleFit();

            postAudit('view', await snapshotState('after_loaded'));
        });

        rpt.on('rendered', async () => {
            scheduleFit();
            postAudit('render', await snapshotState('after_rendered'));
        });

        rpt.on('error', (event) => {
            if (loadTimer) { try { clearTimeout(loadTimer); } catch (_) {} loadTimer = null; }
            const detail = event?.detail;
            const msg = (typeof detail === 'string') ? detail : (detail?.message || @json(__('applications.runtime.errors.render_failed')));
            showError(msg);
            postAudit('error', { error_message: String(msg) });
        });

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
        if (!REPORT_UUID) return;
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
            const filtersReport = await window.report?.getFilters().catch(() => []);
            let filtersVisuals = [];
            try {
                const visuals = await window.report?.getVisuals();
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
            const pgs = await window.report?.getPages();
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
