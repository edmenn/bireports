<?php

return [
    'index' => [
        'title' => 'Applications',
        'subtitle' => 'Organise Power BI reports by application',
        'empty' => 'No applications created yet.',
    ],

    'create' => [
        'title' => 'New Application',
    ],

    'edit' => [
        'title' => 'Edit :name',
        'subtitle' => 'Manage access, sections and assigned reports',
    ],

    'flash' => [
        'created' => 'Application created successfully.',
        'updated' => 'Application updated successfully.',
        'deleted' => 'Application deleted successfully.',
    ],

    'sections' => [
        'flash' => [
            'created' => 'Section added to the application.',
            'updated' => 'Section updated.',
            'deleted' => 'Section removed.',
        ],
        'none' => 'Unassigned',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    'actions' => [
        'create' => 'Create application',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'update' => 'Save changes',
        'delete' => 'Delete',
        'edit' => 'Edit',
        'back' => 'Back',
        'open' => 'Open',
        'exit_application' => 'Exit application',
        'go_home' => 'Go to Home',
        'add_section' => 'Add section',
        'close_alert' => 'Close alert',
    ],

    'confirm' => [
        'delete' => 'Are you sure you want to delete this application?',
    ],

    'fields' => [
        'name' => 'Name',
        'slug' => 'Slug',
        'description' => 'Description',
        'display_order' => 'Display order',
        'is_active' => 'Application enabled',
        'status' => 'Status',
        'sections' => 'Sections',
        'reports' => 'Reports',
        'users' => 'Users',
        'groups' => 'Groups',
        'updated_at' => 'Last update',
        'actions' => 'Actions',
        'reports_count' => 'Reports',
        'sections_count' => 'Sections',
        'section_name' => 'Section name',
        'section_slug' => 'Section slug',
        'section_delete' => 'Remove',
        'new_sections' => 'New sections',
        'report_name' => 'Report',
    ],

    'placeholders' => [
        'slug' => 'leave blank to auto-generate',
        'section_name' => 'Section name',
    ],

    'cards' => [
        'details_title' => 'Application details',
        'assignments_title' => 'Access assignments',
        'reports_title' => 'Reports in the application',
        'sections_title' => 'Sections within the application',
        'report_sections_title' => 'Report sections',
    ],

    'hints' => [
        'users' => 'Users added here will see this application when it is active.',
        'groups' => 'Groups inherit access to the application.',
        'reports' => 'Select the reports that belong to this application.',
        'sections_existing' => 'Rename or mark sections to remove them. The reports inside will become “unassigned”.',
        'sections_order' => 'Sections and reports are ordered alphabetically.',
        'report_sections' => 'Organise existing reports into sections. Leave “Unassigned” to keep them outside sections.',
    ],

    'tooltips' => [
        'name' => 'Name displayed in admin and user views.',
        'slug' => 'URL-friendly identifier; leave it blank to generate it automatically.',
        'description' => 'Optional description that is shown in the portal header.',
        'display_order' => 'Reserved for future manual sorting. It has no effect today.',
        'is_active' => 'Only active applications appear in the user portal.',
        'users' => 'People who will access the application directly.',
        'groups' => 'Entire groups that inherit access automatically.',
        'reports' => 'Reports included in this application.',
        'sections' => 'Create sections to organise reports in the portal sidebar.',
        'report_sections' => 'Choose the section where each report will appear.',
        'section_name' => 'Update the section name if needed.',
        'section_delete' => 'Remove the section. Reports move to “Unassigned”.',
        'add_section' => 'Add another input to define a new section.',
    ],

    'portal' => [
        'title' => 'My applications',
        'subtitle' => 'Launch an application to browse its reports',
        'empty_title' => 'No applications assigned',
        'empty_description' => 'Your assigned applications will appear here when administrators publish them.',
        'reports_count' => 'Reports',
        'sections_count' => 'Sections',
    ],

    'runtime' => [
        'subtitle' => 'Browse the reports linked to this application',
        'no_reports' => 'This application does not have reports yet.',
        'sidebar_title' => 'Available reports',
        'other_reports' => 'Other reports',
    ],
];
