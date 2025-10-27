<div class="portal-card portal-audit-card">
    <div class="card-header">
        <div class="d-flex align-items-center gap-3">
            <span class="portal-icon icon-amber">
                <i class="fas fa-chart-bar"></i>
            </span>
            <div>
                <h5 class="mb-0">{{ $title }}</h5>
                <small>{{ trans_choice('audit.tables.records', count($rows), ['count' => count($rows)]) }}</small>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table portal-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">{{ __('audit.tables.report') }}</th>
                        <th scope="col">{{ __('audit.tables.uuid') }}</th>
                        <th scope="col">{{ __('audit.tables.last_access') }}</th>
                        <th scope="col">{{ __('audit.tables.created') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $r)
                    <tr>
                        {{-- Reporte con icono --}}
                        <td class="fw-semibold">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                                     style="width:32px;height:32px;font-size:0.9rem;">
                                    <i class="far fa-file-alt"></i>
                                </div>
                                <span>{{ $r['name'] ?? __('audit.tables.report_fallback', ['id' => $r['report_uuid'] ?? __('audit.tables.placeholder')]) }}</span>
                            </div>
                        </td>

                        {{-- UUID --}}
                        <td>
                            <span class="badge bg-light text-dark border">
                                <code>{{ $r['report_uuid'] }}</code>
                            </span>
                        </td>

                        {{-- Último acceso --}}
                        <td>
                            @if(!empty($r['last_access']))
                                <span class="badge bg-success-subtle text-success fw-semibold px-2 py-1">
                                    <i class="far fa-clock me-1"></i>{{ $r['last_access'] }}
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">{{ __('audit.tables.placeholder') }}</span>
                            @endif
                        </td>

                        {{-- Fecha de creación --}}
                        <td class="text-muted small">
                            <i class="far fa-calendar-alt me-1"></i>{{ $r['created_at'] ?? __('audit.tables.placeholder') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted p-4">
                            <i class="fas fa-info-circle me-1"></i> {{ __('audit.alerts.no_data') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
