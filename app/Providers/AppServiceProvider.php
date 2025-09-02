<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
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
            if (! $isDark) {
                if (!empty($cfg->theme_navbar_class)) {
                    config(['adminlte.classes_topnav' => $cfg->theme_navbar_class]);
                }
                if (!empty($cfg->theme_sidebar_class)) {
                    config(['adminlte.classes_sidebar' => $cfg->theme_sidebar_class]);
                }
            } else {
                // En modo oscuro, forzamos clases dark para navbar y sidebar
                config([
                    'adminlte.classes_topnav' => 'navbar-dark navbar-dark',
                    'adminlte.classes_sidebar' => 'sidebar-dark-primary elevation-4',
                ]);
            }
            if (!empty($cfg->site_logo_path)) {
                $logoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cfg->site_logo_path);
                config([
                    'adminlte.logo_img' => ltrim(parse_url($logoUrl, PHP_URL_PATH), '/')
                ]);
            }
            if (!empty($cfg->site_logo_compact_path)) {
                $logoSmallUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cfg->site_logo_compact_path);
                config([
                    'adminlte.logo_img_xl' => ltrim(parse_url($logoSmallUrl, PHP_URL_PATH), '/'),
                    'adminlte.logo_img_xl_class' => 'brand-image-xs',
                ]);
            }
            if (!empty($cfg->site_favicon_path)) {
                // Si hay favicon, habilitamos el uso de .ico simple
                config(['adminlte.use_ico_only' => true]);
            }
            if (!empty($cfg->login_logo_path)) {
                $authLogoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cfg->login_logo_path);
                config([
                    'adminlte.auth_logo.enabled' => true,
                    'adminlte.auth_logo.img.path' => ltrim(parse_url($authLogoUrl, PHP_URL_PATH), '/'),
                ]);
            }

            // Inyectar CSS de tema y footer mediante stacks (compatibles con AdminLTE)
            View::composer('adminlte::page', function ($view) use ($cfg) {
                $vars = is_array($cfg->theme_config) ? $cfg->theme_config : (is_string($cfg->theme_config) ? json_decode($cfg->theme_config, true) : []);
                $isDark = (bool) session('adminlte_dark_mode', (bool) config('adminlte.layout_dark_mode', false));

                // CSS inline en <head> SOLO si hay configuración de tema y el modo no es oscuro
                if (!empty($vars) && ! $isDark) {
                    $view->getFactory()->startPush('css', view('partials.theme-styles', ['theme' => $vars])->render());
                }

                // Footer fijo (texto) dentro del contenedor estándar + JS de sincronización de colores
                $text = 'Desarrollado por Edgar Menna - 2025';
                $view->getFactory()->startSection('footer', view('partials.footer-fixed', ['text' => $text])->render());
                $view->getFactory()->startPush('js', view('partials.footer-sync-js')->render());
            });
        }
    }
}
