<?php

return [
    'title' => 'Auditoria de métricas de uso',

    'actions' => [
        'export' => 'Exportar',
    ],

    'tabs' => [
        'dashboard' => 'Painel',
        'users_most' => 'Usuários mais ativos',
        'users_least' => 'Usuários menos ativos',
        'users_inactive' => 'Usuários inativos',
        'reports_most' => 'Relatórios mais acessados',
        'reports_least' => 'Relatórios menos acessados',
        'reports_inactive' => 'Relatórios não acessados',
        'user_profile' => 'Perfil do usuário',
    ],

    'filters' => [
        'from' => 'De',
        'to' => 'Até',
        'user' => 'Usuário',
        'select_user' => '— Selecione um usuário —',
        'hint' => 'O perfil e suas métricas consideram o usuário e o intervalo selecionados.',
        'apply' => 'Aplicar filtros',
        'clear' => 'Limpar',
    ],

    'lists' => [
        'users_most' => 'Top 100 usuários mais ativos (intervalo)',
        'users_least' => 'Top 100 usuários menos ativos (≥1 acesso no intervalo)',
        'users_inactive' => 'Usuários sem acessos no intervalo',
        'reports_most' => 'Top 100 relatórios mais acessados',
        'reports_least' => 'Relatórios menos acessados (≥1 acesso no intervalo)',
        'reports_inactive' => 'Relatórios não acessados no intervalo',
    ],

    'tables' => [
        'records' => '{0} Sem registros|{1} 1 registro|[2,*] :count registros',
        'user' => 'Usuário',
        'email' => 'Email',
        'accesses' => 'Acessos',
        'last_access' => 'Último acesso',
        'created' => 'Criado',
        'report' => 'Relatório',
        'uuid' => 'UUID',
        'no_data' => 'Sem dados disponíveis',
        'placeholder' => '—',
        'user_fallback' => 'Usuário #:id',
        'report_fallback' => 'Relatório #:id',
    ],

    'dashboard' => [
        'cards' => [
            'access_by_month' => [
                'title' => 'Acessos por mês',
                'subtitle' => 'Distribuição mensal',
                'aria' => 'Gráfico de barras de acessos por mês',
            ],
            'access_by_day' => [
                'title' => 'Tendência de acessos diários',
                'subtitle' => 'Registros recentes',
                'aria' => 'Gráfico de linhas de acessos diários',
            ],
            'access_by_user' => [
                'title' => 'Acessos por usuário (Top 10)',
                'subtitle' => 'Usuários mais ativos',
                'aria' => 'Gráfico de barras de usuários com mais acessos',
            ],
            'access_by_report' => [
                'title' => 'Acessos por relatório (Top 10)',
                'subtitle' => 'Dashboards mais consultados',
                'aria' => 'Gráfico de barras de relatórios com mais acessos',
            ],
        ],
        'heatmap' => [
            'title' => 'Acessos por dia e horário',
            'subtitle' => 'Mapa de calor semanal (1=seg · 7=dom)',
            'hour' => 'Hora',
            'total' => 'Total',
            'legend' => [
                'label' => 'Intensidade',
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
            'accesses' => 'Acessos',
        ],
    ],

    'user_profile' => [
        'no_user_selected' => 'Selecione um usuário para ver a atividade.',
        'user_without_name' => 'Usuário sem nome',
        'id_label' => 'ID',
        'kpis' => [
            'accesses' => 'Acessos (render)',
            'unique_reports' => 'Relatórios únicos',
            'active_days' => 'Dias ativos',
            'last_access' => 'Último acesso',
        ],
       'heatmap_title' => 'Atividade por dia e horário',
       'heatmap_legend_min' => 'Menor',
       'heatmap_legend_max' => 'Maior',
        'heatmap_cell' => ':day @ :hour — :count eventos',
        'top_reports' => 'Relatórios mais usados',
        'top_reports_empty' => 'Sem dados',
        'recent_activity' => 'Atividade recente',
        'recent_activity_empty' => 'Sem atividade',
        'status' => [
            'ok' => 'OK',
            'pending' => '—',
        ],
        'error_prefix' => 'Erro',
    ],

    'alerts' => [
        'no_data' => 'Sem dados',
    ],
];
