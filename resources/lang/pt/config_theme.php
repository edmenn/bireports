<?php

return [
    'title' => 'Configuração do Tema',
    'tabs' => [
        'page' => 'Tema Principal',
        'login' => 'Tema de Login',
    ],
    'cards' => [
        'general' => [
            'title' => 'Configuração Geral',
            'fields' => [
                'site_title' => [
                    'label' => 'Nome da página (título)',
                    'placeholder' => 'Ex.: Meu Portal BI',
                    'help' => 'Exibido na aba do navegador.',
                    'tooltip' => 'Título que o usuário verá na aba do navegador.',
                ],
                'theme_name' => [
                    'label' => 'Tema salvo',
                    'default' => 'Padrão',
                    'custom' => 'Personalizado',
                    'help' => 'Selecione um tema salvo ou ajuste as cores para criar um novo.',
                    'tooltip' => 'Selecione um tema salvo ou crie um novo com "Salvar como...".',
                    'delete' => 'Excluir tema salvo',
                ],
            ],
        ],
        'branding' => [
            'title' => 'Logos, Favicon e Preloader',
            'fields' => [
                'site_logo' => [
                    'label' => 'Logo principal',
                    'placeholder' => 'Escolher arquivo...',
                    'help' => 'PNG/JPG/SVG. Recomenda-se fundo transparente e altura ≤ 60 px.',
                    'tooltip' => 'Exibido no cabeçalho. Use um arquivo leve para melhorar o carregamento.',
                    'preview_alt' => 'Logo principal',
                ],
                'preloader_logo' => [
                    'label' => 'Ícone do preloader (centralizado ao carregar)',
                    'placeholder' => 'Escolher arquivo...',
                    'help' => 'Mostrado no centro enquanto a página carrega.',
                    'size_label' => 'Largura × Altura (px)',
                    'hint' => 'Recomendado: 60–120 px.',
                    'preview_alt' => 'Ícone do preloader',
                ],
                'site_logo_compact' => [
                    'label' => 'Logo reduzido',
                    'placeholder' => 'Escolher arquivo...',
                    'help' => 'Usado com a sidebar recolhida. Altura sugerida ≤ 60 px.',
                    'tooltip' => 'Versão compacta do logo para espaços reduzidos.',
                    'preview_alt' => 'Logo compacto',
                ],
                'site_favicon' => [
                    'label' => 'Favicon',
                    'placeholder' => 'Escolher arquivo...',
                    'help' => 'ICO ou PNG 32×32 recomendado.',
                    'tooltip' => 'Pequeno ícone exibido na aba do navegador.',
                    'preview_alt' => 'Favicon',
                ],
            ],
        ],
        'palette' => [
            'title' => 'Paleta de Cores Personalizada',
            'groups' => [
                'base' => [
                    'title' => 'Base',
                    'tooltip' => 'Cores principais da interface (fundo e texto base).',
                    'fields' => [
                        'main_bg' => 'Fundo principal',
                        'main_text' => 'Texto principal',
                    ],
                ],
                'navbar' => [
                    'title' => 'Navbar',
                    'tooltip' => 'Barra superior: fundo e texto do cabeçalho.',
                    'fields' => [
                        'background' => 'Fundo',
                        'text' => 'Texto',
                    ],
                ],
                'sidebar' => [
                    'title' => 'Sidebar',
                    'tooltip' => 'Menu lateral: cores de fundo e texto.',
                    'fields' => [
                        'background' => 'Fundo',
                        'text' => 'Texto',
                    ],
                ],
                'content' => [
                    'title' => 'Conteúdo (cards)',
                    'tooltip' => 'Cards e contêineres: fundos e texto.',
                    'fields' => [
                        'card_background' => 'Fundo do card',
                        'header_background' => 'Fundo do header',
                        'text' => 'Texto',
                    ],
                ],
                'accents' => [
                    'title' => 'Acentos e Botões',
                    'tooltip' => 'Destaques e botões: estados normal, hover e ativo.',
                    'fields' => [
                        'highlight' => 'Destaque',
                        'highlight_text' => 'Texto em destaque',
                        'button' => 'Botão',
                        'button_text' => 'Texto do botão',
                        'button_hover' => 'Botão hover',
                        'button_active' => 'Botão ativo',
                    ],
                ],
            ],
            'common' => [
                'background' => 'Fundo',
                'text' => 'Texto',
            ],
        ],
    ],
    'buttons' => [
        'save_as' => 'Salvar como...',
    ],
    'modal' => [
        'save' => [
            'title' => 'Salvar tema como...',
            'name_label' => 'Nome do tema',
            'name_placeholder' => 'Ex.: Corporativo Azul',
            'name_help' => 'Escolha um nome único. Nomes dos temas padrão não são permitidos.',
            'close' => 'Fechar',
        ],
        'delete_confirm' => 'Excluir este tema salvo?',
    ],
];
