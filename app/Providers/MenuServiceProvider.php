<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Override AdminLTE menu to use translation keys
        config([
            'adminlte.menu' => [
                [
                    'type' => 'navbar-search',
                    'text' => 'search',
                    'topnav_right' => true,
                ],
                [ 'type' => 'fullscreen-widget', 'topnav_right' => true ],

                // Portal
                [
                    'text' => 'my_space',
                    'icon' => 'fas fa-user-circle',
                    'can'  => 'menu.portal',
                    'classes' => 'menu-open',
                    'submenu' => [
                        [ 'text'=>'home',          'icon'=>'fas fa-home',       'route'=>'portal.index',     'can'=>'menu.portal' ],
                        [ 'text'=>'favorites',     'icon'=>'fas fa-star',       'route'=>'portal.favoritos', 'can'=>'menu.portal' ],
                        [ 'text'=>'most_accessed', 'icon'=>'fas fa-fire',       'route'=>'portal.mas',       'can'=>'menu.portal' ],
                        [ 'text'=>'applications',  'icon'=>'fas fa-th-large',   'route'=>'portal.apps',      'can'=>'menu.portal' ],
                    ],
                ],

                // Power BI
                [
                    'text' => 'power_bi',
                    'icon' => 'fas fa-bolt',
                    'can'  => 'menu.reports_index',
                    'classes' => 'menu-open',
                    'submenu' => [
                        [ 'text'=>'reports',       'icon'=>'fas fa-chart-bar',       'route'=>'reports.index',         'can'=>'menu.reports_index' ],
                        [ 'text'=>'datasets',      'icon'=>'fas fa-database',        'route'=>'datasets.index',        'can'=>'menu.reports_index' ],
                        [ 'text'=>'applications',  'icon'=>'fas fa-th',              'route'=>'applications.index',    'can'=>'menu.applications' ],
                        [ 'text'=>'workspaces',    'icon'=>'fas fa-project-diagram', 'route'=>'workspaces.index',      'can'=>'menu.workspaces' ],
                        [ 'text'=>'capacities',    'icon'=>'fas fa-cogs',            'route'=>'capacities.index',      'can'=>'menu.capacities' ],
                    ],
                ],

                // Organization
                [
                    'text' => 'organization',
                    'icon' => 'fas fa-sitemap',
                    'can'  => 'menu.users',
                    'classes' => 'menu-open',
                    'submenu' => [
                        [ 'text'=>'users',    'icon'=>'fas fa-users',        'route'=>'users.index',   'can'=>'menu.users' ],
                        [ 'text'=>'groups',   'icon'=>'fas fa-user-friends', 'route'=>'grupos.index',  'can'=>'menu.groups' ],
                        [ 'text'=>'folders',  'icon'=>'fas fa-folder',       'route'=>'folders.index', 'can'=>'menu.folders' ],
                        [ 'text'=>'roles',    'icon'=>'fas fa-user-shield',  'route'=>'roles.index',   'can'=>'menu.users' ],
                    ],
                ],

                // Audit
                [
                    'text' => 'audit',
                    'icon' => 'fas fa-chart-line',
                    'can'  => 'menu.auditoria',
                    'classes' => 'menu-open',
                    'submenu' => [
                        [ 'text'=>'usage_metrics', 'icon'=>'fas fa-chart-bar',    'route'=>'auditoria.index',        'can'=>'menu.auditoria' ],
                        [ 'text'=>'audit_reset',   'icon'=>'fas fa-broom',        'route'=>'admin.audit.reset.show', 'can'=>'menu.auditoria' ],
                        [ 'text'=>'changes',       'icon'=>'fas fa-exchange-alt', 'route'=>'auditoria.changes.index','can'=>'menu.auditoria' ],
                    ],
                ],

                // Settings
                [
                    'text' => 'settings',
                    'icon' => 'fas fa-cogs',
                    'can'  => 'menu.configuraciones',
                    'classes' => 'menu-open',
                    'submenu' => [
                        [ 'text'=>'parameters', 'icon'=>'fas fa-sliders-h',   'route'=>'configuraciones.index', 'can'=>'menu.configuraciones' ],
                        [ 'text'=>'theme',      'icon'=>'fas fa-paint-brush', 'route'=>'configuraciones.tema',  'can'=>'menu.configuraciones' ],
                    ],
                ],
            ],
        ]);
    }
}
