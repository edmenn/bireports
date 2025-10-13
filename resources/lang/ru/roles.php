<?php

return [
    'page' => [
        'title' => 'Роли и доступ',
        'create' => 'Новая роль',
        'edit' => 'Редактировать роль',
    ],

    'alerts' => [
        'success' => 'Успешно',
        'warning' => 'Предупреждение',
        'error' => 'Ошибка',
        'protected' => 'Роль <strong>admin</strong> обеспечивает доступ ко <strong>всему</strong>, и ее разрешения нельзя изменить.',
    ],

    'index' => [
        'header_button' => 'Новая роль',
        'list_title' => 'Список ролей',
        'search_placeholder' => 'Поиск роли по названию',
        'search' => 'Поиск',
        'clear' => 'Сбросить',
        'table' => [
            'name' => 'Название',
            'users' => 'Пользователи',
            'permissions' => 'Разрешения',
            'actions' => 'Действия',
            'cant_delete' => 'Нельзя удалить: назначено :count пользователь(ей)',
            'empty' => 'Роли еще не созданы.',
        ],
        'delete_confirm' => 'Удалить роль :name?',
    ],

    'form' => [
        'card_title' => 'Сведения о роли',
        'name_label' => 'Название',
        'name_placeholder' => 'Например: аудитор, менеджер, аналитик',
        'badge_bg_label' => 'Цвет фона',
        'badge_text_label' => 'Цвет текста/границы',
        'buttons' => [
            'save' => 'Сохранить',
            'cancel' => 'Отмена',
            'delete' => 'Удалить',
        ],
    ],

    'permissions' => [
        'select_all' => 'Выбрать все',
        'deselect_all' => 'Снять выделение',
    ],
];
