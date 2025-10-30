<?php

return [
    'assigned_roles' => [
        'label' => 'Perfis atribuídos',
    ],

    'alerts' => [
        'entra' => [
            'title' => 'Informação',
            'profile_message' => 'Este perfil é proveniente do Microsoft Entra. O nome sincroniza com o diretório corporativo e não pode ser alterado pela aplicação.',
            'password_message' => 'Você entra com Microsoft Entra. A senha é gerenciada pelo seu provedor de identidade.',
        ],
    ],

    'flash' => [
        'created' => 'Perfil criado.',
        'updated' => 'Perfil atualizado.',
        'deleted' => 'Perfil excluído.',
        'cannot_rename_admin' => 'O perfil "admin" não pode ser renomeado.',
        'cannot_delete_admin' => 'O perfil "admin" não pode ser excluído.',
        'cannot_delete_assigned' => 'Não foi possível excluir o perfil: :count usuário(s) estão atribuídos.',
        'assignments_unknown' => 'Não foi possível verificar os usuários atribuídos; o perfil não foi excluído por segurança.',
        'delete_failed' => 'Não foi possível excluir o perfil.',
    ],

    'permissions' => [
        'unknown_section' => 'Outras permissões',
        'section_prefix' => [
            'menu' => 'Menu',
            'portal' => 'Portal',
            'reports' => 'Relatórios',
            'datasets' => 'Conjuntos de dados',
            'workspaces' => 'Espaços de trabalho',
            'applications' => 'Aplicações',
            'application_sections' => 'Aplicações',
            'capacities' => 'Capacidades',
            'users' => 'Usuários',
            'roles' => 'Perfis',
            'groups' => 'Grupos',
            'folders' => 'Pastas',
            'config' => 'Configurações',
            'audit' => 'Auditoria',
        ],
        'sections' => [
            'menu' => 'Menu e navegação',
            'portal' => 'Portal de acesso',
            'reports' => 'Relatórios e incorporação',
            'datasets' => 'Conjuntos de dados',
            'workspaces' => 'Espaços de trabalho',
            'applications' => 'Aplicações',
            'capacities' => 'Capacidades',
            'users' => 'Gestão de usuários',
            'roles' => 'Perfis e acessos',
            'groups' => 'Grupos',
            'folders' => 'Pastas',
            'config' => 'Configurações',
            'audit' => 'Auditoria',
            'other' => 'Outras permissões',
        ],
        'labels' => [
            'menu' => [
                'portal' => 'Portal: Meu espaço',
                'reports_index' => 'Power BI: Relatórios',
                'workspaces' => 'Power BI: Espaços de trabalho',
                'capacities' => 'Power BI: Capacidades',
                'users' => 'Organização: Usuários',
                'groups' => 'Organização: Grupos',
                'folders' => 'Organização: Pastas',
                'applications' => 'Organização: Aplicações',
                'configurations' => 'Configurações',
                'audit' => 'Auditoria',
                'roles' => 'Organização: Perfis e acessos',
            ],
            'portal' => [
                'view' => 'Acessar o portal',
            ],
            'reports' => [
                'view' => 'Ver e acessar relatórios',
                'create' => 'Criar relatórios',
                'edit' => 'Editar relatórios',
                'delete' => 'Excluir relatórios',
                'export' => 'Exportar relatórios',
                'subscriptions_manage' => 'Gerenciar assinaturas de relatórios',
            ],
            'datasets' => [
                'view' => 'Ver conjuntos de dados',
                'refresh' => 'Executar atualização',
                'schedule_view' => 'Ver agenda de atualização',
                'schedule_edit' => 'Editar agenda de atualização',
                'export' => 'Exportar conjuntos de dados',
            ],
            'workspaces' => [
                'view' => 'Ver espaços de trabalho',
            ],
            'applications' => [
                'view' => 'Ver aplicações',
                'create' => 'Criar aplicações',
                'edit' => 'Editar aplicações',
                'delete' => 'Excluir aplicações',
            ],
            'application_sections' => [
                'manage' => 'Gerenciar seções das aplicações',
            ],
            'capacities' => [
                'view' => 'Ver capacidades',
                'pause' => 'Pausar capacidades',
                'resume' => 'Retomar capacidades',
                'schedule_view' => 'Ver agenda de capacidade',
                'schedule_edit' => 'Editar agenda de capacidade',
                'audit_view' => 'Ver auditoria da capacidade',
            ],
            'users' => [
                'view' => 'Ver usuários',
                'create' => 'Criar usuários',
                'edit' => 'Editar usuários',
                'delete' => 'Excluir usuários',
                'sync' => 'Sincronizar usuários',
            ],
            'roles' => [
                'view' => 'Ver perfis',
                'create' => 'Criar perfis',
                'edit' => 'Editar perfis',
                'delete' => 'Excluir perfis',
            ],
            'groups' => [
                'view' => 'Ver grupos',
                'create' => 'Criar grupos',
                'edit' => 'Editar grupos',
                'delete' => 'Excluir grupos',
            ],
            'folders' => [
                'view' => 'Ver pastas',
                'create' => 'Criar pastas',
                'edit' => 'Editar pastas',
                'delete' => 'Excluir pastas',
            ],
            'config' => [
                'view' => 'Ver configurações',
                'edit' => 'Editar configurações',
            ],
            'audit' => [
                'view' => 'Ver auditoria',
                'export' => 'Exportar auditoria',
                'reset' => 'Restabelecer auditoria',
            ],
        ],
    ],
];
