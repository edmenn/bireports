@extends('adminlte::page')

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
        <x-adminlte-card title="{{ __('folders.cards.details.title') }}" icon="fas fa-folder text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <x-adminlte-input
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
                    <x-adminlte-textarea
                        name="description"
                        label="{{ __('folders.form.description_label') }}"
                        rows="1"
                        placeholder="{{ __('folders.form.description_placeholder') }}"
                        class="no-validation @error('description') is-invalid @enderror"
                    >{{ old('description', $folder->description) }}</x-adminlte-textarea>
                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </x-adminlte-card>

        {{-- Card: Assignments --}}
        <x-adminlte-card title="{{ __('folders.cards.assignments.title') }}" icon="fas fa-users text-lightblue"
                         theme="primary" theme-mode="outline" class="mb-3">
            <div class="row g-3">
                <div class="col-md-4 d-flex flex-column">
                    <label class="fw-semibold mb-2">{{ __('folders.form.users_label') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($users as $user)
                            <div class="custom-control custom-checkbox mb-2">
                                <input class="custom-control-input" type="checkbox" name="users[]"
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

                <div class="col-md-4 d-flex flex-column">
                    <label class="fw-semibold mb-2">{{ __('folders.form.groups_label') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($groups as $group)
                            <div class="custom-control custom-checkbox mb-2">
                                <input class="custom-control-input" type="checkbox" name="groups[]"
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

                <div class="col-md-4 d-flex flex-column">
                    <label class="fw-semibold mb-2">{{ __('folders.form.reports_label') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1">
                        @forelse($reports as $report)
                            <div class="custom-control custom-checkbox mb-2">
                                <input class="custom-control-input" type="checkbox" name="reports[]"
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
        </x-adminlte-card>

        {{-- Card: Actions --}}
        <x-adminlte-card theme="primary" theme-mode="outline">
            <div class="d-flex justify-content-center">
                <div class="d-flex" style="gap: .75rem; width: 320px;">
                    <x-adminlte-button type="submit" theme="primary" label="{{ __('folders.form.buttons.save') }}" icon="fas fa-save" class="btn-eq"/>
                    <x-adminlte-button :href="route('folders.index')" theme="secondary" label="{{ __('folders.form.buttons.cancel') }}" icon="fas fa-times" class="btn-eq"/>
                </div>
            </div>
        </x-adminlte-card>
    </form>
@stop

@push('css')
<style>
/* Header buttons */
.header-actions{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:.5rem;}
.header-actions .header-btn{display:inline-flex;align-items:center;justify-content:center;height:38px;line-height:1;padding:0 .9rem;font-size:.875rem;}
.header-actions .header-btn i{font-size:.9rem;margin-right:.4rem;}

/* Inputs/textarea same height and clean look */
input.form-control, textarea.form-control{border:1px solid #ced4da!important;border-radius:.375rem!important;padding:.5rem .75rem!important;box-shadow:none!important;height:42px!important;resize:none!important;}
input:focus, textarea:focus{border-color:#007bff!important;box-shadow:0 0 0 .1rem rgba(0,123,255,.25)!important;}

/* Remove native validation icon specifically for the optional textarea */
textarea.no-validation:invalid,
textarea.no-validation:required,
textarea.no-validation:focus:invalid{box-shadow:none!important;border-color:#ced4da!important;outline:none!important;}
textarea.no-validation::-webkit-validation-bubble,
textarea.no-validation::-webkit-validation-bubble-message,
textarea.no-validation::-webkit-validation-bubble-arrow{display:none!important}

/* Checkbox list container */
.checkbox-list-container{max-height:300px;overflow-y:auto;background:#f8f9fa;}
.custom-control{margin-bottom:.4rem;}
.custom-control-label{font-size:.9rem;}
</style>
@endpush
