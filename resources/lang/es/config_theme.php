<?php

return [
    'title' => 'Configuración del Tema',
    'tabs' => [
        'page_theme' => 'Tema Principal',
        'login_theme' => 'Tema del Login',
    ],
    'cards' => [
        'general' => [ 'title' => 'Configuración General' ],
        'branding' => [ 'title' => 'Logos, Favicon y Preloader' ],
        'palette'  => [ 'title' => 'Paleta de Colores Personalizada' ],
    ],
    'fields' => [
        'site_title' => [
            'label' => 'Nombre de la página (título)',
            'placeholder' => 'Ej: Mi Portal BI',
            'help' => 'Se muestra en la pestaña del navegador.',
            'help_tip' => 'Título que verá el usuario en la pestaña del navegador.',
        ],
        'theme_preset' => [
            'label' => 'Tema predefinido',
            'help' => 'Aplica una paleta inicial de colores.',
            'tip'  => 'Aplica una paleta base; luego podés ajustar los colores debajo.',
        ],
    ],
];

