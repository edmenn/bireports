<?php

return [
    'menu' => [
        'title' => 'My space',
        'home' => 'Home',
        'favorites' => 'Favorites',
        'most_accessed' => 'Most accessed',
        'applications' => 'Applications',
    ],
    'home' => [
        'title' => 'My home',
        'folders' => [
            'title' => 'Folders',
            'empty' => 'No folders assigned.',
        ],
        'reports' => [
            'title' => 'Reports',
            'in_folder' => 'in :name',
            'empty' => 'No reports available.',
        ],
    ],
    'favorites' => [
        'title' => 'My favorites',
        'empty' => 'You have not added favorites yet.',
        'flash' => [
            'added' => 'Report added to favorites.',
            'removed' => 'Report removed from favorites.',
        ],
    ],
    'most_accessed' => [
        'title' => 'Most accessed',
        'empty' => 'No access data recorded yet.',
        'columns' => [
            'report' => 'Report',
            'views' => 'Views',
        ],
        'actions' => [
            'open' => 'Open',
        ],
    ],
    'report_card' => [
        'open' => 'Open',
        'open_title' => 'Open :name',
        'cover_aria' => 'Report cover',
        'cover_alt' => 'Cover for :name',
        'cover_generic_aria' => 'Default cover illustration',
        'favorite_add' => 'Add to favorites',
        'favorite_remove' => 'Remove from favorites',
        'workspace_missing' => 'Workspace not assigned',
    ],
];
