<?php

$vendorPath = function_exists('base_path')
    ? base_path('vendor/jeroennoten/laravel-adminlte/resources/lang/en/menu.php')
    : dirname(__DIR__, 3) . '/vendor/jeroennoten/laravel-adminlte/resources/lang/en/menu.php';

$vendor = file_exists($vendorPath) ? require $vendorPath : [];

return array_merge($vendor, [
    'search' => 'Search',
    'nav' => [
        'portal' => 'My Space',
        'portal_home' => 'Home',
        'portal_favorites' => 'Favorites',
        'portal_most_accessed' => 'Most accessed',
        'portal_applications' => 'Applications',
        'power_bi' => 'Power BI',
        'reports' => 'Reports',
        'datasets' => 'Datasets',
        'applications' => 'Applications',
        'workspaces' => 'Workspaces',
        'capacities' => 'Capacities',
        'organization' => 'Organization',
        'users' => 'Users',
        'groups' => 'Groups',
        'folders' => 'Folders',
        'roles' => 'Roles & Access',
        'audit' => 'Audit',
        'audit_usage_metrics' => 'Usage metrics',
        'audit_reset' => 'Reset audit',
        'audit_changes' => 'Changes (system & settings)',
        'settings' => 'Settings',
        'settings_parameters' => 'Parameters',
        'settings_theme' => 'Theme',
    ],
    'user' => [
        'profile' => 'Profile',
        'logout' => 'Sign out',
    ],
]);
