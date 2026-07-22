<?php

return [
    [
        'text' => 'nav.portal', 'icon' => 'fas fa-user-circle', 'can' => 'menu.portal',
        'children' => [
            ['text' => 'nav.portal_home', 'icon' => 'fas fa-home', 'route' => 'portal.index', 'can' => 'menu.portal'],
            ['text' => 'nav.portal_favorites', 'icon' => 'fas fa-star', 'route' => 'portal.favorites', 'can' => 'menu.portal'],
            ['text' => 'nav.portal_most_accessed', 'icon' => 'fas fa-fire', 'route' => 'portal.most-accessed', 'can' => 'menu.portal'],
            ['text' => 'nav.portal_applications', 'icon' => 'fas fa-th-large', 'route' => 'portal.apps', 'can' => 'menu.portal'],
        ],
    ],
    [
        'text' => 'nav.power_bi', 'icon' => 'fas fa-bolt', 'can' => 'menu.reports_index',
        'children' => [
            ['text' => 'nav.reports', 'icon' => 'fas fa-chart-bar', 'route' => 'reports.index', 'can' => 'menu.reports_index'],
            ['text' => 'nav.reports_subscriptions', 'icon' => 'fas fa-envelope-open-text', 'route' => 'subscriptions.index', 'can' => 'report_subscriptions.manage'],
            ['text' => 'nav.datasets', 'icon' => 'fas fa-database', 'route' => 'datasets.index', 'can' => 'menu.reports_index'],
            ['text' => 'nav.workspaces', 'icon' => 'fas fa-project-diagram', 'route' => 'workspaces.index', 'can' => 'menu.workspaces'],
            ['text' => 'nav.capacities', 'icon' => 'fas fa-cogs', 'route' => 'capacities.index', 'can' => 'menu.capacities'],
        ],
    ],
    [
        'text' => 'nav.organization', 'icon' => 'fas fa-sitemap', 'can' => 'menu.users',
        'children' => [
            ['text' => 'nav.users', 'icon' => 'fas fa-users', 'route' => 'users.index', 'can' => 'menu.users'],
            ['text' => 'nav.groups', 'icon' => 'fas fa-user-friends', 'route' => 'groups.index', 'can' => 'menu.groups'],
            ['text' => 'nav.folders', 'icon' => 'fas fa-folder', 'route' => 'folders.index', 'can' => 'menu.folders'],
            ['text' => 'nav.applications', 'icon' => 'fas fa-th', 'route' => 'applications.index', 'can' => 'menu.applications'],
            ['text' => 'nav.roles', 'icon' => 'fas fa-user-shield', 'route' => 'roles.index', 'can' => 'menu.roles'],
        ],
    ],
    [
        'text' => 'nav.audit', 'icon' => 'fas fa-chart-line', 'can' => 'menu.audit',
        'children' => [
            ['text' => 'nav.audit_usage_metrics', 'icon' => 'fas fa-chart-bar', 'route' => 'audit.index', 'can' => 'menu.audit'],
            ['text' => 'nav.audit_changes', 'icon' => 'fas fa-exchange-alt', 'route' => 'audit.changes.index', 'can' => 'menu.audit'],
        ],
    ],
    [
        'text' => 'nav.settings', 'icon' => 'fas fa-cogs', 'can' => 'menu.configurations',
        'children' => [
            ['text' => 'nav.settings_parameters', 'icon' => 'fas fa-sliders-h', 'route' => 'configurations.index', 'can' => 'menu.configurations'],
            ['text' => 'nav.settings_theme', 'icon' => 'fas fa-paint-brush', 'route' => 'configurations.theme', 'can' => 'menu.configurations'],
        ],
    ],
];
