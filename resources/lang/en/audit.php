<?php

return [
    'title' => 'Usage metrics audit',

    'actions' => [
        'export' => 'Export',
    ],

    'tabs' => [
        'dashboard' => 'Dashboard',
        'users_most' => 'Most active users',
        'users_least' => 'Least active users',
        'users_inactive' => 'Inactive users',
        'reports_most' => 'Most accessed reports',
        'reports_least' => 'Least accessed reports',
        'reports_inactive' => 'Reports without access',
        'user_profile' => 'User profile',
    ],

    'filters' => [
        'from' => 'From',
        'to' => 'To',
        'user' => 'User',
        'select_user' => '— Select a user —',
        'hint' => 'The profile and metrics honour the selected user and date range.',
        'apply' => 'Apply filters',
        'clear' => 'Clear',
    ],

    'lists' => [
        'users_most' => 'Top 100 most active users (range)',
        'users_least' => 'Top 100 least active users (≥1 access in range)',
        'users_inactive' => 'Users without access in range',
        'reports_most' => 'Top 100 most accessed reports',
        'reports_least' => 'Least accessed reports (≥1 access in range)',
        'reports_inactive' => 'Reports without access in range',
    ],

    'tables' => [
        'records' => '{0} No records|{1} 1 record|[2,*] :count records',
        'user' => 'User',
        'email' => 'Email',
        'accesses' => 'Accesses',
        'last_access' => 'Last access',
        'created' => 'Created',
        'report' => 'Report',
        'uuid' => 'UUID',
        'no_data' => 'No data available',
        'placeholder' => '—',
        'user_fallback' => 'User #:id',
        'report_fallback' => 'Report #:id',
    ],

    'dashboard' => [
        'cards' => [
            'access_by_month' => [
                'title' => 'Accesses by month',
                'subtitle' => 'Monthly distribution',
                'aria' => 'Bar chart of accesses by month',
            ],
            'access_by_day' => [
                'title' => 'Daily access trend',
                'subtitle' => 'Latest records',
                'aria' => 'Line chart of daily accesses',
            ],
            'access_by_user' => [
                'title' => 'Accesses by user (Top 10)',
                'subtitle' => 'Most active users',
                'aria' => 'Bar chart of users with most accesses',
            ],
            'access_by_report' => [
                'title' => 'Accesses by report (Top 10)',
                'subtitle' => 'Most consulted dashboards',
                'aria' => 'Bar chart of reports with most accesses',
            ],
        ],
        'heatmap' => [
            'title' => 'Accesses by day and hour',
            'subtitle' => 'Weekly heatmap (1=Mon · 7=Sun)',
            'hour' => 'Hour',
            'total' => 'Total',
            'legend' => [
                'label' => 'Intensity',
                'buckets' => [
                    '0' => '0',
                    '1' => '1-20',
                    '2' => '21-40',
                    '3' => '41-60',
                    '4' => '61-80',
                    '5' => '81-100',
                    '6' => '101-300',
                    '7' => '300+',
                ],
            ],
        ],
        'labels' => [
            'accesses' => 'Accesses',
        ],
    ],

    'user_profile' => [
        'no_user_selected' => 'Select a user to view their activity.',
        'user_without_name' => 'User without name',
        'id_label' => 'ID',
        'kpis' => [
            'accesses' => 'Accesses (render)',
            'unique_reports' => 'Unique reports',
            'active_days' => 'Active days',
            'last_access' => 'Last access',
        ],
       'heatmap_title' => 'Activity by day and hour',
       'heatmap_legend_min' => 'Lower',
       'heatmap_legend_max' => 'Higher',
        'heatmap_cell' => ':day @ :hour — :count events',
        'top_reports' => 'Most used reports',
        'top_reports_empty' => 'No data',
        'recent_activity' => 'Recent activity',
        'recent_activity_empty' => 'No activity',
        'status' => [
            'ok' => 'OK',
            'pending' => '—',
        ],
        'error_prefix' => 'Error',
    ],

    'placeholders' => [
        'user' => 'User #:id',
    ],

    'summary' => [
        'range_from'       => 'Range from',
        'range_to'         => 'Range to',
        'display_timezone' => 'Display timezone',
        'total_accesses'   => 'Total accesses',
        'total_users'      => 'Total users',
        'total_reports'    => 'Total reports',
        'requested_tab'    => 'Requested tab',
    ],

    'export' => [
        'creator'          => 'Audit',
        'title'            => 'Audit of Usage Metrics',
        'filename_prefix'  => 'audit_',
        'default_sheet'    => 'Sheet :number',
        'no_data_sheet'    => 'Data',
        'no_data_message'  => 'No data to export',

        'headers' => [
            'message'     => 'Message',
            'indicator'   => 'Indicator',
            'value'       => 'Value',
            'month'       => 'Month',
            'date'        => 'Date',
            'hour'        => 'Hour',
            'user'        => 'User',
            'report'      => 'Report',
            'errors'      => 'Errors',
            'type'        => 'Type',
            'user_id'     => 'User ID',
            'name'        => 'Name',
            'email'       => 'Email',
            'accesses'    => 'Accesses',
            'last_access' => 'Last access',
            'created'     => 'Created',
            'report_id'   => 'Report ID',
            'bookmark'    => 'Bookmark',
            'duration_ms' => 'Duration (ms)',
            'success'     => 'Success',
            'error'       => 'Error',
            'key'         => 'Key',
            'action'      => 'Action',
            'page'        => 'Page',
        ],

        'sheets' => [
            'summary'             => 'Summary',
            'dashboard_monthly'   => 'Dashboard - Monthly',
            'dashboard_daily'     => 'Dashboard - Daily',
            'dashboard_heatmap'   => 'Dashboard - Heatmap',
            'dashboard_top_users' => 'Dashboard - Top users',
            'dashboard_top_reports' => 'Dashboard - Top reports',
            'dashboard_errors'    => 'Dashboard - Errors',
            'dashboard_alerts'    => 'Dashboard - Alerts',
            'users_most'          => 'Users - Most active',
            'users_least'         => 'Users - Least active',
            'users_inactive'      => 'Users - No accesses',
            'reports_most'        => 'Reports - Most accessed',
            'reports_least'       => 'Reports - Least accessed',
            'reports_inactive'    => 'Reports - No accesses',
            'profile_summary'     => 'Profile - Summary',
            'profile_heatmap'     => 'Profile - Heatmap',
            'profile_top_reports' => 'Profile - Top reports',
            'profile_events'      => 'Profile - Events',
        ],
    ],

    'alerts' => [
        'no_data'       => 'No data',
        'usage_drop'    => 'Usage drop ≥30%: Last 7 days (:current) vs previous (:previous) -> :delta%.',
        'high_error_rate' => 'High error rate in 7 days: :rate% (:errors errors / :views accesses).',
    ],
];
