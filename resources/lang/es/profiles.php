<?php

return [
    'assigned_roles' => [
        'label' => 'Perfiles asignados',
    ],

    'alerts' => [
        'entra' => [
            'title' => 'Información',
            'profile_message' => 'Este perfil proviene de Microsoft Entra. El nombre se sincroniza con tu directorio corporativo y no puede modificarse desde la aplicación.',
            'password_message' => 'Inicias sesión con Microsoft Entra. La contraseña se gestiona desde tu proveedor de identidad.',
        ],
    ],

    'flash' => [
        'created' => 'Perfil creado.',
        'updated' => 'Perfil actualizado.',
        'deleted' => 'Perfil eliminado.',
        'cannot_rename_admin' => 'El perfil "admin" no se puede renombrar.',
        'cannot_delete_admin' => 'El perfil "admin" no se puede eliminar.',
        'cannot_delete_assigned' => 'El perfil no se pudo eliminar porque tiene :count usuario(s) asignado(s).',
        'assignments_unknown' => 'No se pudo verificar usuarios asignados; el perfil no se eliminó por seguridad.',
        'delete_failed' => 'No se pudo eliminar el perfil.',
    ],

    'permissions' => [
        'unknown_section' => 'Otros permisos',
        'section_prefix' => [
            'menu' => 'Menú',
            'portal' => 'Portal',
            'reports' => 'Reportes',
            'datasets' => 'Conjuntos de datos',
            'workspaces' => 'Espacios de trabajo',
            'applications' => 'Aplicaciones',
            'application_sections' => 'Aplicaciones',
            'capacities' => 'Capacidades',
            'users' => 'Usuarios',
            'roles' => 'Perfiles',
            'groups' => 'Grupos',
            'folders' => 'Carpetas',
            'config' => 'Configuraciones',
            'audit' => 'Auditoría',
        ],
        'sections' => [
            'menu' => 'Menú y navegación',
            'portal' => 'Portal de acceso',
            'reports' => 'Reportes e incrustación',
            'datasets' => 'Conjuntos de datos',
            'workspaces' => 'Espacios de trabajo',
            'applications' => 'Aplicaciones',
            'capacities' => 'Capacidades',
            'users' => 'Gestión de usuarios',
            'roles' => 'Perfiles y accesos',
            'groups' => 'Grupos',
            'folders' => 'Carpetas',
            'config' => 'Configuraciones',
            'audit' => 'Auditoría',
            'other' => 'Otros permisos',
        ],
        'labels' => [
            'menu' => [
                'portal' => 'Portal: Mi espacio',
                'reports_index' => 'Power BI: Reportes',
                'workspaces' => 'Power BI: Espacios de trabajo',
                'capacities' => 'Power BI: Capacidades',
                'users' => 'Organización: Usuarios',
                'groups' => 'Organización: Grupos',
                'folders' => 'Organización: Carpetas',
                'applications' => 'Organización: Aplicaciones',
                'configurations' => 'Configuraciones',
                'audit' => 'Auditoría',
                'roles' => 'Organización: Perfiles y accesos',
            ],
            'portal' => [
                'view' => 'Acceder al portal',
            ],
            'reports' => [
                'view' => 'Ver y acceder a reportes',
                'create' => 'Crear reportes',
                'edit' => 'Editar reportes',
                'delete' => 'Eliminar reportes',
                'export' => 'Exportar reportes',
                'subscriptions_manage' => 'Gestionar suscripciones de reportes',
            ],
            'datasets' => [
                'view' => 'Ver conjuntos de datos',
                'refresh' => 'Ejecutar actualización',
                'schedule_view' => 'Ver programación de actualización',
                'schedule_edit' => 'Editar programación de actualización',
                'export' => 'Exportar conjuntos de datos',
            ],
            'workspaces' => [
                'view' => 'Ver espacios de trabajo',
            ],
            'applications' => [
                'view' => 'Ver aplicaciones',
                'create' => 'Crear aplicaciones',
                'edit' => 'Editar aplicaciones',
                'delete' => 'Eliminar aplicaciones',
            ],
            'application_sections' => [
                'manage' => 'Gestionar secciones de aplicaciones',
            ],
            'capacities' => [
                'view' => 'Ver capacidades',
                'pause' => 'Pausar capacidades',
                'resume' => 'Reanudar capacidades',
                'schedule_view' => 'Ver programación de capacidad',
                'schedule_edit' => 'Editar programación de capacidad',
                'audit_view' => 'Ver auditoría de capacidad',
            ],
            'users' => [
                'view' => 'Ver usuarios',
                'create' => 'Crear usuarios',
                'edit' => 'Editar usuarios',
                'delete' => 'Eliminar usuarios',
                'sync' => 'Sincronizar usuarios',
            ],
            'roles' => [
                'view' => 'Ver perfiles',
                'create' => 'Crear perfiles',
                'edit' => 'Editar perfiles',
                'delete' => 'Eliminar perfiles',
            ],
            'groups' => [
                'view' => 'Ver grupos',
                'create' => 'Crear grupos',
                'edit' => 'Editar grupos',
                'delete' => 'Eliminar grupos',
            ],
            'folders' => [
                'view' => 'Ver carpetas',
                'create' => 'Crear carpetas',
                'edit' => 'Editar carpetas',
                'delete' => 'Eliminar carpetas',
            ],
            'config' => [
                'view' => 'Ver configuraciones',
                'edit' => 'Editar configuraciones',
            ],
            'audit' => [
                'view' => 'Ver auditoría',
                'export' => 'Exportar auditoría',
                'reset' => 'Restablecer auditoría',
            ],
        ],
    ],
];
