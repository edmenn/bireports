<?php

return [
    'page' => [
        'title' => 'Perfis e Acessos',
        'create' => 'Novo perfil',
        'edit' => 'Editar perfil',
    ],

    'alerts' => [
        'success' => 'Sucesso',
        'warning' => 'Atenção',
        'error' => 'Erro',
        'protected' => 'O perfil <strong>admin</strong> possui acesso a <strong>tudo</strong> e não permite alterar suas permissões.',
    ],

    'index' => [
        'header_button' => 'Novo perfil',
        'list_title' => 'Lista de Perfis',
        'search_placeholder' => 'Buscar perfil pelo nome',
        'search' => 'Pesquisar',
        'clear' => 'Limpar',
        'table' => [
            'name' => 'Nome',
            'users' => 'Usuários',
            'permissions' => 'Permissões',
            'actions' => 'Ações',
            'cant_delete' => 'Não é possível excluir: :count usuário(s) associado(s)',
            'empty' => 'Nenhum perfil cadastrado.',
        ],
        'delete_confirm' => 'Excluir o perfil :name?',
    ],

    'form' => [
        'card_title' => 'Dados do Perfil',
        'name_label' => 'Nome',
        'name_placeholder' => 'Ex.: auditor, gestor, analista',
        'badge_bg_label' => 'Cor de fundo',
        'badge_text_label' => 'Cor do texto/borda',
        'buttons' => [
            'back' => 'Voltar',
            'save' => 'Salvar',
            'cancel' => 'Cancelar',
            'delete' => 'Excluir',
        ],
    ],

    'permissions' => [
        'select_all' => 'Selecionar todos',
        'deselect_all' => 'Remover todos',
    ],
];
