@extends('layouts.app')

@section('title', __('folders.page.create'))

@section('content_header')
    <x-page-header :title="__('folders.page.create')" icon="fas fa-folder-plus" />
@stop

@section('content')
    {{-- Important: disable browser validation bubbles --}}
    <form action="{{ route('folders.store') }}" method="POST" novalidate>
        @csrf

        {{-- CARD 1: Folder details --}}
        <x-ui-card title="{{ __('folders.cards.details.title') }}" icon="fas fa-folder text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row">
                {{-- Name (required) --}}
                <div class="col-md-6 mb-3">
                    <x-ui-input name="name"
                        label="{{ __('folders.form.name_label') }}"
                        placeholder="{{ __('folders.form.name_placeholder') }}"
                        value="{{ old('name') }}"
                        required
                        class="@error('name') is-invalid @enderror"/>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Description (optional) --}}
                <div class="col-md-6 mb-3">
                    <x-ui-textarea name="description"
                        label="{{ __('folders.form.description_label') }}"
                        rows="1"
                        placeholder="{{ __('folders.form.description_placeholder') }}"
                        class="@error('description') is-invalid @enderror"
                        data-no-required="true">{{ old('description') }}</x-ui-textarea>
                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </x-ui-card>

        {{-- CARD 2: Assignments --}}
        <x-ui-card title="{{ __('folders.cards.assignments.title') }}" icon="fas fa-users text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row g-3">

                {{-- Users --}}
                <div class="col-md-4 d-flex flex-column">
                    {{-- HEADER CORREGIDO: pl-3 para alinear con lista, d-flex para centrar check/titulo --}}
                    <div class="assignments-header mb-2 py-2 pl-3 bg-light border rounded d-flex align-items-center">
                        <div class="custom-control custom-checkbox m-0">
                            <input type="checkbox" class="custom-control-input js-check-all" id="chk_all_folder_users"
                                   data-target-class="assign-folder-users"
                                   aria-label="{{ __('folders.form.select_all_users') }}">
                            <label class="custom-control-label selectall-label" for="chk_all_folder_users"></label>
                        </div>
                        <button type="button" class="sort-toggle ml-2 d-flex align-items-center text-dark text-decoration-none" id="sort_users_btn">
                            <span class="fw-bold">{{ __('folders.form.users_label') }}</span>
                            <i class="fas fa-sort-alpha-down ml-2" id="sort_users_icon"></i>
                        </button>
                    </div>

                    @php($usersSorted = $users->sortBy(fn($u) => mb_strtolower($u->name ?? '')))
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($usersSorted as $user)
                            <div class="custom-control custom-checkbox mb-2 js-sort-item sort-folder-users" data-sort-name="{{ mb_strtolower($user->name ?? '') }}">
                                <input type="checkbox" class="custom-control-input assign-folder-users" name="users[]"
                                       value="{{ $user->id }}" id="user_{{ $user->id }}"
                                       @checked(in_array($user->id, old('users', [])))>
                                <label class="custom-control-label" for="user_{{ $user->id }}">
                                    {{ $user->name }} <small class="text-muted">&lt;{{ $user->email }}&gt;</small>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('folders.empty.users') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Groups --}}
                <div class="col-md-4 d-flex flex-column">
                    {{-- HEADER CORREGIDO --}}
                    <div class="assignments-header mb-2 py-2 pl-3 bg-light border rounded d-flex align-items-center">
                        <div class="custom-control custom-checkbox m-0">
                            <input type="checkbox" class="custom-control-input js-check-all" id="chk_all_folder_groups"
                                   data-target-class="assign-folder-groups"
                                   aria-label="{{ __('folders.form.select_all_groups') }}">
                            <label class="custom-control-label selectall-label" for="chk_all_folder_groups"></label>
                        </div>
                        <button type="button" class="sort-toggle ml-2 d-flex align-items-center text-dark text-decoration-none" id="sort_groups_btn">
                            <span class="fw-bold">{{ __('folders.form.groups_label') }}</span>
                            <i class="fas fa-sort-alpha-down ml-2" id="sort_groups_icon"></i>
                        </button>
                    </div>

                    @php($groupsSorted = $groups->sortBy(fn($g) => mb_strtolower($g->name ?? '')))
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($groupsSorted as $group)
                            <div class="custom-control custom-checkbox mb-2 js-sort-item sort-folder-groups" data-sort-name="{{ mb_strtolower($group->name ?? '') }}">
                                <input type="checkbox" class="custom-control-input assign-folder-groups" name="groups[]"
                                       value="{{ $group->id }}" id="group_{{ $group->id }}"
                                       @checked(in_array($group->id, old('groups', [])))>
                                <label class="custom-control-label" for="group_{{ $group->id }}">
                                    {{ $group->name }}
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('folders.empty.groups') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Reports --}}
                <div class="col-md-4 d-flex flex-column">
                    {{-- HEADER CORREGIDO --}}
                    <div class="assignments-header mb-2 py-2 pl-3 bg-light border rounded d-flex align-items-center">
                        <div class="custom-control custom-checkbox m-0">
                            <input type="checkbox" class="custom-control-input js-check-all" id="chk_all_folder_reports"
                                   data-target-class="assign-folder-reports"
                                   aria-label="{{ __('folders.form.select_all_reports') }}">
                            <label class="custom-control-label selectall-label" for="chk_all_folder_reports"></label>
                        </div>
                        <button type="button" class="sort-toggle ml-2 d-flex align-items-center text-dark text-decoration-none" id="sort_reports_btn">
                            <span class="fw-bold">{{ __('folders.form.reports_label') }}</span>
                            <i class="fas fa-sort-alpha-down ml-2" id="sort_reports_icon"></i>
                        </button>
                    </div>

                    @php($reportsSorted = $reports->sortBy(fn($r) => mb_strtolower($r->name ?? '')))
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($reportsSorted as $report)
                            <div class="custom-control custom-checkbox mb-2 js-sort-item sort-folder-reports" data-sort-name="{{ mb_strtolower($report->name ?? '') }}">
                                <input type="checkbox" class="custom-control-input assign-folder-reports" name="reports[]"
                                       value="{{ $report->id }}" id="report_{{ $report->id }}"
                                       @checked(in_array($report->id, old('reports', [])))>
                                <label class="custom-control-label" for="report_{{ $report->id }}">
                                    {{ $report->name }}
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('folders.empty.reports') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-ui-card>

        {{-- CARD 3: Actions --}}
        <x-ui-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="form-actions w-100">
                    <x-ui-button type="submit" theme="primary"
                        label="{{ __('folders.form.buttons.save') }}" icon="fas fa-save" class="btn-eq"/>
                    <x-ui-button :href="route('folders.index')" theme="secondary"
                        label="{{ __('folders.form.buttons.cancel') }}" icon="fas fa-times" class="btn-eq"/>
                </div>
            </div>
        </x-ui-card>
    </form>
