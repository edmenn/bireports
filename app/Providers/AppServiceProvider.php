<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // En producción, forzar que las URLs absolutas usen el dominio y esquema HTTPS
        if (app()->environment('production')) {
            try {
                if (config('app.url')) {
                    URL::forceRootUrl(config('app.url'));
                }
            } catch (\Throwable $e) {
                // No bloquear el arranque por problemas de configuración
            }
            URL::forceScheme('https');
        }
        // Footer fijo siempre habilitado (usa layout nativo de AdminLTE)
        config(['adminlte.layout_fixed_footer' => true]);
        // Cargar branding/tema desde configuraciones (fila única)
        try {
            $cfg = \App\Models\Configuracion::query()->find(1);
        } catch (\Throwable $e) {
            $cfg = null;
        }

        if ($cfg) {
            $isDark = (bool) session('adminlte_dark_mode', (bool) config('adminlte.layout_dark_mode', false));
            // Footer global fijo con texto
            config(['adminlte.layout_fixed_footer' => true]);

            if (!empty($cfg->site_title)) {
                config(['adminlte.title' => $cfg->site_title]);
            }
            if ($isDark) {
                // En modo oscuro, forzamos clases dark para navbar y sidebar
                config([
                    'adminlte.classes_topnav' => 'navbar-dark navbar-dark',
                    'adminlte.classes_sidebar' => 'sidebar-dark-primary elevation-4',
                ]);
            }
            // AdminLTE espera:
            //  - logo_img     => versión reducida (xs)
            //  - logo_img_xl  => logo principal (xl)
            // Invertimos las asignaciones para que coincidan con los campos
            if (!empty($cfg->site_logo_path)) {
                // Logo principal (grande)
                $logoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cfg->site_logo_path);
                config([
                    'adminlte.logo_img_xl' => ltrim(parse_url($logoUrl, PHP_URL_PATH), '/'),
                    'adminlte.logo_img_xl_class' => 'brand-image',
                ]);
            }
            if (!empty($cfg->site_logo_compact_path)) {
                // Logo reducido (compacto)
                $logoSmallUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cfg->site_logo_compact_path);
                config([
                    'adminlte.logo_img' => ltrim(parse_url($logoSmallUrl, PHP_URL_PATH), '/'),
                    'adminlte.logo_img_class' => 'brand-image',
                ]);
            }
            if (!empty($cfg->site_favicon_path)) {
                // Si hay favicon, habilitamos el uso de .ico simple y
                // exponemos la ruta para la vista principal.
                $favUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cfg->site_favicon_path);
                config([
                    'adminlte.use_ico_only' => true,
                    // Ruta relativa para usar con asset(...)
                    'adminlte.favicon_path' => ltrim(parse_url($favUrl, PHP_URL_PATH), '/'),
                ]);
            }
            // Preloader (imagen central al refrescar)
            if (!empty($cfg->preloader_logo_path)) {
                $preUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cfg->preloader_logo_path);
                config([
                    'adminlte.preloader.img.path'   => ltrim(parse_url($preUrl, PHP_URL_PATH), '/'),
                    'adminlte.preloader.img.alt'    => $cfg->site_title ?: 'Cargando...',
                    // Evitar efectos que puedan distorsionar la percepción del ícono
                    'adminlte.preloader.img.effect' => null,
                ]);
            }
            if (!empty($cfg->preloader_logo_width)) {
                config(['adminlte.preloader.img.width' => (int) $cfg->preloader_logo_width]);
            }
            if (!empty($cfg->preloader_logo_height)) {
                config(['adminlte.preloader.img.height' => (int) $cfg->preloader_logo_height]);
            }
            // Opcional: tamaño del preloader desde theme_config (si está definido allí)
            try {
                $varsTheme = is_array($cfg->theme_config)
                    ? $cfg->theme_config
                    : (is_string($cfg->theme_config) ? (json_decode($cfg->theme_config, true) ?: []) : []);
                if (!empty($varsTheme['preloader_logo_width'])) {
                    config(['adminlte.preloader.img.width' => (int) $varsTheme['preloader_logo_width']]);
                }
                if (!empty($varsTheme['preloader_logo_height'])) {
                    config(['adminlte.preloader.img.height' => (int) $varsTheme['preloader_logo_height']]);
                }
            } catch (\Throwable $e) {}
            if (!empty($cfg->login_logo_path)) {
                $authLogoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cfg->login_logo_path);
                config([
                    'adminlte.auth_logo.enabled' => true,
                    'adminlte.auth_logo.img.path' => ltrim(parse_url($authLogoUrl, PHP_URL_PATH), '/'),
                ]);
                // Tamaño opcional del logo de login desde theme_config
                $varsAuth = is_array($cfg->theme_config) ? $cfg->theme_config : (is_string($cfg->theme_config) ? (json_decode($cfg->theme_config, true) ?: []) : []);
                if (!empty($varsAuth['login_logo_width'])) {
                    config(['adminlte.auth_logo.img.width' => (int) $varsAuth['login_logo_width']]);
                }
                if (!empty($varsAuth['login_logo_height'])) {
                    config(['adminlte.auth_logo.img.height' => (int) $varsAuth['login_logo_height']]);
                }
                // Tamaño opcional del logo de login desde theme_config
                $varsAuth = is_array($cfg->theme_config) ? $cfg->theme_config : (is_string($cfg->theme_config) ? (json_decode($cfg->theme_config, true) ?: []) : []);
                if (!empty($varsAuth['login_logo_width'])) {
                    config(['adminlte.auth_logo.img.width' => (int) $varsAuth['login_logo_width']]);
                }
                if (!empty($varsAuth['login_logo_height'])) {
                    config(['adminlte.auth_logo.img.height' => (int) $varsAuth['login_logo_height']]);
                }
            }

            // Inyectar CSS de tema y footer mediante stacks (compatibles con AdminLTE)
            View::composer('adminlte::page', function ($view) use ($cfg) {
                $vars = is_array($cfg->theme_config) ? $cfg->theme_config : (is_string($cfg->theme_config) ? json_decode($cfg->theme_config, true) : []);
                $isDark = (bool) session('adminlte_dark_mode', (bool) config('adminlte.layout_dark_mode', false));

                // CSS inline en <head> SOLO si hay configuración de tema y el modo no es oscuro
                if (!empty($vars) && ! $isDark) {
                    $view->getFactory()->startPush('css', view('partials.theme-styles', ['theme' => $vars])->render());
                }

                // Ajustes de tamaño de logos
                $css = '';
                $lh = (int) ($cfg->site_logo_height ?? 0);
                $ch = (int) ($cfg->site_logo_compact_height ?? 0);
                if ($lh > 0) {
                    $h = max(16, min($lh, 128));
                    $css .= ".brand-link .brand-image{height: {$h}px !important; max-height: {$h}px; width:auto; object-fit: contain;}\n";
                }
                if ($ch > 0) {
                    $h = max(16, min($ch, 128));
                    $css .= ".brand-link .brand-image-xs,.brand-link .brand-image-sm{height: {$h}px !important; max-height: {$h}px; width:auto; object-fit: contain;}\n";
                }
                if ($css !== '') {
                    $view->getFactory()->startPush('css', "<style>".$css."</style>");
                }

                // Footer fijo (texto) dentro del contenedor estándar + JS de sincronización de colores
                $text = 'Desarrollado por Edgar Menna - 2025';
                $view->getFactory()->startSection('footer', view('partials.footer-fixed', ['text' => $text])->render());
                $view->getFactory()->startPush('js', view('partials.footer-sync-js')->render());
            });

            // CSS específico para login: imagen de fondo responsive (contain) + tamaño de logo
            View::composer([
                'adminlte::auth.auth-page',
                'adminlte::auth.login',
                'adminlte::auth.register',
                'adminlte::auth.passwords.email',
                'adminlte::auth.passwords.reset',
            ], function ($view) use ($cfg) {
                $css = '';
                try {
                    if (!empty($cfg->login_bg_path)) {
                        $bgUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cfg->login_bg_path);
                        $bgRel = ltrim(parse_url($bgUrl, PHP_URL_PATH), '/');
                        $css .= ".login-page,.register-page,.auth-page{background-image:url('/{$bgRel}');background-position:center center;background-repeat:no-repeat;background-size:contain;background-attachment:fixed;}\n";
                    }
                } catch (\Throwable $e) {}
                $varsAuth = is_array($cfg->theme_config) ? $cfg->theme_config : (is_string($cfg->theme_config) ? (json_decode($cfg->theme_config, true) ?: []) : []);
                $w = isset($varsAuth['login_logo_width']) ? (int) $varsAuth['login_logo_width'] : 0;
                $h = isset($varsAuth['login_logo_height']) ? (int) $varsAuth['login_logo_height'] : 0;
                if ($w > 0 || $h > 0) {
                    $s = '';
                    if ($w > 0) { $s .= "max-width: {$w}px;"; }
                    if ($h > 0) { $s .= "max-height: {$h}px;"; }
                    $css .= ".auth-logo img,.login-logo img{ {$s} width:auto; height:auto; object-fit:contain; }\n";
                }
                if ($css !== '') {
                    try { $view->getFactory()->startPush('css', "<style>{$css}</style>"); } catch (\Throwable $e) {}
                }
            });
            // CSS específico para login: fondo responsive y tamaño del logo
            View::composer(['adminlte::auth.auth-page','adminlte::auth.login','adminlte::auth.register','adminlte::auth.passwords.email','adminlte::auth.passwords.reset'], function ($view) use ($cfg) {
                $css = '';
                try {
                    if (!empty($cfg->login_bg_path)) {
                        $bgUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cfg->login_bg_path);
                        $bgRel = ltrim(parse_url($bgUrl, PHP_URL_PATH), '/');
                        $css .= ".login-page,.register-page,.auth-page{background-image:url('/{$bgRel}');background-position:center center;background-repeat:no-repeat;background-size:contain;background-attachment:fixed;}\n";
                    }
                } catch (\Throwable $e) {}
                $varsAuth = is_array($cfg->theme_config) ? $cfg->theme_config : (is_string($cfg->theme_config) ? (json_decode($cfg->theme_config, true) ?: []) : []);
                $w = isset($varsAuth['login_logo_width']) ? (int) $varsAuth['login_logo_width'] : 0;
                $h = isset($varsAuth['login_logo_height']) ? (int) $varsAuth['login_logo_height'] : 0;
                if ($w > 0 || $h > 0) {
                    $s = '';
                    if ($w > 0) { $s .= "max-width: {$w}px;"; }
                    if ($h > 0) { $s .= "max-height: {$h}px;"; }
                    $css .= ".auth-logo img,.login-logo img{ {$s} width:auto; height:auto; object-fit:contain; }\n";
                }
                if ($css !== '') {
                    try { $view->getFactory()->startPush('css', "<style>{$css}</style>"); } catch (\Throwable $e) {}
                }
            });

            // Cache busting para assets estáticos servidos por Nginx con cache largo
            try {
                $css = public_path('css/custom.css');
                if (is_file($css)) {
                    $v = (string) @filemtime($css) ?: (string) time();
                    config([
                        'adminlte.plugins.AppTheme.files' => [
                            ['type' => 'css', 'asset' => true, 'location' => 'css/custom.css?v=' . $v],
                        ],
                    ]);
                }
            } catch (\Throwable $e) {
                // Ignorar fallos de lectura
            }
            try {
                $js = public_path('js/menu-state.js');
                if (is_file($js)) {
                    $v = (string) @filemtime($js) ?: (string) time();
                    config([
                        'adminlte.plugins.AppMenuState.files' => [
                            ['type' => 'js', 'asset' => true, 'location' => 'js/menu-state.js?v=' . $v],
                        ],
                    ]);
                }
            } catch (\Throwable $e) {
                // Ignorar fallos de lectura
            }
        }
    }
}
