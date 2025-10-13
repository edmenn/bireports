<?php

$legacy = [
    'search' => 'Search',
    'my_space' => 'My Space',
    'home' => 'Home',
    'favorites' => 'Favorites',
    'most_accessed' => 'Most Accessed',
    'my_reports' => 'My Reports',
    'power_bi' => 'Power BI',
    'reports' => 'Reports',
    'datasets' => 'Datasets',
    'apps' => 'Apps',
    'applications' => 'Applications',
    'workspaces' => 'Workspaces',
    'capacities' => 'Capacities',
    'organization' => 'Organization',
    'users' => 'Users',
    'groups' => 'Groups',
    'folders' => 'Folders',
    'roles' => 'Roles & Access',
    'audit' => 'Audit',
    'usage_metrics' => 'Usage Metrics',
    'audit_reset' => 'Reset Audit',
    'changes' => 'Changes (System + Settings)',
    'settings' => 'Settings',
    'parameters' => 'Parameters',
    'theme' => 'Theme',
];

$vendor = require base_path('vendor/jeroennoten/laravel-adminlte/resources/lang/en/menu.php');
$common = require __DIR__ . '/common.php';

return array_merge($legacy, $vendor, [
    'common' => [
        'menu' => $common['menu'] ?? [],
        'footer' => $common['footer'] ?? [],
        'theme' => $common['theme'] ?? [],
    ],
]);
