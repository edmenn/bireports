@extends('layouts.app')

@section('title', __('users.page.create'))

@section('content_header')
    <x-page-header :title="__('users.page.create')" icon="fas fa-user-plus" />
@stop

@section('content')
    {{-- Flash messages --}}
    @if (session('success'))
        <x-ui-alert theme="success" title="{{ __('users.alerts.success') }}" dismissable class="mb-2">{{ session('success') }}</x-ui-alert>
    @endif
    @if (session('error'))
        <x-ui-alert theme="danger" title="{{ __('users.alerts.error') }}" dismissable class="mb-2">{{ session('error') }}</x-ui-alert>
    @endif

    <form action="{{ route('users.store') }}" method="POST" novalidate>
        @csrf

        {{-- Card 1: User Details --}}
        <x-ui-card title="{{ __('users.page.create') }}" icon="fas fa-user-plus text-lightblue"
                         theme="primary" theme-mode="outline" class="mt-3" body-class="pb-2">

            {{-- Name --}}
            <div class="form-group">
                <label for="name">{{ __('users.form.name') }}</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label for="email">{{ __('users.form.email') }}</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    required
                >
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label for="password">{{ __('users.form.password') }}</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                >
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Role --}}
            <div class="form-group">
                <label for="role">{{ __('users.form.role') }}</label>
                <select id="role" name="role" class="custom-select @error('role') is-invalid @enderror" required>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" @selected(old('role') === $r)>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </x-ui-card>

        {{-- Card 2: Action Buttons --}}
        <x-ui-card theme="primary" theme-mode="outline" class="mt-3">
            <div class="d-flex justify-content-center align-items-center">
                <div class="actions-group">
                    {{-- Save first --}}
                    <x-ui-button type="submit" theme="primary" label="{{ __('users.form.buttons.save') }}" icon="fas fa-save" class="btn-eq action-btn"/>
                    {{-- Cancel goes back --}}
                    <button type="button" onclick="history.back()" class="btn btn-secondary action-btn">
                        <i class="fas fa-times mr-1"></i>{{ __('users.form.buttons.cancel') }}
                    </button>
                </div>
            </div>
        </x-ui-card>
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

/* ===== Form compact style ===== */
.form-control, .custom-select {
    height: calc(2.2rem + 2px);
    padding: .35rem .6rem;
    font-size: .9rem;
}

/* ===== Equal action buttons (Save / Cancel) ===== */
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
