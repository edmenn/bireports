@extends('layouts.app')

@section('title', __('groups.page.create'))

@section('content_header')
    <x-page-header :title="__('groups.page.create')" icon="fas fa-users">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('groups.index') }}" class="btn btn-secondary btn-sm header-btn">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('groups.form.buttons.back') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    {{-- Disable browser native validation bubbles --}}
    <form action="{{ route('groups.store') }}" method="POST" novalidate>
        @csrf

        {{-- CARD 1: Group details --}}
        <x-ui-card title="{{ __('groups.cards.details.title') }}" icon="fas fa-users text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row">
                {{-- Name --}}
                <div class="col-md-6 mb-3">
                    <x-ui-input name="name"
                        label="{{ __('groups.form.name_label') }}"
                        placeholder="{{ __('groups.form.name_placeholder') }}"
                        value="{{ old('name') }}"
                        required
                        class="@error('name') is-invalid @enderror"/>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Description --}}
                <div class="col-md-6 mb-3">
                    <x-ui-textarea name="description"
                        label="{{ __('groups.form.description_label') }}"
                        rows="1"
                        placeholder="{{ __('groups.form.description_placeholder') }}"
                        class="@error('description') is-invalid @enderror"
                        data-no-required="true">{{ old('description') }}</x-ui-textarea>
                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </x-ui-card>

        {{-- CARD 2: Assignments (Users) --}}
        <x-ui-card title="{{ __('groups.cards.assignments.title') }}" icon="fas fa-user-check text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row g-3">
                <div class="col-md-12 d-flex flex-column">
                    {{-- HEADER CORREGIDO: pl-3 para alinear con la lista, d-flex para centrar check/titulo --}}
                    <div class="assignments-header mb-2 py-2 pl-3 bg-light border rounded d-flex align-items-center">
                        <div class="custom-control custom-checkbox m-0">
                            <input type="checkbox" class="custom-control-input js-check-all" id="chk_all_users"
                                   data-target-class="assign-users" aria-label="{{ __('groups.form.select_all_users') }}">
                            <label class="custom-control-label selectall-label" for="chk_all_users"></label>
                        </div>
                        <button type="button" class="sort-toggle ml-2 d-flex align-items-center text-dark text-decoration-none" id="sort_group_users_btn" data-sort-class="sort-group-users" aria-label="{{ __('groups.form.sort_alpha') }}">
                            <span class="fw-bold">{{ __('groups.form.users_label') }}</span>
                            <i class="fas fa-sort-alpha-down ml-2" id="sort_group_users_icon"></i>
                        </button>
                    </div>

                    @php
                        $usersSorted = $users->sortBy(fn($u) => mb_strtolower($u->name ?? ''));
                    @endphp
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($usersSorted as $user)
                            <div class="custom-control custom-checkbox mb-2 js-sort-item sort-group-users" data-sort-name="{{ mb_strtolower($user->name ?? '') }}">
                                <input type="checkbox" class="custom-control-input assign-users" name="users[]"
                                       value="{{ $user->id }}" id="user_{{ $user->id }}"
                                       @checked(in_array($user->id, old('users', [])))>
                                <label class="custom-control-label" for="user_{{ $user->id }}">
                                    {{ $user->name }} <small class="text-muted">&lt;{{ $user->email }}&gt;</small>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">{{ __('groups.empty.users') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-ui-card>

        {{-- CARD 3: Actions --}}
        <x-ui-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="form-actions w-100">
                    {{-- Save first --}}
                    <x-ui-button type="submit" theme="primary"
                        label="{{ __('groups.form.buttons.save') }}" icon="fas fa-save" class="btn-eq"/>
                    {{-- Cancel goes back --}}
                    <button type="button" onclick="history.back()" class="btn btn-secondary btn-eq">
                        <i class="fas fa-times mr-1"></i>{{ __('groups.form.buttons.cancel') }}
                    </button>
                </div>
            </div>
        </x-ui-card>
    </form>
@stop

@push('css')
<style>
/* ===== Header actions ===== */
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

/* ===== Checkbox containers ===== */
.checkbox-list-container {
    max-height: 300px;
    overflow-y: auto;
    background-color: var(--app-card-alt-bg, #f8f9fa);
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
/* 3. Estilo del título */
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
.selectall-label{padding:0; margin:0; width:100%; height:1rem; display:inline-block;}


/* ===== Inputs and textarea visual consistency ===== */
input.form-control,
textarea.form-control {
    border: 1px solid var(--app-input-border, #ced4da) !important;
    border-radius: .375rem !important;
    padding: .5rem .75rem !important;
    box-shadow: none !important;
    height: 42px !important;
}
textarea.form-control { resize: none !important; }

/* ===== Disable browser native validation visuals ===== */
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

/* ===== Focus style ===== */
input:focus,
textarea:focus {
    border-color: var(--app-btn-bg, #007bff) !important;
    box-shadow: 0 0 0 0.1rem rgba(0,123,255,.25) !important;
    box-shadow: 0 0 0 0.1rem color-mix(in srgb, var(--app-btn-bg, #007bff) 25%, transparent) !important;
}

/* ===== Equal action buttons ===== */
.btn-eq {
    height: 38px !important;
    min-width: 140px;
    font-size: .875rem;
    font-weight: 600;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    line-height: 1 !important;
}
.btn-eq i {
    font-size: .9rem;
    margin-right: .35rem;
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
            if (!enabled.length) { master.checked = false; master.indeterminate = false; return; }
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
    var btn = document.getElementById('sort_group_users_btn');
    var icon = document.getElementById('sort_group_users_icon');
    var state = 'asc';
    if (!btn) return;
    function sortList(){
        var items = Array.prototype.slice.call(document.querySelectorAll('.js-sort-item.sort-group-users'));
        if (!items.length) return;
        var container = items[0].parentElement;
        items.sort(function(a,b){
            var ta = (a.getAttribute('data-sort-name') || '').toLowerCase();
            var tb = (b.getAttribute('data-sort-name') || '').toLowerCase();
            if (ta === tb) return 0;
            var cmp = ta < tb ? -1 : 1;
            return state === 'desc' ? -cmp : cmp;
        });
        items.forEach(function(it){ container.appendChild(it); });
        if (icon){
            icon.className = state === 'desc' ? 'fas fa-sort-alpha-up ml-2' : 'fas fa-sort-alpha-down ml-2';
        }
    }
    btn.addEventListener('click', function(){
        state = state === 'desc' ? 'asc' : 'desc';
        sortList();
    });
})();
</script>
@endpush
