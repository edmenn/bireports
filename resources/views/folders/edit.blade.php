@extends('layouts.app')

@section('title', __('folders.page.edit'))

@section('content_header')
    <x-page-header :title="__('folders.page.edit')" icon="fas fa-folder-open">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('folders.index') }}" class="btn btn-secondary btn-sm header-btn">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('folders.form.buttons.back') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    <form action="{{ route('folders.update', ['folder' => $folder->id]) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        {{-- Card: Folder details --}}
        <x-ui-card title="{{ __('folders.cards.details.title') }}" icon="fas fa-folder text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <x-ui-input
                        name="name"
                        label="{{ __('folders.form.name_label') }}"
                        placeholder="{{ __('folders.form.name_placeholder') }}"
                        value="{{ old('name', $folder->name) }}"
                        required
                        class="@error('name') is-invalid @enderror"
                    />
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 mb-3">
                    <x-ui-textarea
                        name="description"
                        label="{{ __('folders.form.description_label') }}"
                        rows="1"
                        placeholder="{{ __('folders.form.description_placeholder') }}"
                        class="no-validation @error('description') is-invalid @enderror"
                    >{{ old('description', $folder->description) }}</x-ui-textarea>
                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </x-ui-card>

        {{-- Card: Assignments --}}
        <x-ui-card title="{{ __('folders.cards.assignments.title') }}" icon="fas fa-users text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row g-3">

                {{-- Users --}}
                <div class="col-md-4 d-flex flex-column">
                    {{-- HEADER CORREGIDO --}}
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
                                <input class="custom-control-input assign-folder-users" type="checkbox" name="users[]"
                                       value="{{ $user->id }}" id="user_{{ $user->id }}"
                                       @checked(in_array($user->id, old('users', $folder->users->pluck('id')->toArray())))>
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
                                <input class="custom-control-input assign-folder-groups" type="checkbox" name="groups[]"
                                       value="{{ $group->id }}" id="group_{{ $group->id }}"
                                       @checked(in_array($group->id, old('groups', $folder->groups->pluck('id')->toArray())))>
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
                                <input class="custom-control-input assign-folder-reports" type="checkbox" name="reports[]"
                                       value="{{ $report->id }}" id="report_{{ $report->id }}"
                                       @checked(in_array($report->id, old('reports', $folder->reports->pluck('id')->toArray())))>
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

        {{-- Card: Actions --}}
        <x-ui-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="form-actions w-100">
                    <x-ui-button type="submit" theme="primary" label="{{ __('folders.form.buttons.save') }}" icon="fas fa-save" class="btn-eq"/>
                    <x-ui-button :href="route('folders.index')" theme="secondary" label="{{ __('folders.form.buttons.cancel') }}" icon="fas fa-times" class="btn-eq"/>
                </div>
            </div>
        </x-ui-card>
    </form>
@stop

@push('css')
<style>
/* Header buttons */
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

/* Inputs/textarea same height and clean look */
input.form-control,
textarea.form-control {
    border: 1px solid var(--app-input-border, #ced4da) !important;
    border-radius: .375rem !important;
    padding: .5rem .75rem !important;
    box-shadow: none !important;
    height: 42px !important;
    resize: none !important;
}
input:focus,
textarea:focus {
    border-color: var(--app-btn-bg, #007bff) !important;
    box-shadow: 0 0 0 .1rem rgba(0,123,255,.25) !important;
    box-shadow: 0 0 0 .1rem color-mix(in srgb, var(--app-btn-bg, #007bff) 25%, transparent) !important;
}

/* Remove native validation icon specifically for the optional textarea */
textarea.no-validation:invalid,
textarea.no-validation:required,
textarea.no-validation:focus:invalid {
    box-shadow: none !important;
    border-color: var(--app-input-border, #ced4da) !important;
    outline: none !important;
}
textarea.no-validation::-webkit-validation-bubble,
textarea.no-validation::-webkit-validation-bubble-message,
textarea.no-validation::-webkit-validation-bubble-arrow {
    display: none !important
}

/* Checkbox list container */
.checkbox-list-container {
    max-height: 300px;
    overflow-y: auto;
    background: var(--app-card-alt-bg, #f8f9fa);
}

/* === ALINEACIÓN CORRECTA DEL HEADER === */

/* 1. Ajuste fino del check */
.assignments-header .custom-control-label::before,
.assignments-header .custom-control-label::after {
    top: 0.2rem;
}
/* 2. Reseteo de márgenes y espacios */
.assignments-header .custom-control {
    min-height: auto;
    padding-left: 1.5rem;
}
.assignments-header .custom-control-label {
    cursor: pointer;
}
/* 3. Estilo del título (Sin color rojo) */
.assignments-header .sort-toggle {
    background: transparent;
    border: 0;
    padding: 0;
    appearance: none;
    line-height: 1.2;
    color: inherit;
    font-weight: 600;
    cursor: pointer;
}
.assignments-header .sort-toggle:hover {
    color: inherit;
}
/* ====================================== */

.custom-control-label { font-size: 0.9rem; }
.selectall-label { padding:0; margin:0; width:100%; height:1rem; display:inline-block; }
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
    var sortStates = {users:'asc', groups:'asc', reports:'asc'};
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
