<?php

return [
    'menu' => [
        'title' => 'Mi espacio',
        'home' => 'Inicio',
        'favorites' => 'Favoritos',
        'most_accessed' => 'Mas accedidos',
        'applications' => 'Aplicaciones',
    ],
    'home' => [
        'title' => 'Mi inicio',
        'folders' => [
            'title' => 'Carpetas',
            'empty' => 'Sin carpetas asignadas.',
        ],
        'reports' => [
            'title' => 'Reportes',
            'in_folder' => 'en :name',
            'empty' => 'No hay reportes para mostrar.',
        ],
    ],
    'favorites' => [
        'title' => 'Mis favoritos',
        'empty' => 'Todavía no marcaste favoritos.',
        'flash' => [
            'added' => 'Reporte agregado a favoritos.',
            'removed' => 'Reporte quitado de favoritos.',
        ],
    ],
    'most_accessed' => [
        'title' => 'Mas accedidos',
        'empty' => 'Sin accesos registrados aún.',
        'columns' => [
            'report' => 'Reporte',
            'views' => 'Accesos',
        ],
        'actions' => [
            'open' => 'Abrir',
        ],
    ],
    'report_card' => [
        'open' => 'Abrir',
        'open_title' => 'Abrir :name',
        'cover_aria' => 'Portada del reporte',
        'cover_alt' => 'Portada de :name',
        'cover_generic_aria' => 'Portada genérica',
        'favorite_add' => 'Agregar a favoritos',
        'favorite_remove' => 'Quitar de favoritos',
        'workspace_missing' => 'Workspace no asignado',
    ],
];
