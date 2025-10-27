<?php

return [
    'microsoft' => [
        'not_configured'             => 'Microsoft login is not configured.',
        'config_missing'             => 'Missing configuration.',
        'invalid_state'              => 'Invalid state received from Microsoft.',
        'login_cancelled_with_reason'=> 'Microsoft sign-in cancelled: :reason',
        'login_cancelled'            => 'Microsoft sign-in cancelled.',
        'missing_code'               => 'Authorization code is missing.',
        'missing_id_token'           => 'Microsoft did not return an id_token.',
        'invalid_token'              => 'Invalid token.',
        'invalid_nonce'              => 'Invalid nonce.',
        'missing_email'              => 'Unable to determine the email address from Microsoft.',
        'account_blocked'            => 'Your account is blocked. Contact the administrator.',
        'account_disabled'           => 'Your Microsoft Entra account is disabled.',
        'process_error'              => 'Error processing Microsoft response.',
    ],
];
