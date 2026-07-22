@extends('layouts.app')

@section('title', __('users.page.title'))

{{-- =========================
     HEADER
     ========================= --}}
@section('content_header')
    {{-- Header with title on the left and compact, evenly spaced actions on the right (Tabler and Bootstrap 5) --}}
    <x-page-header :title="__('users.page.title')" icon="fas fa-users">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            {{-- New user --}}
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-plus"></i>
                <span>{{ __('users.header.new') }}</span>
            </a>

            {{-- Import from Microsoft Entra --}}
            <button type="button"
                    class="btn btn-outline-primary btn-sm header-btn"
                    data-toggle="modal" data-target="#modalSyncEntra"
                    data-bs-toggle="modal" data-bs-target="#modalSyncEntra">
                <i class="fab fa-microsoft"></i>
                <span>{{ __('users.header.import') }}</span>
            </button>

            {{-- Refresh existing Entra users --}}
            <form action="{{ route('users.sync-entra.refresh-existing') }}" method="POST" class="d-inline header-form">
                @csrf
                <button type="submit"
                        class="btn btn-outline-primary btn-sm header-btn"
                        title="{{ __('users.header.refresh_title') }}"
                        onclick="return confirm('{{ __('users.header.refresh_confirm') }}');">
                    <i class="fas fa-sync-alt"></i>
                    <span>{{ __('users.header.refresh') }}</span>
                </button>
            </form>
        </div>
    </x-page-header>
@stop

{{-- =========================
     CONTENT
     ========================= --}}
