@extends('adminlte::page')

@section('title', __('folders.page.create'))

@section('content_header')
    <x-page-header :title="__('folders.page.create')" icon="fas fa-folder-plus">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('folders.index') }}" class="btn btn-secondary btn-sm header-btn">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('folders.form.buttons.back') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    {{-- Important: disable browser validation bubbles --}}
    <form action="{{ route('folders.store') }}" method="POST" novalidate>
        @csrf

        {{-- CARD 1: Folder details --}}
        <x-adminlte-card title="{{ __('folders.cards.details.title') }}" icon="fas fa-folder text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row">
                {{-- Name (required) --}}
                <div class="col-md-6 mb-3">
                    <x-adminlte-input name="name"
                        label="{{ __('folders.form.name_label') }}"
                        placeholder="{{ __('folders.form.name_placeholder') }}"
                        value="{{ old('name') }}"
                        required
                        class="@error('name') is-invalid @enderror"/>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Description (optional) --}}
                <div class="col-md-6 mb-3">
                    <x-adminlte-textarea name="description"
                        label="{{ __('folders.form.description_label') }}"
                        rows="1"
                        placeholder="{{ __('folders.form.description_placeholder') }}"
                        class="@error('description') is-invalid @enderror"
                        data-no-required="true">{{ old('description') }}</x-adminlte-textarea>
                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </x-adminlte-card>

        {{-- CARD 2: Assignments --}}
        <x-adminlte-card title="{{ __('folders.cards.assignments.title') }}" icon="fas fa-users text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row g-3">

                {{-- Users --}}
                <div class="col-md-4 d-flex flex-column">
                    <label class="fw-semibold mb-2">{{ __('folders.form.users_label') }}</label>
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
                            <p class="text-muted small mb-0">{{ __('folders.empty.users') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Groups --}}
                <div class="col-md-4 d-flex flex-column">
                    <label class="fw-semibold mb-2">{{ __('folders.form.groups_label') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($groups as $group)
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" name="groups[]"
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
                    <label class="fw-semibold mb-2">{{ __('folders.form.reports_label') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($reports as $report)
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" name="reports[]"
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
        </x-adminlte-card>

        {{-- CARD 3: Actions --}}
        <x-adminlte-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="d-flex gap-3" style="width: 320px;">
                    <x-adminlte-button type="submit" theme="primary"
                        label="{{ __('folders.form.buttons.save') }}" icon="fas fa-save" class="btn-eq"/>
                    <x-adminlte-button :href="route('folders.index')" theme="secondary"
                        label="{{ __('folders.form.buttons.cancel') }}" icon="fas fa-times" class="btn-eq"/>
                </div>
            </div>
        </x-adminlte-card>
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
    background-color: #f8f9fa;
}
.custom-control { margin-bottom: 0.4rem; }
.custom-control-label { font-size: 0.9rem; }

/* Inputs and textarea same height */
input.form-control,
textarea.form-control {
    border: 1px solid #ced4da !important;
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

/* Focus style */
input:focus,
textarea:focus {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.1rem rgba(0,123,255,.25) !important;
}
</style>
@endpush
