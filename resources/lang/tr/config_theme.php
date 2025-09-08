<?php

return [
    'title' => 'Tema Yapılandırması',
    'tabs' => [
        'page_theme' => 'Ana Tema',
        'login_theme' => 'Giriş Teması',
    ],
    'cards' => [
        'general' => [ 'title' => 'Genel Yapılandırma' ],
        'branding' => [ 'title' => 'Logolar, Favicon ve Preloader' ],
        'palette'  => [ 'title' => 'Özel Renk Paleti' ],
    ],
    'fields' => [
        'site_title' => [
            'label' => 'Sayfa adı (title)',
            'placeholder' => 'Örn.: BI Portalım',
            'help' => 'Tarayıcı sekmesinde gösterilir.',
            'help_tip' => 'Kullanıcının tarayıcı sekmesinde göreceği başlık.',
        ],
        'theme_preset' => [
            'label' => 'Hazır tema',
            'help' => 'Başlangıç renk paletini uygular.',
            'tip'  => 'Temel bir palet uygular; ardından renkleri aşağıda düzenleyebilirsiniz.',
        ],
    ],
];

