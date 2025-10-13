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
        // Delay menu override until the application is fully booted so the
        // translator is available for AdminLTE filters.
        $this->app->booted(function () {
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
                        'text' => 'nav.portal',
                        'icon' => 'fas fa-user-circle',
                        'can'  => 'menu.portal',
                        'classes' => 'menu-open',
                        'submenu' => [
                            [ 'text' => 'nav.portal_home',          'icon'=>'fas fa-home',       'route'=>'portal.index',         'can'=>'menu.portal' ],
                            [ 'text' => 'nav.portal_favorites',     'icon'=>'fas fa-star',       'route'=>'portal.favorites',     'can'=>'menu.portal' ],
                            [ 'text' => 'nav.portal_most_accessed', 'icon'=>'fas fa-fire',       'route'=>'portal.most-accessed', 'can'=>'menu.portal' ],
                            [ 'text' => 'nav.portal_applications',  'icon'=>'fas fa-th-large',   'route'=>'portal.apps',          'can'=>'menu.portal' ],
                        ],
                    ],

                    // Power BI
                    [
                        'text' => 'nav.power_bi',
                        'icon' => 'fas fa-bolt',
                        'can'  => 'menu.reports_index',
                        'classes' => 'menu-open',
                        'submenu' => [
                            [ 'text' => 'nav.reports',      'icon'=>'fas fa-chart-bar',       'route'=>'reports.index',         'can'=>'menu.reports_index' ],
                            [ 'text' => 'nav.datasets',     'icon'=>'fas fa-database',        'route'=>'datasets.index',        'can'=>'menu.reports_index' ],
                            [ 'text' => 'nav.applications', 'icon'=>'fas fa-th',              'route'=>'applications.index',    'can'=>'menu.applications' ],
                            [ 'text' => 'nav.workspaces',   'icon'=>'fas fa-project-diagram', 'route'=>'workspaces.index',      'can'=>'menu.workspaces' ],
                            [ 'text' => 'nav.capacities',   'icon'=>'fas fa-cogs',            'route'=>'capacities.index',      'can'=>'menu.capacities' ],
                        ],
                    ],

                    // Organization
                    [
                        'text' => 'nav.organization',
                        'icon' => 'fas fa-sitemap',
                        'can'  => 'menu.users',
                        'classes' => 'menu-open',
                        'submenu' => [
                            [ 'text' => 'nav.users',   'icon'=>'fas fa-users',        'route'=>'users.index',   'can'=>'menu.users' ],
                            [ 'text' => 'nav.groups',  'icon'=>'fas fa-user-friends', 'route'=>'groups.index',  'can'=>'menu.groups' ],
                            [ 'text' => 'nav.folders', 'icon'=>'fas fa-folder',       'route'=>'folders.index', 'can'=>'menu.folders' ],
                            [ 'text' => 'nav.roles',   'icon'=>'fas fa-user-shield',  'route'=>'roles.index',   'can'=>'menu.roles' ],
                        ],
                    ],

                    // Audit
                    [
                        'text' => 'nav.audit',
                        'icon' => 'fas fa-chart-line',
                        'can'  => 'menu.audit',
                        'classes' => 'menu-open',
                        'submenu' => [
                            [ 'text' => 'nav.audit_usage_metrics', 'icon'=>'fas fa-chart-bar',    'route'=>'audit.index',        'can'=>'menu.audit' ],
                            [ 'text' => 'nav.audit_reset',         'icon'=>'fas fa-broom',        'route'=>'admin.audit.reset.show', 'can'=>'menu.audit' ],
                            [ 'text' => 'nav.audit_changes',       'icon'=>'fas fa-exchange-alt', 'route'=>'audit.changes.index','can'=>'menu.audit' ],
                        ],
                    ],

                    // Settings
                    [
                        'text' => 'nav.settings',
                        'icon' => 'fas fa-cogs',
                        'can'  => 'menu.configurations',
                        'classes' => 'menu-open',
                        'submenu' => [
                            [ 'text' => 'nav.settings_parameters', 'icon'=>'fas fa-sliders-h',   'route'=>'configurations.index', 'can'=>'menu.configurations' ],
                            [ 'text' => 'nav.settings_theme',      'icon'=>'fas fa-paint-brush', 'route'=>'configurations.theme', 'can'=>'menu.configurations' ],
                        ],
                    ],
                ],
            ]);
        });
    }
}
