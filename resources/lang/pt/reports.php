<?php

return [
    'index' => [
        'title' => 'Todos os relatórios',
        'subtitle' => 'Lista importada do Microsoft Fabric / Power BI',
        'import_now' => 'Importar agora',
        'empty' => [
            'title' => 'Ainda não há relatórios importados',
            'hint' => 'Execute uma importação para ver seus dashboards aqui.',
            'action' => 'Importar agora',
        ],
        'metrics' => [
            'total' => 'Relatórios totais',
            'with_cover' => 'Com capa',
            'without_cover' => 'Sem capa',
        ],
        'filters' => [
            'search_label' => 'Pesquisar',
            'search_placeholder' => 'Título, report ID, workspace ou dataset ID...',
            'workspace_label' => 'Workspace',
            'workspace_all' => 'Todos',
            'cover_label' => 'Capa',
            'cover_all' => 'Todas',
            'cover_yes' => 'Com capa',
            'cover_no' => 'Sem capa',
            'order_label' => 'Ordenar',
            'order_options' => [
                'name_asc' => 'Nome (A-Z)',
                'name_desc' => 'Nome (Z-A)',
                'workspace_asc' => 'Workspace (A-Z)',
                'workspace_desc' => 'Workspace (Z-A)',
            ],
            'hint' => 'Os filtros e a ordenação são aplicados no navegador, sem recarregar a página.',
        ],
        'cards' => [
            'open_title' => 'Abrir relatório',
            'edit_title' => 'Editar capa e descrição',
            'cover_aria' => 'Capa do relatório',
            'cover_alt' => 'Capa de :name',
            'default_cover_aria' => 'Ilustração padrão',
        ],
        'actions' => [
            'open' => 'Abrir',
            'edit' => 'Editar',
            'view' => 'Ver relatório',
        ],
        'badges' => [
            'workspace' => 'Workspace',
            'dataset' => 'Dataset',
            'report' => 'Report',
        ],
        'copy' => [
            'dataset_title' => 'Copiar ID do dataset',
            'dataset_label' => 'Dataset',
            'report_title' => 'Copiar ID do relatório',
            'report_label' => 'Report',
            'success' => 'Copiado',
            'error' => 'Não foi possível copiar para a área de transferência.',
        ],
    ],

    'show' => [
        'title_fallback' => 'Relatório',
        'loading' => 'Carregando relatório...',
        'fullscreen_enter' => 'Ver em tela cheia',
        'fullscreen_exit' => 'Sair da tela cheia',
        'missing_embed' => [
            'title' => 'Não foi possível preparar o embed do relatório.',
            'hint' => 'Verifique as credenciais e as permissões do Power BI em Configurações.',
            'link' => 'Configurações',
            'summary' => 'Payload retornado por PowerBiService::generateReportEmbedToken()',
            'items' => [
                'embed_url' => 'URL de incorporação',
                'access_token' => 'Token de acesso',
                'report_id' => 'ID do relatório',
            ],
            'status' => [
                'ok' => 'Disponível',
                'missing' => 'Ausente',
            ],
        ],
        'errors' => [
            'no_credentials' => 'Faltam credenciais do Power BI em Configurações.',
            'embed_failure' => 'Não foi possível preparar o embed do relatório. Verifique credenciais, capacidade e permissões.',
            'missing_fields' => 'Faltam dados de embedUrl, accessToken ou reportId.',
            'client_library' => 'Não foi possível carregar a biblioteca powerbi-client.',
            'capacity_paused' => 'A capacidade está pausada fora do horário operacional.',
            'capacity_timeout' => 'Não foi possível ativar a capacidade a tempo (timeout).',
            'load_timeout' => 'O relatório demorou muito para carregar (timeout de 10 s). Verifique a rede, o token ou a capacidade.',
            'debug_template' => ":exception: :message\nCódigo: :code\nArquivo: :file (linha :line)\n\nRastreamento:\n:trace",
        ],
        'on_demand' => [
            'starting' => 'Ligando a capacidade, aguarde alguns segundos...',
        ],
    ],

    'edit' => [
        'title' => 'Editar relatório',
        'card_title' => 'Capa e descrição',
        'view_report' => 'Ver relatório',
        'preview' => [
            'card_title' => 'Pré-visualização do relatório',
            'scale_label' => 'Tamanho na lista',
            'scale_display' => 'Tamanho atual: :value%',
            'width_label' => 'Largura',
            'height_label' => 'Altura',
            'width_display' => 'Largura: :value %',
            'height_display' => 'Altura: :value %',
            'lock_label' => 'Bloquear propor�ao',
        ],
        'alerts' => [
            'errors_title' => 'Revise os erros:',
            'close' => 'Fechar',
        ],
        'form' => [
            'read_only' => [
                'title' => 'Título',
                'workspace_id' => 'Workspace ID',
                'report_id' => 'Report ID',
            ],
            'preview' => [
                'label' => 'Pré-visualização',
                'hint' => 'Se você não enviar arquivo nem informar uma URL válida, será usada a capa padrão (se existir) ou o ícone do BI.',
            ],
            'upload' => [
                'label' => 'Enviar imagem (opcional)',
                'hint' => 'Formatos permitidos: JPG, PNG, WEBP, GIF ou SVG. Máximo de 2 MB.',
            ],
            'remove_cover' => [
                'label' => 'Remover imagem atual',
                'hint' => 'Retorna � capa padr�o ou ao �cone padr�o.',
            ],
            'make_default' => [
                'label' => 'Usar esta imagem como capa padrão para relatórios sem capa',
                'hint' => 'Válido apenas quando você envia um arquivo.',
            ],
            'url' => [
                'label' => 'URL da imagem (opcional)',
                'placeholder' => 'https://seu-cdn/capas/relatorio.png',
                'hint' => 'Se você enviar um arquivo, a URL será ignorada. A opção de padrão vale apenas para o arquivo enviado.',
            ],
            'description' => [
                'label' => 'Descrição (opcional)',
                'placeholder' => 'Breve descrição do conteúdo, público-alvo, filtros em destaque etc.',
            ],
            'allow_resume' => [
                'label' => 'Permitir aquecer a capacidade fora do horário comercial para este relatório',
                'hint' => 'Com a opção ativa, qualquer usuário pode ligá-la ao abrir o relatório; depois ela será desligada por inatividade.',
            ],
        ],
        'buttons' => [
            'cancel' => 'Cancelar',
            'save' => 'Salvar alterações',
        ],
    ],

    'validation' => [
        'cover_url_url' => 'A imagem deve ser uma URL válida.',
        'cover_file_max' => 'A imagem não pode ultrapassar 2 MB.',
        'cover_file_mimetypes' => 'Formatos permitidos: JPG, PNG, WEBP, GIF ou SVG.',
        'cover_scale_between' => 'O tamanho deve ficar entre 50% e 150%.',
        'cover_scale_numeric' => 'O tamanho deve ser um valor numérico.',
    ],

    'flash' => [
        'updated_default' => 'Relatório atualizado e capa definida como padrão.',
        'updated' => 'Relatório atualizado com sucesso.',
    ],
];
