<?php

return [
    'page' => [
        'title' => 'Roles & Access',
        'create' => 'New role',
        'edit' => 'Edit role',
    ],

    'alerts' => [
        'success' => 'Success',
        'warning' => 'Warning',
        'error' => 'Error',
        'protected' => 'The <strong>admin</strong> role grants access to <strong>everything</strong> and its permissions cannot be changed.',
    ],

    'index' => [
        'header_button' => 'New role',
        'list_title' => 'Role list',
        'search_placeholder' => 'Search role by name',
        'search' => 'Search',
        'clear' => 'Clear',
        'table' => [
            'name' => 'Name',
            'users' => 'Users',
            'permissions' => 'Permissions',
            'actions' => 'Actions',
            'cant_delete' => 'Cannot delete: :count user(s) assigned',
            'empty' => 'No roles yet.',
        ],
        'delete_confirm' => 'Delete role :name?',
    ],

    'form' => [
        'card_title' => 'Role Details',
        'name_label' => 'Name',
        'name_placeholder' => 'E.g.: auditor, manager, analyst',
        'badge_bg_label' => 'Background color',
        'badge_text_label' => 'Text/border color',
        'buttons' => [
            'back' => 'Back',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'delete' => 'Delete',
        ],
    ],

    'permissions' => [
        'select_all' => 'Select all',
        'deselect_all' => 'Unselect all',
    ],
];
