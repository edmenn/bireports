@php
    // Valores por defecto si no están configurados
    $theme = array_merge([
        'main_bg' => '#f4f6f9',
        'main_text' => '#111111',
        'navbar_bg' => '#ffffff',
        'navbar_text' => '#111111',
        'menu_bg' => '#343a40',
        'menu_text' => '#c2c7d0',
        'highlight_bg' => '#2563eb',
        'highlight_text' => '#ffffff',
        'container_bg' => '#ffffff',
        'container_alt_bg' => '#f8fafc',
        'container_text' => '#111111',
        'icon_bg' => '#e5e7eb',
        'icon_text' => '#111111',
        'btn_bg' => '#2563eb',
        'btn_text' => '#ffffff',
        'btn_hover' => '#1d4ed8',
        'btn_active' => '#1e40af',
    ], $theme ?? []);
@endphp
<style id="theme-preview">
    :root{
        --app-main-bg: {{ $theme['main_bg'] }};
        --app-main-text: {{ $theme['main_text'] }};
        --app-navbar-bg: {{ $theme['navbar_bg'] }};
        --app-navbar-text: {{ $theme['navbar_text'] }};
        --app-menu-bg: {{ $theme['menu_bg'] }};
        --app-menu-text: {{ $theme['menu_text'] }};
        --app-hi-bg: {{ $theme['highlight_bg'] }};
        --app-hi-text: {{ $theme['highlight_text'] }};
        --app-card-bg: {{ $theme['container_bg'] }};
        --app-card-alt-bg: {{ $theme['container_alt_bg'] }};
        --app-card-text: {{ $theme['container_text'] }};
        --app-icon-bg: {{ $theme['icon_bg'] }};
        --app-icon-text: {{ $theme['icon_text'] }};
        --app-btn-bg: {{ $theme['btn_bg'] }};
        --app-btn-text: {{ $theme['btn_text'] }};
        --app-btn-hover: {{ $theme['btn_hover'] }};
        --app-btn-active: {{ $theme['btn_active'] }};
    }

    body:not(.dark-mode), body:not(.dark-mode) .content-wrapper{ background-color: var(--app-main-bg) !important; color: var(--app-main-text); }
    body:not(.dark-mode) .main-header.navbar{ background-color: var(--app-navbar-bg) !important; color: var(--app-navbar-text) !important; }
    body:not(.dark-mode) .main-header.navbar .nav-link, body:not(.dark-mode) .main-header .navbar-nav .nav-link { color: var(--app-navbar-text) !important; }
    body:not(.dark-mode) .main-sidebar{ background-color: var(--app-menu-bg) !important; color: var(--app-menu-text) !important; }
    body:not(.dark-mode) .sidebar .nav-link{ color: var(--app-menu-text) !important; }
    body:not(.dark-mode) .sidebar .nav-link.active, body:not(.dark-mode) .sidebar .nav-link:hover{ background-color: var(--app-hi-bg) !important; color: var(--app-hi-text) !important; }
    body:not(.dark-mode) .card{ background-color: var(--app-card-bg); color: var(--app-card-text); }
    body:not(.dark-mode) .card .card-header{ background-color: var(--app-card-alt-bg); }
    body:not(.dark-mode) .btn-primary, body:not(.dark-mode) .btn-default, body:not(.dark-mode) .btn{ background-color: var(--app-btn-bg); color: var(--app-btn-text); border-color: var(--app-btn_bg, var(--app-btn-bg)); }
    body:not(.dark-mode) .btn:hover{ background-color: var(--app-btn-hover); color: var(--app-btn-text); }
    body:not(.dark-mode) .btn:active, body:not(.dark-mode) .btn.active{ background-color: var(--app-btn-active); color: var(--app-btn-text); }

    /* Footer corporativo integrado (solo en claro; en oscuro hereda AdminLTE) */
    body:not(.dark-mode) .main-footer{ background: var(--app-navbar-bg) !important; color: var(--app-navbar-text) !important; text-align:center; }

    /* Asegurar tonos oscuros cuando está activo el Dark Mode */
    body.dark-mode .main-header.navbar{ background-color: #1f2d3d !important; color: #c2c7d0 !important; }
    body.dark-mode .main-header.navbar .nav-link{ color: #c2c7d0 !important; }
    body.dark-mode .main-footer{ background-color: #1f2d3d !important; color: #c2c7d0 !important; text-align:center; }
    .main-footer a{ color: inherit; }
</style>
