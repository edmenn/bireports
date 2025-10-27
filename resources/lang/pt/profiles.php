<?php

return [
    'assigned_roles' => [
        'label' => 'Perfis atribuídos',
    ],

    'alerts' => [
        'entra' => [
            'title' => 'Informação',
            'profile_message' => 'Este perfil vem do Microsoft Entra. O nome é sincronizado com o seu diretório corporativo e não pode ser alterado pela aplicação.',
            'password_message' => 'Você usa login com o Microsoft Entra. A senha é administrada pelo seu provedor de identidade.',
        ],
    ],

    'flash' => [
        'created' => 'Perfil criado.',
        'updated' => 'Perfil atualizado.',
        'deleted' => 'Perfil removido.',
        'cannot_rename_admin' => 'O perfil "admin" não pode ser renomeado.',
        'cannot_delete_admin' => 'O perfil "admin" não pode ser excluído.',
        'cannot_delete_assigned' => 'Não é possível excluir o perfil: há :count usuário(s) associado(s).',
        'assignments_unknown' => 'Não foi possível verificar usuários associados; o perfil não foi excluído por segurança.',
        'delete_failed' => 'Não foi possível excluir o perfil.',
    ],

    'permissions' => [
        'unknown_section' => 'Outras permissões',
        'section_prefix' => [
            'menu' => 'Menu',
            'portal' => 'Portal',
            'reports' => 'Relatórios',
            'datasets' => 'Datasets',
            'workspaces' => 'Workspaces',
            'applications' => 'Aplicações',
            'application_sections' => 'Aplicações',
            'capacities' => 'Capacidades',
            'users' => 'Usuários',
            'roles' => 'Perfis',
            'groups' => 'Grupos',
            'folders' => 'Pastas',
            'config' => 'Configuração',
            'audit' => 'Auditoria',
        ],
        'sections' => [
            'menu' => 'Menu e Navegação',
            'portal' => 'Portal de acesso',
            'reports' => 'Relatórios e Embedding',
            'datasets' => 'Datasets',
            'workspaces' => 'Workspaces',
            'applications' => 'Aplicações',
            'capacities' => 'Capacidades',
            'users' => 'Gestão de usuários',
            'roles' => 'Perfis e acessos',
            'groups' => 'Grupos',
            'folders' => 'Pastas',
            'config' => 'Configuração',
            'audit' => 'Auditoria',
            'other' => 'Outras permissões',
        ],
        'labels' => [
            'menu' => [
                'portal' => 'Portal: Meu espaço',
                'reports_index' => 'Power BI: Relatórios',
                'workspaces' => 'Power BI: Workspaces',
                'capacities' => 'Power BI: Capacidades',
                'users' => 'Organização: Usuários',
                'groups' => 'Organização: Grupos',
                'folders' => 'Organização: Pastas',
                'applications' => 'Organização: Aplicações',
                'configurations' => 'Configurações',
                'audit' => 'Auditoria',
                'roles' => 'Organização: Papéis e acessos',
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
            ],
            'datasets' => [
                'view' => 'Ver datasets',
                'refresh' => 'Executar refresh',
                'schedule_view' => 'Ver programação de refresh',
                'schedule_edit' => 'Editar programação de refresh',
                'export' => 'Exportar datasets',
            ],
            'workspaces' => [
                'view' => 'Ver workspaces',
            ],
            'applications' => [
                'view' => 'Ver aplicações',
                'create' => 'Criar aplicações',
                'edit' => 'Editar aplicações',
                'delete' => 'Excluir aplicações',
            ],
            'application_sections' => [
                'manage' => 'Gerir seções de aplicações',
            ],
            'capacities' => [
                'view' => 'Ver capacidades',
                'pause' => 'Pausar capacidades',
                'resume' => 'Retomar capacidades',
                'schedule_view' => 'Ver programação de capacidades',
                'schedule_edit' => 'Editar programação de capacidades',
                'audit_view' => 'Ver auditoria das capacidades',
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
                'view' => 'Ver configuração',
                'edit' => 'Editar configuração',
            ],
            'audit' => [
                'view' => 'Ver auditoria',
                'export' => 'Exportar auditoria',
                'reset' => 'Restaurar auditoria',
            ],
        ],
    ],
];
