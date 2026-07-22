<?php

$vendorPath = function_exists('base_path')
    ? base_path('vendor/jeroennoten/laravel-adminlte/resources/lang/pt/menu.php')
    : dirname(__DIR__, 3) . '/vendor/jeroennoten/laravel-adminlte/resources/lang/pt/menu.php';

$vendor = file_exists($vendorPath) ? require $vendorPath : [];

return array_merge($vendor, [
    'main_navigation' => 'NAVEGAÇÃO PRINCIPAL',
    'blog' => 'Blog',
    'pages' => 'Páginas',
    'account_settings' => 'CONFIGURAÇÕES DA CONTA',
    'profile' => 'Perfil',
    'change_password' => 'Alterar senha',
    'multilevel' => 'Multinível',
    'level_one' => 'Nível 1',
    'level_two' => 'Nível 2',
    'level_three' => 'Nível 3',
    'labels' => 'RÓTULOS',
    'important' => 'Importante',
    'warning' => 'Aviso',
    'information' => 'Informação',
    'search' => 'Pesquisar',
    'nav' => [
        'portal' => 'Meu espaço',
        'portal_home' => 'Início',
        'portal_favorites' => 'Favoritos',
        'portal_most_accessed' => 'Mais acessados',
        'portal_applications' => 'Aplicativos',
        'power_bi' => 'Power BI',
        'reports' => 'Relatórios',
        'reports_subscriptions' => 'Assinaturas de relatórios',
        'datasets' => 'Conjuntos de dados',
        'applications' => 'Aplicativos',
        'workspaces' => 'Espaços de trabalho',
        'capacities' => 'Capacidades',
        'organization' => 'Organização',
        'users' => 'Usuários',
        'groups' => 'Grupos',
        'folders' => 'Pastas',
        'roles' => 'Perfis e acessos',
        'audit' => 'Auditoria',
        'audit_usage_metrics' => 'Métricas de uso',
        'audit_reset' => 'Reiniciar auditoria',
        'audit_changes' => 'Alterações',
        'settings' => 'Configurações',
        'settings_parameters' => 'Parâmetros',
        'settings_theme' => 'Tema',
    ],
    'user' => [
        'profile' => 'Perfil',
        'logout' => 'Sair',
    ],
]);
