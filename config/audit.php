<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Entidades con auditoría automática de cambios
    |--------------------------------------------------------------------------
    |
    | Lista de modelos que desean registrar create/update/delete en la tabla
    | entity_audits a través del EntityObserver. Ajusta según los módulos que
    | expongan opciones administrativas en tu instalación.
    |
    */
    'entities' => [
        \App\Models\User::class,
        \App\Models\Group::class,
        \App\Models\Folder::class,
        \App\Models\Workspace::class,
        \App\Models\Application::class,
        \App\Models\ApplicationSection::class,
        \App\Models\Report::class,
        \App\Models\Dataset::class,
        \App\Models\DatasetSchedule::class,
        \App\Models\Capacity::class,
        \App\Models\CapacitySchedule::class,
        \App\Models\Configuration::class,
        \App\Models\ConfigurationThemePreset::class,
        \App\Models\ReportSubscription::class,
        \App\Models\ReportSubscriptionGroup::class,
        \App\Models\ReportSubscriptionUser::class,
        \App\Models\ReportSubscriptionPage::class,
        \App\Models\ReportSubscriptionNotification::class,
        \Spatie\Permission\Models\Role::class,
        \Spatie\Permission\Models\Permission::class,
    ],
];

