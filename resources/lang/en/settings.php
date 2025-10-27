<?php

return [
    'page' => [
        'title' => 'Settings',
        'subtitle' => 'Microsoft Fabric / Power BI credentials for App-Owns-Data',
    ],

    'actions' => [
        'view_audit' => 'View audit',
        'import_now' => 'Import now',
        'save' => 'Save',
        'cancel' => 'Cancel',
    ],

    'alerts' => [
        'success' => 'Success',
        'error' => 'Error',
        'validation' => 'Validation errors',
    ],

    'menu' => [
        'parameters' => 'Parameters',
        'theme' => 'Theme',
    ],

    'service_principal' => [
        'card_title' => 'Connection credentials (Service Principal)',
        'intro' => 'Configure an Entra ID application (Service Principal) with Power BI permissions for unattended authentication.',
        'fields' => [
            'client_id' => [
                'label' => 'Client ID (Application ID)',
                'placeholder' => 'Application ID',
                'help' => 'Azure Entra ID → App registrations → Overview → Application (client) ID.',
                'tooltip' => 'Identifier of the app (Client ID) registered in Entra ID.',
            ],
            'tenant_id' => [
                'label' => 'Tenant ID (Directory ID)',
                'placeholder' => 'Directory ID',
                'help' => 'Azure Entra ID → Overview → Tenant ID.',
                'tooltip' => 'Must match the tenant where the app is registered.',
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'placeholder' => 'Client secret',
                'help' => 'Stored encrypted. Leave blank to keep the current value.',
                'tooltip' => 'Generate the secret in Azure Entra ID → Certificates & secrets. Avoid pasting masked values.',
            ],
            'refresh_minutes' => [
                'label' => 'Refresh (min)',
                'placeholder' => '0 = auto',
                'help' => '0 = handled automatically by the SDK.',
                'tooltip' => 'Use 0 to let the library manage the renewal; adjust only if needed.',
            ],
            'timezone' => [
                'label' => 'Default time zone',
                'tooltip' => 'Impacts displayed times and scheduled tasks execution.',
                'help' => 'It will apply to the scheduler and as the default value for dataset scheduling.',
            ],
        ],
    ],

    'auth' => [
        'microsoft' => [
            'title' => 'Microsoft sign-in',
            'description' => 'Allows users to authenticate using their Microsoft accounts.',
        ],
        'local' => [
            'title' => 'Local sign-in',
            'description' => 'Traditional email and password form. In emergencies, the environment can force-enable it with :env.',
        ],
    ],

    'prewarm' => [
        'title' => 'Capacity pre-warm',
        'description' => 'If enabled, before a scheduled refresh we resume (start) the related capacity to avoid failures due to being paused. Disable it if you prefer to control this externally.',
    ],

    'mail' => [
        'card_title' => 'Email sending configuration',
        'fields' => [
            'driver' => [
                'label' => 'Mail driver',
                'placeholder' => 'Select driver',
                'help' => 'Example: smtp, sendmail, log',
            ],
            'host' => [
                'label' => 'SMTP Host',
                'placeholder' => 'smtp.mailjet.com',
                'help' => 'Your mail server hostname',
            ],
            'port' => [
                'label' => 'Port',
                'placeholder' => '587',
                'help' => 'Common ports: 25, 465, 587',
            ],
            'encryption' => [
                'label' => 'Encryption',
                'placeholder' => 'Select encryption',
                'help' => 'Usually tls or ssl',
            ],
            'username' => [
                'label' => 'Username',
                'placeholder' => 'user@mail.com',
            ],
            'password' => [
                'label' => 'Password',
                'placeholder' => '********',
            ],
            'from_address' => [
                'label' => 'From address',
                'placeholder' => 'noreply@domain.com',
            ],
            'from_name' => [
                'label' => 'From name',
                'placeholder' => 'System Notifications',
            ],
        ],
        'test' => [
            'button' => 'Send test email',
            'modal_title' => 'Send test email',
            'description' => 'Use the saved mail settings to send a quick test message.',
            'recipient_label' => 'Recipient',
            'recipient_placeholder' => 'user@example.com',
            'subject_label' => 'Subject',
            'subject_placeholder' => 'Configuration test',
            'message_label' => 'Message',
            'message_placeholder' => 'This is a test message to verify the mail configuration.',
            'send_button' => 'Send test',
            'sending' => 'Sending...',
            'success' => 'Test email sent successfully.',
            'failed' => 'Unable to send the test email: :error',
            'driver_missing' => 'Configure a valid mail driver before sending a test.',
            'smtp_missing' => 'Configure the SMTP host and port before sending a test.',
            'default_subject' => 'Test email from :app',
            'generic_error' => 'We could not send the test email. Please review the configuration and try again.',
        ],
    ],

    'login' => [
        'cards' => [
            'uploads' => [
                'title' => 'Upload Files',
                'fields' => [
                    'logo' => [
                        'label' => 'Login logo (PNG/JPG/SVG)',
                        'placeholder' => 'Choose a file...',
                        'help' => 'Use a transparent background and height ≤ 90px.',
                        'tooltip' => 'Supported formats: PNG, JPG, or SVG. Displayed above the login form.',
                    ],
                    'background' => [
                        'label' => 'Background image (full screen)',
                        'placeholder' => 'Choose a file...',
                        'help' => 'It will scale to cover the entire screen on the login page.',
                        'tooltip' => 'Tip: use 1920×1080 or higher, with good compression.',
                    ],
                ],
            ],
            'preview' => [
                'title' => 'Current Previews',
                'logo_heading' => 'Login Logo:',
                'logo_alt' => 'Login logo preview',
                'no_logo' => 'No login logo uploaded yet.',
                'background_heading' => 'Background Image:',
                'background_alt' => 'Background image preview',
                'no_background' => 'No background image uploaded yet.',
            ],
            'size' => [
                'title' => 'Logo size',
                'width_label' => 'Width (px)',
                'height_label' => 'Height (px)',
                'hint' => 'Leave blank to auto-adjust proportionally.',
            ],
        ],
    ],

    'audit' => [
        'page' => [
            'title' => 'Configuration Audit',
            'subtitle' => 'Changes grouped by event',
        ],
        'actions' => [
            'back' => 'Back to Settings',
        ],
        'form' => [
            'search_label' => 'Search',
            'search_placeholder' => 'Field/value…',
            'user_label' => 'User (ID)',
            'submit' => 'Filter',
        ],
        'table' => [
            'date' => 'Date',
            'user' => 'User',
            'ip' => 'IP',
            'changes' => 'Changes',
            'field' => 'Field',
            'before' => 'Before',
            'after' => 'After',
            'summary' => 'View :count change(s)',
            'empty' => 'No records',
        ],
    ],

    'guide' => [
        'title' => 'Guide & Best Practices',
        'permissions' => [
            'title' => 'Required permissions',
            'items' => [
                'assign' => 'Assign the app to the <em>tenant</em> (Admin Portal → Developer settings).',
                'roles' => 'Typical roles: <code>Tenant.Read.All</code> or <code>Tenant.ReadWrite.All</code>.',
                'access' => 'Grant access to specific <em>workspaces</em>.',
            ],
        ],
        'recommendations' => [
            'title' => 'Recommendations',
            'items' => [
                'secret' => 'Use a secret dedicated solely to this portal.',
                'rotate' => 'Rotate the secrets periodically.',
                'restrict' => 'Restrict the app to the necessary <em>workspaces</em>.',
            ],
        ],
    ],

    'viewer' => [
        'card_title' => 'On-demand capacity (Viewer)',
        'fields' => [
            'enable' => [
                'label' => 'On-demand start',
                'toggle' => 'Enable automatic attempt',
                'help' => 'If disabled, the viewer will not try to start the capacity.',
                'tooltip' => 'If enabled, the viewer will try to start the capacity automatically when a user opens a report and it is paused.',
                'tooltip_help' => 'Disable it if you prefer to show a paused capacity message without attempting to start automatically.',
            ],
            'message' => [
                'label' => 'Start message',
                'placeholder' => 'Starting the capacity, please wait...',
                'default' => 'Starting the capacity, please wait a few seconds...',
                'help' => 'Text shown to the user while the capacity starts.',
                'tooltip' => 'Displayed in a waiting banner until the capacity is active or the timeout is reached.',
            ],
            'start_timeout' => [
                'label' => 'Start timeout (s)',
                'help' => 'Maximum wait time before showing an error (recommended: 120s).',
                'tooltip' => 'If this time is exceeded without the capacity becoming active, an error is shown and the report is not embedded.',
            ],
            'poll_interval' => [
                'label' => 'Polling interval (s)',
                'help' => 'Frequency at which the viewer checks whether the capacity is active.',
                'tooltip' => 'Shorter intervals react faster but generate more calls; 5s is usually a good balance.',
            ],
            'report_timeout' => [
                'label' => 'Report load timeout (s)',
                'help' => 'Starts after the embed begins; if it expires, a load error is shown.',
                'tooltip' => 'Adjust the waiting threshold so Power BI raises the “loaded” event.',
            ],
        ],
    ],
];
