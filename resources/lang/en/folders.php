<?php

return [
    'page' => [
        'title' => 'Folders',
        'create' => 'New folder',
        'edit' => 'Edit folder',
    ],

    'cards' => [
        'details' => [
            'title' => 'Folder details',
        ],
        'assignments' => [
            'title' => 'Assignments',
        ],
    ],

    'form' => [
        'name_label' => 'Name',
        'name_placeholder' => 'Enter folder name',
        'description_label' => 'Description',
        'description_placeholder' => 'Optional description',
        'users_label' => 'Users',
        'groups_label' => 'Groups',
        'reports_label' => 'Reports',
        'buttons' => [
            'back' => 'Back',
            'save' => 'Save',
            'cancel' => 'Cancel',
        ],
    ],

    'alerts' => [
        'success_title' => 'Success',
    ],

    'flash' => [
        'created' => 'Folder created.',
        'updated' => 'Folder updated.',
        'deleted' => 'Folder deleted.',
    ],

    'index' => [
        'card_title' => 'Folder list',
        'create_button' => 'New folder',
    ],

    'search' => [
        'placeholder' => 'Search by name or description',
        'submit' => 'Search',
        'clear' => 'Clear',
    ],

    'table' => [
        'name' => 'Name',
        'description' => 'Description',
        'users' => 'Users',
        'groups' => 'Groups',
        'reports' => 'Reports',
        'actions' => 'Actions',
        'edit' => 'Edit',
        'edit_title' => 'Edit folder',
        'delete' => 'Delete',
        'delete_title' => 'Delete folder',
        'delete_confirm' => 'Delete this folder? This action cannot be undone.',
    ],

    'empty' => [
        'users' => 'No users available.',
        'groups' => 'No groups available.',
        'reports' => 'No reports available.',
        'list' => 'No folders found.',
    ],

    'workspaces' => [
        'page' => [
            'title' => 'Workspaces',
            'subtitle' => 'Imported list from Microsoft Fabric / Power BI',
        ],

        'actions' => [
            'import_now' => 'Import now',
            'copy_capacity' => 'Copy Capacity ID',
            'view_reports' => 'View reports',
        ],

        'empty' => [
            'title' => 'No workspaces imported yet',
            'hint' => 'Run an import to see your workspaces here.',
        ],

        'kpis' => [
            'total' => 'Total workspaces',
            'premium' => 'On Premium capacity',
            'no_capacity' => 'Without assigned Capacity',
        ],

        'filters' => [
            'search_label' => 'Search',
            'search_placeholder' => 'Filter by name, type or capacity id…',
            'premium_label' => 'Premium',
            'premium_all' => 'All',
            'premium_yes' => 'Only Premium',
            'premium_no' => 'Only Non-Premium',
            'type_label' => 'Type',
            'type_all' => 'All',
            'type_empty' => 'No type',
        ],

        'table' => [
            'headers' => [
                'name' => 'Name',
                'type' => 'Type',
                'premium' => 'Premium',
                'capacity' => 'Capacity ID',
                'actions' => 'Actions',
            ],
            'id_label' => 'ID:',
        ],

        'badges' => [
            'no_type' => 'No type',
            'yes' => 'Yes',
            'no' => 'No',
        ],

        'js' => [
            'copy_failed' => 'Could not copy',
        ],

        'show' => [
            'title' => 'Workspace: :name',
            'empty' => 'This workspace has no imported reports.',
            'table' => [
                'title' => 'Title',
                'dataset' => 'Dataset ID',
                'actions' => 'Actions',
                'open' => 'Open',
            ],
        ],
    ],

];
