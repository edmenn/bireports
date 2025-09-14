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
                                    <span class="text-muted">-</span>
                                @else
                                    @foreach($u->roles as $role)
                                        @php
                                            $key = strtolower($role->name);
                                            $style = '';
                                            if (!empty($role->badge_bg) || !empty($role->badge_text)) {
                                                $bg = $role->badge_bg ?: '#eef2ff';
                                                $tx = $role->badge_text ?: '#4338ca';
                                                $style = "background: {$bg}; color: {$tx}; border:1px solid {$tx}; font-weight:700;";
                                            }
                                            if ($style === '') {
                                                $badgeClass = 'badge-role';
                                                if ($key === 'admin')      $badgeClass = 'badge-admin';
                                                elseif ($key === 'editor') $badgeClass = 'badge-editor';
                                                elseif ($key === 'usuario')$badgeClass = 'badge-usuario';
                                            }
                                        @endphp
                                        <span class="badge u-badge-fixed {{ $style === '' ? $badgeClass : '' }}" @if($style) style="{{ $style }}" @endif>
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                @endif
                            </td>
                            <td class="py-2 text-center actions-col">
                                <div class="d-inline-flex align-items-center">
                                    <a href="{{ route('users.edit', ['usuario' => $u->id]) }}" class="btn btn-sm btn-info mr-1">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    @php($isSelf = (int)($u->id) === (int)auth()->id())
                                    @if(!$isSelf)
                                        <form action="{{ route('users.destroy', ['usuario' => $u->id]) }}" method="POST" class="m-0 js-confirm-delete-form"
                                              data-confirm-message="¿Eliminar usuario {{ $u->name }}?">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i> Eliminar
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-danger" disabled title="No puedes eliminar tu propia cuenta">
                                            <i class="fas fa-ban"></i> Eliminar
                                        </button>
                                    @endif
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

    {{-- Modal: Importar usuarios desde Microsoft Entra --}}
    <div class="modal fade" id="modalSyncEntra" tabindex="-1" role="dialog" aria-labelledby="modalSyncEntraLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSyncEntraLabel"><i class="fab fa-microsoft mr-1"></i> Importar desde Microsoft Entra</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-md-4 mb-2">
                            <label for="entraQuery" class="mb-1 small">Buscar (nombre o UPN)</label>
                            <input type="text" id="entraQuery" class="form-control" placeholder="Ej: Juan o juan@empresa.com" autocomplete="off">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="entraDomain" class="mb-1 small">Solo dominio</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" id="entraDomain" class="form-control" placeholder="empresa.com" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="form-check mt-4 pt-1">
                                <input class="form-check-input" type="checkbox" id="entraOnlyActive">
                                <label class="form-check-label" for="entraOnlyActive">Solo activos</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="entraAssignRole" class="mb-1 small">Asignar rol (opcional)</label>
                            <select id="entraAssignRole" class="form-control">
                                <option value="">(ninguno)</option>
                                @foreach($roles as $roleName => $label)
                                    <option value="{{ $roleName }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive border rounded">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:36px;" class="text-center">
                                        <input type="checkbox" id="entraChkAll" aria-label="Seleccionar todos">
                                    </th>
                                    <th style="min-width:260px;">Nombre</th>
                                    <th style="min-width:260px;">Email</th>
                                </tr>
                            </thead>
                            <tbody id="entraTbody">
                                <tr><td colspan="3" class="text-center text-muted py-3">Usa la búsqueda para previsualizar usuarios…</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small id="entraStatus" class="text-muted"></small>
                        <button type="button" id="entraBtnMore" class="btn btn-outline-secondary" disabled>
                            Cargar más
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" id="entraBtnImport" class="btn btn-primary" disabled>
                        <i class="fas fa-file-import mr-1"></i> Importar seleccionados
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de confirmación genérico --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirmar acción</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage" class="mb-0">¿Confirmas esta acción?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmOkBtn" class="btn btn-primary">Confirmar</button>
                </div>
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

            openConfirmDialog(`¿Eliminar ${ids.length} usuario(s) seleccionados?`, function(){
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
        });
    }
})();

