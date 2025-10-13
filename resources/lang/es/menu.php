<?php

$legacy = [
    'search' => 'Buscar',
    'my_space' => 'Mi espacio',
    'home' => 'Inicio',
    'favorites' => 'Favoritos',
    'most_accessed' => 'Más accedidos',
    'my_reports' => 'Mis reportes',
    'power_bi' => 'Power BI',
    'reports' => 'Informes',
    'datasets' => 'Conjuntos de datos',
    'apps' => 'Aplicaciones',
    'applications' => 'Aplicaciones',
    'workspaces' => 'Espacios de trabajo',
    'capacities' => 'Capacidades',
    'organization' => 'Organización',
    'users' => 'Usuarios',
    'groups' => 'Grupos',
    'folders' => 'Carpetas',
    'roles' => 'Perfiles y Accesos',
    'audit' => 'Auditoría',
    'usage_metrics' => 'Métricas de uso',
    'audit_reset' => 'Reiniciar auditoría',
    'changes' => 'Cambios (Sistema + Config)',
    'settings' => 'Configuraciones',
    'parameters' => 'Parámetros',
    'theme' => 'Tema',
];

$vendor = require base_path('vendor/jeroennoten/laravel-adminlte/resources/lang/es/menu.php');
$common = require __DIR__ . '/common.php';

return array_merge($legacy, $vendor, [
    'common' => [
        'menu' => $common['menu'] ?? [],
        'footer' => $common['footer'] ?? [],
        'theme' => $common['theme'] ?? [],
    ],
]);
