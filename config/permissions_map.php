<?php

return [
    // Orden sugerido de secciones y labels
    'order' => [
        'menu',
        'portal',
        'reports',
        'datasets',
        'workspaces',
        'applications',
        'capacities',
        'users',
        'roles',
        'groups',
        'folders',
        'config',
        'audit',
        'otros',
    ],

    'unknown_section_label' => 'profiles.permissions.unknown_section',

    // Mapa de secciones a labels y permisos con descripciones claras
    'sections' => [
        'menu' => [
            'label' => 'profiles.permissions.sections.menu',
            'permissions' => [
                'menu.portal' => 'profiles.permissions.labels.menu.portal',
                'menu.reports_index' => 'profiles.permissions.labels.menu.reports_index',
                'menu.workspaces' => 'profiles.permissions.labels.menu.workspaces',
                'menu.capacities' => 'profiles.permissions.labels.menu.capacities',
                'menu.users' => 'profiles.permissions.labels.menu.users',
                'menu.groups' => 'profiles.permissions.labels.menu.groups',
                'menu.folders' => 'profiles.permissions.labels.menu.folders',
                'menu.applications' => 'profiles.permissions.labels.menu.applications',
                'menu.configuraciones' => 'profiles.permissions.labels.menu.configuraciones',
                'menu.auditoria' => 'profiles.permissions.labels.menu.auditoria',
                // 'menu.roles' => 'profiles.permissions.labels.menu.roles',
            ],
        ],
        'portal' => [
            'label' => 'profiles.permissions.sections.portal',
            'permissions' => [
                'portal.view' => 'profiles.permissions.labels.portal.view',
            ],
        ],
        'reports' => [
            'label' => 'profiles.permissions.sections.reports',
            'permissions' => [
                'reports.view' => 'profiles.permissions.labels.reports.view',
                'reports.create' => 'profiles.permissions.labels.reports.create',
                'reports.edit' => 'profiles.permissions.labels.reports.edit',
                'reports.delete' => 'profiles.permissions.labels.reports.delete',
                'reports.export' => 'profiles.permissions.labels.reports.export',
            ],
        ],
        'datasets' => [
            'label' => 'profiles.permissions.sections.datasets',
            'permissions' => [
                'datasets.view' => 'profiles.permissions.labels.datasets.view',
                'datasets.refresh' => 'profiles.permissions.labels.datasets.refresh',
                'datasets.schedule.view' => 'profiles.permissions.labels.datasets.schedule_view',
                'datasets.schedule.edit' => 'profiles.permissions.labels.datasets.schedule_edit',
                'datasets.export' => 'profiles.permissions.labels.datasets.export',
            ],
        ],
        'workspaces' => [
            'label' => 'profiles.permissions.sections.workspaces',
            'permissions' => [
                'workspaces.view' => 'profiles.permissions.labels.workspaces.view',
            ],
        ],
        'applications' => [
            'label' => 'profiles.permissions.sections.applications',
            'permissions' => [
                'applications.view' => 'profiles.permissions.labels.applications.view',
                'applications.create' => 'profiles.permissions.labels.applications.create',
                'applications.edit' => 'profiles.permissions.labels.applications.edit',
                'applications.delete' => 'profiles.permissions.labels.applications.delete',
                'application_sections.manage' => 'profiles.permissions.labels.application_sections.manage',
            ],
        ],
        'capacities' => [
            'label' => 'profiles.permissions.sections.capacities',
            'permissions' => [
                'capacities.view' => 'profiles.permissions.labels.capacities.view',
                'capacities.pause' => 'profiles.permissions.labels.capacities.pause',
                'capacities.resume' => 'profiles.permissions.labels.capacities.resume',
                'capacities.schedule.view' => 'profiles.permissions.labels.capacities.schedule_view',
                'capacities.schedule.edit' => 'profiles.permissions.labels.capacities.schedule_edit',
                'capacities.audit.view' => 'profiles.permissions.labels.capacities.audit_view',
            ],
        ],
        'users' => [
            'label' => 'profiles.permissions.sections.users',
            'permissions' => [
                'users.view' => 'profiles.permissions.labels.users.view',
                'users.create' => 'profiles.permissions.labels.users.create',
                'users.edit' => 'profiles.permissions.labels.users.edit',
                'users.delete' => 'profiles.permissions.labels.users.delete',
                'users.sync' => 'profiles.permissions.labels.users.sync',
            ],
        ],
        'roles' => [
            'label' => 'profiles.permissions.sections.roles',
            'permissions' => [
                'roles.view' => 'profiles.permissions.labels.roles.view',
                'roles.create' => 'profiles.permissions.labels.roles.create',
                'roles.edit' => 'profiles.permissions.labels.roles.edit',
                'roles.delete' => 'profiles.permissions.labels.roles.delete',
            ],
        ],
        'groups' => [
            'label' => 'profiles.permissions.sections.groups',
            'permissions' => [
                'groups.view' => 'profiles.permissions.labels.groups.view',
                'groups.create' => 'profiles.permissions.labels.groups.create',
                'groups.edit' => 'profiles.permissions.labels.groups.edit',
                'groups.delete' => 'profiles.permissions.labels.groups.delete',
            ],
        ],
        'folders' => [
            'label' => 'profiles.permissions.sections.folders',
            'permissions' => [
                'folders.view' => 'profiles.permissions.labels.folders.view',
                'folders.create' => 'profiles.permissions.labels.folders.create',
                'folders.edit' => 'profiles.permissions.labels.folders.edit',
                'folders.delete' => 'profiles.permissions.labels.folders.delete',
            ],
        ],
        'config' => [
            'label' => 'profiles.permissions.sections.config',
            'permissions' => [
                'config.view' => 'profiles.permissions.labels.config.view',
                'config.edit' => 'profiles.permissions.labels.config.edit',
            ],
        ],
        'audit' => [
            'label' => 'profiles.permissions.sections.audit',
            'permissions' => [
                'audit.view' => 'profiles.permissions.labels.audit.view',
                'audit.export' => 'profiles.permissions.labels.audit.export',
                'audit.reset' => 'profiles.permissions.labels.audit.reset',
            ],
        ],
        'otros' => [
            'label' => 'profiles.permissions.sections.otros',
            'permissions' => [],
        ],
    ],
];