@stop

@push('css')
<style>
/* Header actions */
.header-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: .5rem;
}
.header-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 38px;
    line-height: 1;
    padding: 0 .9rem;
    font-size: .875rem;
}
.header-btn i {
    font-size: .9rem;
    margin-right: .4rem;
}

/* Checkbox containers */
.checkbox-list-container {
    max-height: 300px;
    overflow-y: auto;
    background-color: var(--app-card-alt-bg, #f8f9fa);
}

/* === AJUSTES DE ALINEACIÓN DE HEADER === */

/* 1. Ajuste fino para bajar el dibujito del check y centrarlo con el texto */
.assignments-header .custom-control-label::before,
.assignments-header .custom-control-label::after {
    top: 0.2rem; /* Baja el check ligeramente */
}

/* 2. Reseteo de márgenes del control */
.assignments-header .custom-control {
    min-height: auto;
    padding-left: 1.5rem; /* Espacio para el icono */
}
.assignments-header .custom-control-label {
    cursor: pointer;
}

/* 3. Estilo del botón de título */
.assignments-header .sort-toggle {
    background: transparent;
    border: 0;
    padding: 0;
    appearance: none;
    line-height: 1.2;
    font-weight: 600;
    cursor: pointer;
}
.assignments-header .sort-toggle:hover {
    color: inherit;
}
/* ======================================= */

.custom-control-label { font-size: 0.9rem; }
.selectall-label{padding:0; margin:0; width:100%; height:1rem; display:inline-block;}

/* Inputs and textarea same height */
input.form-control,
textarea.form-control {
    border: 1px solid var(--app-input-border, #ced4da) !important;
    border-radius: .375rem !important;
    padding: .5rem .75rem !important;
    box-shadow: none !important;
    height: 42px !important;
}
textarea.form-control { resize: none !important; }

/* Disable browser native validation visuals */
input:invalid,
textarea:invalid,
input:required,
textarea:required {
    box-shadow: none !important;
    border-color: var(--app-input-border, #ced4da) !important;
    outline: none !important;
}
::-webkit-validation-bubble,
::-webkit-validation-bubble-arrow,
::-webkit-validation-bubble-message {
    display: none !important;
}
:focus:invalid {
    outline: none !important;
    box-shadow: none !important;
}

/* Focus style */
input:focus,
textarea:focus {
    border-color: var(--app-btn-bg, #007bff) !important;
    box-shadow: 0 0 0 0.1rem rgba(0,123,255,.25) !important;
    box-shadow: 0 0 0 0.1rem color-mix(in srgb, var(--app-btn-bg, #007bff) 25%, transparent) !important;
}
</style>
@endpush

@push('js')
<script>
(function(){
    var masters = document.querySelectorAll('.js-check-all');
    masters.forEach(function(master){
        var cls = master.getAttribute('data-target-class');
        if (!cls) return;
        var boxes = Array.prototype.slice.call(document.querySelectorAll('input.'+cls));
        function refresh(){
            var enabled = boxes.filter(function(b){ return !b.disabled; });
            if (!enabled.length){ master.checked = false; master.indeterminate = false; return; }
            var allChecked = enabled.every(function(b){ return b.checked; });
            var someChecked = enabled.some(function(b){ return b.checked; });
            master.checked = allChecked;
            master.indeterminate = (!allChecked && someChecked);
        }
        master.addEventListener('change', function(){
            boxes.forEach(function(b){ if (!b.disabled) b.checked = master.checked; });
            refresh();
        });
        boxes.forEach(function(b){ b.addEventListener('change', refresh, {passive:true}); });
        refresh();
    });
})();

(function(){
    var sortStates = {
        users: 'asc',
        groups: 'asc',
        reports: 'asc'
    };
    function sortList(listClass, stateKey, iconId){
        var items = Array.prototype.slice.call(document.querySelectorAll('.js-sort-item.'+listClass));
        if (!items.length) return;
        var container = items[0].parentElement;
        items.sort(function(a,b){
            var ta = (a.getAttribute('data-sort-name') || '').toLowerCase();
            var tb = (b.getAttribute('data-sort-name') || '').toLowerCase();
            if (ta === tb) return 0;
            var cmp = ta < tb ? -1 : 1;
            return sortStates[stateKey] === 'desc' ? -cmp : cmp;
        });
        items.forEach(function(it){ container.appendChild(it); });
        var icon = document.getElementById(iconId);
        if (icon){
            icon.className = sortStates[stateKey] === 'desc'
                ? 'fas fa-sort-alpha-up ml-2'
                : 'fas fa-sort-alpha-down ml-2';
        }
    }

    function attach(btnId, listClass, stateKey, iconId){
        var btn = document.getElementById(btnId);
        if (!btn) return;
        btn.addEventListener('click', function(){
            sortStates[stateKey] = sortStates[stateKey] === 'desc' ? 'asc' : 'desc';
            sortList(listClass, stateKey, iconId);
        });
    }
    attach('sort_users_btn', 'sort-folder-users', 'users', 'sort_users_icon');
    attach('sort_groups_btn', 'sort-folder-groups', 'groups', 'sort_groups_icon');
    attach('sort_reports_btn', 'sort-folder-reports', 'reports', 'sort_reports_icon');
})();
</script>
@endpush
