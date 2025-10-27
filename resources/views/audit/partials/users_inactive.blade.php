<div class="portal-card portal-audit-card">
    <div class="card-header">
        <div class="d-flex align-items-center gap-3">
            <span class="portal-icon icon-emerald">
                <i class="fas fa-user-clock"></i>
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
                        <th>{{ __('audit.tables.user') }}</th>
                        <th>{{ __('audit.tables.email') }}</th>
                        <th>{{ __('audit.tables.last_access') }}</th>
                        <th>{{ __('audit.tables.created') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $r)
                    <tr>
                        {{-- Usuario con icono de usuario --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                                     style="width:32px;height:32px;font-size:0.9rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="fw-semibold">{{ $r['name'] ?? __('audit.tables.user_fallback', ['id' => $r['user_id'] ?? __('audit.tables.placeholder')]) }}</span>
                            </div>
                        </td>

                        {{-- Email --}}
                        <td class="text-muted small">
                            {{ $r['email'] ?? __('audit.tables.placeholder') }}
                        </td>

                        {{-- Último acceso --}}
                        <td class="text-muted small">
                            {{ $r['last_access'] ?? __('audit.tables.placeholder') }}
                        </td>

                        {{-- Fecha de creación --}}
                        <td class="text-muted small">
                            {{ $r['created_at'] ?? __('audit.tables.placeholder') }}
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
