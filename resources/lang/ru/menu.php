<?php

$vendorPath = function_exists('base_path')
    ? base_path('vendor/jeroennoten/laravel-adminlte/resources/lang/ru/menu.php')
    : dirname(__DIR__, 3) . '/vendor/jeroennoten/laravel-adminlte/resources/lang/ru/menu.php';

$vendor = file_exists($vendorPath) ? require $vendorPath : [];

return array_merge($vendor, [
    'search' => 'Поиск',
    'nav' => [
        'portal' => 'Мое пространство',
        'portal_home' => 'Главная',
        'portal_favorites' => 'Избранное',
        'portal_most_accessed' => 'Часто используемые',
        'portal_applications' => 'Приложения',
        'power_bi' => 'Power BI',
        'reports' => 'Отчёты',
        'reports_subscriptions' => 'Подписки на отчёты',
        'datasets' => 'Наборы данных',
        'applications' => 'Приложения',
        'workspaces' => 'Рабочие области',
        'capacities' => 'Вычислительные мощности',
        'organization' => 'Организация',
        'users' => 'Пользователи',
        'groups' => 'Группы',
        'folders' => 'Папки',
        'roles' => 'Роли и доступ',
        'audit' => 'Аудит',
        'audit_usage_metrics' => 'Метрики использования',
        'audit_reset' => 'Сброс аудита',
        'audit_changes' => 'Изменения (система и настройки)',
        'settings' => 'Настройки',
        'settings_parameters' => 'Параметры',
        'settings_theme' => 'Тема',
    ],
    'user' => [
        'profile' => 'Профиль',
        'logout' => 'Выход',
    ],
]);
