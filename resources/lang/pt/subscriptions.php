<?php

return [
    'create' => [
        'title' => 'Criar assinatura',
    ],

    'actions' => [
        'create' => 'Criar assinatura',
        'close' => 'Fechar',
        'cancel' => 'Cancelar',
        'save' => 'Salvar assinatura',
        'view' => 'Ver',
        'edit' => 'Editar',
        'delete' => 'Excluir',
        'send_now' => 'Enviar agora',
        'insert_snippet' => 'Inserir fragmento',
        'delete_confirm' => 'Excluir esta assinatura?',
        'back' => 'Voltar às assinaturas',
    ],

    'tabs' => [
        'configuration' => 'Configuração',
        'report' => 'Relatório',
        'groups' => 'Grupos',
        'users' => 'Usuários',
        'email' => 'E-mail',
        'notifications' => 'Notificações',
    ],

    'fields' => [
        'name' => 'Nome da assinatura',
        'is_enabled' => 'Habilitada',
        'enabled_label' => 'Executar automaticamente',
        'format' => 'Formato do arquivo',
        'recurrence' => 'Recorrência',
        'timezone' => 'Fuso horário',
        'start_date' => 'Data de início',
        'end_date' => 'Data de término',
        'hour' => 'Hora',
        'minute' => 'Minuto',
        'cron_expression' => 'Expressão cron',
        'max_attachment_size_mb' => 'Tamanho máximo do anexo (MB)',
        'report_id' => 'Relatório',
        'groups' => 'Grupos',
        'users' => 'Usuários',
        'mail_subject' => 'Assunto',
        'mail_body' => 'Corpo do e-mail',
        'notify_workspace_admins' => 'Notificar administradores do workspace',
        'notification_users' => 'Notificar usuários',
        'notification_groups' => 'Notificar grupos',
        'schedule_window' => 'Janela de execução',
        'created_by' => 'Criada por',
        'updated_by' => 'Atualizada por',
    ],

    'recurrence' => [
        'daily' => 'Diária',
        'weekly' => 'Semanal',
        'monthly' => 'Mensal',
        'cron' => 'Personalizada (Cron)',
    ],

    'recurrence_labels' => [
        'daily' => 'Diária',
        'weekly' => 'Semanal',
        'monthly' => 'Mensal',
        'cron' => 'Expressão cron',
    ],

    'hints' => [
        'start_date' => 'A assinatura começará a ser executada nesta data.',
        'end_date' => 'Deixe em branco para executar indefinidamente.',
        'cron_expression' => 'Sintaxe cron padrão com cinco ou seis campos.',
        'max_attachment_size_mb' => 'Anexos maiores que o limite serão comprimidos ou ignorados.',
        'report_selection' => 'Selecione um relatório para carregar páginas e destinatários.',
        'pages' => 'Escolha ao menos uma página para incluir na exportação.',
        'groups' => 'Todos os grupos estão disponíveis; seus membros receberão o e-mail.',
        'users' => 'Todos os usuários estão disponíveis; basta terem acesso ao e-mail.',
        'mail_subject' => 'Aceita variáveis como {{report_name}} e {{date}}.',
        'mail_body' => 'Aceita HTML. Variáveis como {{report_name}} e {{datetime}} são válidas.',
        'notify_workspace_admins' => 'Inclui administradores do workspace nas notificações de falha.',
        'notification_users' => 'Usuários adicionais para notificação em caso de falha.',
        'notification_groups' => 'Membros desses grupos serão notificados em caso de falha.',
    ],

    'placeholders' => [
        'report' => 'Selecione um relatório',
        'subject' => 'Relatório programado: {{report_name}} ({{date}})',
        'body' => '<p>Olá,</p><p>Segue o relatório programado em anexo.</p><p>Atenciosamente.</p>',
        'groups_empty' => 'Nenhum grupo disponível',
        'users_empty' => 'Nenhum usuário disponível',
        'notification_users_empty' => 'Nenhum usuário configurado',
        'notification_groups_empty' => 'Nenhum grupo configurado',
    ],

    'sections' => [
        'overview' => 'Resumo da assinatura',
        'recipients' => 'Destinatários',
        'email' => 'Entrega por e-mail',
        'notifications' => 'Notificações de falha',
        'pages' => 'Páginas do relatório',
        'runs' => 'Histórico de execuções',
    ],

    'labels' => [
        'deleted_report' => 'Relatório removido',
        'no_end_date' => 'Sem data de término',
        'time_at' => 'Hora: :time',
        'no_limit' => 'Sem limite',
        'notify_workspace_admins_yes' => 'Administradores do workspace serão notificados',
        'notify_workspace_admins_no' => 'Administradores do workspace não serão notificados',
        'no_groups_selected' => 'Nenhum grupo selecionado',
        'no_users_selected' => 'Nenhum usuário selecionado',
        'subject_default' => 'Relatório programado',
        'body_default' => 'O relatório programado está em anexo.',
        'no_notification_users' => 'Nenhum usuário configurado',
        'no_notification_groups' => 'Nenhum grupo configurado',
        'no_pages' => 'Nenhuma página selecionada',
        'page' => 'Página',
    ],

    'status' => [
        'enabled' => 'Habilitada',
        'disabled' => 'Desabilitada',
    ],

    'statuses' => [
        'pending' => 'Pendente',
        'running' => 'Em execução',
        'success' => 'Concluída',
        'failed' => 'Falha',
        'skipped' => 'Ignorada',
    ],

    'runs' => [
        'scheduled_time' => 'Horário programado',
        'status' => 'Status',
        'empty' => 'Ainda não há execuções registradas.',
    ],

    'index' => [
        'title' => 'Assinaturas de relatórios',
        'subtitle' => 'Gerencie os envios automáticos de relatórios',
        'empty' => [
            'title' => 'Nenhuma assinatura ainda',
            'hint' => 'Crie uma assinatura para começar.',
        ],
    ],

    'table' => [
        'name' => 'Nome',
        'report' => 'Relatório',
        'format' => 'Formato',
        'recurrence' => 'Recorrência',
        'enabled' => 'Habilitada',
        'updated_at' => 'Atualizada',
        'actions' => 'Ações',
    ],

    'show' => [
        'title' => 'Assinatura: :name',
        'subtitle' => 'Relatório: :report',
        'overview' => 'Resumo da assinatura',
    ],

    'validation' => [
        'invalid_cron' => 'Informe uma expressão cron válida (cinco ou seis campos).',
        'hour_required' => 'Hora e minuto são obrigatórios para esta recorrência.',
        'recipient_required' => 'Selecione ao menos um destinatário (grupo ou usuário).',
        'report_missing' => 'Selecione um relatório para esta assinatura.',
        'report_forbidden' => 'Você não tem acesso ao relatório selecionado.',
        'group_forbidden' => 'Algum grupo selecionado não tem acesso ao relatório.',
        'user_forbidden' => 'Algum usuário selecionado não tem acesso ao relatório.',
        'notification_user_forbidden' => 'Algum usuário de notificação não tem acesso ao relatório.',
        'notification_group_forbidden' => 'Algum grupo de notificação não tem acesso ao relatório.',
        'errors_title' => 'Revise os erros abaixo:',
    ],

    'errors' => [
        'no_recipients' => 'A assinatura não pode ser executada porque não tem destinatários.',
        'attachment_too_large' => 'O arquivo exportado excede o limite permitido.',
    ],

    'feedback' => [
        'metadata_error' => 'Não foi possível carregar os metadados do relatório. Tente novamente mais tarde.',
        'save_error' => 'Não foi possível salvar a assinatura. Revise o formulário e tente novamente.',
        'save_success' => 'Assinatura salva com sucesso.',
        'loading' => 'Preparando metadados.',
    ],

    'mail' => [
        'subject' => [
            'default' => 'Relatório programado: {{report_name}} ({{date}})',
        ],
        'body_default' => 'Olá,<br><br>Segue o relatório programado em anexo.<br><br>Atenciosamente.',
    ],

    'email_editor' => [
        'html' => 'Código HTML',
        'design' => 'Editor visual',
        'preview' => 'Visualização',
        'preview_hint' => 'A visualização mostra o e-mail como será entregue ao destinatário.',
        'preview_empty' => 'Ainda não há conteúdo. Adicione HTML na aba de código para gerar a visualização.',
        'design_toolbar' => 'Ferramentas de formatação',
        'font_size' => 'Tamanho da fonte',
    ],

    'notifications' => [
        'failure' => [
            'subject' => 'Falha na assinatura: :name',
            'intro' => 'A assinatura ":name" falhou na última execução.',
            'action' => 'Ver assinatura',
            'footer' => 'Você recebeu este e-mail porque está configurado como destinatário de notificações.',
        ],
    ],

    'snippets' => [
        'report_name' => 'Nome do relatório',
        'date' => 'Data agendada',
        'datetime' => 'Data e hora agendadas',
        'subscription_name' => 'Nome da assinatura',
        'report_link' => 'Link para o relatório no portal',
    ],

    'flash' => [
        'created' => 'Assinatura criada com sucesso.',
        'updated' => 'Assinatura atualizada com sucesso.',
        'deleted' => 'Assinatura excluída com sucesso.',
        'dispatched_now' => 'Assinatura enviada imediatamente.',
    ],
];
