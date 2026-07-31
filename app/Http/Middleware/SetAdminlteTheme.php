<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetAdminlteTheme
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $theme = $user->theme ?? session('theme', 'light');

        $navbarClass = config('adminlte.theme.navbar_class', config('adminlte.classes_topnav', 'navbar-white navbar-light'));
        $sidebarClass = config('adminlte.theme.sidebar_class', config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4'));
        $footerClass = config('adminlte.theme.footer_class', config('adminlte.classes_footer', 'text-sm border-0'));

        if ($theme === 'dark') {
            config([
                'adminlte.classes_sidebar' => 'sidebar-dark-primary elevation-4',
                'adminlte.classes_topnav' => 'navbar-dark navbar-primary',
                'adminlte.classes_footer' => 'bg-dark text-light border-0 text-sm',
                'adminlte.classes_body' => 'dark-mode',
                'adminlte.layout_dark_mode' => true,
            ]);

            // Activate dark theme plugin CSS (optional overrides)
            config(['adminlte.plugins.ThemeDark.active' => true]);
            config(['adminlte.plugins.ThemeLight.active' => false]);
        } else {
            config([
                'adminlte.classes_sidebar' => $sidebarClass,
                'adminlte.classes_topnav' => $navbarClass,
                'adminlte.classes_footer' => $footerClass,
                'adminlte.classes_body' => '',
                'adminlte.layout_dark_mode' => false,
            ]);

            // Activate light theme plugin CSS (optional overrides)
            config(['adminlte.plugins.ThemeLight.active' => true]);
            config(['adminlte.plugins.ThemeDark.active' => false]);
        }

        return $next($request);
    }
}
