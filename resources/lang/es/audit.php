<?php

return [
    'title' => 'Auditoría de métricas de uso',

    'actions' => [
        'export' => 'Exportar',
    ],

    'tabs' => [
        'dashboard' => 'Dashboard',
        'users_most' => 'Usuarios más activos',
        'users_least' => 'Usuarios menos activos',
        'users_inactive' => 'Usuarios no activos',
        'reports_most' => 'Reportes más accedidos',
        'reports_least' => 'Reportes menos accedidos',
        'reports_inactive' => 'Reportes no accedidos',
        'user_profile' => 'Perfil de usuario',
    ],

    'filters' => [
        'from' => 'Desde',
        'to' => 'Hasta',
        'user' => 'Usuario',
        'select_user' => '— Selecciona un usuario —',
        'hint' => 'El perfil y sus métricas se filtran por el usuario y rango seleccionados.',
        'apply' => 'Aplicar filtros',
        'clear' => 'Limpiar',
    ],

    'lists' => [
        'users_most' => 'Top 100 usuarios más activos (rango)',
        'users_least' => 'Top 100 usuarios menos activos (≥1 acceso en el rango)',
        'users_inactive' => 'Usuarios sin accesos en el rango',
        'reports_most' => 'Top 100 reportes más accedidos',
        'reports_least' => 'Reportes menos accedidos (≥1 acceso en el rango)',
        'reports_inactive' => 'Reportes no accedidos en el rango',
    ],

    'charts' => [
        'monthly_access' => 'Accesos mensuales',
    ],

    'tables' => [
        'records' => '{0} Sin registros|{1} 1 registro|[2,*] :count registros',
        'user' => 'Usuario',
        'email' => 'Correo',
        'accesses' => 'Accesos',
        'last_access' => 'Último acceso',
        'created' => 'Creado',
        'report' => 'Reporte',
        'uuid' => 'UUID',
        'no_data' => 'Sin datos disponibles',
        'placeholder' => '—',
        'user_fallback' => 'Usuario #:id',
        'report_fallback' => 'Reporte #:id',
    ],

    'dashboard' => [
        'cards' => [
            'access_by_month' => [
                'title' => 'Accesos por mes',
                'subtitle' => 'Distribución mensual',
                'aria' => 'Gráfico de barras de accesos por mes',
            ],
            'access_by_day' => [
                'title' => 'Tendencia de accesos diarios',
                'subtitle' => 'Últimos registros',
                'aria' => 'Gráfico de líneas de accesos diarios',
            ],
            'access_by_user' => [
                'title' => 'Accesos por usuario (Top 10)',
                'subtitle' => 'Usuarios más activos',
                'aria' => 'Gráfico de barras de usuarios con más accesos',
            ],
            'access_by_report' => [
                'title' => 'Accesos por reporte (Top 10)',
                'subtitle' => 'Dashboards más consultados',
                'aria' => 'Gráfico de barras de reportes con más accesos',
            ],
        ],
        'heatmap' => [
            'title' => 'Accesos por día y horario',
            'subtitle' => 'Mapa de calor semanal (1=lun · 7=dom)',
            'hour' => 'Hora',
            'total' => 'Total',
            'legend' => [
                'label' => 'Intensidad',
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
            'accesses' => 'Accesos',
        ],
    ],

    'user_profile' => [
        'no_user_selected' => 'Selecciona un usuario para ver su actividad.',
        'user_without_name' => 'Usuario sin nombre',
        'id_label' => 'ID',
        'kpis' => [
            'accesses' => 'Accesos (render)',
            'unique_reports' => 'Reportes únicos',
            'active_days' => 'Días activos',
            'last_access' => 'Último acceso',
        ],
        'heatmap_title' => 'Actividad por día y hora',
        'heatmap_legend_min' => 'Menor',
        'heatmap_legend_max' => 'Mayor',
        'heatmap_cell' => ':day @ :hour — :count eventos',
        'top_reports' => 'Reportes más usados',
        'top_reports_empty' => 'Sin datos',
        'recent_activity' => 'Actividad reciente',
        'recent_activity_empty' => 'Sin actividad',
        'status' => [
            'ok' => 'OK',
            'pending' => '—',
        ],
        'error_prefix' => 'Error',
    ],

    'placeholders' => [
        'user' => 'Usuario #:id',
    ],

    'summary' => [
        'range_from'       => 'Rango desde',
        'range_to'         => 'Rango hasta',
        'display_timezone' => 'Zona horaria (visualización)',
        'total_accesses'   => 'Total accesos',
        'total_users'      => 'Total usuarios',
        'total_reports'    => 'Total reportes',
        'requested_tab'    => 'Pestaña solicitada',
    ],

    'export' => [
        'creator'          => 'Auditoría',
        'title'            => 'Auditoría de Métricas de Uso',
        'filename_prefix'  => 'auditoria_',
        'default_sheet'    => 'Hoja :number',
        'no_data_sheet'    => 'Datos',
        'no_data_message'  => 'No hay datos para exportar',

        'headers' => [
            'message'     => 'Mensaje',
            'indicator'   => 'Indicador',
            'value'       => 'Valor',
            'month'       => 'Mes',
            'date'        => 'Fecha',
            'hour'        => 'Hora',
            'user'        => 'Usuario',
            'report'      => 'Reporte',
            'errors'      => 'Errores',
            'type'        => 'Tipo',
            'user_id'     => 'ID usuario',
            'name'        => 'Nombre',
            'email'       => 'Email',
            'accesses'    => 'Accesos',
            'last_access' => 'Último acceso',
            'created'     => 'Creado',
            'report_id'   => 'ID reporte',
            'bookmark'    => 'Marcador',
            'duration_ms' => 'Duración (ms)',
            'success'     => 'Éxito',
            'error'       => 'Error',
            'key'         => 'Clave',
            'action'      => 'Acción',
            'page'        => 'Página',
        ],

        'sheets' => [
            'summary'             => 'Resumen',
            'dashboard_monthly'   => 'Dashboard - Mensual',
            'dashboard_daily'     => 'Dashboard - Diario',
            'dashboard_heatmap'   => 'Dashboard - Heatmap',
            'dashboard_top_users' => 'Dashboard - Top usuarios',
            'dashboard_top_reports' => 'Dashboard - Top reportes',
            'dashboard_errors'    => 'Dashboard - Errores',
            'dashboard_alerts'    => 'Dashboard - Alertas',
            'users_most'          => 'Usuarios - Más activos',
            'users_least'         => 'Usuarios - Menos activos',
            'users_inactive'      => 'Usuarios - Sin accesos',
            'reports_most'        => 'Reportes - Más accesados',
            'reports_least'       => 'Reportes - Menos accesados',
            'reports_inactive'    => 'Reportes - Sin accesos',
            'profile_summary'     => 'Perfil - Resumen',
            'profile_heatmap'     => 'Perfil - Heatmap',
            'profile_top_reports' => 'Perfil - Top reportes',
            'profile_events'      => 'Perfil - Eventos',
        ],
    ],

    'alerts' => [
        'no_data'       => 'Sin datos',
        'usage_drop'    => 'Caída de uso ≥30%: Últimos 7 días (:current) vs previos (:previous) → :delta%.',
        'high_error_rate' => 'Tasa de errores alta en 7 días: :rate% (:errors errores / :views accesos).',
    ],
    'admin' => [
        'confirm_exact' => 'Debes escribir exactamente RESET para confirmar.',
        'reset_success' => 'Se borraron todos los registros de auditoria y se reinicio el contador de IDs.',
        'reset_error'   => 'No se pudo resetear la auditoria: :message',
    ],

    'api' => [
        'store_failed' => 'No se pudo registrar el evento de auditoria.',
    ],
];



