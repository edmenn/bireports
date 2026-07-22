@extends('layouts.app')

@section('title', __('roles.page.edit'))

@section('content_header')
    <x-page-header :title="__('roles.page.edit')" icon="fas fa-user-shield">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm header-btn">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('roles.form.buttons.back') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    {{-- Alerts --}}
    @if (session('success'))
        <x-ui-alert theme="success" title="{{ __('roles.alerts.success') }}" dismissable class="mb-2">
            {{ session('success') }}
        </x-ui-alert>
    @endif
    @if (session('warning'))
        <x-ui-alert theme="warning" title="{{ __('roles.alerts.warning') }}" dismissable class="mb-2">
            {{ session('warning') }}
        </x-ui-alert>
    @endif
    @if (session('error'))
        <x-ui-alert theme="danger" title="{{ __('roles.alerts.error') }}" dismissable class="mb-2">
            {{ session('error') }}
        </x-ui-alert>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <x-ui-alert theme="danger" title="{{ __('roles.alerts.error') }}" dismissable>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui-alert>
    @endif

    <form method="POST" action="{{ route('roles.update', $role) }}" novalidate>
        @csrf
        @method('PUT')

        {{-- Card 1: Role details --}}
        <x-ui-card title="{{ __('roles.form.card_title') }}" icon="fas fa-id-badge text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row">
                {{-- Name --}}
                <div class="col-md-6 mb-3">
                    <label for="name">{{ __('roles.form.name_label') }}</label>
                    <input type="text" name="name" id="name"
                           class="form-control form-control-sm @error('name') is-invalid @enderror"
                           value="{{ old('name', $role->name) }}" required maxlength="150">
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Badge background --}}
                <div class="col-md-3 mb-3">
                    <label for="badge_bg">{{ __('roles.form.badge_bg_label') }}</label>
                    <input type="color" name="badge_bg" id="badge_bg"
                           class="form-control form-control-sm"
                           value="{{ old('badge_bg', $role->badge_bg ?: '#eef2ff') }}">
                </div>

                {{-- Badge text --}}
                <div class="col-md-3 mb-3">
                    <label for="badge_text">{{ __('roles.form.badge_text_label') }}</label>
                    <input type="color" name="badge_text" id="badge_text"
                           class="form-control form-control-sm"
                           value="{{ old('badge_text', $role->badge_text ?: '#4338ca') }}">
                </div>
            </div>
        </x-ui-card>

        {{-- Card 2: Permissions --}}
        @php $sel = old('permissions', $current ?? []); @endphp
        @if(!empty($isAdmin))
            <x-ui-alert theme="info" title="{{ __('roles.alerts.warning') }}" class="mb-2">
                {!! __('roles.alerts.protected') !!}
            </x-ui-alert>
            @include('roles._permissions', ['sections' => $sections, 'current' => $sel, 'disableInputs' => true])
        @else
            @include('roles._permissions', ['sections' => $sections, 'current' => $sel])
        @endif

        {{-- Card 3: Action buttons --}}
        <x-ui-card theme="primary" theme-mode="outline" class="mt-3">
            <div class="d-flex justify-content-center align-items-center">
                <div class="actions-group">
                    {{-- Save --}}
                    <x-ui-button type="submit" theme="primary"
                        label="{{ __('roles.form.buttons.save') }}" icon="fas fa-save" class="btn-eq action-btn"/>
                    {{-- Cancel --}}
                    <button type="button" onclick="history.back()" class="btn btn-secondary btn-eq action-btn">
                        <i class="fas fa-times mr-1"></i>{{ __('roles.form.buttons.cancel') }}
                    </button>
                    {{-- Delete --}}
                    @if (strtolower($role->name) !== 'admin')
                        <button type="submit" form="roleDeleteForm" class="btn btn-danger btn-eq action-btn"
                                onclick="return confirm('{{ __('roles.index.delete_confirm', ['name' => $role->name]) }}');">
                            <i class="fas fa-trash mr-1"></i>{{ __('roles.form.buttons.delete') }}
                        </button>
                    @endif
                </div>
            </div>
        </x-ui-card>
    </form>

    {{-- Hidden delete form --}}
    @if (strtolower($role->name) !== 'admin')
        <form id="roleDeleteForm" action="{{ route('roles.destroy', $role) }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endif
@stop

@push('css')
<style>
/* ===== Header actions ===== */
.header-actions {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .5rem;
}
.header-actions a.btn,
.header-actions button.btn {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    height: 38px !important; line-height: 1 !important; padding: 0 .9rem !important; font-size: .875rem !important;
}
.header-actions i { font-size: .9rem; margin-right: .4rem; }

/* ===== Compact form inputs ===== */
.form-control, .custom-select {
    height: calc(2rem + 2px) !important;
    padding: .35rem .6rem !important;
    font-size: .875rem !important;
    line-height: 1.2 !important;
}

/* ===== Permissions checkboxes ===== */
.checkbox-list-container {
    max-height: 300px;
    overflow-y: auto;
    background-color: #f8f9fa;
    border-radius: .375rem;
}
.custom-control { margin-bottom: .4rem; }

/* ===== TRUE CENTER for Select all / Unselect all ===== */
.permissions-section-actions {
    display: grid !important;
    place-items: center !important;
    width: 100% !important;
    margin-bottom: 1rem !important;
    margin-top: .25rem !important;
    padding: 0 !important;
}
.permissions-section-actions .btn {
    height: 30px !important;
    font-size: .8rem !important;
    font-weight: 500 !important;
    padding: 0 .8rem !important;
    border-radius: 6px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
}
.permissions-section-actions .btn i {
    font-size: .75rem !important;
    margin-right: .25rem !important;
}

/* ===== Equal action buttons (Save / Cancel / Delete) ===== */
.actions-group {
    display: flex; flex-wrap: wrap; justify-content: center; gap: .75rem;
}
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
.btn-eq i { font-size: .9rem; margin-right: .35rem; }

/* ===== Focus styles ===== */
input.form-control:focus,
textarea.form-control:focus {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.1rem rgba(0,123,255,.25) !important;
}
</style>
@endpush
