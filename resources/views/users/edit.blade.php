@extends('adminlte::page')

@section('title', __('users.page.edit'))

@section('content_header')
    @php
        /** @var \App\Models\User $user */
        $isEntra = method_exists($user, 'isEntra')
            ? $user->isEntra()
            : (($user->auth_provider ?? null) === 'entra');
    @endphp

    {{-- Header with title and back button --}}
    <x-page-header :title="__('users.page.edit')" icon="fas fa-user-edit">
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
    @if (session('error'))
        <x-adminlte-alert theme="danger" title="{{ __('users.alerts.error') }}" dismissable class="mb-2">{{ session('error') }}</x-adminlte-alert>
    @endif
    @if (session('success'))
        <x-adminlte-alert theme="success" title="{{ __('users.alerts.success') }}" dismissable class="mb-2">{{ session('success') }}</x-adminlte-alert>
    @endif

    <form action="{{ route('users.update', ['user' => $user->id]) }}" method="POST" novalidate>
        @csrf
        @method('PUT')

        {{-- Card 1: User Details --}}
        <x-adminlte-card title="{{ __('users.page.edit') }}" icon="fas fa-user-edit text-lightblue"
                         theme="primary" theme-mode="outline" class="mt-3" body-class="pb-2">

            {{-- Name --}}
            <div class="form-group">
                <label for="name">{{ __('users.form.name') }}</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}"
                    @if($isEntra) readonly @endif
                    required
                >
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label for="email">{{ __('users.form.email') }}</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}"
                    @if($isEntra) readonly @endif
                    required
                >
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Password (hidden if Entra) --}}
            @if(!$isEntra)
                <div class="form-group">
                    <label for="password">{{ __('users.form.password') }}
                        <small class="text-muted">{{ __('users.form.password_hint') }}</small>
                    </label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        autocomplete="new-password"
                    >
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @else
                <div class="alert alert-info d-flex align-items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    <div>{!! __('users.alerts.entra_managed_html') !!}</div>
                </div>
            @endif

            {{-- Role --}}
            <div class="form-group">
                <label for="role">{{ __('users.form.role') }}</label>
                <select id="role" name="role" class="custom-select @error('role') is-invalid @enderror" required>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" @selected(old('role', $user->roles->pluck('name')->first()) === $r)>
                            {{ ucfirst($r) }}
                        </option>
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Extra info (only Entra) --}}
            @if($isEntra)
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="mb-0 text-muted small">{{ __('users.form.provider') }}</label>
                        <div>{{ __('users.form.entra_label') }}</div>
                    </div>
                    @if(!empty($user->external_id))
                        <div class="form-group col-md-6">
                            <label class="mb-0 text-muted small">{{ __('users.form.object_id') }}</label>
                            <div class="text-monospace">{{ $user->external_id }}</div>
                        </div>
                    @endif
                </div>
            @endif
        </x-adminlte-card>

        {{-- Card 2: Action Buttons --}}
        <x-adminlte-card theme="primary" theme-mode="outline" class="mt-3">
            <div class="d-flex justify-content-center align-items-center">
                <div class="actions-group">
                    {{-- Save first --}}
                    <x-adminlte-button type="submit" theme="primary" label="{{ __('users.form.buttons.save') }}" icon="fas fa-save" class="btn-eq action-btn"/>
                    {{-- Cancel goes back --}}
                    <button type="button" onclick="history.back()" class="btn btn-secondary action-btn">
                        <i class="fas fa-times mr-1"></i>{{ __('users.form.buttons.cancel') }}
                    </button>
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
