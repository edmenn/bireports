<?php

$vendorPath = function_exists('base_path')
    ? base_path('vendor/jeroennoten/laravel-adminlte/resources/lang/es/menu.php')
    : dirname(__DIR__, 3) . '/vendor/jeroennoten/laravel-adminlte/resources/lang/es/menu.php';

$vendor = file_exists($vendorPath) ? require $vendorPath : [];

return array_merge($vendor, [
    'search' => 'Buscar',
    'nav' => [
        'portal' => 'Mi espacio',
        'portal_home' => 'Inicio',
        'portal_favorites' => 'Favoritos',
        'portal_most_accessed' => 'Más accedidos',
        'portal_applications' => 'Aplicaciones',
        'power_bi' => 'Power BI',
        'reports' => 'Informes',
        'reports_subscriptions' => 'Suscripciones de reportes',
        'datasets' => 'Conjuntos de datos',
        'applications' => 'Aplicaciones',
        'workspaces' => 'Espacios de trabajo',
        'capacities' => 'Capacidades',
        'organization' => 'Organización',
        'users' => 'Usuarios',
        'groups' => 'Grupos',
        'folders' => 'Carpetas',
        'roles' => 'Perfiles y accesos',
        'audit' => 'Auditoría',
        'audit_usage_metrics' => 'Métricas de uso',
        'audit_reset' => 'Reiniciar auditoría',
        'audit_changes' => 'Cambios',
        'settings' => 'Configuraciones',
        'settings_parameters' => 'Parámetros',
        'settings_theme' => 'Tema',
    ],
    'user' => [
        'profile' => 'Perfil',
        'logout' => 'Salir',
    ],
]);
