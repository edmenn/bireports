@extends('adminlte::page')

@section('title', __('users.page.title'))

@section('content_header')
    <div class="users-header">
        <h1 class="m-0">{{ __('users.page.title') }}</h1>
        <div class="actions">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('users.header.new') }}
            </a>
            <button type="button" class="btn btn-outline-primary"
                    data-toggle="modal" data-target="#modalSyncEntra"
                    data-bs-toggle="modal" data-bs-target="#modalSyncEntra">
                <i class="fab fa-microsoft"></i> {{ __('users.header.import') }}
            </button>
            <form action="{{ route('users.sync-entra.refresh-existing') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success"
                        title="{{ __('users.header.refresh_title') }}"
                        onclick="return confirm('{{ __('users.header.refresh_confirm') }}');">
                    <i class="fas fa-sync-alt"></i> {{ __('users.header.refresh') }}
                </button>
            </form>
        </div>
    </div>
@stop

@section('content')
    @if (session('success')) <x-adminlte-alert theme="success" title="{{ __('users.alerts.success') }}" dismissable class="mb-2">{{ session('success') }}</x-adminlte-alert> @endif
    @if (session('warning')) <x-adminlte-alert theme="warning" title="{{ __('users.alerts.warning') }}" dismissable class="mb-2">{{ session('warning') }}</x-adminlte-alert> @endif
    @if (session('error'))   <x-adminlte-alert theme="danger"  title="{{ __('users.alerts.error') }}"    dismissable class="mb-2">{{ session('error') }}</x-adminlte-alert>   @endif

    @php
        $roles = $roles ?? \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name','name');
        $selectedRole = request('role');
        $q = $q ?? request('q');
        $selectedStatus = request('status');
    @endphp

    <x-adminlte-card title="{{ __('users.index.card_title') }}" icon="fas fa-users text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3" body-class="p-0">

        {{-- Filtros + barra de selección --}}
        <div class="p-3">
            <form id="usersFilterForm" method="GET" class="mb-3" role="search">
                <div class="form-row">
                    <div class="form-group col-md-6 mb-2">
                        <input type="text" name="q" value="{{ $q }}" class="form-control"
                               placeholder="{{ __('users.index.filters.search_placeholder') }}" autocomplete="off">
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <select name="role" id="filter_role" class="form-control">
                            <option value="">{{ __('users.index.filters.role_all') }}</option>
                            @foreach($roles as $roleName => $label)
                                <option value="{{ $roleName }}" @selected($selectedRole === $roleName)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-2">
                        <select name="status" id="filter_status" class="form-control">
                            <option value="">{{ __('users.index.filters.status_all') }}</option>
                            <option value="activo" @selected(in_array($selectedStatus, ['activo','active','0','false'], true))>{{ __('users.index.filters.status_active') }}</option>
                            <option value="bloqueado" @selected(in_array($selectedStatus, ['bloqueado','blocked','1','true'], true))>{{ __('users.index.filters.status_blocked') }}</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3 mb-2 d-flex align-items-stretch" id="usersFilterBtns">
                        <button class="btn btn-primary mr-2 flex-fill" type="submit">
                            <i class="fas fa-search"></i> {{ __('users.index.filters.search') }}
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary flex-fill">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <small class="text-muted">{{ __('users.index.bulk.selected_prefix') }} <span id="selCountUsers">0</span></small>
                <div class="btn-group" role="group" aria-label="{{ __('users.index.bulk.actions_aria') }}">
                    <button id="btnBulkBlock" type="button" class="btn btn-sm btn-outline-warning" disabled>
                        <i class="fas fa-user-lock"></i> {{ __('users.index.bulk.block') }}
                    </button>
                    <button id="btnBulkUnblock" type="button" class="btn btn-sm btn-outline-success" disabled>
                        <i class="fas fa-user-check"></i> {{ __('users.index.bulk.unblock') }}
                    </button>
                    <button id="btnBulkDelete" type="button" class="btn btn-sm btn-danger" disabled>
                        <i class="fas fa-trash-alt"></i> {{ __('users.index.bulk.delete') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0" id="usersTable">
                <thead class="thead-light">
                    <tr>
                        <th style="width:36px;" class="text-center">
                            <input type="checkbox" id="chkAllUsers" aria-label="{{ __('users.index.table.select_all') }}">
                        </th>
                        <th style="min-width:220px;">{{ __('users.index.table.name') }}</th>
                        <th style="min-width:220px;">Email</th>
                        <th class="text-center status-col">{{ __('users.index.table.status') }}</th>
                        
                        <th class="text-center role-col">{{ __('users.index.table.role') }}</th>
                        <th class="text-center actions-col">{{ __('users.index.table.actions') }}</th>
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
                                <input type="checkbox" class="chk-user-select" value="{{ $u->id }}" aria-label="{{ __('users.index.table.select_user', ['name' => $u->name]) }}">
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
                            <td class="py-2 text-center status-col">
                                @if((bool)($u->is_blocked ?? false))
                                    <span class="badge u-badge-fixed badge-danger">{{ __('users.index.badges.blocked') }}</span>
                                @else
                                    <span class="badge u-badge-fixed badge-success">{{ __('users.index.badges.active') }}</span>
                                @endif
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
                                                elseif ($key === 'viewer') $badgeClass = 'badge-viewer';
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
                                    <a href="{{ route('users.edit', ['user' => $u->id]) }}" class="btn btn-sm btn-info mr-1">
                                        <i class="fas fa-edit"></i> {{ __('users.index.buttons.edit') }}
                                    </a>
                                    @php($isSelf = (int)($u->id) === (int)auth()->id())
                                    @if(!$isSelf)
                                        <form action="{{ route('users.toggle-block', ['user' => $u->id]) }}" method="POST" class="m-0 mr-1 js-confirm-delete-form"
                                              data-confirm-message="{{ (bool)($u->is_blocked ?? false) ? __('users.index.confirm.unblock_user', ['name' => $u->name]) : __('users.index.confirm.block_user', ['name' => $u->name]) }}">
                                            @csrf
                                            <button class="btn btn-sm {{ (bool)($u->is_blocked ?? false) ? 'btn-success' : 'btn-warning' }}">
                                                <i class="fas {{ (bool)($u->is_blocked ?? false) ? 'fa-unlock' : 'fa-user-lock' }}"></i>
                                                {{ (bool)($u->is_blocked ?? false) ? __('users.index.buttons.unblock') : __('users.index.buttons.block') }}
                                            </button>
                                        </form>
                                    @endif
                                    @if(!$isSelf)
                                        <form action="{{ route('users.destroy', ['user' => $u->id]) }}" method="POST" class="m-0 js-confirm-delete-form"
                                              data-confirm-message="{{ __('users.index.confirm.delete_user', ['name' => $u->name]) }}">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash-alt"></i> {{ __('users.index.buttons.delete') }}
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-danger" disabled title="{{ __('users.index.messages.self_delete') }}">
                                            <i class="fas fa-ban"></i> {{ __('users.index.buttons.delete') }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-4 text-muted">
                                <i class="far fa-user mr-1"></i> {{ __('users.index.messages.empty') }}
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
                        {{ __('users.index.messages.showing', ['from' => $users->firstItem(), 'to' => $users->lastItem(), 'total' => $users->total()]) }}
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
                    <h5 class="modal-title" id="modalSyncEntraLabel"><i class="fab fa-microsoft mr-1"></i> {{ __('users.index.entra.title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="{{ __('users.index.buttons.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-md-4 mb-2">
                            <label for="entraQuery" class="mb-1 small">{{ __('users.index.entra.search_label') }}</label>
                            <input type="text" id="entraQuery" class="form-control" placeholder="{{ __('users.index.entra.search_placeholder') }}" autocomplete="off">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="entraDomain" class="mb-1 small">{{ __('users.index.entra.domain_label') }}</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" id="entraDomain" class="form-control" placeholder="{{ __('users.index.entra.domain_placeholder') }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="form-check mt-4 pt-1">
                                <input class="form-check-input" type="checkbox" id="entraOnlyActive">
                                <label class="form-check-label" for="entraOnlyActive">{{ __('users.index.entra.only_active') }}</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="entraAssignRole" class="mb-1 small">{{ __('users.index.entra.assign_role') }}</label>
                            <select id="entraAssignRole" class="form-control">
                                @foreach($roles as $roleName => $label)
                                    <option value="{{ $roleName }}" @selected($roleName === 'viewer')>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive border rounded">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:36px;" class="text-center">
                                        <input type="checkbox" id="entraChkAll" aria-label="{{ __('users.index.entra.select_all') }}">
                                    </th>
                                    <th style="min-width:260px;">{{ __('users.index.table.name') }}</th>
                                    <th style="min-width:260px;">{{ __('users.index.table.email') }}</th>
                                </tr>
                            </thead>
                            <tbody id="entraTbody">
                                <tr><td colspan="3" class="text-center text-muted py-3">{{ __('users.index.messages.entra_use_search') }}</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small id="entraStatus" class="text-muted"></small>
                        <button type="button" id="entraBtnMore" class="btn btn-outline-secondary" disabled>
                            {{ __('users.index.buttons.load_more') }}
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">{{ __('users.index.buttons.close') }}</button>
                    <button type="button" id="entraBtnImport" class="btn btn-primary" disabled>
                        <i class="fas fa-file-import mr-1"></i> {{ __('users.index.buttons.import_selected') }}
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
                    <h5 class="modal-title" id="confirmModalLabel">{{ __('users.index.confirm.title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="{{ __('users.index.buttons.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage" class="mb-0">{{ __('users.index.confirm.general') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">{{ __('users.index.buttons.close') }}</button>
                    <button type="button" id="confirmOkBtn" class="btn btn-primary">{{ __('users.index.buttons.confirm') }}</button>
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
.status-col{ width:140px; text-align:center !important; }
 
/* Base (otros roles) */
.badge-role{ background:#eef2ff;color:#4338ca;border:1px solid rgba(67,56,202,.15);font-weight:600; }
/* Colores solicitados */
.badge-admin  { background:#fee2e2; color:#b91c1c; border:1px solid rgba(185,28,28,.25); font-weight:700; }
.badge-editor { background:#fef9c3; color:#a16207; border:1px solid rgba(161,98,7,.25);  font-weight:700; }
.badge-viewer{ background:#dcfce7; color:#166534; border:1px solid rgba(22,101,52,.25);  font-weight:700; }
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
const usersI18n = {
    bulkDelete: @json(__('users.index.confirm.bulk_delete')),
    bulkBlock: @json(__('users.index.confirm.bulk_block')),
    bulkUnblock: @json(__('users.index.confirm.bulk_unblock')),
    loading: @json(__('users.index.messages.entra_status_loading')),
    error: @json(__('users.index.messages.entra_status_error')),
    loaded: @json(__('users.index.messages.entra_status_loaded')),
    loadedMore: @json(__('users.index.messages.entra_status_loaded_more')),
    useSearch: @json(__('users.index.messages.entra_use_search')),
    noResults: @json(__('users.index.messages.entra_no_results')),
    generalConfirm: @json(__('users.index.confirm.general'))
};
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
    const btnBulkBlock = document.getElementById('btnBulkBlock');
    const btnBulkUnblock = document.getElementById('btnBulkUnblock');
    const selCount = document.getElementById('selCountUsers');

    function getRowCheckboxes(){
        return Array.from(document.querySelectorAll('#usersTable tbody .chk-user-select'));
    }
    function updateSelectedCount(){
        const chks = getRowCheckboxes();
        const count = chks.filter(c => c.checked && !c.closest('tr').classList.contains('d-none')).length;
        selCount.textContent = count;
        const disabled = (count === 0);
        btnBulk.disabled = disabled;
        if (btnBulkBlock) btnBulkBlock.disabled = disabled;
        if (btnBulkUnblock) btnBulkUnblock.disabled = disabled;
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

            openConfirmDialog(usersI18n.bulkDelete.replace(':count', ids.length), function(){
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

    function submitBulkAction(url, messageTemplate){
        const ids = getRowCheckboxes()
            .filter(c => c.checked && !c.closest('tr').classList.contains('d-none'))
            .map(c => c.value);
        if (ids.length === 0) return;
        openConfirmDialog(messageTemplate.replace(':count', ids.length), function(){
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            const t = document.createElement('input'); t.type='hidden'; t.name='_token'; t.value=@json(csrf_token()); form.appendChild(t);
            ids.forEach((id) => {
                const f = document.createElement('input'); f.type='hidden'; f.name='ids[]'; f.value=id; form.appendChild(f);
            });
            document.body.appendChild(form);
            form.submit();
        });
    }

    if (btnBulkBlock){
        btnBulkBlock.addEventListener('click', function(){
            submitBulkAction(@json(route('users.bulk-block')), usersI18n.bulkBlock);
        });
    }
    if (btnBulkUnblock){
        btnBulkUnblock.addEventListener('click', function(){
            submitBulkAction(@json(route('users.bulk-unblock')), usersI18n.bulkUnblock);
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
        statusEl.textContent = msg || (loading ? usersI18n.loading : '');
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
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">' + usersI18n.loading + '</td></tr>';
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
        if (!temp.children.length) {
            if (reset) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">' + usersI18n.noResults + '</td></tr>';
            }
        } else {
            Array.from(temp.children).forEach(tr => tbody.appendChild(tr));
        }
            nextLink = data.next || null;
            btnMore.disabled = !nextLink;

            attachRowHandlers(tbody);
            syncChkAllState();
            updateImportBtn();
            const count = data.count ?? 0;
            if (!temp.children.length) {
                statusEl.textContent = usersI18n.noResults;
            } else {
                statusEl.textContent = (nextLink ? usersI18n.loadedMore : usersI18n.loaded).replace(':count', count);
            }
        } catch (e) {
            statusEl.textContent = usersI18n.error;
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
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">' + usersI18n.loading + '</td></tr>';
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
        if (msgEl) msgEl.textContent = message || usersI18n.generalConfirm;
        if (window.bootstrap && window.bootstrap.Modal){
            if (!bsModal) bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        } else {
            if (window.confirm(message || usersI18n.generalConfirm)) {
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
            const msg = form.getAttribute('data-confirm-message') || usersI18n.generalConfirm;
            show(msg, function(){ form.submit(); });
        });
    });
})();
</script>
@endsection
