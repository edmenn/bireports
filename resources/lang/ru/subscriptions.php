<?php

return [
    'create' => [
        'title' => 'Создать подписку',
    ],

    'actions' => [
        'create' => 'Создать подписку',
        'close' => 'Закрыть',
        'cancel' => 'Отменить',
        'save' => 'Сохранить',
        'view' => 'Просмотр',
        'edit' => 'Редактировать',
        'delete' => 'Удалить',
        'send_now' => 'Отправить сейчас',
        'insert_snippet' => 'Вставить шаблон',
        'delete_confirm' => 'Удалить эту подписку?',
        'back' => 'Назад',
    ],

    'tabs' => [
        'configuration' => 'Настройки',
        'report' => 'Отчёт',
        'groups' => 'Группы',
        'users' => 'Пользователи',
        'email' => 'Письмо',
        'notifications' => 'Уведомления',
    ],

    'fields' => [
        'name' => 'Название подписки',
        'is_enabled' => 'Включена',
        'enabled_label' => 'Запускать автоматически',
        'format' => 'Формат файла',
        'recurrence' => 'Периодичность',
        'timezone' => 'Часовой пояс',
        'start_date' => 'Дата начала',
        'end_date' => 'Дата окончания',
        'hour' => 'Час',
        'minute' => 'Минута',
        'cron_expression' => 'Cron-выражение',
        'max_attachment_size_mb' => 'Максимальный размер вложения (МБ)',
        'report_id' => 'Отчёт',
        'groups' => 'Группы',
        'users' => 'Пользователи',
        'mail_subject' => 'Тема письма',
        'mail_body' => 'Текст письма',
        'notify_workspace_admins' => 'Уведомлять администраторов рабочего пространства',
        'ensure_capacity_active' => 'Включать емкость перед выполнением',
        'notification_users' => 'Уведомлять пользователей',
        'notification_groups' => 'Уведомлять группы',
        'schedule_window' => 'Окно запуска',
        'created_by' => 'Создано',
        'updated_by' => 'Обновлено',
    ],

    'recurrence' => [
        'once' => 'Однократно',
        'daily' => 'Ежедневно',
        'weekly' => 'Еженедельно',
        'monthly' => 'Ежемесячно',
        'cron' => 'Произвольно (Cron)',
    ],

    'recurrence_labels' => [
        'once' => 'Однократно',
        'daily' => 'Ежедневно',
        'weekly' => 'Еженедельно',
        'monthly' => 'Ежемесячно',
        'cron' => 'Cron-выражение',
    ],

    'hints' => [
        'start_date' => 'Подписка начнёт выполняться с этой даты.',
        'end_date' => 'Оставьте пустым для бессрочного запуска.',
        'cron_expression' => 'Стандартное Cron-выражение из пяти или шести полей.',
        'max_attachment_size_mb' => 'Вложения больше лимита сжимаются или пропускаются.',
        'report_selection' => 'Выберите отчёт, чтобы загрузить страницы и получателей.',
        'pages' => 'Выберите минимум одну страницу для экспорта.',
        'groups' => 'Доступны все группы; их участники получат письмо.',
        'users' => 'Доступны все пользователи; им нужен только доступ к электронной почте.',
        'mail_subject' => 'Поддерживаются плейсхолдеры {{report_name}} и {{date}}.',
        'mail_body' => 'Допустим HTML. Плейсхолдеры {{report_name}} и {{datetime}} поддерживаются.',
        'notify_workspace_admins' => 'Добавляет администраторов рабочего пространства в уведомления об ошибках.',
        'ensure_capacity_active' => 'Если включено, перед запуском подписки емкость возобновляется. После выполнения она не отключается автоматически.',
        'notification_users' => 'Дополнительные пользователи для уведомлений о сбоях.',
        'notification_groups' => 'Члены этих групп будут уведомлены при сбоях.',
    ],

    'placeholders' => [
        'report' => 'Выберите отчёт',
        'subject' => 'Запланированный отчёт: {{report_name}} ({{date}})',
        'body' => '<p>Здравствуйте,</p><p>Во вложении запланированный отчёт.</p><p>С уважением.</p>',
        'groups_empty' => 'Нет доступных групп',
        'users_empty' => 'Нет доступных пользователей',
        'notification_users_empty' => 'Нет настроенных пользователей',
        'notification_groups_empty' => 'Нет настроенных групп',
    ],

    'sections' => [
        'overview' => 'Обзор подписки',
        'recipients' => 'Получатели',
        'email' => 'Доставка по почте',
        'notifications' => 'Уведомления об ошибках',
        'pages' => 'Страницы отчёта',
        'runs' => 'История запусков',
    ],

    'labels' => [
        'deleted_report' => 'Отчёт удалён',
        'no_end_date' => 'Бессрочно',
        'time_at' => 'Время: :time',
        'no_limit' => 'Без ограничения',
        'notify_workspace_admins_yes' => 'Администраторы рабочего пространства будут уведомлены',
        'notify_workspace_admins_no' => 'Администраторы рабочего пространства не уведомляются',
        'ensure_capacity_active_yes' => 'Емкость возобновляется перед запуском',
        'ensure_capacity_active_no' => 'Возобновление емкости отключено',
        'no_groups_selected' => 'Группы не выбраны',
        'no_users_selected' => 'Пользователи не выбраны',
        'subject_default' => 'Запланированный отчёт',
        'body_default' => 'Во вложении запланированный отчёт.',
        'no_notification_users' => 'Пользователи для уведомлений не настроены',
        'no_notification_groups' => 'Группы для уведомлений не настроены',
        'no_pages' => 'Страницы не выбраны',
        'page' => 'Страница',
    ],

    'status' => [
        'enabled' => 'Включена',
        'disabled' => 'Выключена',
    ],

    'statuses' => [
        'pending' => 'В ожидании',
        'running' => 'Выполняется',
        'success' => 'Успешно',
        'failed' => 'Ошибка',
        'skipped' => 'Пропущено',
    ],

    'runs' => [
        'scheduled_time' => '??????????????? ?????',
        'status' => '??????',
        'empty' => '??????? ??? ?? ???????????.',
        'per_page' => 'Количество записей на странице',
    ],

    'index' => [
        'title' => 'Подписки на отчёты',
        'subtitle' => 'Управляйте автоматической отправкой отчётов',
        'empty' => [
            'title' => 'Подписок пока нет',
            'hint' => 'Создайте первую подписку, чтобы начать.',
        ],
    ],

    'table' => [
        'name' => 'Название',
        'report' => 'Отчёт',
        'format' => 'Формат',
        'recurrence' => 'Периодичность',
        'enabled' => 'Включена',
        'updated_at' => 'Обновлена',
        'actions' => 'Действия',
    ],

    'show' => [
        'title' => 'Подписка: :name',
        'subtitle' => 'Отчёт: :report',
        'overview' => 'Обзор подписки',
    ],

    'validation' => [
        'invalid_cron' => 'Введите корректное cron-выражение (пять или шесть полей).',
        'hour_required' => 'Час и минута обязательны для выбранной периодичности.',
        'recipient_required' => 'Выберите хотя бы одного получателя (группу или пользователя).',
        'report_missing' => 'Выберите отчёт для подписки.',
        'report_forbidden' => 'У вас нет доступа к выбранному отчёту.',
        'group_forbidden' => 'Одна из выбранных групп не имеет доступа к отчёту.',
        'user_forbidden' => 'Один из выбранных пользователей не имеет доступа к отчёту.',
        'notification_user_forbidden' => 'Один из пользователей уведомлений не имеет доступа к отчёту.',
        'notification_group_forbidden' => 'Одна из групп уведомлений не имеет доступа к отчёту.',
        'errors_title' => 'Проверьте ошибки ниже:',
    ],

    'errors' => [
        'no_recipients' => 'Подписка не может выполниться без получателей.',
        'attachment_too_large' => 'Размер вложения превышает допустимый предел.',
    ],

    'feedback' => [
        'metadata_error' => 'Не удалось загрузить метаданные отчёта. Попробуйте позже.',
        'save_error' => 'Не удалось сохранить подписку. Проверьте форму и повторите попытку.',
        'save_success' => 'Подписка успешно сохранена.',
        'loading' => 'Подготавливаем метаданные.',
    ],

    'mail' => [
        'subject' => [
            'default' => 'Запланированный отчёт: {{report_name}} ({{date}})',
        ],
        'body_default' => 'Здравствуйте,<br><br>Во вложении запланированный отчёт.<br><br>С уважением.',
    ],

    'email_editor' => [
        'html' => 'HTML-код',
        'design' => 'Визуальный редактор',
        'preview' => 'Предпросмотр',
        'preview_hint' => 'Предпросмотр показывает письмо так, как его увидит получатель.',
        'preview_empty' => 'Контент пока отсутствует. Добавьте HTML на вкладке кода, чтобы увидеть результат.',
        'design_toolbar' => 'Панель форматирования',
        'font_size' => 'Размер шрифта',
    ],

    'schedule' => [
        'weekly' => [
            'label' => 'Ezhenedelnyi grafik',
            'days' => 'Zapusk po etim dnyam',
            'interval' => 'Povtorjat kazhdyе (nedeli)',
            'interval_hint' => 'Primer: 1 - kazhduju nedelju, 2 - cherez nedelju.',
            'day_labels' => [
                'mon' => 'Pon',
                'tue' => 'Vtor',
                'wed' => 'Sre',
                'thu' => 'Chet',
                'fri' => 'Pjat',
                'sat' => 'Sub',
                'sun' => 'Vos',
            ],
            'summary' => 'Kazhdyе :interval nedeli po dnyam :days',
            'default_day' => 'den nachala',
        ],
        'monthly' => [
            'label' => 'Ezhemesjachnyi grafik',
            'on_day' => 'Zapusk v ukazannyi den kazhdogo mesjaca',
            'day_hint' => 'Esli v mesiace net etogo chisla, ispolzuetsja poslednij dostupnyj den.',
            'on_last_day' => 'Zapusk v poslednij den mesjaca',
            'summary_day' => 'V den :day kazhdogo mesjaca',
            'summary_last' => 'V poslednij den kazhdogo mesjaca',
        ],
    ],

    'notifications' => [
        'failure' => [
            'subject' => 'Сбой подписки: :name',
            'intro' => 'Подписка «:name» завершилась с ошибкой при последнем запуске.',
            'action' => 'Открыть подписку',
            'footer' => 'Вы получили это письмо, потому что указаны получателем уведомлений.',
        ],
    ],

    'snippets' => [
        'report_name' => 'Название отчёта',
        'date' => 'Запланированная дата',
        'datetime' => 'Запланированные дата и время',
        'subscription_name' => 'Название подписки',
        'report_link' => 'Ссылка на отчёт в портале',
    ],

    'flash' => [
        'created' => 'Подписка успешно создана.',
        'updated' => 'Подписка успешно обновлена.',
        'deleted' => 'Подписка успешно удалена.',
        'dispatched_now' => 'Подписка отправлена немедленно.',
    ],
];



