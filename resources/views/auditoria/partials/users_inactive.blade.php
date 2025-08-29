<div class="card">
    <div class="card-header"><strong>{{ $title }}</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th>Usuario</th>
                <th>Email</th>
                <th>Último acceso</th>
                <th>Creado</th>
            </tr>
            </thead>
            <tbody>
            @forelse($rows as $r)
                <tr>
                    {{-- $r viene de usersWithoutAccess(): user_id, name, email, last_access, created_at --}}
                    <td>{{ $r['name'] ?? ('User #'.($r['user_id'] ?? '')) }}</td>
                    <td>{{ $r['email'] ?? '' }}</td>
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
