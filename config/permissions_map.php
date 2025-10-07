<?php

return [
    // Orden sugerido de secciones y labels
    'order' => ['menu', 'reports', 'otros'],

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
                'menu.folders'=> 'profiles.permissions.labels.menu.folders',
                'menu.applications' => 'profiles.permissions.labels.menu.applications',
                'menu.configuraciones' => 'profiles.permissions.labels.menu.configuraciones',
                'menu.auditoria' => 'profiles.permissions.labels.menu.auditoria',
                // 'menu.roles' => 'profiles.permissions.labels.menu.roles', // opcional si lo agregas al seeder
            ],
        ],
        'reports' => [
            'label' => 'profiles.permissions.sections.reports',
            'permissions' => [
                'reports.view' => 'profiles.permissions.labels.reports.view',
            ],
        ],
        'otros' => [
            'label' => 'profiles.permissions.sections.otros',
            'permissions' => [],
        ],
    ],
];





