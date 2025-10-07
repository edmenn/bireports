<?php

return [
    'page' => [
        'title' => 'Perfiles y Accesos',
        'create' => 'Nuevo perfil',
        'edit' => 'Editar perfil',
    ],

    'alerts' => [
        'success' => 'Éxito',
        'warning' => 'Atención',
        'error' => 'Error',
        'protected' => 'El perfil <strong>admin</strong> tiene acceso a <strong>todas</strong> las opciones y no permite modificar sus accesos.',
    ],

    'index' => [
        'header_button' => 'Nuevo perfil',
        'list_title' => 'Listado de Perfiles',
        'search_placeholder' => 'Buscar perfil por nombre',
        'search' => 'Buscar',
        'clear' => 'Limpiar',
        'table' => [
            'name' => 'Nombre',
            'users' => 'Usuarios',
            'permissions' => 'Permisos',
            'actions' => 'Acciones',
            'cant_delete' => 'No se puede eliminar: tiene :count usuario(s) asignado(s)',
            'empty' => 'No hay perfiles aún.',
        ],
        'delete_confirm' => '¿Eliminar el perfil :name?',
    ],

    'form' => [
        'card_title' => 'Datos del Perfil',
        'name_label' => 'Nombre',
        'name_placeholder' => 'Ej: auditor, gestor, analista',
        'badge_bg_label' => 'Color de fondo',
        'badge_text_label' => 'Color de texto/borde',
        'buttons' => [
            'save' => 'Guardar',
            'cancel' => 'Cancelar',
            'delete' => 'Eliminar',
        ],
    ],

    'permissions' => [
        'select_all' => 'Seleccionar todos',
        'deselect_all' => 'Quitar todos',
    ],
];
