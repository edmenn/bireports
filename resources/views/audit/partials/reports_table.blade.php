<div class="card shadow-sm border-0 mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-primary">
            <i class="fas fa-file-alt me-2"></i>{{ $title }}
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('audit.tables.report') }}</th>
                        <th>{{ __('audit.tables.uuid') }}</th>
                        <th>{{ __('audit.tables.accesses') }}</th>
                        <th>{{ __('audit.tables.last_access') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $r)
                    <tr>
                        <td class="fw-semibold">
                            {{ $r['name'] ?? __('audit.tables.report_fallback', ['id' => $r['report_uuid'] ?? __('audit.tables.placeholder')]) }}
                        </td>
                        <td>
                            <code class="text-muted">{{ $r['report_uuid'] }}</code>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary fw-semibold">
                                {{ $r['total'] ?? 0 }}
                            </span>
                        </td>
                        <td class="text-muted small">
                            {{ $r['last_access'] ?? __('audit.tables.placeholder') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted p-3">
                            <i class="far fa-folder-open me-1"></i>{{ __('audit.alerts.no_data') }}
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
