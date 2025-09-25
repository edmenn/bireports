<?php

return [
    // Orden sugerido de secciones y labels
    'order' => ['menu', 'reports', 'otros'],

    'unknown_section_label' => 'Otros permisos',

    // Mapa de secciones a labels y permisos con descripciones claras
    'sections' => [
        'menu' => [
            'label' => 'Menú y Navegación',
            'permissions' => [
                'menu.portal' => 'Portal: Mi espacio',
                'menu.reports_index' => 'Power BI: Informes',
                'menu.workspaces' => 'Power BI: Espacios de trabajo',
                'menu.capacities' => 'Power BI: Capacidades',
                'menu.users' => 'Organización: Usuarios',
                'menu.groups' => 'Organización: Grupos',
                'menu.folders'=> 'Organización: Carpetas',
                'menu.applications' => 'Organización: Aplicaciones',
                'menu.configuraciones' => 'Configuraciones',
                'menu.auditoria' => 'Auditoría',
                // 'menu.roles' => 'Organización: Perfiles y Accesos', // opcional si lo agregas al seeder
            ],
        ],
        'reports' => [
            'label' => 'Informes y Embebido',
            'permissions' => [
                'reports.view' => 'Ver y acceder a informes',
            ],
        ],
    ],
];





