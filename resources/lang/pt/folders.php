<?php

return [
    'page' => [
        'title' => 'Pastas',
        'create' => 'Nova pasta',
        'edit' => 'Editar pasta',
    ],

    'cards' => [
        'details' => [
            'title' => 'Detalhes da pasta',
        ],
        'assignments' => [
            'title' => 'Atribuições',
        ],
    ],

    'form' => [
        'name_label' => 'Nome',
        'name_placeholder' => 'Informe o nome da pasta',
        'description_label' => 'Descrição',
        'description_placeholder' => 'Descrição opcional',
        'users_label' => 'Usuários',
        'groups_label' => 'Grupos',
        'reports_label' => 'Relatórios',
        'buttons' => [
            'back' => 'Voltar',
            'save' => 'Salvar',
            'cancel' => 'Cancelar',
        ],
    ],

    'alerts' => [
        'success_title' => 'Sucesso',
    ],

    'flash' => [
        'created' => 'Pasta criada.',
        'updated' => 'Pasta atualizada.',
        'deleted' => 'Pasta removida.',
    ],

    'index' => [
        'card_title' => 'Lista de pastas',
        'create_button' => 'Nova pasta',
    ],

    'search' => [
        'placeholder' => 'Pesquisar por nome ou descrição',
        'submit' => 'Pesquisar',
        'clear' => 'Limpar',
    ],

    'table' => [
        'name' => 'Nome',
        'description' => 'Descrição',
        'users' => 'Usuários',
        'groups' => 'Grupos',
        'reports' => 'Relatórios',
        'actions' => 'Ações',
        'edit' => 'Editar',
        'edit_title' => 'Editar pasta',
        'delete' => 'Excluir',
        'delete_title' => 'Excluir pasta',
        'delete_confirm' => 'Excluir esta pasta? Esta ação não pode ser desfeita.',
    ],

    'empty' => [
        'users' => 'Nenhum usuário disponível.',
        'groups' => 'Nenhum grupo disponível.',
        'reports' => 'Nenhum relatório disponível.',
        'list' => 'Nenhuma pasta encontrada.',
    ],

    'workspaces' => [
        'page' => [
            'title' => 'Workspaces',
            'subtitle' => 'Lista importada do Microsoft Fabric / Power BI',
        ],

        'actions' => [
            'import_now' => 'Importar agora',
            'copy_capacity' => 'Copiar Capacity ID',
            'view_reports' => 'Ver relatórios',
        ],

        'empty' => [
            'title' => 'Nenhum workspace importado ainda',
            'hint' => 'Execute uma importação para ver seus workspaces aqui.',
        ],

        'kpis' => [
            'total' => 'Workspaces totais',
            'premium' => 'Em capacidade Premium',
            'no_capacity' => 'Sem Capacity atribuída',
        ],

        'filters' => [
            'search_label' => 'Pesquisar',
            'search_placeholder' => 'Filtre por nome, tipo ou capacity id…',
            'premium_label' => 'Premium',
            'premium_all' => 'Todos',
            'premium_yes' => 'Apenas Premium',
            'premium_no' => 'Apenas Não Premium',
            'type_label' => 'Tipo',
            'type_all' => 'Todos',
            'type_empty' => 'Sem tipo',
        ],

        'table' => [
            'headers' => [
                'name' => 'Nome',
                'type' => 'Tipo',
                'premium' => 'Premium',
                'capacity' => 'Capacity ID',
                'actions' => 'Ações',
            ],
            'id_label' => 'ID:',
        ],

        'badges' => [
            'no_type' => 'Sem tipo',
            'yes' => 'Sim',
            'no' => 'Não',
        ],

        'js' => [
            'copy_failed' => 'Não foi possível copiar',
        ],

        'show' => [
            'title' => 'Workspace: :name',
            'empty' => 'Este workspace não possui relatórios importados.',
            'table' => [
                'title' => 'Título',
                'dataset' => 'Dataset ID',
                'actions' => 'Ações',
                'open' => 'Abrir',
            ],
        ],
    ],

];
