@extends('adminlte::page')

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
        <x-adminlte-card title="{{ __('groups.cards.details.title') }}" icon="fas fa-users text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row">
                {{-- Name --}}
                <div class="col-md-6 mb-3">
                    <x-adminlte-input name="name"
                        label="{{ __('groups.form.name_label') }}"
                        placeholder="{{ __('groups.form.name_placeholder') }}"
                        value="{{ old('name') }}"
                        required
                        class="@error('name') is-invalid @enderror"/>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Description --}}
                <div class="col-md-6 mb-3">
                    <x-adminlte-textarea name="description"
                        label="{{ __('groups.form.description_label') }}"
                        rows="1"
                        placeholder="{{ __('groups.form.description_placeholder') }}"
                        class="@error('description') is-invalid @enderror"
                        data-no-required="true">{{ old('description') }}</x-adminlte-textarea>
                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </x-adminlte-card>

        {{-- CARD 2: Assignments (Users) --}}
        <x-adminlte-card title="{{ __('groups.cards.assignments.title') }}" icon="fas fa-user-check text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row g-3">
                <div class="col-md-12 d-flex flex-column">
                    <label class="fw-semibold mb-2">{{ __('groups.form.users_label') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($users as $user)
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" name="users[]"
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
        </x-adminlte-card>

        {{-- CARD 3: Actions --}}
        <x-adminlte-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="d-flex gap-3" style="width: 320px;">
                    {{-- Save first --}}
                    <x-adminlte-button type="submit" theme="primary"
                        label="{{ __('groups.form.buttons.save') }}" icon="fas fa-save" class="btn-eq"/>
                    {{-- Cancel goes back --}}
                    <button type="button" onclick="history.back()" class="btn btn-secondary btn-eq">
                        <i class="fas fa-times mr-1"></i>{{ __('groups.form.buttons.cancel') }}
                    </button>
                </div>
            </div>
        </x-adminlte-card>
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
    background-color: #f8f9fa;
}
.custom-control { margin-bottom: 0.4rem; }
.custom-control-label { font-size: 0.9rem; }

/* ===== Inputs and textarea visual consistency ===== */
input.form-control,
textarea.form-control {
    border: 1px solid #ced4da !important;
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
    border-color: #ced4da !important;
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
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.1rem rgba(0,123,255,.25) !important;
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
