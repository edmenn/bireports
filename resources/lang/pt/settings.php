<?php

return [
    'page' => [
        'title' => 'Configurações',
        'subtitle' => 'Credenciais do Microsoft Fabric / Power BI para App-Owns-Data',
    ],

    'actions' => [
        'view_audit' => 'Ver auditoria',
        'import_now' => 'Importar agora',
        'save' => 'Salvar',
        'cancel' => 'Cancelar',
    ],

    'alerts' => [
        'success' => 'Sucesso',
        'error' => 'Erro',
        'validation' => 'Erros de validação',
    ],

    'menu' => [
        'parameters' => 'Parametros',
        'theme' => 'Tema',
    ],

    'service_principal' => [
        'card_title' => 'Credenciais de conexão (Service Principal)',
        'intro' => 'Configure um aplicativo do Entra ID (Service Principal) com permissões do Power BI para autenticação sem interação.',
        'fields' => [
            'client_id' => [
                'label' => 'Client ID (Application ID)',
                'placeholder' => 'ID do aplicativo',
                'help' => 'Azure Entra ID → App registrations → Overview → Application (client) ID.',
                'tooltip' => 'Identificador do aplicativo (Client ID) registrado no Entra ID.',
            ],
            'tenant_id' => [
                'label' => 'Tenant ID (Directory ID)',
                'placeholder' => 'ID do diretório',
                'help' => 'Azure Entra ID → Overview → Tenant ID.',
                'tooltip' => 'Deve coincidir com o tenant onde o aplicativo está registrado.',
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'placeholder' => 'Segredo do cliente',
                'help' => 'É armazenado criptografado. Deixe em branco para não alterar.',
                'tooltip' => 'Gere o segredo em Azure Entra ID → Certificates & secrets. Evite colar valores mascarados.',
            ],
            'refresh_minutes' => [
                'label' => 'Atualizar (min)',
                'placeholder' => '0 = auto',
                'help' => '0 = gerenciado automaticamente pelo SDK.',
                'tooltip' => 'Use 0 para deixar a biblioteca gerenciar a renovação; ajuste somente se necessário.',
            ],
            'timezone' => [
                'label' => 'Fuso horário padrão',
                'tooltip' => 'Afeta horários exibidos e a execução das tarefas agendadas.',
                'help' => 'Será aplicado ao scheduler e como valor padrão no agendamento de datasets.',
            ],
        ],
    ],

    'auth' => [
        'microsoft' => [
            'title' => 'Login com Microsoft',
            'description' => 'Permite que os usuários se autentiquem com suas contas Microsoft.',
        ],
        'local' => [
            'title' => 'Login local',
            'description' => 'Formulário tradicional de email e senha. Em caso de emergência, o ambiente pode forçar a habilitação com :env.',
        ],
    ],

    'prewarm' => [
        'title' => 'Pré-aquecimento de capacidades',
        'description' => 'Se estiver ativo, antes de uma atualização agendada retomamos (ligamos) a capacidade associada para evitar falhas por estar pausada. Desative se preferir controlar isso externamente.',
    ],

    'login' => [
        'cards' => [
            'uploads' => [
                'title' => 'Arquivos para upload',
                'fields' => [
                    'logo' => [
                        'label' => 'Logotipo de login (PNG/JPG/SVG)',
                        'placeholder' => 'Escolher um arquivo...',
                        'help' => 'Recomenda-se fundo transparente e altura ≤ 90 px.',
                        'tooltip' => 'Formatos aceitos: PNG, JPG ou SVG. Exibido acima do formulário de login.',
                    ],
                    'background' => [
                        'label' => 'Imagem de fundo (tela cheia)',
                        'placeholder' => 'Escolher um arquivo...',
                        'help' => 'Será ajustada para cobrir toda a tela na página de login.',
                        'tooltip' => 'Sugestão: use 1920×1080 ou superior, com boa compactação.',
                    ],
                ],
            ],
            'preview' => [
                'title' => 'Pré-visualizações atuais',
                'logo_heading' => 'Logotipo de login:',
                'logo_alt' => 'Pré-visualização do logotipo de login',
                'no_logo' => 'Ainda não há logotipo de login enviado.',
                'background_heading' => 'Imagem de fundo:',
                'background_alt' => 'Pré-visualização da imagem de fundo',
                'no_background' => 'Ainda não há imagem de fundo enviada.',
            ],
            'size' => [
                'title' => 'Tamanho do logotipo',
                'width_label' => 'Largura (px)',
                'height_label' => 'Altura (px)',
                'hint' => 'Deixe em branco para ajustar proporcionalmente.',
            ],
        ],
    ],

    'audit' => [
        'page' => [
            'title' => 'Auditoria de Configurações',
            'subtitle' => 'Alterações agrupadas por evento',
        ],
        'actions' => [
            'back' => 'Voltar para Configurações',
        ],
        'form' => [
            'search_label' => 'Pesquisar',
            'search_placeholder' => 'Campo/valor…',
            'user_label' => 'Usuário (ID)',
            'submit' => 'Filtrar',
        ],
        'table' => [
            'date' => 'Data',
            'user' => 'Usuário',
            'ip' => 'IP',
            'changes' => 'Alterações',
            'field' => 'Campo',
            'before' => 'Antes',
            'after' => 'Depois',
            'summary' => 'Ver :count alteração(ões)',
            'empty' => 'Sem registros',
        ],
    ],

    'guide' => [
        'title' => 'Guia e Boas Práticas',
        'permissions' => [
            'title' => 'Permissões necessárias',
            'items' => [
                'assign' => 'Atribua o aplicativo ao <em>tenant</em> (Admin Portal → Developer settings).',
                'roles' => 'Papéis típicos: <code>Tenant.Read.All</code> ou <code>Tenant.ReadWrite.All</code>.',
                'access' => 'Conceda acesso aos <em>workspaces</em> específicos.',
            ],
        ],
        'recommendations' => [
            'title' => 'Recomendações',
            'items' => [
                'secret' => 'Use um segredo dedicado apenas a este portal.',
                'rotate' => 'Gire os segredos periodicamente.',
                'restrict' => 'Restrinja o aplicativo aos <em>workspaces</em> necessários.',
            ],
        ],
    ],

    'viewer' => [
        'card_title' => 'Capacidade on-demand (Viewer)',
        'fields' => [
            'enable' => [
                'label' => 'Ligamento on-demand',
                'toggle' => 'Ativar tentativa automática',
                'help' => 'Se estiver desativado, o viewer não tentará ligar a capacidade.',
                'tooltip' => 'Quando ativado, o viewer tentará ligar a capacidade automaticamente quando um usuário abrir um relatório e ela estiver pausada.',
                'tooltip_help' => 'Desative se preferir mostrar uma mensagem de capacidade pausada sem tentar ligá-la automaticamente.',
            ],
            'message' => [
                'label' => 'Mensagem de ligamento',
                'placeholder' => 'Ligando a capacidade, aguarde...',
                'default' => 'Ligando a capacidade, aguarde alguns segundos...',
                'help' => 'Texto exibido ao usuário enquanto a capacidade é ligada.',
                'tooltip' => 'Mostrado em um banner de espera até que a capacidade fique ativa ou o tempo máximo seja atingido.',
            ],
            'start_timeout' => [
                'label' => 'Timeout de ligamento (s)',
                'help' => 'Tempo máximo de espera antes de exibir erro (recomendado: 120s).',
                'tooltip' => 'Se esse tempo for excedido sem que a capacidade fique ativa, é exibido um erro e o relatório não é incorporado.',
            ],
            'poll_interval' => [
                'label' => 'Intervalo de verificação (s)',
                'help' => 'Frequência com que o viewer verifica se a capacidade já está ativa.',
                'tooltip' => 'Intervalos menores reagem mais rápido, mas geram mais chamadas; 5s costuma ser um bom equilíbrio.',
            ],
            'report_timeout' => [
                'label' => 'Timeout de carregamento do relatório (s)',
                'help' => 'Começa após iniciar o embed; se expirar, é exibido um erro de carregamento.',
                'tooltip' => 'Ajuste o limite de espera para que o Power BI dispare o evento “loaded”.',
            ],
        ],
    ],
];
