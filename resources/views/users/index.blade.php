@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="m-0">Usuarios</h1>
        <div class="btn-group">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo usuario
            </a>
            <button type="button" class="btn btn-outline-primary"
                    data-toggle="modal" data-target="#modalSyncEntra"
                    data-bs-toggle="modal" data-bs-target="#modalSyncEntra">
                <i class="fab fa-microsoft"></i> Importar Entra
            </button>
        </div>
    </div>
@stop

@section('content')
    @if (session('success')) <x-adminlte-alert theme="success" title="OK" dismissable class="mb-2">{{ session('success') }}</x-adminlte-alert> @endif
    @if (session('warning')) <x-adminlte-alert theme="warning" title="Atención" dismissable class="mb-2">{{ session('warning') }}</x-adminlte-alert> @endif
    @if (session('error'))   <x-adminlte-alert theme="danger"  title="Error"    dismissable class="mb-2">{{ session('error') }}</x-adminlte-alert>   @endif

    @php
        $roles = $roles ?? \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name','name');
        $selectedRole = request('role'); // no lo usamos para auto-submit, solo para mantener el valor si venís del servidor
        $q = $q ?? request('q');
    @endphp

    <div class="card">
        <div class="card-body">

            {{-- Filtros (rol NO hace submit; filtra en cliente) --}}
            <form id="usersFilterForm" method="GET" class="mb-3" role="search">
                <div class="form-row">
                    <div class="form-group col-md-6 mb-2">
                        <input type="text" name="q" value="{{ $q }}" class="form-control"
                               placeholder="Buscar por nombre o email" autocomplete="off">
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <select name="role" id="filter_role" class="form-control">
                            <option value="">Todos los roles</option>
                            @foreach($roles as $roleName => $label)
                                <option value="{{ $roleName }}" @selected($selectedRole === $roleName)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-2 d-flex">
                        <button class="btn btn-outline-secondary mr-2 flex-fill" type="submit">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <button id="btnClearFilters" class="btn btn-outline-secondary flex-fill" type="button">
                            Limpiar
                        </button>
                    </div>
                </div>
            </form>

            {{-- Barra de selección masiva --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted">Seleccionados: <span id="selCountUsers">0</span></small>
                <button id="btnBulkDelete" type="button" class="btn btn-sm btn-danger" disabled>
                    <i class="fas fa-trash-alt"></i> Eliminar seleccionados
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0" id="usersTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:36px;" class="text-center">
                                <input type="checkbox" id="chkAllUsers" aria-label="Seleccionar todos">
                            </th>
                            <th style="min-width:220px;">Nombre</th>
                            <th style="min-width:220px;">Email</th>
                            <th class="text-center role-col">Rol</th>
                            <th class="text-center actions-col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            @php
                                $rolesList = $u->roles->pluck('name');
                                $rolesAttr = strtolower($rolesList->implode('|')); // para filtro en cliente
                            @endphp
                            <tr class="js-user-row" data-roles="{{ $rolesAttr }}">
                                {{-- Checkbox fila --}}
                                <td class="text-center">
                                    <input type="checkbox" class="chk-user-select" value="{{ $u->id }}" aria-label="Seleccionar usuario {{ $u->name }}">
                                </td>

                                {{-- Nombre --}}
                                <td class="py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-initial mr-2" aria-hidden="true">
                                            {{ mb_strtoupper(mb_substr($u->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-weight-600 text-truncate" title="{{ $u->name }}">{{ $u->name }}</div>
                                            @if(method_exists($u, 'getAttribute') && $u->getAttribute('username'))
                                                <small class="text-muted">@{{ $u->username }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Email --}}
                                <td class="py-2">
                                    <span class="cell-ellipsis" title="{{ $u->email }}">{{ $u->email }}</span>
                                </td>

                                {{-- Rol (ancho fijo + colores por rol + tamaño uniforme) --}}
                                <td class="py-2 text-center role-col">
                                    @if($rolesList->isEmpty())
                                        <span class="text-muted">—</span>
                                    @else
                                        @foreach($rolesList as $r)
                                            @php
                                                $key = strtolower($r);
                                                $badgeClass = 'badge-role';
                                                if ($key === 'admin')   $badgeClass = 'badge-admin';
                                                elseif ($key === 'editor') $badgeClass = 'badge-editor';
                                                elseif ($key === 'usuario') $badgeClass = 'badge-usuario';
                                            @endphp
                                            <span class="badge {{ $badgeClass }} u-badge-fixed">{{ $r }}</span>
                                        @endforeach
                                    @endif
                                </td>

                                {{-- Acciones: Editar + Eliminar, lado a lado --}}
                                <td class="py-2 text-center actions-col">
                                    <div class="d-inline-flex align-items-center">
                                        <a href="{{ route('users.edit', ['usuario' => $u->id]) }}" class="btn btn-sm btn-info mr-1">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        <form action="{{ route('users.destroy', ['usuario' => $u->id]) }}" method="POST" class="m-0"
                                              onsubmit="return confirm('¿Eliminar usuario {{ $u->name }}?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i> Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-4 text-muted">
                                    <i class="far fa-user mr-1"></i> Sin usuarios.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">
                    @if($users->total() > 0)
                        Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }}
                    @endif
                </small>
                <div>{{ $users->appends(request()->except('page'))->links() }}</div>
            </div>
        </div>
    </div>

    {{-- Modal Entra (dejo tu contenido original, sin tocar la lógica) --}}
    <div class="modal fade" id="modalSyncEntra" tabindex="-1" role="dialog" aria-labelledby="modalSyncEntraLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                {{-- ... tu modal existente ... --}}
                {{-- Para no alargar, omitido aquí tal cual lo tenías --}}
            </div>
        </div>
    </div>
@stop

@push('css')
<style>
/* Avatar con inicial */
.avatar-initial{
    width:32px;height:32px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    background:#e2e8f0;color:#334155;font-weight:700;font-size:.9rem;flex:0 0 32px;
}
/* Ellipsis en email */
.cell-ellipsis{display:inline-block;max-width:360px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
/* Rol: columna fija + badge tamaño uniforme */
.role-col{ width:140px; text-align:center; }
/* Base (otros roles) */
.badge-role{ background:#eef2ff;color:#4338ca;border:1px solid rgba(67,56,202,.15);font-weight:600; }
/* Colores solicitados */
.badge-admin  { background:#fee2e2; color:#b91c1c; border:1px solid rgba(185,28,28,.25); font-weight:700; }
.badge-editor { background:#fef9c3; color:#a16207; border:1px solid rgba(161,98,7,.25);  font-weight:700; }
.badge-usuario{ background:#dcfce7; color:#166534; border:1px solid rgba(22,101,52,.25);  font-weight:700; }
/* Ancho uniforme de cada badge */
.u-badge-fixed{ display:inline-block;width:90px;text-align:center;white-space:nowrap; }
/* Acciones: centrado + ancho fijo */
.actions-col{ width:180px; text-align:center !important; }
</style>
@endpush

@section('js')
<script>
(function(){
    // ====== Filtro por rol (cliente) SIN refresh ======
    const roleSel = document.getElementById('filter_role');
    function applyClientRoleFilter(){
        const wanted = (roleSel?.value || '').toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr.js-user-row');
        rows.forEach(tr => {
            if (!wanted) { tr.classList.remove('d-none'); return; }
            const roles = (tr.dataset.roles || '').split('|'); // ya en lower
            if (roles.includes(wanted)) tr.classList.remove('d-none'); else tr.classList.add('d-none');
        });
        // al filtrar, actualizar contador de seleccionados visibles
        updateSelectedCount();
    }
    if (roleSel){
        // aplicar en carga si viene preseleccionado (p.ej., desde la URL)
        if (roleSel.value) applyClientRoleFilter();
        roleSel.addEventListener('change', applyClientRoleFilter, {passive:true});
    }

    // ====== Botón LIMPIAR ======
    const form = document.getElementById('usersFilterForm');
    const btnClear = document.getElementById('btnClearFilters');
    if (btnClear){
        btnClear.addEventListener('click', function(){
            // Limpia UI y vuelve a mostrar todas las filas (sin refresh)
            const q = form.querySelector('input[name="q"]');
            if (q) q.value = '';
            if (roleSel) { roleSel.value = ''; applyClientRoleFilter(); }
            // Si querés limpiar también resultados traídos por "q" del backend,
            // descomentá la línea siguiente para recargar sin query:
            // window.location = "{{ url()->current() }}";
        }, {passive:true});
    }

    // ====== Selección múltiple + eliminación masiva ======
    const chkAll = document.getElementById('chkAllUsers');
    const btnBulk = document.getElementById('btnBulkDelete');
    const selCount = document.getElementById('selCountUsers');
    function getRowCheckboxes(){
        return Array.from(document.querySelectorAll('#usersTable tbody .chk-user-select'));
    }
    function updateSelectedCount(){
        const chks = getRowCheckboxes();
        const count = chks.filter(c => c.checked && !c.closest('tr').classList.contains('d-none')).length;
        selCount.textContent = count;
        btnBulk.disabled = (count === 0);
        // sincroniza maestro si todos los visibles están seleccionados
        const visibles = chks.filter(c => !c.closest('tr').classList.contains('d-none'));
        chkAll.checked = (visibles.length > 0 && visibles.every(c => c.checked));
    }
    // maestro (afecta sólo filas visibles)
    if (chkAll){
        chkAll.addEventListener('change', function(){
            const chks = getRowCheckboxes().filter(c => !c.closest('tr').classList.contains('d-none'));
            chks.forEach(c => c.checked = chkAll.checked);
            updateSelectedCount();
        }, {passive:true});
    }
    // cada fila
    getRowCheckboxes().forEach(c => c.addEventListener('change', updateSelectedCount, {passive:true}));

    // botón eliminar seleccionados (crea form dinámico para evitar forms anidados)
    if (btnBulk){
        btnBulk.addEventListener('click', function(){
            const ids = getRowCheckboxes()
                .filter(c => c.checked && !c.closest('tr').classList.contains('d-none'))
                .map(c => c.value);
            if (ids.length === 0) return;
            if (!confirm(`¿Eliminar ${ids.length} usuario(s) seleccionados?`)) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @json(route('users.bulk-destroy')); // definí esta ruta en backend
            // csrf + method spoof
            const t = document.createElement('input'); t.type='hidden'; t.name='_token'; t.value=@json(csrf_token());
            const m = document.createElement('input'); m.type='hidden'; m.name='_method'; m.value='DELETE';
            form.appendChild(t); form.appendChild(m);
            ids.forEach((id, i) => {
                const f = document.createElement('input');
                f.type='hidden'; f.name='ids[]'; f.value=id;
                form.appendChild(f);
            });
            document.body.appendChild(form);
            form.submit();
        });
    }
})();
</script>
@endsection
