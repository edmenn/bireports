<?php

return [
    'title' => 'Theme Configuration',
    'tabs' => [
        'page' => 'Main Theme',
        'login' => 'Login Theme',
    ],
    'cards' => [
        'general' => [
            'title' => 'General Configuration',
            'fields' => [
                'site_title' => [
                    'label' => 'Page name (title)',
                    'placeholder' => 'E.g.: My BI Portal',
                    'help' => 'Shown on the browser tab.',
                    'tooltip' => 'Title users will see on the browser tab.',
                ],
                'theme_name' => [
                    'label' => 'Saved theme',
                    'default' => 'Default',
                    'custom' => 'Custom',
                    'help' => 'Select a saved theme or adjust the colors to create a new one.',
                    'tooltip' => 'Select a saved theme or create a new one with "Save as...".',
                    'delete' => 'Delete saved theme',
                ],
            ],
        ],
        'branding' => [
            'title' => 'Logos, Favicon & Preloader',
            'fields' => [
                'site_logo' => [
                    'label' => 'Main logo',
                    'placeholder' => 'Choose a file...',
                    'help' => 'PNG/JPG/SVG. Transparent background and height ≤ 60px recommended.',
                    'tooltip' => 'Shown on the header. Use a lightweight file for faster loading.',
                    'preview_alt' => 'Main logo',
                ],
                'preloader_logo' => [
                    'label' => 'Preloader icon (centered while loading)',
                    'placeholder' => 'Choose a file...',
                    'help' => 'Displayed in the center while the page loads.',
                    'size_label' => 'Width × Height (px)',
                    'hint' => 'Recommended: 60–120 px.',
                    'preview_alt' => 'Preloader icon',
                ],
                'site_logo_compact' => [
                    'label' => 'Compact logo',
                    'placeholder' => 'Choose a file...',
                    'help' => 'Used when the sidebar is collapsed. Suggested height ≤ 60px.',
                    'tooltip' => 'Compact logo version for reduced spaces.',
                    'preview_alt' => 'Compact logo',
                ],
                'site_favicon' => [
                    'label' => 'Favicon',
                    'placeholder' => 'Choose a file...',
                    'help' => 'ICO or PNG 32×32 recommended.',
                    'tooltip' => 'Small icon shown on the browser tab.',
                    'preview_alt' => 'Favicon',
                ],
            ],
        ],
        'palette' => [
            'title' => 'Custom Color Palette',
            'groups' => [
                'base' => [
                    'title' => 'Base',
                    'tooltip' => 'Main interface colors (background and primary text).',
                    'fields' => [
                        'main_bg' => 'Main background',
                        'main_text' => 'Main text',
                    ],
                ],
                'navbar' => [
                    'title' => 'Navbar',
                    'tooltip' => 'Top bar background and header text.',
                    'fields' => [
                        'background' => 'Background',
                        'text' => 'Text',
                    ],
                ],
                'sidebar' => [
                    'title' => 'Sidebar',
                    'tooltip' => 'Side menu background and text colors.',
                    'fields' => [
                        'background' => 'Background',
                        'text' => 'Text',
                    ],
                ],
                'content' => [
                    'title' => 'Content (cards)',
                    'tooltip' => 'Cards and containers: backgrounds and text.',
                    'fields' => [
                        'card_background' => 'Card background',
                        'header_background' => 'Header background',
                        'text' => 'Text',
                    ],
                ],
                'accents' => [
                    'title' => 'Accents & Buttons',
                    'tooltip' => 'Highlights and buttons: normal, hover, and active states.',
                    'fields' => [
                        'highlight' => 'Highlight',
                        'highlight_text' => 'Highlight text',
                        'button' => 'Button',
                        'button_text' => 'Button text',
                        'button_hover' => 'Button hover',
                        'button_active' => 'Button active',
                    ],
                ],
            ],
            'common' => [
                'background' => 'Background',
                'text' => 'Text',
            ],
        ],
    ],
    'buttons' => [
        'save_as' => 'Save as...',
    ],
    'modal' => [
        'save' => [
            'title' => 'Save theme as...',
            'name_label' => 'Theme name',
            'name_placeholder' => 'E.g.: Corporate Blue',
            'name_help' => 'Choose a unique name. Default theme names are not allowed.',
            'close' => 'Close',
        ],
        'delete_confirm' => 'Delete this saved theme?',
    ],
];
