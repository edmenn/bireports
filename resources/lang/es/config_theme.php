<?php

return [
    'title' => 'Configuración del Tema',
    'tabs' => [
        'page' => 'Tema Principal',
        'login' => 'Tema del Login',
    ],
    'cards' => [
        'general' => [
            'title' => 'Configuración General',
            'fields' => [
                'site_title' => [
                    'label' => 'Nombre de la página (título)',
                    'placeholder' => 'Ej: Mi Portal BI',
                    'help' => 'Se muestra en la pestaña del navegador.',
                    'tooltip' => 'Título que verá el usuario en la pestaña del navegador.',
                ],
                'theme_name' => [
                    'label' => 'Tema guardado',
                    'default' => 'Predeterminado',
                    'custom' => 'Personalizado',
                    'help' => 'Selecciona un tema guardado o ajusta los colores para crear uno nuevo.',
                    'tooltip' => 'Selecciona un tema guardado o crea uno nuevo con "Guardar como...".',
                    'delete' => 'Eliminar tema guardado',
                ],
            ],
        ],
        'branding' => [
            'title' => 'Logos, Favicon y Preloader',
            'fields' => [
                'site_logo' => [
                    'label' => 'Logo principal',
                    'placeholder' => 'Elegir archivo...',
                    'help' => 'PNG o JPG, hasta 2 MB. Recomendado fondo transparente y altura ≤ 60 px.',
                    'tooltip' => 'Se muestra en el encabezado. Usa un archivo liviano para mejor carga.',
                    'preview_alt' => 'Logo principal',
                ],
                'preloader_logo' => [
                    'label' => 'Icono de preloader (centrado al cargar)',
                    'placeholder' => 'Elegir archivo...',
                    'help' => 'Se muestra en el centro mientras carga la página.',
                    'size_label' => 'Ancho × Alto (px)',
                    'hint' => 'Recomendado: 60–120 px.',
                    'preview_alt' => 'Preloader',
                ],
                'site_logo_compact' => [
                    'label' => 'Logo reducido',
                    'placeholder' => 'Elegir archivo...',
                    'help' => 'Se usa con sidebar colapsada. Altura sugerida ≤ 60px.',
                    'tooltip' => 'Versión compacta del logo para espacios reducidos.',
                    'preview_alt' => 'Logo compacto',
                ],
                'site_favicon' => [
                    'label' => 'Favicon',
                    'placeholder' => 'Elegir archivo...',
                    'help' => 'ICO o PNG 32×32 recomendado.',
                    'tooltip' => 'Pequeño ícono de pestaña del navegador.',
                    'preview_alt' => 'Favicon',
                ],
            ],
        ],
        'palette' => [
            'title' => 'Paleta de Colores Personalizada',
            'groups' => [
                'base' => [
                    'title' => 'Base',
                    'tooltip' => 'Colores principales de la interfaz (fondo y texto base).',
                    'fields' => [
                        'main_bg' => 'Fondo principal',
                        'main_text' => 'Texto principal',
                    ],
                ],
                'navbar' => [
                    'title' => 'Navbar',
                    'tooltip' => 'Barra superior: fondo y texto del encabezado.',
                    'fields' => [
                        'background' => 'Fondo',
                        'text' => 'Texto',
                    ],
                ],
                'sidebar' => [
                    'title' => 'Sidebar',
                    'tooltip' => 'Menú lateral: colores de fondo y textos.',
                    'fields' => [
                        'background' => 'Fondo',
                        'text' => 'Texto',
                    ],
                ],
                'content' => [
                    'title' => 'Contenido (cards)',
                    'tooltip' => 'Tarjetas y contenedores: fondos y texto.',
                    'fields' => [
                        'card_background' => 'Card fondo',
                        'header_background' => 'Header fondo',
                        'text' => 'Texto',
                    ],
                ],
                'icons' => [
                    'title' => 'Iconos',
                ],
                'accents' => [
                    'title' => 'Acentos y Botones',
                    'tooltip' => 'Resaltados y botones: estados normal, hover y activo.',
                    'fields' => [
                        'highlight' => 'Destacado',
                        'highlight_text' => 'Texto destacado',
                        'button' => 'Botón',
                        'button_text' => 'Texto botón',
                        'button_hover' => 'Botón hover',
                        'button_active' => 'Botón activo',
                    ],
                ],
            ],
            'common' => [
                'background' => 'Fondo',
                'text' => 'Texto',
            ],
        ],
    ],
    'buttons' => [
        'save_as' => 'Guardar como...',
    ],
    'errors' => [
        'file_too_large' => 'El archivo supera el máximo de :max MB.',
        'invalid_file_type' => 'Formato no admitido. Usa PNG o JPG; el favicon también admite ICO.',
        'invalid_image' => 'No se pudo leer la imagen. Elegí otro archivo.',
    ],
    'modal' => [
        'save' => [
            'title' => 'Guardar tema como...',
            'name_label' => 'Nombre del tema',
            'name_placeholder' => 'Ej: Corporativo Azul',
            'name_help' => 'Elige un nombre único. No se permiten nombres de los temas predeterminados.',
            'close' => 'Cerrar',
        ],
        'delete_confirm' => '¿Eliminar este tema guardado?',
    ],
];
