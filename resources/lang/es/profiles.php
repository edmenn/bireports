<?php

return [
    'assigned_roles' => [
        'label' => 'Perfiles asignados',
    ],

    'alerts' => [
        'entra' => [
            'title' => 'Información',
            'profile_message' => 'Este perfil proviene de Microsoft Entra. El nombre se sincroniza con tu directorio corporativo y no puede cambiarse desde la aplicación.',
            'password_message' => 'Usas inicio de sesión con Microsoft Entra. La contraseña se administra en tu proveedor.',
        ],
    ],

    'flash' => [
        'created' => 'Perfil creado.',
        'updated' => 'Perfil actualizado.',
        'deleted' => 'Perfil eliminado.',
        'cannot_rename_admin' => 'El rol "admin" no puede ser renombrado.',
        'cannot_delete_admin' => 'El rol "admin" no puede eliminarse.',
        'cannot_delete_assigned' => 'No se puede eliminar el perfil: tiene :count usuario(s) asignado(s).',
        'assignments_unknown' => 'No se pudo verificar usuarios asignados; no se elimina por seguridad.',
        'delete_failed' => 'No se pudo eliminar el perfil.',
    ],

    'permissions' => [
        'unknown_section' => 'Otros permisos',
        'section_prefix' => [
            'menu' => 'Menú',
            'portal' => 'Portal',
            'reports' => 'Informes',
            'datasets' => 'Datasets',
            'workspaces' => 'Espacios de trabajo',
            'applications' => 'Aplicaciones',
            'application_sections' => 'Aplicaciones',
            'capacities' => 'Capacidades',
            'users' => 'Usuarios',
            'roles' => 'Perfiles',
            'groups' => 'Grupos',
            'folders' => 'Carpetas',
            'config' => 'Configuración',
            'audit' => 'Auditoría',
        ],
        'sections' => [
            'menu' => 'Menú y Navegación',
            'portal' => 'Portal de acceso',
            'reports' => 'Informes y Embebido',
            'datasets' => 'Datasets',
            'workspaces' => 'Espacios de trabajo',
            'applications' => 'Aplicaciones',
            'capacities' => 'Capacidades',
            'users' => 'Administración de usuarios',
            'roles' => 'Perfiles y accesos',
            'groups' => 'Grupos',
            'folders' => 'Carpetas',
            'config' => 'Configuración',
            'audit' => 'Auditoría',
            'other' => 'Otros permisos',
        ],
        'labels' => [
            'menu' => [
                'portal' => 'Portal: Mi espacio',
                'reports_index' => 'Power BI: Informes',
                'workspaces' => 'Power BI: Espacios de trabajo',
                'capacities' => 'Power BI: Capacidades',
                'users' => 'Organización: Usuarios',
                'groups' => 'Organización: Grupos',
                'folders' => 'Organización: Carpetas',
                'applications' => 'Organización: Aplicaciones',
                'configurations' => 'Configuraciones',
                'audit' => 'Auditoría',
                'roles' => 'Organización: Roles y accesos',
            ],
            'portal' => [
                'view' => 'Acceder al portal',
            ],
            'reports' => [
                'view' => 'Ver y acceder a informes',
                'create' => 'Crear informes',
                'edit' => 'Editar informes',
                'delete' => 'Eliminar informes',
                'export' => 'Exportar informes',
            ],
            'datasets' => [
                'view' => 'Ver datasets',
                'refresh' => 'Ejecutar refresh',
                'schedule_view' => 'Ver programación de refresh',
                'schedule_edit' => 'Editar programación de refresh',
                'export' => 'Exportar datasets',
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
                'manage' => 'Administrar secciones de aplicaciones',
            ],
            'capacities' => [
                'view' => 'Ver capacidades',
                'pause' => 'Pausar capacidades',
                'resume' => 'Reanudar capacidades',
                'schedule_view' => 'Ver programación de capacidades',
                'schedule_edit' => 'Editar programación de capacidades',
                'audit_view' => 'Ver auditoría de capacidades',
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
                'view' => 'Ver configuración',
                'edit' => 'Editar configuración',
            ],
            'audit' => [
                'view' => 'Ver auditoría',
                'export' => 'Exportar auditoría',
                'reset' => 'Restablecer auditoría',
            ],
        ],
    ],
];
