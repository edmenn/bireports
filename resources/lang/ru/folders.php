<?php

return [
    'page' => [
        'title' => 'Папки',
        'create' => 'Новая папка',
        'edit' => 'Редактировать папку',
    ],

    'cards' => [
        'details' => [
            'title' => 'Сведения о папке',
        ],
        'assignments' => [
            'title' => 'Назначения',
        ],
    ],

    'form' => [
        'name_label' => 'Название',
        'name_placeholder' => 'Введите название папки',
        'description_label' => 'Описание',
        'description_placeholder' => 'Необязательное описание',
        'users_label' => 'Пользователи',
        'groups_label' => 'Группы',
        'reports_label' => 'Отчеты',
        'buttons' => [
            'back' => 'Назад',
            'save' => 'Сохранить',
            'cancel' => 'Отмена',
        ],
    ],

    'alerts' => [
        'success_title' => 'Успех',
    ],

    'flash' => [
        'created' => 'Папка создана.',
        'updated' => 'Папка обновлена.',
        'deleted' => 'Папка удалена.',
    ],

    'index' => [
        'card_title' => 'Список папок',
        'create_button' => 'Новая папка',
    ],

    'search' => [
        'placeholder' => 'Поиск по названию или описанию',
        'submit' => 'Поиск',
        'clear' => 'Сбросить',
    ],

    'table' => [
        'name' => 'Название',
        'description' => 'Описание',
        'users' => 'Пользователи',
        'groups' => 'Группы',
        'reports' => 'Отчеты',
        'actions' => 'Действия',
        'edit' => 'Редактировать',
        'edit_title' => 'Редактировать папку',
        'delete' => 'Удалить',
        'delete_title' => 'Удалить папку',
        'delete_confirm' => 'Удалить эту папку? Это действие нельзя отменить.',
    ],

    'empty' => [
        'users' => 'Нет доступных пользователей.',
        'groups' => 'Нет доступных групп.',
        'reports' => 'Нет доступных отчетов.',
        'list' => 'Папки не найдены.',
    ],

    'workspaces' => [
        'page' => [
            'title' => 'Рабочие области',
            'subtitle' => 'Импортированный список из Microsoft Fabric / Power BI',
        ],

        'actions' => [
            'import_now' => 'Импортировать сейчас',
            'copy_capacity' => 'Скопировать ID вместимости',
            'view_reports' => 'Просмотреть отчеты',
        ],

        'empty' => [
            'title' => 'Рабочие области еще не импортированы',
            'hint' => 'Выполните импорт, чтобы увидеть рабочие области.',
        ],

        'kpis' => [
            'total' => 'Всего рабочих областей',
            'premium' => 'На премиальной вместимости',
            'no_capacity' => 'Без назначенной вместимости',
        ],

        'filters' => [
            'search_label' => 'Поиск',
            'search_placeholder' => 'Фильтр по названию, типу или ID вместимости.',
            'premium_label' => 'Premium',
            'premium_all' => 'Все',
            'premium_yes' => 'Только Premium',
            'premium_no' => 'Только без Premium',
            'type_label' => 'Тип',
            'type_all' => 'Все',
            'type_empty' => 'Нет типа',
        ],

        'table' => [
            'headers' => [
                'name' => 'Название',
                'type' => 'Тип',
                'premium' => 'Premium',
                'capacity' => 'ID вместимости',
                'actions' => 'Действия',
            ],
            'id_label' => 'ID:',
        ],

        'badges' => [
            'no_type' => 'Нет типа',
            'yes' => 'Да',
            'no' => 'Нет',
        ],

        'js' => [
            'copy_failed' => 'Не удалось скопировать',
        ],

        'show' => [
            'title' => 'Рабочая область: :name',
            'empty' => 'У этой рабочей области нет импортированных отчетов.',
            'table' => [
                'title' => 'Название',
                'dataset' => 'ID набора данных',
                'actions' => 'Действия',
                'open' => 'Открыть',
            ],
        ],
    ],

];
