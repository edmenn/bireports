<?php

return [
    'create' => [
        'title' => 'Create subscription',
    ],

    'actions' => [
        'create' => 'Create Subscription',
        'close' => 'Close',
        'cancel' => 'Cancel',
        'save' => 'Save Subscription',
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'send_now' => 'Send now',
        'insert_snippet' => 'Insert snippet',
        'delete_confirm' => 'Are you sure you want to delete this subscription?',
        'back' => 'Back to subscriptions',
    ],

    'tabs' => [
        'configuration' => 'Configuration',
        'report' => 'Report',
        'groups' => 'Groups',
        'users' => 'Users',
        'email' => 'Email',
        'notifications' => 'Notifications',
    ],

    'fields' => [
        'name' => 'Subscription name',
        'is_enabled' => 'Enabled',
        'enabled_label' => 'Run automatically',
        'format' => 'File format',
        'recurrence' => 'Recurrence',
        'timezone' => 'Timezone',
        'start_date' => 'Start date',
        'end_date' => 'End date',
        'hour' => 'Hour',
        'minute' => 'Minute',
        'cron_expression' => 'Cron expression',
        'max_attachment_size_mb' => 'Maximum attachment size (MB)',
        'report_id' => 'Report',
        'groups' => 'Groups',
        'users' => 'Users',
        'mail_subject' => 'Subject',
        'mail_body' => 'Email body',
        'notify_workspace_admins' => 'Notify workspace administrators',
        'notification_users' => 'Notify users',
        'notification_groups' => 'Notify groups',
        'schedule_window' => 'Schedule window',
        'created_by' => 'Created by',
        'updated_by' => 'Updated by',
    ],

    'recurrence' => [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'cron' => 'Custom (Cron)',
    ],

    'recurrence_labels' => [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'cron' => 'Cron expression',
    ],

    'hints' => [
        'end_date' => 'Leave blank to run indefinitely.',
        'cron_expression' => 'Standard cron syntax with five or six fields.',
        'max_attachment_size_mb' => 'Attachments larger than this limit will be compressed or skipped.',
        'report_selection' => 'Select a report to load its pages and recipients.',
        'pages' => 'Choose at least one page to include in the export.',
        'start_date' => 'The subscription starts running on this date.',
        'groups' => 'All groups are available; members receive the emailed export.',
        'users' => 'All users are available; recipients need only email access.',
        'mail_subject' => 'Placeholders like {{report_name}} and {{date}} are supported.',
        'mail_body' => 'HTML content allowed. Placeholders like {{report_name}} and {{datetime}} are supported.',
        'notify_workspace_admins' => 'Adds workspace administrators to the failure notifications.',
        'notification_users' => 'Additional users to notify when a run fails.',
        'notification_groups' => 'Members of these groups are notified when a run fails.',
    ],

    'placeholders' => [
        'report' => 'Select a report',
        'subject' => 'Scheduled report: {{report_name}} ({{date}})',
        'body' => '<p>Hello,</p><p>The scheduled report is attached.</p><p>Regards,</p>',
        'groups_empty' => 'No groups available',
        'users_empty' => 'No users available',
        'notification_users_empty' => 'No notification users configured',
        'notification_groups_empty' => 'No notification groups configured',
    ],

    'sections' => [
        'overview' => 'Subscription overview',
        'recipients' => 'Recipients',
        'email' => 'Email delivery',
        'notifications' => 'Failure notifications',
        'pages' => 'Report pages',
        'runs' => 'Execution history',
    ],

    'labels' => [
        'deleted_report' => 'Report removed',
        'no_end_date' => 'No end date',
        'time_at' => 'Time: :time',
        'no_limit' => 'No limit',
        'notify_workspace_admins_yes' => 'Workspace administrators will be notified',
        'notify_workspace_admins_no' => 'Workspace administrators are not notified',
        'no_groups_selected' => 'No groups selected',
        'no_users_selected' => 'No users selected',
        'subject_default' => 'Scheduled report',
        'body_default' => 'The scheduled report is attached.',
        'no_notification_users' => 'No notification users configured',
        'no_notification_groups' => 'No notification groups configured',
        'no_pages' => 'No pages selected',
        'page' => 'Page',
    ],

    'status' => [
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
    ],

    'statuses' => [
        'pending' => 'Pending',
        'running' => 'Running',
        'success' => 'Success',
        'failed' => 'Failed',
        'skipped' => 'Skipped',
    ],

    'runs' => [
        'scheduled_time' => 'Scheduled time',
        'status' => 'Status',
        'empty' => 'No executions recorded yet.',
    ],

    'index' => [
        'title' => 'Report subscriptions',
        'subtitle' => 'Manage scheduled report deliveries',
        'empty' => [
            'title' => 'No subscriptions yet',
            'hint' => 'Start by creating a subscription for a report.',
        ],
    ],

    'table' => [
        'name' => 'Name',
        'report' => 'Report',
        'format' => 'Format',
        'recurrence' => 'Recurrence',
        'enabled' => 'Enabled',
        'updated_at' => 'Updated',
        'actions' => 'Actions',
    ],

    'show' => [
        'title' => 'Subscription: :name',
        'subtitle' => 'Report: :report',
        'overview' => 'Subscription overview',
    ],

    'validation' => [
        'invalid_cron' => 'Enter a valid cron expression (five or six fields).',
        'hour_required' => 'Hour and minute are required for this recurrence type.',
        'recipient_required' => 'Select at least one recipient (group or user).',
        'report_missing' => 'Select a report for this subscription.',
        'report_forbidden' => 'You do not have access to the selected report.',
        'group_forbidden' => 'One or more selected groups cannot access the report.',
        'user_forbidden' => 'One or more selected users cannot access the report.',
        'notification_user_forbidden' => 'One or more notification users cannot access the report.',
        'notification_group_forbidden' => 'One or more notification groups cannot access the report.',
        'errors_title' => 'Please review the errors:',
    ],

    'errors' => [
        'no_recipients' => 'The subscription cannot run because there are no recipients.',
        'attachment_too_large' => 'The exported file exceeds the allowed size limit.',
    ],

    'feedback' => [
        'metadata_error' => 'We could not load the report metadata. Try again later.',
        'save_error' => 'We could not save the subscription. Review the form and try again.',
        'save_success' => 'Subscription saved successfully.',
        'loading' => 'Preparing metadata.',
    ],

    'mail' => [
        'subject' => [
            'default' => 'Scheduled report: {{report_name}} ({{date}})',
        ],
        'body_default' => 'Hello,<br><br>Please find the scheduled report attached.<br><br>Regards.',
    ],

    'email_editor' => [
        'html' => 'HTML Source',
        'design' => 'Visual Editor',
        'preview' => 'Preview',
        'preview_hint' => 'Preview renders the HTML body as the recipient will see it.',
        'preview_empty' => 'No content yet. Add HTML on the source tab to generate a preview.',
        'design_toolbar' => 'Formatting tools',
        'font_size' => 'Font size',
    ],

    'notifications' => [
        'failure' => [
            'subject' => 'Report subscription failed: :name',
            'intro' => 'The subscription ":name" failed during the last execution.',
            'action' => 'View subscription',
            'footer' => 'You receive this email because you are configured as a notification recipient.',
        ],
    ],

    'snippets' => [
        'report_name' => 'Report name',
        'date' => 'Scheduled date',
        'datetime' => 'Scheduled date & time',
        'subscription_name' => 'Subscription name',
        'report_link' => 'Link to the report in the portal',
    ],

    'flash' => [
        'created' => 'Subscription created successfully.',
        'updated' => 'Subscription updated successfully.',
        'deleted' => 'Subscription deleted successfully.',
        'dispatched_now' => 'Subscription queued to send immediately.',
    ],
];
