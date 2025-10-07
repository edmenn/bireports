<?php

return [
    'assigned_roles' => [
        'label' => 'Perfis atribuídos',
    ],

    'alerts' => [
        'entra' => [
            'title' => 'Informação',
            'profile_message' => 'Este perfil vem do Microsoft Entra. O nome é sincronizado com seu diretório corporativo e não pode ser alterado pela aplicação.',
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
        ],
        'sections' => [
            'menu' => 'Menu e Navegação',
            'reports' => 'Relatórios e Embedding',
            'otros' => 'Outras permissões',
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
                'configuraciones' => 'Configurações',
                'auditoria' => 'Auditoria',
                'roles' => 'Organização: Perfis e Acessos',
            ],
            'reports' => [
                'view' => 'Ver e acessar relatórios',
            ],
        ],
    ],
];
