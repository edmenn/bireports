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
            'portal' => 'Portal',
            'reports' => 'Reports',
            'datasets' => 'Datasets',
            'workspaces' => 'Workspaces',
            'applications' => 'Applications',
            'application_sections' => 'Applications',
            'capacities' => 'Capacities',
            'users' => 'Users',
            'roles' => 'Roles',
            'groups' => 'Groups',
            'folders' => 'Folders',
            'config' => 'Configuration',
            'audit' => 'Audit',
        ],
        'sections' => [
            'menu' => 'Menu & Navigation',
            'portal' => 'Access portal',
            'reports' => 'Reports & Embedding',
            'datasets' => 'Datasets',
            'workspaces' => 'Workspaces',
            'applications' => 'Applications',
            'capacities' => 'Capacities',
            'users' => 'User management',
            'roles' => 'Roles & access',
            'groups' => 'Groups',
            'folders' => 'Folders',
            'config' => 'Configuration',
            'audit' => 'Audit',
            'other' => 'Other permissions',
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
                'configurations' => 'Settings',
                'audit' => 'Audit',
                'roles' => 'Organization: Roles & Access',
            ],
            'portal' => [
                'view' => 'Access the portal',
            ],
            'reports' => [
                'view' => 'View and access reports',
                'create' => 'Create reports',
                'edit' => 'Edit reports',
                'delete' => 'Delete reports',
                'export' => 'Export reports',
            ],
            'datasets' => [
                'view' => 'View datasets',
                'refresh' => 'Run refresh',
                'schedule_view' => 'View refresh schedule',
                'schedule_edit' => 'Edit refresh schedule',
                'export' => 'Export datasets',
            ],
            'workspaces' => [
                'view' => 'View workspaces',
            ],
            'applications' => [
                'view' => 'View applications',
                'create' => 'Create applications',
                'edit' => 'Edit applications',
                'delete' => 'Delete applications',
            ],
            'application_sections' => [
                'manage' => 'Manage application sections',
            ],
            'capacities' => [
                'view' => 'View capacities',
                'pause' => 'Pause capacities',
                'resume' => 'Resume capacities',
                'schedule_view' => 'View capacity schedule',
                'schedule_edit' => 'Edit capacity schedule',
                'audit_view' => 'View capacity audit',
            ],
            'users' => [
                'view' => 'View users',
                'create' => 'Create users',
                'edit' => 'Edit users',
                'delete' => 'Delete users',
                'sync' => 'Sync users',
            ],
            'roles' => [
                'view' => 'View roles',
                'create' => 'Create roles',
                'edit' => 'Edit roles',
                'delete' => 'Delete roles',
            ],
            'groups' => [
                'view' => 'View groups',
                'create' => 'Create groups',
                'edit' => 'Edit groups',
                'delete' => 'Delete groups',
            ],
            'folders' => [
                'view' => 'View folders',
                'create' => 'Create folders',
                'edit' => 'Edit folders',
                'delete' => 'Delete folders',
            ],
            'config' => [
                'view' => 'View configuration',
                'edit' => 'Edit configuration',
            ],
            'audit' => [
                'view' => 'View audit',
                'export' => 'Export audit',
                'reset' => 'Reset audit',
            ],
        ],
    ],
];
