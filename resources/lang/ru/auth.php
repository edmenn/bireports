<?php

return [
    'microsoft' => [
        'not_configured'             => 'Вход через Microsoft не настроен.',
        'config_missing'             => 'Отсутствует конфигурация.',
        'invalid_state'              => 'От Microsoft получено недопустимое состояние.',
        'login_cancelled_with_reason'=> 'Вход через Microsoft отменен: :reason',
        'login_cancelled'            => 'Вход через Microsoft отменен.',
        'missing_code'               => 'Отсутствует код авторизации.',
        'missing_id_token'           => 'Microsoft не вернула id_token.',
        'invalid_token'              => 'Недопустимый токен.',
        'invalid_nonce'              => 'Недопустимый nonce.',
        'missing_email'              => 'Не удалось определить адрес электронной почты от Microsoft.',
        'account_blocked'            => 'Ваш аккаунт заблокирован. Обратитесь к администратору.',
        'account_disabled'           => 'Ваш аккаунт Microsoft Entra отключен.',
        'process_error'              => 'Ошибка при обработке ответа Microsoft.',
    ],
];
