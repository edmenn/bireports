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
    'classes_brand' => 'd-flex justify-content-center align-items-center',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',
    'classes_footer' => 'bg-white text-muted border-0 text-sm',

    /*
    |--------------------------------------------------------------------------
    | Sidebar (sidebar)
    |--------------------------------------------------------------------------
    */
    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
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
            'icon'         => 'fas fa-language',
            'topnav_right' => true,
            'classes'      => 'd-none d-sm-inline',
            'submenu'      => [
                [ 'text' => 'Spanish',   'url'  => 'lang/es', 'icon' => 'far fa-flag' ],
                [ 'text' => 'Portuguese', 'url'  => 'lang/pt', 'icon' => 'far fa-flag' ],
            ],
        ],
        // Language (mobile: icon only to save space)
        [
            'text'         => '',
            'icon'         => 'fas fa-language',
            'topnav_right' => true,
            'classes'      => 'd-inline d-sm-none',
            'submenu'      => [
                [ 'text' => 'Spanish',   'url'  => 'lang/es', 'icon' => 'far fa-flag' ],
                [ 'text' => 'Portuguese', 'url'  => 'lang/pt', 'icon' => 'far fa-flag' ],
            ],
        ],
        [ 'type' => 'fullscreen-widget', 'topnav_right' => true ],

        // Sidebar search removed as requested

        [
            'text' => 'common.menu.portal',
            'icon' => 'fas fa-user-circle',
            'can'  => 'menu.portal',
            'classes' => 'menu-open',
            'submenu' => [
                [ 'text' => 'common.menu.portal_home',       'icon'=>'fas fa-home',       'route'=>'portal.index',     'can'=>'menu.portal' ],
                [ 'text' => 'common.menu.portal_favorites',    'icon'=>'fas fa-star',       'route'=>'portal.favorites', 'can'=>'menu.portal' ],
                [ 'text' => 'common.menu.portal_most_accessed','icon'=>'fas fa-fire',       'route'=>'portal.most-accessed',       'can'=>'menu.portal' ],
                [ 'text' => 'common.menu.portal_applications', 'icon'=>'fas fa-th-large',   'route'=>'portal.apps',     'can'=>'menu.portal' ],
            ],
        ],

        [
            'text' => 'common.menu.power_bi',
            'icon' => 'fas fa-bolt',
            'can'  => 'menu.reports_index',
            'classes' => 'menu-open',
            'submenu' => [
                [ 'text' => 'common.menu.reports',           'icon'=>'fas fa-chart-bar',       'route'=>'reports.index',     'can'=>'menu.reports_index' ],
                [ 'text' => 'common.menu.datasets', 'icon'=>'fas fa-database',        'route'=>'datasets.index',    'can'=>'menu.reports_index' ],
                [ 'text' => 'common.menu.workspaces','icon'=>'fas fa-project-diagram', 'route'=>'workspaces.index',  'can'=>'menu.workspaces'],
                [ 'text' => 'common.menu.capacities',        'icon'=>'fas fa-cogs',            'route'=>'capacities.index',  'can'=>'menu.capacities'],
            ],
        ],

        [
            'text' => 'common.menu.organization',
            'icon' => 'fas fa-sitemap',
            'can'  => 'menu.users',
            'classes' => 'menu-open',
            'submenu' => [
                [ 'text' => 'common.menu.users', 'icon'=>'fas fa-users',         'route'=>'users.index',   'can'=>'menu.users' ],
                [ 'text' => 'common.menu.groups',   'icon'=>'fas fa-user-friends',  'route'=>'groups.index',  'can'=>'menu.groups' ],
                [ 'text' => 'common.menu.folders', 'icon'=>'fas fa-folder',        'route'=>'folders.index', 'can'=>'menu.folders' ],
                [ 'text' => 'common.menu.applications', 'icon'=>'fas fa-th',            'route'=>'applications.index', 'can'=>'menu.applications' ],
                [ 'text' => 'common.menu.roles', 'icon'=>'fas fa-user-shield', 'route'=>'roles.index', 'can'=>'menu.roles' ],
            ],
        ],

        [
            'text' => 'common.menu.audit',
            'icon' => 'fas fa-chart-line',
            'can'  => 'menu.audit',
            'classes' => 'menu-open',
            'submenu' => [
                [ 'text' => 'common.menu.audit_usage_metrics',     'icon'=>'fas fa-chart-bar', 'route'=>'audit.index',        'can'=>'menu.audit' ],
                [ 'text' => 'common.menu.audit_reset', 'icon'=>'fas fa-broom',     'route'=>'admin.audit.reset.show', 'can'=>'menu.audit' ],
                [ 'text' => 'common.menu.audit_changes', 'icon'=>'fas fa-exchange-alt', 'route'=>'audit.changes.index', 'can'=>'menu.audit' ],
            ],
        ],

        [
            'text' => 'common.menu.settings',
            'icon' => 'fas fa-cogs',
            'can'  => 'menu.configurations',
            'classes' => 'menu-open',
            'submenu' => [
                [ 'text' => 'common.menu.settings_parameters', 'icon'=>'fas fa-sliders-h',   'route'=>'configurations.index', 'can'=>'menu.configurations' ],
                [ 'text' => 'common.menu.settings_theme',       'icon'=>'fas fa-paint-brush', 'route'=>'configurations.theme',  'can'=>'menu.configurations' ],
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
            'active' => true,
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




