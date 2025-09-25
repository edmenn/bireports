@extends('adminlte::page')

@section('title', $application->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-0">{{ $application->name }}</h1>
            @if(!empty($application->description))
                <small class="text-muted">{{ $application->description }}</small>
            @else
                <small class="text-muted">{{ __('applications.runtime.subtitle') }}</small>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('portal.apps') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('applications.actions.exit_application') }}
            </a>
            <a href="{{ route('portal.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-home me-1"></i> {{ __('applications.actions.go_home') }}
            </a>
        </div>
    </div>
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

    @if(!$currentReport)
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-info-circle me-1"></i> {{ __('applications.runtime.no_reports') }}
        </div>
    @else
        <div class="app-wrapper">
            <div id="appContainer" aria-live="polite" aria-busy="true">
                <div id="appLoading">
                    <div class="text-center">
                        <div class="spinner-border" role="status" aria-label="{{ __('reports.show.loading') }}"></div>
                        <div class="mt-2">{{ __('reports.show.loading') }}</div>
                    </div>
                </div>
                <div id="appError" class="alert alert-danger d-none" role="alert"></div>

                <iframe
                    id="appFrame"
                    src="{{ $iframeUrl }}"
                    title="{{ $currentReport->name }}"
                    allowfullscreen
                    frameborder="0"></iframe>
            </div>

            @if(!empty($currentReport->description))
                <div class="mt-3">
                    <small class="text-muted">{{ $currentReport->description }}</small>
                </div>
            @endif
        </div>
    @endif
@stop

@push('css')
<style>
:root { --app-viewport-h: 100vh; }

.preloader { display: none !important; }

/* Alturas al 100% para calcular bien el viewport */
html.portal-application-runtime,
html.portal-application-runtime body,
body.portal-application-runtime .content-wrapper,
body.portal-application-runtime .content,
body.portal-application-runtime .container-fluid { height: 100%; }

/* ✅ Mantené el scroll principal de la página */
body.portal-application-runtime { overflow: auto !important; }

/* No forzar min-heights que introducen scroll intermedio */
.application-layout,
.application-viewer,
.app-wrapper {
    display: flex;
    flex-direction: column;
    min-height: 0 !important;
    height: auto !important;
    overflow: visible !important;
    background: transparent;
    box-shadow: none;
    border-radius: 0;
}
.application-content { padding: 0; }
.application-content-wrapper { padding-top: 0; }

/* Contenedor del iframe: toma altura exacta y NO scrollea él */
#appContainer{
    position: relative;
    width: 100%;
    height: var(--app-viewport-h);
    min-height: 0 !important;
    overflow: hidden; /* ← elimina la barra del contenedor (scroll intermedio) */
    background: transparent;
}

/* Iframe ocupa todo el contenedor */
#appFrame{
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* Overlays */
#appLoading, #appError{ position: absolute; inset: 0; z-index: 3; }
#appLoading{
    display:flex; align-items:center; justify-content:center;
    background: transparent; color:#666;
}
#appError{ left:1rem; right:1rem; top:1rem; }

/* Sidebar dinámico (tus estilos) */
.portal-application-runtime .content-wrapper { padding-top: 0 !important; padding-bottom: 0 !important; }
.portal-application-runtime .content-wrapper { padding-top: 0 !important; padding-bottom: 0 !important; }
.portal-application-runtime .content { padding-top: 0 !important; padding-bottom: 0 !important; }
.portal-application-runtime .container-fluid { padding-top: 0 !important; padding-bottom: 0 !important; }
.nav-sidebar[data-portal-app-nav="1"] .nav-header { font-size: 0.75rem; letter-spacing: .05em; }
.nav-sidebar[data-portal-app-nav="1"] .nav-header.app-title { font-size: 1rem; font-weight: 700; text-transform: none; letter-spacing: 0; }
.nav-sidebar[data-portal-app-nav="1"] .nav-treeview > .nav-item > .nav-link { padding-left: 2.75rem; }
.nav-sidebar[data-portal-app-nav="1"] .nav-treeview > .nav-item > .nav-link p { font-size: 0.8rem; margin: 0; }

/* === Quitar el “bache” entre header y contenido === */
.content-header{
  margin-bottom: 0 !important;
  padding-bottom: 0 !important;
}
.content{
  padding-top: 0 !important;   /* AdminLTE mete padding arriba */
}
.content .container-fluid{
  padding-top: 0 !important;   /* y Bootstrap suma otro */
}
/* Por si algún wrapper trae margen colapsando hacia arriba */
.app-wrapper, #appContainer{
  margin-top: 0 !important;
}

