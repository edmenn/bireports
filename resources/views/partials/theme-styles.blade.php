@php
    // Valores por defecto si no están configurados
    $theme = array_merge([
        'main_bg' => '#f4f6f9',
        'main_text' => '#111111',
        'navbar_bg' => '#374151',
        'navbar_text' => '#f9fafb',
        'menu_bg' => '#1f2d37',
        'menu_text' => '#d1d5db',
        'highlight_bg' => '#6b7280',
        'highlight_text' => '#ffffff',
        'container_bg' => '#ffffff',
        'container_alt_bg' => '#f8fafc',
        'container_text' => '#111111',
        'icon_bg' => '#e5e7eb',
        'icon_text' => '#111111',
        'btn_bg' => '#6b7280',
        'btn_text' => '#ffffff',
        'btn_hover' => '#4b5563',
        'btn_active' => '#374151',
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
        --app-highlight-bg: var(--app-hi-bg);
        --app-highlight-text: var(--app-hi-text);
        --app-card-bg: {{ $theme['container_bg'] }};
        --app-card-alt-bg: {{ $theme['container_alt_bg'] }};
        --app-card-text: {{ $theme['container_text'] }};
        --app-icon-bg: {{ $theme['icon_bg'] }};
        --app-icon-text: {{ $theme['icon_text'] }};
        --app-btn-bg: {{ $theme['btn_bg'] }};
        --app-btn-text: {{ $theme['btn_text'] }};
        --app-btn-hover: {{ $theme['btn_hover'] }};
        --app-btn-active: {{ $theme['btn_active'] }};
        /* Bootstrap/AdminLTE 3 compatibility aliases used by shared components. */
        --bs-body-bg: var(--app-card-bg);
        --bs-body-color: var(--app-card-text);
        --bs-emphasis-color: var(--app-card-text);
        --bs-secondary-color: var(--app-main-text);
        --bs-border-color: var(--app-card-alt-bg);
        --bs-primary: var(--app-btn-bg);
        --pbd-card-border: var(--app-card-alt-bg);
        --pbd-th-color: var(--app-card-text);
        --pbd-th-border: var(--app-card-alt-bg);
        --pbd-td-border: var(--app-card-alt-bg);
    }

    body:not(.dark-mode), body:not(.dark-mode) .content-wrapper{ background-color: var(--app-main-bg) !important; color: var(--app-main-text); }
    body:not(.dark-mode) .main-header.navbar{ background-color: var(--app-navbar-bg) !important; color: var(--app-navbar-text) !important; }
    body:not(.dark-mode) .main-header .navbar-brand{ background-color: var(--app-navbar-bg) !important; color: var(--app-navbar-text) !important; }
    body:not(.dark-mode) .main-header.navbar .nav-link, body:not(.dark-mode) .main-header .navbar-nav .nav-link { color: var(--app-navbar-text) !important; }
    body:not(.dark-mode) .main-sidebar{ background-color: var(--app-menu-bg) !important; color: var(--app-menu-text) !important; }
    body:not(.dark-mode) .main-sidebar .brand-link{ background-color: var(--app-menu-bg) !important; color: var(--app-menu-text) !important; }
    body:not(.dark-mode) .sidebar .nav-link{ color: var(--app-menu-text) !important; }
    body:not(.dark-mode) .sidebar .nav-link.active, body:not(.dark-mode) .sidebar .nav-link:hover{ background-color: var(--app-hi-bg) !important; color: var(--app-hi-text) !important; }
    body:not(.dark-mode) .card{ background-color: var(--app-card-bg) !important; color: var(--app-card-text); }
    body:not(.dark-mode) .card .card-header,
    body:not(.dark-mode) .card .card-footer{ background-color: var(--app-card-alt-bg) !important; color: var(--app-card-text); }
    body:not(.dark-mode) .portal-icon{ background-color: var(--app-icon-bg); color: var(--app-icon-text); }

    /* Neutral surfaces shared by portal, administration and authentication. */
    body:not(.dark-mode) .modal-content,
    body:not(.dark-mode) .dropdown-menu,
    body:not(.dark-mode) .list-group-item,
    body:not(.dark-mode) .login-card,
    body:not(.dark-mode) .report-preview-card,
    body:not(.dark-mode) .preview-control-card,
    body:not(.dark-mode) .preview-card-wrapper .report-card,
    body:not(.dark-mode) .portal-card,
    body:not(.dark-mode) .portal-card .card-body,
    body:not(.dark-mode) .application-card,
    body:not(.dark-mode) .portal-app-card .pbi-wrapper,
    body:not(.dark-mode) .portal-list,
    body:not(.dark-mode) .portal-table tbody td,
    body:not(.dark-mode) .heat-wrap,
    body:not(.dark-mode) .up-heat,
    body:not(.dark-mode) .up-hm-table .sticky-col,
    body:not(.dark-mode) .up-top-item,
    body:not(.dark-mode) .up-kpi {
        background: var(--app-card-bg) !important;
        color: var(--app-card-text) !important;
        border-color: var(--app-card-alt-bg) !important;
    }
    body:not(.dark-mode) .modal-header,
    body:not(.dark-mode) .modal-footer,
    body:not(.dark-mode) .portal-summary-item,
    body:not(.dark-mode) .portal-email-preview,
    body:not(.dark-mode) .app-page-toolbar,
    body:not(.dark-mode) .checkbox-list,
    body:not(.dark-mode) .checkbox-list-container,
    body:not(.dark-mode) .color-card,
    body:not(.dark-mode) .permissions-list {
        background: var(--app-card-alt-bg) !important;
        color: var(--app-card-text) !important;
        border-color: var(--app-card-alt-bg) !important;
    }
    body:not(.dark-mode) .portal-table thead th,
    body:not(.dark-mode) .cap-table thead th,
    body:not(.dark-mode) .ws-table thead th,
    body:not(.dark-mode) .report-table thead th,
    body:not(.dark-mode) .table thead th {
        background-color: var(--app-card-alt-bg) !important;
        color: var(--app-card-text) !important;
        border-color: var(--app-card-alt-bg) !important;
    }
    body:not(.dark-mode) .table-responsive { background-color: var(--app-card-bg) !important; }
    body:not(.dark-mode) .table,
    body:not(.dark-mode) .table td { color: var(--app-card-text); border-color: var(--app-card-alt-bg) !important; }
    body:not(.dark-mode) .table-hover tbody tr:hover td,
    body:not(.dark-mode) .cap-table tbody tr:hover,
    body:not(.dark-mode) .ws-table tbody tr:hover,
    body:not(.dark-mode) .diff-table .diff-row:hover,
    body:not(.dark-mode) .portal-table tbody tr:hover td { background-color: var(--app-card-alt-bg) !important; }
    body:not(.dark-mode) .form-control,
    body:not(.dark-mode) .custom-select,
    body:not(.dark-mode) .form-select,
    body:not(.dark-mode) .input-group-text,
    body:not(.dark-mode) .select2-selection,
    body:not(.dark-mode) .page-link {
        background-color: var(--app-card-bg) !important;
        color: var(--app-card-text) !important;
        border-color: var(--app-card-alt-bg) !important;
    }
    body:not(.dark-mode) .color-item {
        background-color: var(--app-card-bg) !important;
        color: var(--app-card-text) !important;
        border-color: var(--app-card-alt-bg) !important;
    }
    body:not(.dark-mode) .form-control::placeholder { color: var(--app-main-text); opacity: .62; }
    body:not(.dark-mode) .dropdown-item,
    body:not(.dark-mode) .nav-tabs .nav-link,
    body:not(.dark-mode) .text-muted,
    body:not(.dark-mode) .summary-label,
    body:not(.dark-mode) .summary-value,
    body:not(.dark-mode) .portal-section-heading,
    body:not(.dark-mode) .portal-per-page-form label,
    body:not(.dark-mode) .report-preview-title,
    body:not(.dark-mode) .up-kpi-label { color: var(--app-card-text) !important; }
    body:not(.dark-mode) .dropdown-item:hover,
    body:not(.dark-mode) .dropdown-item:focus,
    body:not(.dark-mode) .nav-tabs .nav-link.active,
    body:not(.dark-mode) .page-item.active .page-link {
        background-color: var(--app-hi-bg) !important;
        color: var(--app-hi-text) !important;
        border-color: var(--app-hi-bg) !important;
    }
    body:not(.dark-mode) .login-card .input-group { border-color: var(--app-card-alt-bg) !important; }
    body:not(.dark-mode) .login-page::before { background: var(--app-main-bg) !important; opacity: .28; }
    body:not(.dark-mode) .btn-brand {
        background-color: var(--app-btn-bg) !important;
        color: var(--app-btn-text) !important;
        border-color: var(--app-btn-bg) !important;
    }
    body:not(.dark-mode) .btn-brand:hover { background-color: var(--app-btn-hover) !important; }
    body:not(.dark-mode) .btn-primary,
    body:not(.dark-mode) .btn-default,
    body:not(.dark-mode) .btn-secondary{
        background-color: var(--app-btn-bg);
        color: var(--app-btn-text);
        border-color: var(--app-btn-bg);
    }
    body:not(.dark-mode) .btn-primary:hover,
    body:not(.dark-mode) .btn-default:hover,
    body:not(.dark-mode) .btn-secondary:hover{
        background-color: var(--app-btn-hover);
        color: var(--app-btn-text);
    }
    body:not(.dark-mode) .btn-primary:active,
    body:not(.dark-mode) .btn-primary.active,
    body:not(.dark-mode) .btn-default:active,
    body:not(.dark-mode) .btn-default.active,
    body:not(.dark-mode) .btn-secondary:active,
    body:not(.dark-mode) .btn-secondary.active{
        background-color: var(--app-btn-active);
        color: var(--app-btn-text);
    }
    /* Outline buttons adapt to theme colors */
    body:not(.dark-mode) .btn-outline-primary{
        color: var(--app-btn-bg);
        border-color: var(--app-btn-bg);
        background-color: transparent;
    }
    body:not(.dark-mode) .btn-outline-primary:hover,
    body:not(.dark-mode) .btn-outline-primary:focus,
    body:not(.dark-mode) .btn-outline-primary:active{
        color: var(--app-btn-text);
        background-color: var(--app-btn-bg);
        border-color: var(--app-btn-bg);
    }
    body:not(.dark-mode) .btn-outline-secondary{
        color: var(--app-main-text);
        border-color: var(--app-main-text);
        background-color: transparent;
    }
    body:not(.dark-mode) .btn-outline-secondary:hover,
    body:not(.dark-mode) .btn-outline-secondary:focus,
    body:not(.dark-mode) .btn-outline-secondary:active{
        color: var(--app-btn-text);
        background-color: var(--app-btn-bg);
        border-color: var(--app-btn-bg);
    }

    /* Footer corporativo integrado (solo en claro; en oscuro hereda AdminLTE) */
    body:not(.dark-mode) .main-footer{ background: var(--app-navbar-bg) !important; color: var(--app-navbar-text) !important; text-align:center; }

    /* Asegurar tonos oscuros cuando está activo el Dark Mode */
    body.dark-mode .main-header.navbar{ background-color: #1f2d3d !important; color: #c2c7d0 !important; }
    body.dark-mode .main-header.navbar .nav-link{ color: #c2c7d0 !important; }
    body.dark-mode .main-footer{ background-color: #1f2d3d !important; color: #c2c7d0 !important; text-align:center; }
    .main-footer a{ color: inherit; }


</style>
