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
    <form action="{{ route('groups.store') }}" method="POST">
        @csrf

        {{-- Card 1: Group Details --}}
        <x-adminlte-card title="{{ __('groups.cards.details.title') }}" icon="fas fa-users text-lightblue"
                         theme="primary" theme-mode="outline" class="mt-3" body-class="pb-2">
            <div class="row">
                <div class="col-12 mb-3">
                    <x-adminlte-input name="name" label="{{ __('groups.form.name_label') }}" placeholder="{{ __('groups.form.name_placeholder') }}"
                        value="{{ old('name') }}" required
                        class="form-control-sm @error('name') is-invalid @enderror"/>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <x-adminlte-textarea name="description" label="{{ __('groups.form.description_label') }}" rows="3"
                        placeholder="{{ __('groups.form.description_placeholder') }}"
                        class="form-control-sm @error('description') is-invalid @enderror">{{ old('description') }}</x-adminlte-textarea>
                    @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </x-adminlte-card>

        {{-- Card 2: Assignments (Users) --}}
        <x-adminlte-card title="{{ __('groups.cards.assignments.title') }}" icon="fas fa-user-check text-lightblue"
                         theme="primary" theme-mode="outline" class="mt-3" body-class="pb-2">
            <div class="row">
                <div class="col-md-12 d-flex flex-column">
                    <label class="font-weight-semibold mb-2">{{ __('groups.form.users_label') }}</label>
                    <div class="checkbox-list-container p-3 border rounded flex-grow-1 bg-light">
                        @forelse($users as $user)
                            <div class="custom-control custom-checkbox mb-2">
                                <input class="custom-control-input" type="checkbox"
                                       name="users[]" value="{{ $user->id }}" id="user_{{ $user->id }}"
                                       @checked(in_array($user->id, old('users', [])))>
                                <label class="custom-control-label" for="user_{{ $user->id }}">
                                    {{ $user->name }} <small class="text-muted">&lt;{{ $user->email }}&gt;</small>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted mb-0">{{ __('groups.empty.users') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </x-adminlte-card>

        {{-- Card 3: Action Buttons --}}
        <x-adminlte-card theme="primary" theme-mode="outline" class="mt-3">
            <div class="d-flex justify-content-center align-items-center">
                <div class="actions-group">
                    <x-adminlte-button type="submit" theme="primary" label="{{ __('groups.form.buttons.save') }}" icon="fas fa-save" class="btn-eq action-btn"/>
                    <x-adminlte-button :href="route('groups.index')" theme="secondary" label="{{ __('groups.form.buttons.cancel') }}" icon="fas fa-times" class="btn-eq action-btn"/>
                </div>
            </div>
        </x-adminlte-card>
    </form>
@stop

@push('css')
<style>
/* ===== Header buttons ===== */
.header-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: .5rem;
}
.header-actions a.btn,
.header-actions button.btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 38px !important;
    line-height: 1 !important;
    padding: 0 .9rem !important;
    font-size: .875rem !important;
}
.header-actions i {
    font-size: .9rem;
    margin-right: .4rem;
}

/* ===== Checkbox list ===== */
.checkbox-list-container {
    max-height: 300px;
    overflow-y: auto;
    background-color: #f8f9fa;
}
.custom-control { margin-bottom: .5rem; }

/* ===== Equal action buttons ===== */
.actions-group {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: .75rem;
}
.actions-group .action-btn {
    height: 38px !important;
    min-width: 140px;
    font-size: .875rem;
    font-weight: 600;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    line-height: 1 !important;
}
.actions-group .action-btn i {
    font-size: .9rem;
    margin-right: .35rem;
}
</style>
@endpush