/* (opcional) si ves una línea fina arriba, quita cualquier borde */
#appContainer{ border-top: 0 !important; }

</style>
@endpush

@push('js')
<script>
    window.portalAppNav = @json($sidebarNav);
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const data = window.portalAppNav;

    /* Marcas de runtime para aplicar CSS/altura */
    document.documentElement.classList.add('portal-application-runtime');
    document.body.classList.add('portal-application-runtime');
    document.querySelector('.content-wrapper')?.classList.add('application-content-wrapper');
    document.querySelector('.content')?.classList.add('application-content');

    /* ====== Sidebar dinámico ====== */
    (function initDynamicSidebar(){
        if (!data) return;
        const nav = document.querySelector('.main-sidebar .nav-sidebar');
        const hasSections = Array.isArray(data.sections) && data.sections.some(s => Array.isArray(s.reports) && s.reports.length > 0);
        const hasLoose = Array.isArray(data.looseReports) && data.looseReports.length > 0;
        if (!(nav && (hasSections || hasLoose))) return;

        const activeId = data.activeReportId ? String(data.activeReportId) : null;
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

        if (data.applicationName) {
            const title = makeHeader(data.applicationName);
            title.classList.add('app-title');
            nav.appendChild(title);
        }
        (data.sections || []).forEach((section) => {
            if (Array.isArray(section.reports) && section.reports.length) nav.appendChild(makeSectionNode(section));
        });
        if (hasLoose) {
            const label = data.labels?.otherReports || 'Otros';
            nav.appendChild(makeSectionNode({
                name: label,
                reports: data.looseReports,
                forceOpen: data.looseReports?.some(r => activeId && String(r.id) === activeId),
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

    /* ====== Altura EXACTA a pantalla ====== */
    const container = document.getElementById('appContainer');
    const iframe    = document.getElementById('appFrame');
    const loading   = document.getElementById('appLoading');
    const errorBox  = document.getElementById('appError');

    const px = (n) => (typeof n === 'number' && isFinite(n)) ? n : 0;
    const hOf = (sel) => {
        const el = document.querySelector(sel);
        if (!el) return 0;
        const r = el.getBoundingClientRect();
        return px(r.height);
    };

    // ========= FUNCIÓN CORREGIDA =========
    function fitToViewport(){
        const vh = window.innerHeight || document.documentElement.clientHeight || 800;
        const headerH  = hOf('.main-header');
        const cHeaderH = hOf('.content-header');
        const footerH  = hOf('.main-footer');
        
        // Añadimos la corrección por el padding del contenedor principal
        const contentPaddingY = 16; // 8px arriba + 8px abajo aprox.

        let H = vh - headerH - cHeaderH - footerH - contentPaddingY;

        // Un alto mínimo para evitar colapsos
        if (!isFinite(H) || H < 400) H = 400;

        document.documentElement.style.setProperty('--app-viewport-h', H + 'px');
        if (container) container.style.height = H + 'px';
    }
    // =====================================

    const scheduleFit = () => requestAnimationFrame(fitToViewport);

    if ('ResizeObserver' in window) {
        const ro = new ResizeObserver(scheduleFit);
        ['.main-header', '.content-header', '.main-footer'].forEach(sel=>{
            const el = document.querySelector(sel);
            if (el) ro.observe(el);
        });
    }
    window.addEventListener('load', scheduleFit, { once:true });
    window.addEventListener('resize', scheduleFit);
    window.addEventListener('orientationchange', scheduleFit);
    document.addEventListener('transitionend', (e) => {
        const c = e.target?.classList;
        if (c && (c.contains('main-sidebar') || c.contains('content-wrapper'))) scheduleFit();
    });
    scheduleFit();
    setTimeout(scheduleFit, 60);

    /* ====== Loading/errores ====== */
    let loaded = false;
    let loadTimer = setTimeout(() => {
        if (loaded) return;
        errorBox?.classList.remove('d-none');
        errorBox && (errorBox.textContent = 'El contenido tardó demasiado en cargar (timeout). Revisá red/origen o X-Frame-Options.');
        loading && loading.remove();
    }, 10000);

    iframe?.addEventListener('load', () => {
        loaded = true;
        clearTimeout(loadTimer);
        loading && loading.remove();
        container?.setAttribute('aria-busy', 'false');
        scheduleFit();
    });
});
</script>
@endpush
