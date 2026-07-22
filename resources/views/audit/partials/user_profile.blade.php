{{-- resources/views/audit/partials/user_profile.blade.php --}}
@php
    $u     = $userProfile['user']    ?? [];
    $kpis  = $userProfile['kpis']    ?? [];
    $hmap  = $userProfile['heatmap'] ?? [];
    $tops  = $userProfile['topReports'] ?? [];
    $placeholder = __('audit.tables.placeholder');
    $uid   = (string)($u['id'] ?? $placeholder);
    $uname = trim((string)($u['name'] ?? ''));
    $mail  = trim((string)($u['email'] ?? ''));
    $initials = '';
    if ($uname !== '') {
        $parts = preg_split('/\s+/', $uname, -1, PREG_SPLIT_NO_EMPTY);
        $initials = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
    } elseif ($mail !== '') {
        $initials = mb_strtoupper(mb_substr($mail, 0, 1));
    } else {
        $initials = 'U';
    }

    $totalAccesos   = (int)($kpis['totalAccesos']   ?? 0);
    $reportesUnicos = (int)($kpis['reportesUnicos'] ?? 0);
    $diasActivos    = (int)($kpis['diasActivos']    ?? 0);
    $ultimoAcceso   = (string)($kpis['ultimoAcceso'] ?? $placeholder);

    $maxTop = 0;
    foreach ($tops as $tr) { $maxTop = max($maxTop, (int)($tr['total'] ?? 0)); }
    $maxTop = $maxTop > 0 ? $maxTop : 1;
@endphp

