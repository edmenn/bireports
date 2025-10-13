<?php

return [
    'menu' => [
        'title' => 'Мое пространство',
        'home' => 'Главная',
        'favorites' => 'Избранное',
        'most_accessed' => 'Часто просматриваемые',
        'applications' => 'Приложения',
    ],
    'home' => [
        'title' => 'Главная',
        'folders' => [
            'title' => 'Папки',
            'empty' => 'Папки не назначены.',
        ],
        'reports' => [
            'title' => 'Отчеты',
            'in_folder' => 'в «:name»',
            'empty' => 'Нет доступных отчетов.',
        ],
    ],
    'favorites' => [
        'title' => 'Мое избранное',
        'empty' => 'Вы еще не добавили избранные отчеты.',
        'flash' => [
            'added' => 'Отчет добавлен в избранное.',
            'removed' => 'Отчет удален из избранного.',
        ],
    ],
    'most_accessed' => [
        'title' => 'Часто просматриваемые',
        'empty' => 'Данные об обращениях еще не собраны.',
        'columns' => [
            'report' => 'Отчет',
            'views' => 'Просмотры',
        ],
        'actions' => [
            'open' => 'Открыть',
        ],
    ],
    'report_card' => [
        'open' => 'Открыть',
        'open_title' => 'Открыть :name',
        'cover_aria' => 'Обложка отчета',
        'cover_alt' => 'Обложка для :name',
        'cover_generic_aria' => 'Иллюстрация обложки по умолчанию',
        'favorite_add' => 'Добавить в избранное',
        'favorite_remove' => 'Удалить из избранного',
        'workspace_missing' => 'Рабочая область не назначена',
    ],
];
