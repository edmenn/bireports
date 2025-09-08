<?php

return [
    'title' => 'Configuração do Tema',
    'tabs' => [
        'page_theme' => 'Tema Principal',
        'login_theme' => 'Tema de Login',
    ],
    'cards' => [
        'general' => [ 'title' => 'Configuração Geral' ],
        'branding' => [ 'title' => 'Logos, Favicon e Preloader' ],
        'palette'  => [ 'title' => 'Paleta de Cores Personalizada' ],
    ],
    'fields' => [
        'site_title' => [
            'label' => 'Nome da página (título)',
            'placeholder' => 'Ex.: Meu Portal BI',
            'help' => 'Exibido na aba do navegador.',
            'help_tip' => 'Título que o usuário verá na aba do navegador.',
        ],
        'theme_preset' => [
            'label' => 'Tema predefinido',
            'help' => 'Aplica uma paleta inicial de cores.',
            'tip'  => 'Aplica uma paleta base; depois você pode ajustar as cores abaixo.',
        ],
    ],
];

