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
        ],
        'sections' => [
            'menu' => 'Menú y Navegación',
            'reports' => 'Informes y Embebido',
            'otros' => 'Otros permisos',
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
                'configuraciones' => 'Configuraciones',
                'auditoria' => 'Auditoría',
                'roles' => 'Organización: Perfiles y Accesos',
            ],
            'reports' => [
                'view' => 'Ver y acceder a informes',
            ],
        ],
    ],
];
