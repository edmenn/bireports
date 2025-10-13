<?php

return [
    'page' => [
        'title' => 'Аудит изменений',
        'header' => 'Аудит изменений (система + конфигурация)',
    ],
    'buttons' => [
        'clear_filters' => 'Сбросить фильтры',
        'search' => 'Поиск',
    ],
    'cards' => [
        'filters' => [
            'title' => 'Фильтры',
        ],
        'events' => [
            'title' => 'События',
        ],
    ],
    'filters' => [
        'action_label' => 'Действие',
        'all_actions' => 'Все',
        'model_label' => 'Модель',
        'model_placeholder' => 'Класс модели (например, App\\Models\\User)',
        'user_label' => 'Пользователь',
        'user_placeholder' => 'ID пользователя',
    ],
    'actions' => [
        'created' => 'Создано',
        'updated' => 'Обновлено',
        'deleted' => 'Удалено',
        'attached' => 'Связано',
        'detached' => 'Отвязано',
        'synced' => 'Синхронизировано',
    ],
    'sources' => [
        'sistema' => 'Система',
        'manual' => 'Вручную',
        'usuario' => 'Пользователь',
    ],
    'table' => [
        'date' => 'Дата',
        'user' => 'Пользователь',
        'source' => 'Источник',
        'action' => 'Действие',
        'model' => 'Модель',
        'entity' => 'Сущность',
        'details' => 'Подробности',
        'view_changes' => 'Просмотреть изменения',
        'key' => 'Ключ',
        'before' => 'До',
        'after' => 'После',
        'no_events' => 'Нет событий.',
        'no_details' => '-',
    ],
];
