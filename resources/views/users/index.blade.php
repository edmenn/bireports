@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <div class="users-header">
        <h1 class="m-0">Usuarios</h1>
        <div class="actions">
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
        $selectedRole = request('role');
        $q = $q ?? request('q');
    @endphp

    <x-adminlte-card title="Listado de Usuarios" icon="fas fa-users text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3" body-class="p-0">

        {{-- Filtros + barra de selección --}}
        <div class="p-3">
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
                    <div class="form-group col-md-3 mb-2 d-flex align-items-stretch" id="usersFilterBtns">
                        <button class="btn btn-primary mr-2 flex-fill" type="submit">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary flex-fill">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted">Seleccionados: <span id="selCountUsers">0</span></small>
                <button id="btnBulkDelete" type="button" class="btn btn-sm btn-danger" disabled>
                    <i class="fas fa-trash-alt"></i> Eliminar seleccionados
                </button>
            </div>
        </div>

        {{-- Tabla --}}
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
                            $rolesAttr = strtolower($rolesList->implode('|'));
                        @endphp
                        <tr class="js-user-row" data-roles="{{ $rolesAttr }}">
                            <td class="text-center">
                                <input type="checkbox" class="chk-user-select" value="{{ $u->id }}" aria-label="Seleccionar usuario {{ $u->name }}">
                            </td>
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
                            <td class="py-2">
                                <span class="cell-ellipsis" title="{{ $u->email }}">{{ $u->email }}</span>
                            </td>
                            <td class="py-2 text-center role-col">
                                @if($rolesList->isEmpty())
                                    <span class="text-muted">—</span>
                                @else
                                    @foreach($rolesList as $r)
                                        @php
                                            $key = strtolower($r);
                                            $badgeClass = 'badge-role';
                                            if ($key === 'admin')      $badgeClass = 'badge-admin';
                                            elseif ($key === 'editor') $badgeClass = 'badge-editor';
                                            elseif ($key === 'usuario')$badgeClass = 'badge-usuario';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} u-badge-fixed">{{ $r }}</span>
                                    @endforeach
                                @endif
                            </td>
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

        {{-- Pie de la tarjeta con paginación y contador --}}
        <x-slot name="footerSlot">
            <div class="d-flex justify-content-between align-items-center w-100">
                <small class="text-muted mb-0">
                    @if($users->total() > 0)
                        Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }}
                    @endif
                </small>
                <div class="mb-0">{{ $users->appends(request()->except('page'))->links() }}</div>
            </div>
        </x-slot>
    </x-adminlte-card>
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

/* Encabezado SOLO de Usuarios */
.users-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.75rem;
    flex-wrap:wrap; /* si no entra, baja solo el bloque de acciones */
}
.users-header .actions{
    display:flex;flex-wrap:wrap;gap:.5rem;
}
.users-header .actions .btn{
    min-width:auto;  /* anula min-width global */
    margin:0;        /* usamos gap, no margin */
    white-space:nowrap;
}
.users-header h1{ color:#212529 !important; } /* mismo tono que “Grupos” */

/* Controles de filtros: que no se desborden y mantengan altura */
#usersFilterForm #usersFilterBtns .btn{
    min-width:0;     /* anula el min-width global de .btn */
    flex:1 1 0;      /* se reparten el ancho */
}
#usersFilterForm .form-control,
#usersFilterForm select,
#usersFilterForm #usersFilterBtns .btn{
    height:44px;
}
#usersFilterForm #usersFilterBtns{ gap:.5rem; }

/* Responsive: botones del header en ancho completo si hace falta */
@media (max-width:768px){
    .users-header .actions{ width:100%; }
    .users-header .actions .btn{ flex:1 1 220px; }
}
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
        updateSelectedCount();
    }
    if (roleSel){
        if (roleSel.value) applyClientRoleFilter();
        roleSel.addEventListener('change', applyClientRoleFilter, {passive:true});
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
        const visibles = chks.filter(c => !c.closest('tr').classList.contains('d-none'));
        chkAll.checked = (visibles.length > 0 && visibles.every(c => c.checked));
    }

    if (chkAll){
        chkAll.addEventListener('change', function(){
            const chks = getRowCheckboxes().filter(c => !c.closest('tr').classList.contains('d-none'));
            chks.forEach(c => c.checked = chkAll.checked);
            updateSelectedCount();
        }, {passive:true});
    }
    getRowCheckboxes().forEach(c => c.addEventListener('change', updateSelectedCount, {passive:true}));

    if (btnBulk){
        btnBulk.addEventListener('click', function(){
            const ids = getRowCheckboxes()
                .filter(c => c.checked && !c.closest('tr').classList.contains('d-none'))
                .map(c => c.value);
            if (ids.length === 0) return;
            if (!confirm(`¿Eliminar ${ids.length} usuario(s) seleccionados?`)) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @json(route('users.bulk-destroy'));
            // csrf + method spoof
            const t = document.createElement('input'); t.type='hidden'; t.name='_token'; t.value=@json(csrf_token());
            const m = document.createElement('input'); m.type='hidden'; m.name='_method'; m.value='DELETE';
            form.appendChild(t); form.appendChild(m);
            ids.forEach((id) => {
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
