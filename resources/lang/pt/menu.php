<?php

$legacy = [
    'search' => 'Pesquisar',
    'my_space' => 'Meu espaço',
    'home' => 'Início',
    'favorites' => 'Favoritos',
    'most_accessed' => 'Mais acessados',
    'my_reports' => 'Meus relatórios',
    'power_bi' => 'Power BI',
    'reports' => 'Relatórios',
    'datasets' => 'Conjuntos de dados',
    'apps' => 'Aplicativos',
    'applications' => 'Aplicativos',
    'workspaces' => 'Espaços de trabalho',
    'capacities' => 'Capacidades',
    'organization' => 'Organização',
    'users' => 'Usuários',
    'groups' => 'Grupos',
    'folders' => 'Pastas',
    'roles' => 'Perfis e acessos',
    'audit' => 'Auditoria',
    'usage_metrics' => 'Métricas de uso',
    'audit_reset' => 'Reiniciar auditoria',
    'changes' => 'Alterações (Sistema + Config)',
    'settings' => 'Configurações',
    'parameters' => 'Parâmetros',
    'theme' => 'Tema',
];

$vendor = require base_path('vendor/jeroennoten/laravel-adminlte/resources/lang/pt/menu.php');
$common = require __DIR__ . '/common.php';

return array_merge($legacy, $vendor, [
    'common' => [
        'menu' => $common['menu'] ?? [],
        'footer' => $common['footer'] ?? [],
        'theme' => $common['theme'] ?? [],
    ],
]);