@section('content')
    {{-- Flash messages --}}
    @if (session('success')) <x-ui-alert theme="success" title="{{ __('users.alerts.success') }}" dismissable class="mb-2">{{ session('success') }}</x-ui-alert> @endif
    @if (session('warning')) <x-ui-alert theme="warning" title="{{ __('users.alerts.warning') }}" dismissable class="mb-2">{{ session('warning') }}</x-ui-alert> @endif
    @if (session('error'))   <x-ui-alert theme="danger"  title="{{ __('users.alerts.error') }}"    dismissable class="mb-2">{{ session('error') }}</x-ui-alert>   @endif

    @php
        // Keep original data loading and dynamic role palettes
        $roles = $roles ?? \Spatie\Permission\Models\Role::query()->orderBy('name')->pluck('name','name');
        $selectedRole = request('role');
        $q = $q ?? request('q');
        $selectedStatus = request('status');

        $customRoleStyles = [];
        foreach($users as $styledUser) {
            foreach($styledUser->roles as $styledRole) {
                if (!empty($styledRole->badge_bg) || !empty($styledRole->badge_text)) {
                    $customRoleStyles[$styledRole->getKey()] = [
                        'bg'   => $styledRole->badge_bg   ?: '#eef2ff',
                        'text' => $styledRole->badge_text ?: '#4338ca',
                    ];
                }
            }
        }
    @endphp

    @push('css')
        @if(!empty($customRoleStyles))
            <style>
                /* Per-role palette generated on backend (left intact) */
                @foreach($customRoleStyles as $roleId => $palette)
                    .role-badge-{{ $roleId }}{
                        background-color: {{ $palette['bg'] }};
                        color: {{ $palette['text'] }};
                        border: 1px solid {{ $palette['text'] }};
                        font-weight: 700;
                    }
                @endforeach
            </style>
        @endif
    @endpush

    <x-ui-card title="{{ __('users.index.card_title') }}" icon="fas fa-users text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3" body-class="p-0">

        {{-- =========================
             FILTERS + BULK ACTIONS
             ========================= --}}
        <div class="p-3">
            {{-- Compact, Bootstrap 4-friendly controls --}}
            <form id="usersFilterForm" method="GET" class="row align-items-end mb-3" role="search" aria-label="{{ __('users.index.filters.search') }}">
                <div class="col-12 col-lg-4 mb-2">
                    <label for="filter_q" class="form-label mb-1">{{ __('users.index.filters.search_label') }}</label>
                    <input type="text"
                           name="q"
                           id="filter_q"
                           value="{{ $q }}"
                           class="form-control"
                           placeholder="{{ __('users.index.filters.search_placeholder') }}"
                           autocomplete="off">
                </div>

                <div class="col-12 col-sm-6 col-lg-2 mb-2">
                    <label for="filter_role" class="form-label mb-1">{{ __('users.index.filters.role_label') }}</label>
                    <select name="role" id="filter_role" class="custom-select">
                        <option value="">{{ __('users.index.filters.role_all') }}</option>
                        @foreach($roles as $roleName => $label)
                            <option value="{{ $roleName }}" @selected($selectedRole === $roleName)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-2 mb-2">
                    <label for="filter_status" class="form-label mb-1">{{ __('users.index.filters.status_label') }}</label>
                    <select name="status" id="filter_status" class="custom-select">
                        <option value="">{{ __('users.index.filters.status_all') }}</option>
                        <option value="activo" @selected(in_array($selectedStatus, ['activo','active','0','false'], true))>{{ __('users.index.filters.status_active') }}</option>
                        <option value="bloqueado" @selected(in_array($selectedStatus, ['bloqueado','blocked','1','true'], true))>{{ __('users.index.filters.status_blocked') }}</option>
                    </select>
                </div>

                {{-- Equal-sized Search/Clear buttons --}}
                <div class="col-12 col-lg-4 mb-2" id="usersFilterBtns">
                    <div class="filter-actions">
                        <button class="btn btn-primary filter-btn" type="submit">
                            <i class="fas fa-search mr-1"></i> {{ __('users.index.filters.search') }}
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary filter-btn">
                            {{ __('users.index.filters.clear') }}
                        </a>
                    </div>
                </div>
            </form>

            {{-- Bulk toolbar (kept; labels remain) --}}
            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                <small class="text-muted mb-2 mb-sm-0">
                    {{ __('users.index.bulk.selected_prefix') }} <span id="selCountUsers">0</span>
                </small>
                <div class="btn-group bulk-actions" role="group" aria-label="{{ __('users.index.bulk.actions_aria') }}">
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

        {{-- =========================
             USERS TABLE
             ========================= --}}
        <div class="table-responsive shadow-sm rounded-4">
            <table class="table table-striped table-hover align-middle mb-0" id="usersTable">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center table-col-icon" style="width:42px;">
                            <input type="checkbox" id="chkAllUsers" aria-label="{{ __('users.index.table.select_all') }}">
                        </th>
                        <th class="table-col-md">{{ __('users.index.table.name') }}</th>
                        <th class="table-col-md">{{ __('users.index.table.email') }}</th>
                        <th class="text-center table-col-sm">{{ __('users.index.table.status') }}</th>
                        <th class="text-center table-col-sm">{{ __('users.index.table.role') }}</th>
                        <th class="text-center table-col-lg">{{ __('users.index.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        @php
                            $rolesList = $u->roles->pluck('name');
                            $rolesAttr = strtolower($rolesList->implode('|'));
                        @endphp
                        <tr class="js-user-row" data-roles="{{ $rolesAttr }}">
                            <td class="text-center table-col-icon">
                                <input type="checkbox" class="chk-user-select" value="{{ $u->id }}" aria-label="{{ __('users.index.table.select_user', ['name' => $u->name]) }}">
                            </td>
                            <td class="py-3 table-col-md">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-initial mr-2" aria-hidden="true">
                                        {{ mb_strtoupper(mb_substr($u->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-weight-semibold text-truncate" title="{{ $u->name }}">{{ $u->name }}</div>
                                        @if(method_exists($u, 'getAttribute') && $u->getAttribute('username'))
                                            <small class="text-muted">@{{ $u->username }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 table-col-md">
                                <span class="text-truncate d-inline-block" style="max-width: 260px;" title="{{ $u->email }}">{{ $u->email }}</span>
                            </td>
                            <td class="py-3 text-center table-col-sm">
                                @if((bool)($u->is_blocked ?? false))
                                    <span class="badge u-badge-fixed badge-danger">{{ __('users.index.badges.blocked') }}</span>
                                @else
                                    <span class="badge u-badge-fixed badge-success">{{ __('users.index.badges.active') }}</span>
                                @endif
                            </td>
                            <td class="py-3 text-center table-col-sm">
                                @if($rolesList->isEmpty())
                                    <span class="text-muted">-</span>
                                @else
                                    @foreach($u->roles as $role)
                                        @php
                                            $key = strtolower($role->name);
                                            $badgeClass = 'badge-role';
                                            if (!empty($role->badge_bg) || !empty($role->badge_text)) {
                                                $badgeClass = 'badge-role role-badge-' . $role->getKey();
                                            } elseif ($key === 'admin') {
                                                $badgeClass = 'badge-admin';
                                            } elseif ($key === 'editor') {
                                                $badgeClass = 'badge-editor';
                                            } elseif ($key === 'viewer') {
                                                $badgeClass = 'badge-viewer';
                                            }
                                        @endphp
                                        <span class="badge u-badge-fixed {{ $badgeClass }}">{{ $role->name }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td class="py-3 text-center table-col-lg">
                                {{-- Icon-only, compact actions (28x28) --}}
                                <div class="actions-group">
                                    {{-- Edit --}}
                                    <a href="{{ route('users.edit', ['user' => $u->id]) }}"
                                       class="btn btn-outline-info btn-icon"
                                       title="{{ __('users.index.buttons.edit') }}">
                                        <i class="fas fa-pen"></i>
                                    </a>

                                    @php($isSelf = (int)($u->id) === (int)auth()->id())
                                    @if(!$isSelf)
                                        {{-- Block / Unblock --}}
                                        <form action="{{ route('users.toggle-block', ['user' => $u->id]) }}" method="POST" class="m-0 js-confirm-delete-form">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-icon {{ (bool)($u->is_blocked ?? false) ? 'btn-outline-success' : 'btn-outline-warning' }}"
                                                    title="{{ (bool)($u->is_blocked ?? false) ? __('users.index.buttons.unblock') : __('users.index.buttons.block') }}">
                                                <i class="fas {{ (bool)($u->is_blocked ?? false) ? 'fa-unlock' : 'fa-user-lock' }}"></i>
                                            </button>
                                        </form>

                                        {{-- Delete (trash can) --}}
                                        <form action="{{ route('users.destroy', ['user' => $u->id]) }}" method="POST" class="m-0 js-confirm-delete-form"
                                              onsubmit="return confirm('{{ __('users.index.confirm.delete') }}');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-icon" title="{{ __('users.index.buttons.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Self delete disabled --}}
                                        <button type="button" class="btn btn-outline-danger btn-icon" disabled
                                                title="{{ __('users.index.messages.self_delete') }}">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4 text-muted">
                                <i class="far fa-user mr-2"></i> {{ __('users.index.messages.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- =========================
             FOOTER (pagination + counter)
             ========================= --}}
        <x-slot name="footerSlot">
            <div class="d-flex flex-column align-items-center w-100 text-center">
                <small class="text-muted mb-2">
                    @if($users->total() > 0)
                        {{ __('users.index.messages.showing', ['from' => $users->firstItem(), 'to' => $users->lastItem(), 'total' => $users->total()]) }}
                    @endif
                </small>
                <div class="mb-0 d-flex justify-content-center w-100">
                    {{ $users->appends(request()->except('page'))->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </x-slot>
    </x-ui-card>

    {{-- =========================
         MODAL: Microsoft Entra import
         ========================= --}}
    <div class="modal fade" id="modalSyncEntra" tabindex="-1" role="dialog" aria-labelledby="modalSyncEntraLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSyncEntraLabel"><i class="fab fa-microsoft mr-2"></i> {{ __('users.index.entra.title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="{{ __('users.index.buttons.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Compact modal controls --}}
                    <div class="row align-items-end mb-3">
                        <div class="col-md-4 mb-2">
                            <label for="entraQuery" class="mb-1 small">{{ __('users.index.entra.search_label') }}</label>
                            <input type="text" id="entraQuery" class="form-control" placeholder="{{ __('users.index.entra.search_placeholder') }}" autocomplete="off">
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="form-check mt-4 pt-1">
                                <input class="form-check-input" type="checkbox" id="entraOnlyNew">
                                <label class="form-check-label" for="entraOnlyNew">{{ __('users.index.entra.only_new') }}</label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="entraOnlyActive">
                                <label class="form-check-label" for="entraOnlyActive">{{ __('users.index.entra.only_active') }}</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="entraAssignRole" class="mb-1 small">{{ __('users.index.entra.assign_role') }}</label>
                            <select id="entraAssignRole" class="custom-select">
                                @foreach($roles as $roleName => $label)
                                    <option value="{{ $roleName }}" @selected($roleName === 'viewer')>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive shadow-sm rounded-4">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:42px;">
                                        <input type="checkbox" id="entraChkAll" aria-label="{{ __('users.index.entra.select_all') }}">
                                    </th>
                                    <th class="table-col-lg">
                                        <button type="button" id="entraSortName" class="sort-toggle" aria-label="{{ __('users.index.entra.sort_name_asc') }}">
                                            {{ __('users.index.table.name') }}
                                            <i id="entraSortIcon" class="fas fa-sort-alpha-down ml-1"></i>
                                        </button>
                                    </th>
                                    <th class="table-col-lg">
                                        <button type="button" id="entraSortPosition" class="sort-toggle" aria-label="{{ __('users.index.entra.sort_position_asc') }}">
                                            {{ __('users.index.table.position') }}
                                            <i id="entraSortPositionIcon" class="fas fa-sort-alpha-down ml-1"></i>
                                        </button>
                                    </th>
                                    <th class="table-col-lg">{{ __('users.index.table.email') }}</th>
                                </tr>
                                <tr id="entraFilterRow" class="d-none">
                                    <th></th>
                                    <th>
                                        <input type="text" id="entraFilterName" class="form-control form-control-sm"
                                               placeholder="{{ __('users.index.entra.filter_name') }}" autocomplete="off">
                                    </th>
                                    <th>
                                        <input type="text" id="entraFilterPosition" class="form-control form-control-sm"
                                               placeholder="{{ __('users.index.entra.filter_position') }}" autocomplete="off">
                                    </th>
                                    <th>
                                        <input type="text" id="entraFilterEmail" class="form-control form-control-sm"
                                               placeholder="{{ __('users.index.entra.filter_email') }}" autocomplete="off">
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="entraTbody">
                                <tr><td colspan="4" class="text-center text-muted py-3">{{ __('users.index.messages.entra_use_search') }}</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="d-flex align-items-center">
                            <small id="entraStatus" class="text-muted"></small>
                            <small id="entraListCount" class="text-muted ml-3"></small>
                        </div>
                        <button type="button" id="entraBtnMore" class="btn btn-outline-secondary btn-sm" disabled>
                            {{ __('users.index.buttons.load_more') }}
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm entra-footer-btn" data-dismiss="modal" data-bs-dismiss="modal">{{ __('users.index.buttons.close') }}</button>
                    <button type="button" id="entraBtnImport" class="btn btn-primary btn-sm entra-footer-btn" disabled>
                        <i class="fas fa-file-import mr-1"></i> <span id="entraImportLabel">{{ __('users.index.buttons.import_selected') }}</span><span id="entraImportCount"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- =========================
         MODAL: Generic confirmation
         ========================= --}}
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
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-bs-dismiss="modal">{{ __('users.index.buttons.close') }}</button>
                    <button type="button" id="confirmOkBtn" class="btn btn-primary btn-sm">{{ __('users.index.buttons.confirm') }}</button>
                </div>
            </div>
        </div>
    </div>
@stop

{{-- =========================
     VIEW-SPECIFIC CSS (keeps BS4/Tabler3)
     ========================= --}}
@push('css')
<style>
/* Filters: compact inputs and equal buttons */
#usersFilterForm .form-control, #usersFilterForm .custom-select{ min-height:38px; padding:.375rem .75rem; font-size:.875rem; }
#usersFilterForm .form-label{ font-weight:600; font-size:.85rem; }
.filter-actions .filter-btn{
    flex:1 1 9rem; font-size:.875rem; font-weight:600;
}
.filter-actions .filter-btn i{ font-size:.9rem; }

/* Avatars and role badges */
.avatar-initial{ width:36px; height:36px; border-radius:50%; background:var(--app-icon-bg); color:var(--app-icon-text); display:flex; align-items:center; justify-content:center; font-weight:600; }
.u-badge-fixed{ font-size:.75rem; padding:.35rem .55rem; border-radius:8px; font-weight:600; }
.badge-admin{ background:#dc3545; color:#fff; }
.badge-editor{ background:#17a2b8; color:#fff; }
.badge-viewer{ background:#6c757d; color:#fff; }

/* Table alignment + pagination */
table th, table td{ vertical-align:middle !important; }
.pagination{ margin-bottom:0 !important; }

/* Actions group: uniform spacing */
.actions-group{ display:flex; flex-wrap:wrap; justify-content:center; align-items:center; gap:.35rem; }

/* Icon-only compact buttons inside tables.
   Override global .btn min-width (from your theme) to avoid huge buttons. */
.table .btn-icon{
    width:30px !important; height:30px !important;
    border-radius:.5rem !important; padding:0 !important;
    display:inline-flex !important; align-items:center; justify-content:center;
    line-height:1 !important; font-size:.8rem !important;
}
.table .btn-icon i{ margin:0 !important; font-size:.8rem !important; }
.table .btn-icon:hover{ filter:brightness(.94); }
.sort-toggle{
    background: transparent;
    border: 0;
    padding: 0;
    appearance: none;
    color: inherit;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}
.sort-toggle:hover, .sort-toggle:focus{ text-decoration:none; color:inherit; }
.sort-toggle i{ font-size:.9rem; }
.entra-footer-btn{
    flex: 1 1 10rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
</style>
@endpush

{{-- =========================
     VIEW-SPECIFIC JS
     ========================= --}}
@section('js')
<script>
/* i18n strings used by JS */
const usersI18n = {
    bulkDelete: @json(__('users.index.confirm.bulk_delete')),
    bulkBlock: @json(__('users.index.confirm.bulk_block')),
    bulkUnblock: @json(__('users.index.confirm.bulk_unblock')),
    loading: @json(__('users.index.messages.entra_status_loading')),
    error: @json(__('users.index.messages.entra_status_error')),
    loaded: @json(__('users.index.messages.entra_status_loaded')),
    loadedMore: @json(__('users.index.messages.entra_status_loaded_more')),
    loadedAll: @json(__('users.index.messages.entra_status_loaded_all')),
    truncated: @json(__('users.index.messages.entra_status_truncated')),
    listCount: @json(__('users.index.messages.entra_list_count')),
    useSearch: @json(__('users.index.messages.entra_use_search')),
    noResults: @json(__('users.index.messages.entra_no_results')),
    generalConfirm: @json(__('users.index.confirm.general'))
};

/* ===== Client-side role filter (no page refresh) ===== */
(function(){
    var roleSel = document.getElementById('filter_role');
    function applyClientRoleFilter(){
        var wanted = (roleSel && roleSel.value ? roleSel.value : '').toLowerCase();
        var rows = document.querySelectorAll('#usersTable tbody tr.js-user-row');
        Array.prototype.forEach.call(rows, function(tr){
            if (!wanted){ tr.classList.remove('d-none'); return; }
            var roles = (tr.getAttribute('data-roles') || '').split('|');
            if (roles.indexOf(wanted) !== -1) tr.classList.remove('d-none'); else tr.classList.add('d-none');
        });
        updateSelectedCount();
    }
    if (roleSel){
        if (roleSel.value) applyClientRoleFilter();
        roleSel.addEventListener('change', applyClientRoleFilter, {passive:true});
    }

    /* ===== Multi-select + bulk buttons ===== */
    var chkAll = document.getElementById('chkAllUsers');
    var btnBulk = document.getElementById('btnBulkDelete');
    var btnBulkBlock = document.getElementById('btnBulkBlock');
    var btnBulkUnblock = document.getElementById('btnBulkUnblock');
    var selCount = document.getElementById('selCountUsers');

    function getRowCheckboxes(){
        return Array.prototype.slice.call(document.querySelectorAll('#usersTable tbody .chk-user-select'));
    }
    function updateSelectedCount(){
        var chks = getRowCheckboxes();
        var count = chks.filter(function(c){
            return c.checked && !c.closest('tr').classList.contains('d-none');
        }).length;
        if (selCount) selCount.textContent = count;
        var disabled = (count === 0);
        if (btnBulk) btnBulk.disabled = disabled;
        if (btnBulkBlock) btnBulkBlock.disabled = disabled;
        if (btnBulkUnblock) btnBulkUnblock.disabled = disabled;

        var visibles = chks.filter(function(c){ return !c.closest('tr').classList.contains('d-none'); });
        if (chkAll) chkAll.checked = (visibles.length > 0 && visibles.every(function(c){ return c.checked; }));
    }

    if (chkAll){
        chkAll.addEventListener('change', function(){
            var chks = getRowCheckboxes().filter(function(c){ return !c.closest('tr').classList.contains('d-none'); });
            chks.forEach(function(c){ c.checked = chkAll.checked; });
            updateSelectedCount();
        }, {passive:true});
    }
    getRowCheckboxes().forEach(function(c){ c.addEventListener('change', updateSelectedCount, {passive:true}); });

    if (btnBulk){
        btnBulk.addEventListener('click', function(){
            var ids = getRowCheckboxes()
                .filter(function(c){ return c.checked && !c.closest('tr').classList.contains('d-none'); })
                .map(function(c){ return c.value; });
            if (ids.length === 0) return;

            openConfirmDialog(usersI18n.bulkDelete.replace(':count', ids.length), function(){
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = @json(route('users.bulk-destroy'));
                var t = document.createElement('input'); t.type='hidden'; t.name='_token'; t.value=@json(csrf_token());
                var m = document.createElement('input'); m.type='hidden'; m.name='_method'; m.value='DELETE';
                form.appendChild(t); form.appendChild(m);
                ids.forEach(function(id){
                    var f = document.createElement('input'); f.type='hidden'; f.name='ids[]'; f.value=id;
                    form.appendChild(f);
                });
                document.body.appendChild(form);
                form.submit();
            });
        });
    }

    function submitBulkAction(url, messageTemplate){
        var ids = getRowCheckboxes()
            .filter(function(c){ return c.checked && !c.closest('tr').classList.contains('d-none'); })
            .map(function(c){ return c.value; });
        if (ids.length === 0) return;
        openConfirmDialog(messageTemplate.replace(':count', ids.length), function(){
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            var t = document.createElement('input'); t.type='hidden'; t.name='_token'; t.value=@json(csrf_token()); form.appendChild(t);
            ids.forEach(function(id){
                var f = document.createElement('input'); f.type='hidden'; f.name='ids[]'; f.value=id; form.appendChild(f);
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

/* ===== Entra import modal (preview, pagination, submit) ===== */
(function(){
    var modal = document.getElementById('modalSyncEntra');
    if (!modal) return;

    var tbody = document.getElementById('entraTbody');
    var chkAll = document.getElementById('entraChkAll');
    var btnMore = document.getElementById('entraBtnMore');
    var btnImport = document.getElementById('entraBtnImport');
    var importLabel = document.getElementById('entraImportLabel');
    var importCount = document.getElementById('entraImportCount');
    var statusEl = document.getElementById('entraStatus');
    var listCountEl = document.getElementById('entraListCount');

    var inpQuery = document.getElementById('entraQuery');
    var chkOnlyActive = document.getElementById('entraOnlyActive');
    var chkOnlyNew = document.getElementById('entraOnlyNew');
    var filterRow = document.getElementById('entraFilterRow');
    var filterName = document.getElementById('entraFilterName');
    var filterPosition = document.getElementById('entraFilterPosition');
    var filterEmail = document.getElementById('entraFilterEmail');
    var btnSortName = document.getElementById('entraSortName');
    var sortIcon = document.getElementById('entraSortIcon');
    var btnSortPosition = document.getElementById('entraSortPosition');
    var sortPositionIcon = document.getElementById('entraSortPositionIcon');
    var sortState = 'name_asc';
    var selAssignRole = document.getElementById('entraAssignRole');

    var previewUrl = @json(route('users.sync-entra.preview'));
    var importUrl  = @json(route('users.sync-entra.selected'));
    var csrfToken  = @json(csrf_token());
    var pageSize   = 999;

    var nextLink = null;
    var selected = new Map(); // email -> {email,name,aliases}

    function updateImportBtn(){
        var count = selected.size;
        if (btnImport) btnImport.disabled = (count === 0);
        if (importCount) importCount.textContent = count ? (' (' + count + ')') : '';
        if (!importLabel && btnImport && count) btnImport.textContent = btnImport.textContent.replace(/\s*\(\d+\)\s*$/, '') + ' (' + count + ')';
    }

    function updateListCount(){
        if (!listCountEl || !tbody) return;
        var visible = tbody.querySelectorAll('tr.js-entra-row:not(.d-none)').length;
        listCountEl.textContent = usersI18n.listCount.replace(':count', visible);
    }

    function applyRowFilters(){
        var onlyNew = !!(chkOnlyNew && chkOnlyNew.checked);
        var nameFilter = (filterName && filterName.value ? filterName.value : '').trim().toLowerCase();
        var positionFilter = (filterPosition && filterPosition.value ? filterPosition.value : '').trim().toLowerCase();
        var emailFilter = (filterEmail && filterEmail.value ? filterEmail.value : '').trim().toLowerCase();
        var rows = tbody.querySelectorAll('tr.js-entra-row');
        Array.prototype.forEach.call(rows, function(row){
            if (!onlyNew){
                row.classList.remove('d-none');
            } else {
                var exists = row.getAttribute('data-exists') === '1';
                if (exists) row.classList.add('d-none'); else row.classList.remove('d-none');
            }

            if (!row.classList.contains('d-none')){
                var rowName = (row.getAttribute('data-sort-name') || '').toLowerCase();
                var rowPos = (row.getAttribute('data-sort-position') || '').toLowerCase();
                var emailEl = row.querySelector('.js-entra-row-chk');
                var rowEmail = (emailEl && emailEl.getAttribute('data-email') ? emailEl.getAttribute('data-email') : '').toLowerCase();

                if (nameFilter && rowName.indexOf(nameFilter) === -1) row.classList.add('d-none');
                if (!row.classList.contains('d-none') && positionFilter && rowPos.indexOf(positionFilter) === -1) row.classList.add('d-none');
                if (!row.classList.contains('d-none') && emailFilter && rowEmail.indexOf(emailFilter) === -1) row.classList.add('d-none');
            }
        });
        syncChkAllState();
        updateListCount();
    }

    function sortRows(){
        if (!tbody) return;
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.js-entra-row'));
        if (rows.length === 0) return;
        rows.sort(function(a,b){
            var keyAttr = (sortState.indexOf('position_') === 0) ? 'data-sort-position' : 'data-sort-name';
            var na = (a.getAttribute(keyAttr) || '').toLowerCase();
            var nb = (b.getAttribute(keyAttr) || '').toLowerCase();
            if (na === nb) return 0;
            var cmp = na < nb ? -1 : 1;
            return (sortState.indexOf('_desc') !== -1) ? -cmp : cmp;
        });
        rows.forEach(function(r){ tbody.appendChild(r); });
        syncChkAllState();
    }

    function setLoading(loading, msg){
        if (statusEl) statusEl.textContent = msg || (loading ? usersI18n.loading : '');
        if (btnMore) btnMore.disabled = loading || !nextLink;
    }

    function applySelection(chk, checked){
        var email = chk.getAttribute('data-email') || '';
        var name  = chk.getAttribute('data-name') || email;
        var aliasesStr = chk.getAttribute('data-aliases') || '';
        var aliases = aliasesStr.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
        chk.checked = checked;
        if (email && checked){
            selected.set(email, {email:email, name:name, aliases:aliases});
        } else if (email && !checked){
            selected.delete(email);
        }
    }

    function attachRowHandlers(scope){
        var chks = (scope || document).querySelectorAll('.js-entra-row-chk');
        Array.prototype.forEach.call(chks, function(chk){
            var email = chk.getAttribute('data-email') || '';
            if (email && selected.has(email)) chk.checked = true;
            chk.addEventListener('change', function(){
                if (!email) return;
                applySelection(chk, chk.checked);
                updateImportBtn();
                syncChkAllState();
            }, {passive:true});
        });
    }

    function syncChkAllState(){
        var chks = Array.prototype.slice.call(tbody.querySelectorAll('.js-entra-row-chk'));
        var enabled = chks.filter(function(c){
            var row = c.closest('tr');
            return !c.disabled && row && !row.classList.contains('d-none');
        });
        if (chkAll) chkAll.checked = (enabled.length > 0 && enabled.every(function(c){ return c.checked; }));
    }

    async function load(reset){
        if (reset){
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">' + usersI18n.loading + '</td></tr>';
            nextLink = null;
        }
        setLoading(true);
        try{
            var params = new URLSearchParams();
            var q = (inpQuery && inpQuery.value ? inpQuery.value : '').trim();
            if (q) params.set('query', q);
            if (sortState) params.set('sort', sortState);
            params.set('page_size', pageSize);
            if (chkOnlyActive && chkOnlyActive.checked) params.set('only_active', '1');
            if (nextLink) params.set('next', nextLink);

            var url = previewUrl + (params.toString() ? ('?' + params.toString()) : '');
            var resp = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
            var data = await resp.json();

            var isOnlyActiveAll = (!q && chkOnlyActive && chkOnlyActive.checked);
            if (reset || isOnlyActiveAll) tbody.innerHTML = '';
            var temp = document.createElement('tbody');
            temp.innerHTML = data.rows || '';
            if (!temp.children.length){
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">' + usersI18n.noResults + '</td></tr>';
                if (filterRow) filterRow.classList.add('d-none');
            }else{
                if (isOnlyActiveAll){
                    tbody.innerHTML = data.rows || '';
                } else {
                    Array.prototype.forEach.call(temp.children, function(tr){ tbody.appendChild(tr); });
                }
                if (filterRow) filterRow.classList.remove('d-none');
            }

            nextLink = data.next || null;
            var hasMore = (typeof data.has_more === 'boolean') ? data.has_more : !!nextLink;
            if (isOnlyActiveAll) hasMore = false;
            if (btnMore) btnMore.disabled = !hasMore;

        attachRowHandlers(tbody);
        applyRowFilters();
        syncChkAllState();
        sortRows();
        updateImportBtn();
        updateListCount();
            var count = (typeof data.count === 'number') ? data.count : 0;
            if (!temp.children.length){
                if (statusEl) statusEl.textContent = usersI18n.noResults;
            }else{
                if (data && data.truncated){
                    if (statusEl) statusEl.textContent = usersI18n.truncated.replace(':count', count);
                } else {
                    var msg = hasMore ? usersI18n.loadedMore : usersI18n.loadedAll;
                    if (statusEl) statusEl.textContent = msg.replace(':count', count);
                }
            }
        }catch(e){
            if (statusEl) statusEl.textContent = usersI18n.error;
        }finally{
            setLoading(false);
        }
    }

    function debounce(fn, ms){ var t; return function(){ clearTimeout(t); t = setTimeout(function(){ fn.apply(this, arguments); }, ms); }; }

    if (chkAll){
        chkAll.addEventListener('change', function(){
            var chks = tbody.querySelectorAll('.js-entra-row-chk');
            Array.prototype.forEach.call(chks, function(c){
                var row = c.closest('tr');
                if (!c.disabled && row && !row.classList.contains('d-none')){
                    applySelection(c, chkAll.checked);
                }
            });
            updateImportBtn();
            syncChkAllState();
        });
    }
    if (btnMore) btnMore.addEventListener('click', function(){ load(false); });

    var triggerReload = debounce(function(){ load(true); }, 400);
    if (inpQuery) inpQuery.addEventListener('input', triggerReload);
    if (chkOnlyActive) chkOnlyActive.addEventListener('change', triggerReload);
    if (chkOnlyNew) chkOnlyNew.addEventListener('change', applyRowFilters);
    if (filterName) filterName.addEventListener('input', applyRowFilters, {passive:true});
    if (filterPosition) filterPosition.addEventListener('input', applyRowFilters, {passive:true});
    if (filterEmail) filterEmail.addEventListener('input', applyRowFilters, {passive:true});

    if (btnImport){
        btnImport.addEventListener('click', function(){
            if (selected.size === 0) return;
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = importUrl;
            var t = document.createElement('input'); t.type='hidden'; t.name='_token'; t.value=csrfToken; form.appendChild(t);
            var i = 0; selected.forEach(function(row){
                var e = document.createElement('input'); e.type='hidden'; e.name='selected['+i+'][email]'; e.value=row.email; form.appendChild(e);
                var n = document.createElement('input'); n.type='hidden'; n.name='selected['+i+'][name]';  n.value=row.name;  form.appendChild(n);
                if (Array.isArray(row.aliases)){
                    row.aliases.forEach(function(a, j){
                        var al = document.createElement('input'); al.type='hidden'; al.name='selected['+i+'][aliases]['+j+']'; al.value=a; form.appendChild(al);
                    });
                }
                i++;
            });
            var role = (selAssignRole && selAssignRole.value ? selAssignRole.value.trim() : '');
            if (role){ var r = document.createElement('input'); r.type='hidden'; r.name='assign_role'; r.value=role; form.appendChild(r); }
            document.body.appendChild(form);
            form.submit();
        });
    }

    ['shown.bs.modal', 'show.bs.modal'].forEach(function(evt){
        modal.addEventListener(evt, function(){
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">' + usersI18n.loading + '</td></tr>';
            if (inpQuery) inpQuery.value = '';
            if (chkOnlyActive) chkOnlyActive.checked = false;
            if (chkOnlyNew) chkOnlyNew.checked = false;
            if (filterName) filterName.value = '';
            if (filterPosition) filterPosition.value = '';
            if (filterEmail) filterEmail.value = '';
            if (filterRow) filterRow.classList.add('d-none');
            sortState = 'name_asc';
            if (sortIcon) sortIcon.className = 'fas fa-sort-alpha-down ml-1';
            if (sortPositionIcon) sortPositionIcon.className = 'fas fa-sort-alpha-down ml-1';
            if (chkAll) chkAll.checked = false;
            selected.clear();
            updateImportBtn();
            nextLink = null;
            load(true);
        });
    });

    if (btnSortName){
        btnSortName.addEventListener('click', function(){
            sortState = sortState === 'name_desc' ? 'name_asc' : 'name_desc';
            if (sortIcon){
                sortIcon.className = sortState === 'name_desc'
                    ? 'fas fa-sort-alpha-up ml-1'
                    : 'fas fa-sort-alpha-down ml-1';
            }
            sortRows();
        });
    }

    if (btnSortPosition){
        btnSortPosition.addEventListener('click', function(){
            sortState = sortState === 'position_desc' ? 'position_asc' : 'position_desc';
            if (sortPositionIcon){
                sortPositionIcon.className = sortState === 'position_desc'
                    ? 'fas fa-sort-alpha-up ml-1'
                    : 'fas fa-sort-alpha-down ml-1';
            }
            sortRows();
        });
    }
})();

/* ===== Generic confirmation modal (works with BS4 or BS5) ===== */
(function(){
    var modalEl = document.getElementById('confirmModal');
    if (!modalEl) return;
    var msgEl = document.getElementById('confirmMessage');
    var okBtn = document.getElementById('confirmOkBtn');
    var onConfirm = null;
    var bsModal = null;

    function show(message, confirmCb){
        onConfirm = (typeof confirmCb === 'function') ? confirmCb : null;
        if (msgEl) msgEl.textContent = message || usersI18n.generalConfirm;

        if (window.bootstrap && window.bootstrap.Modal){
            if (!bsModal) bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        } else if (window.$ && window.$.fn.modal){
            $(modalEl).modal('show');
        } else {
            if (window.confirm(message || usersI18n.generalConfirm)){
                if (onConfirm) onConfirm();
            }
            return;
        }
    }
    window.openConfirmDialog = show;

    if (okBtn){
        okBtn.addEventListener('click', function(){
            try { if (onConfirm) onConfirm(); } finally {
                if (bsModal) bsModal.hide();
                if (window.$ && window.$.fn.modal) $(modalEl).modal('hide');
                onConfirm = null;
            }
        });
    }

    document.querySelectorAll('form.js-confirm-delete-form').forEach(function(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var msg = form.getAttribute('data-confirm-message') || usersI18n.generalConfirm;
            show(msg, function(){ form.submit(); });
        });
    });
})();
</script>
@endsection
