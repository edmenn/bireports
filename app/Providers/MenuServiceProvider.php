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
                        'icon' => 'nav-icon fas fa-user-circle fa-fw',
                        'can'  => 'menu.portal',
                        'classes' => 'menu-open',
                        'submenu' => [
                            [ 'text' => 'nav.portal_home',          'icon'=>'nav-icon fas fa-home fa-fw',       'route'=>'portal.index',         'can'=>'menu.portal' ],
                            [ 'text' => 'nav.portal_favorites',     'icon'=>'nav-icon fas fa-star fa-fw',       'route'=>'portal.favorites',     'can'=>'menu.portal' ],
                            [ 'text' => 'nav.portal_most_accessed', 'icon'=>'nav-icon fas fa-fire fa-fw',       'route'=>'portal.most-accessed', 'can'=>'menu.portal' ],
                            [ 'text' => 'nav.portal_applications',  'icon'=>'nav-icon fas fa-th-large fa-fw',   'route'=>'portal.apps',          'can'=>'menu.portal' ],
                        ],
                    ],

                    // Power BI
                    [
                        'text' => 'nav.power_bi',
                        'icon' => 'nav-icon fas fa-bolt fa-fw',
                        'can'  => 'menu.reports_index',
                        'classes' => 'menu-open',
                        'submenu' => [
                            [ 'text' => 'nav.reports',      'icon'=>'nav-icon fas fa-chart-bar fa-fw',       'route'=>'reports.index',         'can'=>'menu.reports_index' ],
                            [ 'text' => 'nav.datasets',     'icon'=>'nav-icon fas fa-database fa-fw',        'route'=>'datasets.index',        'can'=>'menu.reports_index' ],
                            [ 'text' => 'nav.applications', 'icon'=>'nav-icon fas fa-th fa-fw',              'route'=>'applications.index',    'can'=>'menu.applications' ],
                            [ 'text' => 'nav.workspaces',   'icon'=>'nav-icon fas fa-project-diagram fa-fw', 'route'=>'workspaces.index',      'can'=>'menu.workspaces' ],
                            [ 'text' => 'nav.capacities',   'icon'=>'nav-icon fas fa-cogs fa-fw',            'route'=>'capacities.index',      'can'=>'menu.capacities' ],
                        ],
                    ],

                    // Organization
                    [
                        'text' => 'nav.organization',
                        'icon' => 'nav-icon fas fa-sitemap fa-fw',
                        'can'  => 'menu.users',
                        'classes' => 'menu-open',
                        'submenu' => [
                            [ 'text' => 'nav.users',   'icon'=>'nav-icon fas fa-users fa-fw',        'route'=>'users.index',   'can'=>'menu.users' ],
                            [ 'text' => 'nav.groups',  'icon'=>'nav-icon fas fa-user-friends fa-fw', 'route'=>'groups.index',  'can'=>'menu.groups' ],
                            [ 'text' => 'nav.folders', 'icon'=>'nav-icon fas fa-folder fa-fw',       'route'=>'folders.index', 'can'=>'menu.folders' ],
                            [ 'text' => 'nav.roles',   'icon'=>'nav-icon fas fa-user-shield fa-fw',  'route'=>'roles.index',   'can'=>'menu.roles' ],
                        ],
                    ],

                    // Audit
                    [
                        'text' => 'nav.audit',
                        'icon' => 'nav-icon fas fa-chart-line fa-fw',
                        'can'  => 'menu.audit',
                        'classes' => 'menu-open',
                        'submenu' => [
                            [ 'text' => 'nav.audit_usage_metrics', 'icon'=>'nav-icon fas fa-chart-bar fa-fw',    'route'=>'audit.index',        'can'=>'menu.audit' ],
                            [ 'text' => 'nav.audit_changes',       'icon'=>'nav-icon fas fa-exchange-alt fa-fw', 'route'=>'audit.changes.index','can'=>'menu.audit' ],
                        ],
                    ],

                    // Settings
                    [
                        'text' => 'nav.settings',
                        'icon' => 'nav-icon fas fa-cogs fa-fw',
                        'can'  => 'menu.configurations',
                        'classes' => 'menu-open',
                        'submenu' => [
                            [ 'text' => 'nav.settings_parameters', 'icon'=>'nav-icon fas fa-sliders-h fa-fw',   'route'=>'configurations.index', 'can'=>'menu.configurations' ],
                            [ 'text' => 'nav.settings_theme',      'icon'=>'nav-icon fas fa-paint-brush fa-fw', 'route'=>'configurations.theme', 'can'=>'menu.configurations' ],
                        ],
                    ],
                ],
            ]);
        });
    }
}
