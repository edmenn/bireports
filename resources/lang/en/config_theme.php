<?php

return [
    'title' => 'Theme Configuration',
    'tabs' => [
        'page_theme' => 'Main Theme',
        'login_theme' => 'Login Theme',
    ],
    'cards' => [
        'general' => [ 'title' => 'General Configuration' ],
        'branding' => [ 'title' => 'Logos, Favicon & Preloader' ],
        'palette'  => [ 'title' => 'Custom Color Palette' ],
    ],
    'fields' => [
        'site_title' => [
            'label' => 'Page name (title)',
            'placeholder' => 'E.g.: My BI Portal',
            'help' => 'Shown on the browser tab.',
            'help_tip' => 'Title users will see on the browser tab.',
        ],
        'theme_preset' => [
            'label' => 'Preset theme',
            'help' => 'Applies an initial color palette.',
            'tip'  => 'Applies a base palette; then you can tweak colors below.',
        ],
    ],
];