<div class="portal-card portal-profile-card overflow-hidden mb-3">
    {{-- Header without buttons, high contrast --}}
    <div class="card-header up-header p-3 p-md-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="up-avatar rounded-circle d-flex align-items-center justify-content-center">
                    {{ $initials }}
                </div>
                <div>
                    <div class="h5 fw-bold mb-1">{{ $uname !== '' ? $uname : __('audit.user_profile.user_without_name') }}</div>
                    <div class="small opacity-90 d-flex flex-wrap align-items-center gap-2 text-muted">
                        <span><i class="far fa-id-badge me-1"></i>{{ __('audit.user_profile.id_label') }}: <code class="text-muted">{{ $uid }}</code></span>
                        @if($mail !== '')
                            <span><i class="far fa-envelope ms-1 me-1"></i>{{ $mail }}</span>
                        @endif
                    </div>
                </div>
            </div>
            {{-- (sin acciones) --}}
        </div>
    </div>

    <div class="card-body p-3 p-md-4">
        @if(empty($userProfile))
            <div class="alert alert-light border d-flex align-items-center mb-0">
                <i class="far fa-user-circle me-2"></i>
                <div class="text-muted">{{ __('audit.user_profile.no_user_selected') }}</div>
            </div>
        @else
            {{-- KPIs --}}
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="up-kpi up-kpi-blue">
                        <div class="up-kpi-icon"><i class="fas fa-chart-bar"></i></div>
                        <div class="up-kpi-value">{{ number_format($totalAccesos, 0, ',', '.') }}</div>
                        <div class="up-kpi-label">{{ __('audit.user_profile.kpis.accesses') }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="up-kpi up-kpi-violet">
                        <div class="up-kpi-icon"><i class="fas fa-layer-group"></i></div>
                        <div class="up-kpi-value">{{ number_format($reportesUnicos, 0, ',', '.') }}</div>
                        <div class="up-kpi-label">{{ __('audit.user_profile.kpis.unique_reports') }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="up-kpi up-kpi-green">
                        <div class="up-kpi-icon"><i class="far fa-calendar-check"></i></div>
                        <div class="up-kpi-value">{{ number_format($diasActivos, 0, ',', '.') }}</div>
                        <div class="up-kpi-label">{{ __('audit.user_profile.kpis.active_days') }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="up-kpi up-kpi-orange">
                        <div class="up-kpi-icon"><i class="far fa-clock"></i></div>
                        <div class="up-kpi-value small mb-1">{{ $ultimoAcceso }}</div>
                        <div class="up-kpi-label">{{ __('audit.user_profile.kpis.last_access') }}</div>
                    </div>
                </div>
            </div>

            {{-- Heatmap --}}
            <div class="mt-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="fw-600">{{ __('audit.user_profile.heatmap_title') }}</div>
                    <div class="small text-muted">
                        <span>{{ __('audit.user_profile.heatmap_legend_min') }}</span>
                        <span class="up-legend up-hm-0"></span>
                        <span class="up-legend up-hm-2"></span>
                        <span class="up-legend up-hm-4"></span>
                        <span class="up-legend up-hm-6"></span>
                        <span class="up-legend up-hm-7"></span>
                        <span>{{ __('audit.user_profile.heatmap_legend_max') }}</span>
                    </div>
                </div>

                <div class="up-heat card border-0 shadow-xs mt-2">
                    <div class="table-responsive up-hm-wrapper">
                        <table class="table table-borderless align-middle mb-0 up-hm-table">
                            <thead>
                                <tr>
                                    <th class="sticky-col text-muted small text-center">{{ __('audit.dashboard.heatmap.hour') }}</th>
                                    @foreach($dowLabels as $lbl)
                                        <th class="text-muted small text-center">{{ $lbl }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($hmap as $row)
                                <tr>
                                    <th class="sticky-col text-muted small">{{ $row['hour'] }}</th>
                                @for($d=1; $d<=7; $d++)
                                    @php
                                        $v = (int)($row[$d] ?? 0);
                                        $lvl = ($v===0?0:($v<=20?1:($v<=40?2:($v<=60?3:($v<=80?4:($v<=100?5:($v<=300?6:7)))))));
                                        $displayValue = number_format($v, 0, ',', '.');
                                    @endphp
                                    <td class="text-center">
                                        <div class="up-hm-cell up-hm-{{ $lvl }}" title="{{ __('audit.user_profile.heatmap_cell', ['day' => $dowLabels[$d-1] ?? '', 'hour' => $row['hour'], 'count' => $v]) }}">
                                            <span class="up-hm-value">{{ $displayValue }}</span>
                                        </div>
                                    </td>
                                @endfor
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted p-3">{{ __('audit.user_profile.top_reports_empty') }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Top reports --}}
            <div class="mt-4">
                <div class="fw-600 mb-2">{{ __('audit.user_profile.top_reports') }}</div>
                @if(empty($tops))
                    <div class="text-muted">{{ __('audit.user_profile.top_reports_empty') }}</div>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($tops as $r)
                            @php
                                $label = (string)($r['label'] ?? $placeholder);
                                $tot   = (int)($r['total'] ?? 0);
                                $pct   = max(6, min(100, round(($tot / $maxTop) * 100)));
                            @endphp
                            <div class="up-top-item">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="text-truncate" title="{{ $label }}">
                                        <i class="far fa-file-alt text-muted me-1"></i>{{ $label }}
                                    </div>
                                    <span class="badge bg-light text-dark border">{{ $tot }}</span>
                                </div>
                                <div class="progress up-progress mt-2">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Activity feed --}}
            <div class="mt-4">
                <div class="fw-600 mb-2">{{ __('audit.user_profile.recent_activity') }}</div>
                @if(empty($userEvents))
                    <div class="text-muted">{{ __('audit.user_profile.recent_activity_empty') }}</div>
                @else
                    <ul class="up-feed list-unstyled mb-0">
                        @foreach($userEvents as $e)
                            @php
                                $ok = (isset($e['success']) && $e['success']);
                                $action = (string)($e['action'] ?? '');
                                $report = $e['report_name'] ?? ($e['report_uuid'] ?? $placeholder);
                                $page   = $e['page_name'] ?? $placeholder;
                                $errMsg = (string)($e['error'] ?? '');
                                $when   = (string)($e['occurred_at'] ?? '');
                            @endphp
                            <li class="up-feed-item">
                                <div class="up-feed-dot {{ $ok ? 'ok' : 'warn' }}"></div>
                                <div class="up-feed-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="small text-muted"><i class="far fa-clock me-1"></i>{{ $when }}</div>
                                        <span class="badge {{ $ok ? 'bg-success' : 'bg-secondary' }}">{{ $ok ? __('audit.user_profile.status.ok') : __('audit.user_profile.status.pending') }}</span>
                                    </div>
                                    <div class="mt-1">
                                        <code class="me-2">{{ $action }}</code>
                                        <span class="text-truncate d-inline-block" style="max-width: 50ch;" title="{{ $report }}">{{ $report }}</span>
                                        @if($page && $page !== $placeholder)
                                            <span class="text-muted">· {{ $page }}</span>
                                        @endif
                                    </div>
                                    @if($errMsg !== '')
                                        <div class="small text-danger mt-1">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $errMsg }}
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>
</div>