// ====== Modal de Importación Entra ======
(function(){
    const modal = document.getElementById('modalSyncEntra');
    if (!modal) return;

    const tbody = document.getElementById('entraTbody');
    const chkAll = document.getElementById('entraChkAll');
    const btnMore = document.getElementById('entraBtnMore');
    const btnImport = document.getElementById('entraBtnImport');
    const statusEl = document.getElementById('entraStatus');

    const inpQuery = document.getElementById('entraQuery');
    const inpDomain = document.getElementById('entraDomain');
    const chkOnlyActive = document.getElementById('entraOnlyActive');
    const selAssignRole = document.getElementById('entraAssignRole');

    const previewUrl = @json(route('users.sync-entra.preview'));
    const importUrl  = @json(route('users.sync-entra.selected'));
    const csrfToken  = @json(csrf_token());

    let nextLink = null;
    const selected = new Map(); // email -> {email,name,aliases}

    function updateImportBtn(){
        btnImport.disabled = (selected.size === 0);
    }

    function setLoading(loading, msg){
        statusEl.textContent = msg || (loading ? 'Cargando…' : '');
        btnMore.disabled = loading || !nextLink;
    }

    function attachRowHandlers(scope){
        const chks = (scope || document).querySelectorAll('.js-entra-row-chk');
        chks.forEach(chk => {
            const email = chk.getAttribute('data-email') || '';
            const name  = chk.getAttribute('data-name') || email;
            const aliasesStr = chk.getAttribute('data-aliases') || '';
            const aliases = aliasesStr.split(',').map(s => s.trim()).filter(Boolean);
            if (email && selected.has(email)) {
                chk.checked = true;
            }
            chk.addEventListener('change', () => {
                if (!email) return;
                if (chk.checked) selected.set(email, {email, name, aliases});
                else selected.delete(email);
                updateImportBtn();
                // actualizar chkAll según visibles
                syncChkAllState();
            }, {passive:true});
        });
    }

    function syncChkAllState(){
        const chks = Array.from(tbody.querySelectorAll('.js-entra-row-chk'));
        const enabled = chks.filter(c => !c.disabled);
        chkAll.checked = (enabled.length > 0 && enabled.every(c => c.checked));
    }

    async function load(reset){
        if (reset) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">Cargando…</td></tr>';
            nextLink = null;
        }
        setLoading(true);
        try {
            const params = new URLSearchParams();
            const q = (inpQuery.value || '').trim();
            const dom = (inpDomain.value || '').trim();
            if (q) params.set('query', q);
            if (dom) params.set('domain', dom);
            if (chkOnlyActive.checked) params.set('only_active', '1');
            if (nextLink) params.set('next', nextLink);

            const url = previewUrl + (params.toString() ? ('?' + params.toString()) : '');
            const resp = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            const data = await resp.json();

            if (reset) tbody.innerHTML = '';
            const temp = document.createElement('tbody');
            temp.innerHTML = data.rows || '';
            // append rows
            Array.from(temp.children).forEach(tr => tbody.appendChild(tr));
            nextLink = data.next || null;
            btnMore.disabled = !nextLink;

            attachRowHandlers(tbody);
            syncChkAllState();
            updateImportBtn();
            statusEl.textContent = (data.count ?? 0) + ' usuarios cargados' + (nextLink ? ' (hay más)' : '');
        } catch (e) {
            statusEl.textContent = 'Error al consultar Microsoft Graph';
        } finally {
            setLoading(false);
        }
    }

    function debounce(fn, ms){
        let t; return function(){ clearTimeout(t); t = setTimeout(() => fn.apply(this, arguments), ms); };
    }

    // Eventos
    if (chkAll) {
        chkAll.addEventListener('change', () => {
            const chks = tbody.querySelectorAll('.js-entra-row-chk');
            chks.forEach(c => { if (!c.disabled) { c.checked = chkAll.checked; c.dispatchEvent(new Event('change')); }});
        });
    }
    if (btnMore) btnMore.addEventListener('click', () => load(false));

    const triggerReload = debounce(() => load(true), 400);
    if (inpQuery) inpQuery.addEventListener('input', triggerReload);
    if (inpDomain) inpDomain.addEventListener('change', triggerReload);
    if (chkOnlyActive) chkOnlyActive.addEventListener('change', triggerReload);

    // Importar seleccionados
    if (btnImport) {
        btnImport.addEventListener('click', () => {
            if (selected.size === 0) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = importUrl;
            const t = document.createElement('input'); t.type='hidden'; t.name='_token'; t.value=csrfToken; form.appendChild(t);
            let i = 0; selected.forEach(({email,name,aliases}) => {
                const e = document.createElement('input'); e.type='hidden'; e.name=`selected[${i}][email]`; e.value=email; form.appendChild(e);
                const n = document.createElement('input'); n.type='hidden'; n.name=`selected[${i}][name]`;  n.value=name;  form.appendChild(n);
                if (Array.isArray(aliases)) {
                    aliases.forEach((a, j) => {
                        const al = document.createElement('input'); al.type='hidden'; al.name=`selected[${i}][aliases][${j}]`; al.value=a; form.appendChild(al);
                    });
                }
                i++;
            });
            const role = (selAssignRole?.value || '').trim();
            if (role) {
                const r = document.createElement('input'); r.type='hidden'; r.name='assign_role'; r.value=role; form.appendChild(r);
            }
            document.body.appendChild(form);
            form.submit();
        });
    }

    // Reiniciar y cargar al abrir
    ['shown.bs.modal', 'show.bs.modal'].forEach(evt => {
        modal.addEventListener(evt, () => {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">Cargando…</td></tr>';
            inpQuery.value = '';
            inpDomain.value = '';
            chkOnlyActive.checked = false;
            chkAll.checked = false;
            selected.clear();
            updateImportBtn();
            nextLink = null;
            load(true);
        });
    });
})();

// ====== Confirmación bonita (modal) ======
(function(){
    const modalEl = document.getElementById('confirmModal');
    if (!modalEl) return;
    const msgEl = document.getElementById('confirmMessage');
    const okBtn = document.getElementById('confirmOkBtn');
    let onConfirm = null;
    let bsModal = null;

    function show(message, confirmCb){
        onConfirm = typeof confirmCb === 'function' ? confirmCb : null;
        if (msgEl) msgEl.textContent = message || '¿Confirmas esta acción?';
        if (window.bootstrap && window.bootstrap.Modal){
            if (!bsModal) bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        } else {
            if (window.confirm(message || '¿Confirmas esta acción?')) {
                if (onConfirm) onConfirm();
            }
        }
    }

    // Exponer en ámbito global local
    window.openConfirmDialog = show;

    if (okBtn){
        okBtn.addEventListener('click', function(){
            try { if (onConfirm) onConfirm(); } finally {
                if (bsModal) bsModal.hide();
                onConfirm = null;
            }
        });
    }

    // Hook para formularios con clase js-confirm-delete-form
    document.querySelectorAll('form.js-confirm-delete-form').forEach(function(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const msg = form.getAttribute('data-confirm-message') || '¿Confirma la eliminación?';
            show(msg, function(){ form.submit(); });
        });
    });
})();
</script>
@endsection
