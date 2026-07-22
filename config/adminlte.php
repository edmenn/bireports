<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */
    'title' => 'PowerBIDash',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */
    'use_ico_only' => true,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    */
    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Panel logo
    |--------------------------------------------------------------------------
    */
    'logo' => '<b>PowerBI</b>Dash',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'PowerBIDash',

    /*
    |--------------------------------------------------------------------------
    | Authentication logo
    |--------------------------------------------------------------------------
    */
    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader
    |--------------------------------------------------------------------------
    */
    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Loading...',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User menu
    |--------------------------------------------------------------------------
    */
    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => true,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */
    'layout_topnav' => null,
    'layout_boxed' => null,
    // Keep the sidebar fixed so it does not scroll with the content.
    'layout_fixed_sidebar' => true,
    // Keep the navbar fixed so AdminLTE calculates offsets and heights correctly.
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => false,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication view classes
    |--------------------------------------------------------------------------
    */
    'classes_auth_card'   => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body'   => '',
    'classes_auth_footer' => '',
    'classes_auth_icon'   => '',
    'classes_auth_btn'    => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Panel classes
    |--------------------------------------------------------------------------
    */
    'classes_body' => '',
    'classes_brand' => 'd-flex justify-content-center align-items-center bg-body-tertiary border-0 py-3',
    'classes_brand_text' => 'fw-semibold text-primary',
    'classes_content_wrapper' => 'content-wrapper bg-body-tertiary',
    'classes_content_header' => 'container-fluid px-3 px-lg-4 pt-3 pb-2',
    'classes_content' => 'container-fluid px-3 px-lg-4 pb-4',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',
    'classes_footer' => 'bg-body-tertiary text-muted border-0 text-sm py-3',

    /*
    |--------------------------------------------------------------------------
    | Sidebar (sidebar)
    |--------------------------------------------------------------------------
    */
    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => true,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => false,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Sidebar de control (derecha)
    |--------------------------------------------------------------------------
    */
    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs (usamos nombres de rutas)
    |--------------------------------------------------------------------------
    */
    'use_route_url' => true,
    'dashboard_url' => 'dashboards.index',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password.request',
    'password_email_url' => 'password.email',
    'profile_url' => 'profile.edit',
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Empaquetado de assets de Laravel
    |--------------------------------------------------------------------------
    */
    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu items
    |--------------------------------------------------------------------------
    */
    'i18n' => true,

    'menu' => [
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => true,
        ],
        // Language (desktop: text + icon)
        [
            'text'         => 'Language',
            'icon'         => 'nav-icon fas fa-language fa-fw',
            'topnav_right' => true,
            'classes'      => 'd-none d-sm-inline',
            'submenu'      => [
                [ 'text' => 'Spanish',   'url'  => 'lang/es', 'icon' => 'nav-icon far fa-flag fa-fw' ],
                [ 'text' => 'Portuguese', 'url'  => 'lang/pt', 'icon' => 'nav-icon far fa-flag fa-fw' ],
            ],
        ],
        // Language (mobile: icon only to save space)
        [
            'text'         => '',
            'icon'         => 'nav-icon fas fa-language fa-fw',
            'topnav_right' => true,
            'classes'      => 'd-inline d-sm-none',
            'submenu'      => [
                [ 'text' => 'Spanish',   'url'  => 'lang/es', 'icon' => 'nav-icon far fa-flag fa-fw' ],
                [ 'text' => 'Portuguese', 'url'  => 'lang/pt', 'icon' => 'nav-icon far fa-flag fa-fw' ],
            ],
        ],
        [ 'type' => 'fullscreen-widget', 'topnav_right' => true ],

        // Sidebar search removed as requested

        [
            'text' => 'nav.portal',
            'icon' => 'nav-icon fas fa-user-circle fa-fw',
            'can'  => 'menu.portal',
            'classes' => 'menu-open',
            'submenu' => [
                [ 'text' => 'nav.portal_home',       'icon'=>'nav-icon fas fa-home fa-fw',       'route'=>'portal.index',     'can'=>'menu.portal' ],
                [ 'text' => 'nav.portal_favorites',    'icon'=>'nav-icon fas fa-star fa-fw',       'route'=>'portal.favorites', 'can'=>'menu.portal' ],
                [ 'text' => 'nav.portal_most_accessed','icon'=>'nav-icon fas fa-fire fa-fw',       'route'=>'portal.most-accessed',       'can'=>'menu.portal' ],
                [ 'text' => 'nav.portal_applications', 'icon'=>'nav-icon fas fa-th-large fa-fw',   'route'=>'portal.apps',     'can'=>'menu.portal' ],
            ],
        ],

        [
            'text' => 'nav.power_bi',
            'icon' => 'nav-icon fas fa-bolt fa-fw',
            'can'  => 'menu.reports_index',
            'classes' => 'menu-open',
            'submenu' => [
                [ 'text' => 'nav.reports',           'icon'=>'nav-icon fas fa-chart-bar fa-fw',       'route'=>'reports.index',     'can'=>'menu.reports_index' ],
                [ 'text' => 'nav.reports_subscriptions', 'icon'=>'nav-icon fas fa-envelope-open-text fa-fw', 'route'=>'subscriptions.index', 'can'=>'report_subscriptions.manage' ],
                [ 'text' => 'nav.datasets', 'icon'=>'nav-icon fas fa-database fa-fw',        'route'=>'datasets.index',    'can'=>'menu.reports_index' ],
                [ 'text' => 'nav.workspaces','icon'=>'nav-icon fas fa-project-diagram fa-fw', 'route'=>'workspaces.index',  'can'=>'menu.workspaces'],
                [ 'text' => 'nav.capacities',        'icon'=>'nav-icon fas fa-cogs fa-fw',            'route'=>'capacities.index',  'can'=>'menu.capacities'],
            ],
        ],

        [
            'text' => 'nav.organization',
            'icon' => 'nav-icon fas fa-sitemap fa-fw',
            'can'  => 'menu.users',
            'classes' => 'menu-open',
            'submenu' => [
                [ 'text' => 'nav.users', 'icon'=>'nav-icon fas fa-users fa-fw',         'route'=>'users.index',   'can'=>'menu.users' ],
                [ 'text' => 'nav.groups',   'icon'=>'nav-icon fas fa-user-friends fa-fw',  'route'=>'groups.index',  'can'=>'menu.groups' ],
                [ 'text' => 'nav.folders', 'icon'=>'nav-icon fas fa-folder fa-fw',        'route'=>'folders.index', 'can'=>'menu.folders' ],
                [ 'text' => 'nav.applications', 'icon'=>'nav-icon fas fa-th fa-fw',            'route'=>'applications.index', 'can'=>'menu.applications' ],
                [ 'text' => 'nav.roles', 'icon'=>'nav-icon fas fa-user-shield fa-fw', 'route'=>'roles.index', 'can'=>'menu.roles' ],
            ],
        ],

        [
            'text' => 'nav.audit',
            'icon' => 'nav-icon fas fa-chart-line fa-fw',
            'can'  => 'menu.audit',
            'classes' => 'menu-open',
            'submenu' => [
                [ 'text' => 'nav.audit_usage_metrics',     'icon'=>'nav-icon fas fa-chart-bar fa-fw', 'route'=>'audit.index',        'can'=>'menu.audit' ],
                [ 'text' => 'nav.audit_changes', 'icon'=>'nav-icon fas fa-exchange-alt fa-fw', 'route'=>'audit.changes.index', 'can'=>'menu.audit' ],
            ],
        ],

        [
            'text' => 'nav.settings',
            'icon' => 'nav-icon fas fa-cogs fa-fw',
            'can'  => 'menu.configurations',
            'classes' => 'menu-open',
            'submenu' => [
                [ 'text' => 'nav.settings_parameters', 'icon'=>'nav-icon fas fa-sliders-h fa-fw',   'route'=>'configurations.index', 'can'=>'menu.configurations' ],
                [ 'text' => 'nav.settings_theme',       'icon'=>'nav-icon fas fa-paint-brush fa-fw', 'route'=>'configurations.theme',  'can'=>'menu.configurations' ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu filters
    |--------------------------------------------------------------------------
    */
    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins
    |--------------------------------------------------------------------------
    | (activate our global CSS)
    */
    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                ['type' => 'js',  'asset' => false, 'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js'],
                ['type' => 'js',  'asset' => false, 'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js'],
                ['type' => 'css', 'asset' => false, 'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css'],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                ['type' => 'js',  'asset' => false, 'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js'],
                ['type' => 'css', 'asset' => false, 'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css'],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                ['type' => 'js', 'asset' => false, 'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js'],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                ['type' => 'js', 'asset' => false, 'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8'],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                ['type' => 'css', 'asset' => false, 'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css'],
                ['type' => 'js',  'asset' => false, 'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js'],
            ],
        ],

        // Theme overrides (optional)
        'ThemeLight' => [
            'active' => false,
            'files' => [
                ['type' => 'css', 'asset' => true, 'location' => 'css/theme-light.css'],
            ],
        ],
        'ThemeDark' => [
            'active' => false,
            'files' => [
                ['type' => 'css', 'asset' => true, 'location' => 'css/theme-dark.css'],
            ],
        ],

        // === NUEVO: CSS global para TODAS LAS VISTAS ===
        'AppTheme' => [
            // Loaded once by the base layout with file-version cache busting.
            'active' => false,
            'files' => [
                ['type' => 'css', 'asset' => true, 'location' => 'css/custom.css'],
            ],
        ],
        'AppMenuState' => [
            'active' => true,
            'files' => [
                ['type' => 'js', 'asset' => true, 'location' => 'js/menu-state.js'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    */
    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    */
    'livewire' => false,
];



