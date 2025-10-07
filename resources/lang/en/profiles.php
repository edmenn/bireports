<?php

return [
    'assigned_roles' => [
        'label' => 'Assigned roles',
    ],

    'alerts' => [
        'entra' => [
            'title' => 'Information',
            'profile_message' => 'This profile comes from Microsoft Entra. The name syncs with your corporate directory and cannot be changed from the application.',
            'password_message' => 'You sign in with Microsoft Entra. The password is managed by your identity provider.',
        ],
    ],

    'flash' => [
        'created' => 'Profile created.',
        'updated' => 'Profile updated.',
        'deleted' => 'Profile deleted.',
        'cannot_rename_admin' => 'The "admin" role cannot be renamed.',
        'cannot_delete_admin' => 'The "admin" role cannot be deleted.',
        'cannot_delete_assigned' => 'The profile cannot be deleted: :count user(s) are assigned.',
        'assignments_unknown' => 'Assigned users could not be verified; the profile was not deleted for safety.',
        'delete_failed' => 'The profile could not be deleted.',
    ],

    'permissions' => [
        'unknown_section' => 'Other permissions',
        'section_prefix' => [
            'menu' => 'Menu',
        ],
        'sections' => [
            'menu' => 'Menu & Navigation',
            'reports' => 'Reports & Embedding',
            'otros' => 'Other permissions',
        ],
        'labels' => [
            'menu' => [
                'portal' => 'Portal: My space',
                'reports_index' => 'Power BI: Reports',
                'workspaces' => 'Power BI: Workspaces',
                'capacities' => 'Power BI: Capacities',
                'users' => 'Organization: Users',
                'groups' => 'Organization: Groups',
                'folders' => 'Organization: Folders',
                'applications' => 'Organization: Applications',
                'configuraciones' => 'Settings',
                'auditoria' => 'Audit',
                'roles' => 'Organization: Profiles & Access',
            ],
            'reports' => [
                'view' => 'View and access reports',
            ],
        ],
    ],
];
