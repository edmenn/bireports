<?php

return [
    'page' => [
        'title' => 'Carpetas',
        'create' => 'Nueva carpeta',
        'edit' => 'Editar carpeta',
    ],

    'cards' => [
        'details' => [
            'title' => 'Detalles de la carpeta',
        ],
        'assignments' => [
            'title' => 'Asignaciones',
        ],
    ],

    'form' => [
        'name_label' => 'Nombre',
        'name_placeholder' => 'Ingresa el nombre de la carpeta',
        'description_label' => 'Descripción',
        'description_placeholder' => 'Descripción opcional',
        'users_label' => 'Usuarios',
        'select_all_users' => 'Seleccionar todos',
        'groups_label' => 'Grupos',
        'select_all_groups' => 'Seleccionar todos',
        'reports_label' => 'Reportes',
        'select_all_reports' => 'Seleccionar todos',
        'sort_alpha' => 'Ordenar A-Z',
        'buttons' => [
            'back' => 'Volver',
            'save' => 'Guardar',
            'cancel' => 'Cancelar',
        ],
    ],

    'alerts' => [
        'success_title' => 'Éxito',
    ],

    'flash' => [
        'created' => 'Carpeta creada.',
        'updated' => 'Carpeta actualizada.',
        'deleted' => 'Carpeta eliminada.',
    ],

    'index' => [
        'card_title' => 'Listado de carpetas',
        'create_button' => 'Nueva carpeta',
    ],

    'search' => [
        'placeholder' => 'Buscar por nombre o descripción',
        'submit' => 'Buscar',
        'clear' => 'Limpiar',
    ],

    'table' => [
        'name' => 'Nombre',
        'description' => 'Descripción',
        'users' => 'Usuarios',
        'groups' => 'Grupos',
        'reports' => 'Reportes',
        'actions' => 'Acciones',
        'edit' => 'Editar',
        'edit_title' => 'Editar carpeta',
        'delete' => 'Eliminar',
        'delete_title' => 'Eliminar carpeta',
        'delete_confirm' => '¿Eliminar esta carpeta? Esta acción no se puede deshacer.',
    ],

    'empty' => [
        'users' => 'Sin usuarios disponibles.',
        'groups' => 'Sin grupos disponibles.',
        'reports' => 'Sin reportes disponibles.',
        'list' => 'Sin carpetas.',
    ],

    'workspaces' => [
        'page' => [
            'title' => 'Workspaces',
            'subtitle' => 'Lista importada desde Microsoft Fabric / Power BI',
        ],

        'actions' => [
            'import_now' => 'Importar ahora',
            'copy_capacity' => 'Copiar Capacity ID',
            'view_reports' => 'Ver reportes',
        ],

        'empty' => [
            'title' => 'Aún no se importaron workspaces',
            'hint' => 'Ejecuta una importación para ver tus workspaces aquí.',
        ],

        'kpis' => [
            'total' => 'Workspaces totales',
            'premium' => 'En capacidad Premium',
            'no_capacity' => 'Sin Capacity asignada',
        ],

        'filters' => [
            'search_label' => 'Buscar',
            'search_placeholder' => 'Filtra por nombre, tipo o capacity id…',
            'premium_label' => 'Premium',
            'premium_all' => 'Todos',
            'premium_yes' => 'Solo Premium',
            'premium_no' => 'Solo No Premium',
            'type_label' => 'Tipo',
            'type_all' => 'Todos',
            'type_empty' => 'Sin tipo',
        ],

        'table' => [
            'headers' => [
                'name' => 'Nombre',
                'type' => 'Tipo',
                'premium' => 'Premium',
                'capacity' => 'Capacity ID',
                'actions' => 'Acciones',
            ],
            'id_label' => 'ID:',
        ],

        'badges' => [
            'no_type' => 'Sin tipo',
            'yes' => 'Sí',
            'no' => 'No',
        ],

        'js' => [
            'copy_failed' => 'No se pudo copiar',
        ],

        'show' => [
            'title' => 'Workspace: :name',
            'empty' => 'Este workspace no tiene reportes importados.',
            'table' => [
                'title' => 'Título',
                'dataset' => 'Dataset ID',
                'actions' => 'Acciones',
                'open' => 'Abrir',
            ],
        ],
    ],

];
