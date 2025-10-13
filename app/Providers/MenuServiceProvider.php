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
                    'text' => 'common.menu.portal',
                    'icon' => 'fas fa-user-circle',
                    'can'  => 'menu.portal',
                    'classes' => 'menu-open',
                    'submenu' => [
                        [ 'text' => 'common.menu.portal_home',          'icon'=>'fas fa-home',       'route'=>'portal.index',         'can'=>'menu.portal' ],
                        [ 'text' => 'common.menu.portal_favorites',     'icon'=>'fas fa-star',       'route'=>'portal.favorites',     'can'=>'menu.portal' ],
                        [ 'text' => 'common.menu.portal_most_accessed', 'icon'=>'fas fa-fire',       'route'=>'portal.most-accessed', 'can'=>'menu.portal' ],
                        [ 'text' => 'common.menu.portal_applications',  'icon'=>'fas fa-th-large',   'route'=>'portal.apps',          'can'=>'menu.portal' ],
                    ],
                ],

                // Power BI
                [
                    'text' => 'common.menu.power_bi',
                    'icon' => 'fas fa-bolt',
                    'can'  => 'menu.reports_index',
                    'classes' => 'menu-open',
                    'submenu' => [
                        [ 'text' => 'common.menu.reports',      'icon'=>'fas fa-chart-bar',       'route'=>'reports.index',         'can'=>'menu.reports_index' ],
                        [ 'text' => 'common.menu.datasets',     'icon'=>'fas fa-database',        'route'=>'datasets.index',        'can'=>'menu.reports_index' ],
                        [ 'text' => 'common.menu.applications', 'icon'=>'fas fa-th',              'route'=>'applications.index',    'can'=>'menu.applications' ],
                        [ 'text' => 'common.menu.workspaces',   'icon'=>'fas fa-project-diagram', 'route'=>'workspaces.index',      'can'=>'menu.workspaces' ],
                        [ 'text' => 'common.menu.capacities',   'icon'=>'fas fa-cogs',            'route'=>'capacities.index',      'can'=>'menu.capacities' ],
                    ],
                ],

                // Organization
                [
                    'text' => 'common.menu.organization',
                    'icon' => 'fas fa-sitemap',
                    'can'  => 'menu.users',
                    'classes' => 'menu-open',
                    'submenu' => [
                        [ 'text' => 'common.menu.users',   'icon'=>'fas fa-users',        'route'=>'users.index',   'can'=>'menu.users' ],
                        [ 'text' => 'common.menu.groups',  'icon'=>'fas fa-user-friends', 'route'=>'groups.index',  'can'=>'menu.groups' ],
                        [ 'text' => 'common.menu.folders', 'icon'=>'fas fa-folder',       'route'=>'folders.index', 'can'=>'menu.folders' ],
                        [ 'text' => 'common.menu.roles',   'icon'=>'fas fa-user-shield',  'route'=>'roles.index',   'can'=>'menu.roles' ],
                    ],
                ],

                // Audit
                [
                    'text' => 'common.menu.audit',
                    'icon' => 'fas fa-chart-line',
                    'can'  => 'menu.audit',
                    'classes' => 'menu-open',
                    'submenu' => [
                        [ 'text' => 'common.menu.audit_usage_metrics', 'icon'=>'fas fa-chart-bar',    'route'=>'audit.index',        'can'=>'menu.audit' ],
                        [ 'text' => 'common.menu.audit_reset',         'icon'=>'fas fa-broom',        'route'=>'admin.audit.reset.show', 'can'=>'menu.audit' ],
                        [ 'text' => 'common.menu.audit_changes',       'icon'=>'fas fa-exchange-alt', 'route'=>'audit.changes.index','can'=>'menu.audit' ],
                    ],
                ],

                // Settings
                [
                    'text' => 'common.menu.settings',
                    'icon' => 'fas fa-cogs',
                    'can'  => 'menu.configurations',
                    'classes' => 'menu-open',
                    'submenu' => [
                        [ 'text' => 'common.menu.settings_parameters', 'icon'=>'fas fa-sliders-h',   'route'=>'configurations.index', 'can'=>'menu.configurations' ],
                        [ 'text' => 'common.menu.settings_theme',      'icon'=>'fas fa-paint-brush', 'route'=>'configurations.theme', 'can'=>'menu.configurations' ],
                    ],
                ],
            ],
        ]);
    }
}
