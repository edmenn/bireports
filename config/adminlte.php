<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Título
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
    'use_ico_only' => false,
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
    | Logo del panel
    |--------------------------------------------------------------------------
    */
    'logo' => '<b>PowerBI</b>Dash',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png', // cambiá por tu logo cuando lo tengas
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'PowerBIDash',

    /*
    |--------------------------------------------------------------------------
    | Logo en pantallas de autenticación
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
    | Precargador (preloader)
    |--------------------------------------------------------------------------
    */
    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Cargando...',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menú de usuario
    |--------------------------------------------------------------------------
    */
    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => true, // muestra enlace al perfil

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */
    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Clases de vistas de autenticación
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
    | Clases del panel
    |--------------------------------------------------------------------------
    */
    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Barra lateral (sidebar)
    |--------------------------------------------------------------------------
    */
    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
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
    | URLs
    |--------------------------------------------------------------------------
    |
    | Usamos nombres de rutas (Breeze/AdminLTE).
    */
    'use_route_url' => true,                 // <- usar nombres de rutas
    'dashboard_url' => 'dashboards.index',   // enlace del logo/breadcrumb
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password.request',
    'password_email_url' => 'password.email',
    'profile_url' => 'profile.edit',         // abre la página de perfil de Breeze
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
    | Ítems de menú (navbar + sidebar)
    |--------------------------------------------------------------------------
    */
    'menu' => [
        // Navbar (arriba)
        [
            'type' => 'navbar-search',
            'text' => 'Buscar',
            'topnav_right' => true,
        ],
        [
            'text'         => 'Idioma',
            'icon'         => 'fas fa-language',
            'topnav_right' => true,
            'submenu'      => [
                [   
                    'text' => 'Español',
                    'url'  => 'lang/es',
                    'icon' => 'far fa-flag',
                ],
                [
                    'text' => 'Portugués',
                    'url'  => 'lang/pt',
                    'icon' => 'far fa-flag',
                ],
            ],
        ],
            [
                'type' => 'fullscreen-widget',
                'topnav_right' => true,
        ],

        // Búsqueda en el sidebar
        [
            'type' => 'sidebar-menu-search',
            'text' => 'Buscar',
        ],
        // Clientes (solo super_admin)
        [
            'text'  => 'Clientes',
            'icon'  => 'fas fa-handshake',
            'route' => 'admin.clients.index',
            'can'   => 'super-admin.panel',
        ],

        // --- Menú lateral en español ---
        ['header' => 'Power BI'],

        [
            'text'  => 'Informes',
            'icon'  => 'fas fa-chart-bar',
            'route' => 'reports.index',
        ],
        [
            'text'  => 'Conjuntos de datos',
            'icon'  => 'fas fa-database',
            'route' => 'datasets.index',   // la crearemos luego
            'can'   => null,
        ],
        [
            'text'  => 'Aplicaciones',
            'icon'  => 'fas fa-th-large',
            'route' => 'apps.index',       // la crearemos luego
        ],
        [
            'text'  => 'Espacios de trabajo',
            'icon'  => 'fas fa-project-diagram',
            'route' => 'workspaces.index', // la crearemos luego
        ],
        [
            'text'  => 'Capacidades',
            'icon'  => 'fas fa-cogs',
            'route' => 'capacities.index', // la crearemos luego
        ],

        ['header' => 'Organización'],
        [
            'text'  => 'Empresas',
            'icon'  => 'fas fa-building',
            'route' => 'empresas.index',   // la crearemos luego
        ],
        [
            'text'  => 'Usuarios',
            'icon'  => 'fas fa-users',
            'route' => 'users.index',      // opcional: si agregamos UsersController@index
        ],
        [
            'text'  => 'Grupos',
            'icon'  => 'fas fa-user-friends',
            'route' => 'grupos.index',     // la crearemos luego
        ],
        [
            'text'  => 'Carpetas',
            'icon'  => 'fas fa-folder',
            'route' => 'folders.index',    // la crearemos luego
        ],

        ['header' => 'Auditoría'],
        [
            'text'  => 'Métricas de uso',
            'icon'  => 'fas fa-chart-line',
            'route' => 'auditoria.index',  // la crearemos luego
        ],

        ['header' => 'Configuraciones'],
        [
            'text'  => 'Parámetros',
            'icon'  => 'fas fa-cogs',
            'route' => 'configuraciones.index', // la crearemos luego
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Filtros del menú
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
    | (dejamos en false por ahora; los activamos cuando los usemos)
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
