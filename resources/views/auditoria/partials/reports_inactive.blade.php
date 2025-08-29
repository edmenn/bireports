<div class="card">
    <div class="card-header"><strong>{{ $title }}</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-hover">
            <thead><tr>
                <th>Reporte</th><th>UUID</th><th>Último acceso</th><th>Creado</th>
            </tr></thead>
            <tbody>
            @forelse($rows as $r)
                <tr>
                    <td>{{ $r['name'] ?? $r['report_uuid'] }}</td>
                    <td><code>{{ $r['report_uuid'] }}</code></td>
                    <td>{{ $r['last_access'] ?? '—' }}</td>
                    <td>{{ $r['created_at'] ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">Sin datos</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