@push('css')
<style>
    .fw-600{font-weight:600;}

    /* Header */
    .up-header{
        background:var(--app-card-alt-bg);
        color:var(--app-card-text);
        border-bottom:1px solid var(--app-card-alt-bg);
    }

    .up-avatar{
        width:56px;height:56px;
        font-weight:800;font-size:1.05rem;
        color:var(--app-icon-text);background:var(--app-icon-bg);
        border:1px solid var(--app-card-alt-bg);
    }

    /* KPIs */
    .up-kpi{
        position:relative;border-radius:14px;padding:.9rem 1rem;background:var(--app-card-bg);overflow:hidden;
        border:1px solid var(--app-card-alt-bg);box-shadow:0 .35rem 1rem rgba(0,0,0,.06);
    }
    .up-kpi-icon{ position:absolute;right:.75rem;top:.6rem;opacity:.15;font-size:1.6rem; }
    .up-kpi-value{ font-size:1.35rem;font-weight:800;line-height:1; }
    .up-kpi-label{ font-size:.85rem;color:var(--app-card-text);opacity:.72;margin-top:.25rem; }
    .up-kpi-blue,
    .up-kpi-violet,
    .up-kpi-green,
    .up-kpi-orange { background:var(--app-card-bg); }

    /* Heatmap */
    .up-heat{ border-radius:14px; padding:.75rem; background:var(--app-card-bg); }
    .up-hm-wrapper{
        overflow-x:auto;
    }
    .up-hm-table{
        table-layout:fixed;
        width:100%;
        min-width:520px;
    }
    .up-hm-table th{ font-weight:600; text-align:center; }
    .up-hm-table thead th{ font-size:.8rem;color:#6b7280; white-space:nowrap; text-align:center; }
    .up-hm-table tbody th{ text-align:center; font-weight:600; }
    .up-hm-table .sticky-col{
        position:sticky;
        left:0;
        background:var(--app-card-bg);
        z-index:2;
        width:12.5%;
    }
    .up-hm-table thead th,
    .up-hm-table tbody th,
    .up-hm-table td{
        width:12.5%;
    }
    .up-hm-cell{
        min-width:2.6rem;
        height:2.1rem;
        border-radius:6px;
        margin:auto;
        border:1px solid var(--app-card-alt-bg);
        display:flex;
        align-items:center;
        justify-content:center;
        padding:.25rem;
    }
    .up-hm-value{
        display:block;
        text-align:center;
        font-size:.78rem;
        font-weight:600;
        line-height:1;
        color:var(--app-card-text);
        white-space:nowrap;
    }
    .up-hm-0 .up-hm-value,
    .up-hm-1 .up-hm-value,
    .up-hm-2 .up-hm-value{ color:var(--app-card-text); }
    .up-hm-3 .up-hm-value,
    .up-hm-4 .up-hm-value{ color:var(--app-card-text); }
    .up-hm-5 .up-hm-value,
    .up-hm-6 .up-hm-value,
    .up-hm-7 .up-hm-value{ color:var(--app-btn-text); }
    /* Paleta */
    .up-hm-0{ background:var(--app-card-alt-bg); }
    .up-hm-1{ background:color-mix(in srgb, var(--app-btn-bg) 12%, var(--app-card-bg)); }
    .up-hm-2{ background:color-mix(in srgb, var(--app-btn-bg) 24%, var(--app-card-bg)); }
    .up-hm-3{ background:color-mix(in srgb, var(--app-btn-bg) 36%, var(--app-card-bg)); }
    .up-hm-4{ background:color-mix(in srgb, var(--app-btn-bg) 48%, var(--app-card-bg)); }
    .up-hm-5{ background:color-mix(in srgb, var(--app-btn-bg) 60%, var(--app-card-bg)); }
    .up-hm-6{ background:color-mix(in srgb, var(--app-btn-bg) 78%, var(--app-card-bg)); }
    .up-hm-7{ background:var(--app-btn-bg); }

    .up-legend{
        display:inline-block; width:18px; height:10px; border-radius:3px; margin:0 .1rem; vertical-align:middle;
        border:1px solid var(--app-card-alt-bg);
    }

    /* Top reportes */
    .up-top-item{ border:1px solid var(--app-card-alt-bg); border-radius:12px; padding:.6rem .75rem; background:var(--app-card-bg); }
    .up-progress{ height:6px; background:var(--app-card-alt-bg); }
    .up-progress .progress-bar{ background:var(--app-btn-bg); }

    /* Feed */
    .up-feed{ position:relative; padding-left:1.25rem; }
    .up-feed:before{
        content:""; position:absolute; left:.6rem; top:.4rem; bottom:.4rem; width:2px; background:var(--app-card-alt-bg);
    }
    .up-feed-item{ position:relative; margin-bottom:1rem; }
    .up-feed-dot{
        position:absolute; left:-.05rem; top:.35rem; width:10px; height:10px; border-radius:50%;
        border:2px solid var(--app-card-bg); box-shadow:0 0 0 2px var(--app-card-alt-bg); background:var(--app-icon-bg);
    }
    .up-feed-dot.ok{ background:#10b981; }
    .up-feed-dot.warn{ background:#6b7280; }
    .up-feed-body{ padding-left:.75rem; }

    .shadow-xs{ box-shadow:0 .15rem .6rem rgba(0,0,0,.05)!important; }
    .opacity-90{ opacity:.9; }
</style>
@endpush




