@extends('adminlte::page')

@section('title', __('users.page.create'))

@section('content_header')
    {{-- Header with centered title and consistent back button --}}
    <x-page-header :title="__('users.page.create')" icon="fas fa-user-plus">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm header-btn">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('users.form.buttons.back') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    {{-- Flash messages --}}
    @if (session('success'))
        <x-adminlte-alert theme="success" title="{{ __('users.alerts.success') }}" dismissable class="mb-2">{{ session('success') }}</x-adminlte-alert>
    @endif
    @if (session('error'))
        <x-adminlte-alert theme="danger" title="{{ __('users.alerts.error') }}" dismissable class="mb-2">{{ session('error') }}</x-adminlte-alert>
    @endif

    <x-adminlte-card title="{{ __('users.page.create') }}" icon="fas fa-user-plus text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3">

        <form action="{{ route('users.store') }}" method="POST" novalidate>
            @csrf

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

            {{-- Buttons --}}
            <div class="d-flex justify-content-end flex-wrap gap-2 mt-3">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-save mr-1"></i>{{ __('users.form.buttons.save') }}
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times mr-1"></i>{{ __('users.form.buttons.cancel') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>
@stop

@push('css')
<style>
/* --- Header alignment and buttons --- */
.header-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: .5rem;
}

.header-actions a.btn,
.header-actions button.btn,
.header-actions form button.btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 38px !important;
    line-height: 1 !important;
    padding: 0 .9rem !important;
    font-size: .875rem !important;
    margin: 0 !important;
}

.header-actions i {
    font-size: .9rem;
    margin-right: .4rem;
}

.header-actions form {
    margin: 0 !important;
    padding: 0 !important;
    display: flex !important;
    align-items: center !important;
}

/* --- Header button consistency --- */
.header-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 36px !important;
    min-width: 120px;
    padding: 0 .75rem;
    font-size: .85rem;
    line-height: 1.1 !important;
    white-space: nowrap;
}
.header-btn i { font-size: .9rem; margin-right: .35rem; }

@media (max-width: 992px){
    .header-actions { justify-content: center !important; }
    .header-btn { min-width: 110px; height: 34px !important; font-size: .8rem; padding: 0 .6rem; }
}

/* --- Form compact style --- */
.form-control, .custom-select {
    height: calc(2.2rem + 2px);
    padding: .35rem .6rem;
    font-size: .9rem;
}

/* --- Buttons at form bottom --- */
.btn {
    min-width: 120px;
    height: 38px;
    font-size: .875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn i { font-size: .9rem; margin-right: .4rem; }
</style>
@endpush
