<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong class="text-primary">{{ $title }}</strong>
        <span class="badge bg-light text-dark border">{{ count($rows) }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th class="text-center">Accesos</th>
                        <th>Último acceso</th>
                        <th>Creado</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $r)
                    <tr>
                        {{-- Usuario con icono en círculo --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-light text-secondary d-flex align-items-center justify-content-center"
                                     style="width:32px;height:32px;font-size:0.9rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <span class="fw-semibold">{{ $r['name'] ?? ('User #'.$r['user_id']) }}</span>
                            </div>
                        </td>

                        {{-- Email --}}
                        <td>
                            @if(!empty($r['email']))
                                <a href="mailto:{{ $r['email'] }}" class="text-decoration-none">
                                    <i class="far fa-envelope text-muted me-1"></i>{{ $r['email'] }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        {{-- Accesos --}}
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $r['total'] ?? 0 }}</span>
                        </td>

                        {{-- Último acceso --}}
                        <td class="text-muted small">
                            <i class="far fa-clock me-1"></i>{{ $r['last_access'] ?? '—' }}
                        </td>

                        {{-- Fecha creación --}}
                        <td class="text-muted small">
                            <i class="far fa-calendar-alt me-1"></i>{{ $r['created_at'] ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4">
                            <i class="fas fa-inbox me-2"></i> Sin datos disponibles
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
