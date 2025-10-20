<?php

return [
    'index' => [
        'title' => 'All reports',
        'subtitle' => 'List imported from Microsoft Fabric / Power BI',
        'import_now' => 'Import now',
        'empty' => [
            'title' => 'No reports imported yet',
            'hint' => 'Run an import to see your dashboards here.',
            'action' => 'Import now',
        ],
        'metrics' => [
            'total' => 'Total reports',
            'with_cover' => 'With cover image',
            'without_cover' => 'Without cover image',
        ],
        'filters' => [
            'search_label' => 'Search',
            'search_placeholder' => 'Title, report ID, workspace or dataset ID...',
            'workspace_label' => 'Workspace',
            'workspace_all' => 'All',
            'cover_label' => 'Cover',
            'cover_all' => 'All',
            'cover_yes' => 'With cover',
            'cover_no' => 'Without cover',
            'order_label' => 'Sort by',
            'order_options' => [
                'name_asc' => 'Name (A-Z)',
                'name_desc' => 'Name (Z-A)',
                'workspace_asc' => 'Workspace (A-Z)',
                'workspace_desc' => 'Workspace (Z-A)',
            ],
            'hint' => 'Filters and sorting run in the browser without reloading the page.',
        ],
        'cards' => [
            'open_title' => 'Open report',
            'edit_title' => 'Edit cover and description',
            'cover_aria' => 'Report cover',
            'cover_alt' => 'Cover for :name',
            'default_cover_aria' => 'Default cover illustration',
        ],
        'actions' => [
            'open' => 'Open',
            'edit' => 'Edit',
            'view' => 'View report',
        ],
        'badges' => [
            'workspace' => 'Workspace',
            'dataset' => 'Dataset',
            'report' => 'Report',
        ],
        'copy' => [
            'dataset_title' => 'Copy dataset ID',
            'dataset_label' => 'Dataset',
            'report_title' => 'Copy report ID',
            'report_label' => 'Report',
            'success' => 'Copied',
            'error' => 'Could not copy to the clipboard.',
        ],
    ],

    'show' => [
        'title_fallback' => 'Report',
        'loading' => 'Loading report...',
        'fullscreen_enter' => 'Enter full screen',
        'fullscreen_exit' => 'Exit full screen',
        'missing_embed' => [
            'title' => 'We could not prepare the embedded report.',
            'hint' => 'Check the Power BI credentials and workspace permissions in Settings.',
            'link' => 'Settings',
            'summary' => 'Payload returned by PowerBiService::generateReportEmbedToken()',
            'items' => [
                'embed_url' => 'Embed URL',
                'access_token' => 'Access token',
                'report_id' => 'Report ID',
            ],
            'status' => [
                'ok' => 'Available',
                'missing' => 'Missing',
            ],
        ],
        'errors' => [
            'no_credentials' => 'Power BI credentials are missing in Settings.',
            'embed_failure' => 'We could not prepare the embedded report. Check credentials, capacity and permissions.',
            'missing_fields' => 'The embed URL, access token or report ID is missing.',
            'client_library' => 'The Power BI client library could not be loaded.',
            'capacity_paused' => 'The capacity is paused outside its operating window.',
            'capacity_timeout' => 'The capacity could not start in time (timeout).',
            'load_timeout' => 'The report took too long to load (10 s timeout). Check the network, token or capacity.',
            'debug_template' => ":exception: :message\nCode: :code\nFile: :file (line :line)\n\nTrace:\n:trace",
        ],
        'on_demand' => [
            'starting' => 'Warming up the capacity, please wait a few seconds...',
        ],
    ],

    'edit' => [
        'title' => 'Edit report',
        'card_title' => 'Cover & description',
        'view_report' => 'View report',
        'alerts' => [
            'errors_title' => 'Please review the errors:',
            'close' => 'Close',
        ],
        'form' => [
            'read_only' => [
                'title' => 'Title',
                'workspace_id' => 'Workspace ID',
                'report_id' => 'Report ID',
            ],
            'preview' => [
                'label' => 'Preview',
                'hint' => 'If you neither upload a file nor provide a valid URL, the default cover (if any) or the BI icon will be used.',
            ],
            'upload' => [
                'label' => 'Upload image (optional)',
                'hint' => 'Accepted formats: JPG, PNG, WEBP, GIF or SVG. Maximum 2 MB.',
                'button' => 'Choose file',
                'status' => [
                    'empty' => 'No file selected',
                    'selected' => 'Selected: :name',
                ],
            ],
            'make_default' => [
                'label' => 'Use this image as the default cover for reports without one',
                'hint' => 'Applies only when you upload an image file.',
            ],
            'url' => [
                'label' => 'Image URL (optional)',
                'placeholder' => 'https://your-cdn/covers/report.png',
                'hint' => 'If you upload a file, the URL is ignored. The default option applies only to uploaded files.',
            ],
            'description' => [
                'label' => 'Description (optional)',
                'placeholder' => 'Short description of the content, target audience, filters, etc.',
            ],
            'allow_resume' => [
                'label' => 'Allow capacity warm-up outside business hours for this report',
                'hint' => 'When enabled, any user can power it on when opening the report; it will shut down automatically after inactivity.',
            ],
        ],
        'buttons' => [
            'cancel' => 'Cancel',
            'save' => 'Save changes',
        ],
    ],

    'validation' => [
        'cover_url_url' => 'The image must be a valid URL.',
        'cover_file_max' => 'The image must not exceed 2 MB.',
        'cover_file_mimetypes' => 'Allowed formats: JPG, PNG, WEBP, GIF or SVG.',
    ],

    'flash' => [
        'updated_default' => 'Report updated and cover set as the default.',
        'updated' => 'Report updated successfully.',
    ],
];
