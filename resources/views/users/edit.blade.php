@extends('adminlte::page')

@section('title', __('users.page.edit'))

@section('content_header')
    @php
        /** @var \App\Models\User $user */
        $isEntra = method_exists($user, 'isEntra')
            ? $user->isEntra()
            : (($user->auth_provider ?? null) === 'entra');
    @endphp

    <div class="d-flex align-items-center">
        <h1 class="mr-3 mb-0">{{ __('users.page.edit') }}</h1>
        @if($isEntra)
            <span class="badge badge-info">{{ __('users.index.badges.entra') }}</span>
        @endif
    </div>
@stop

@section('content')
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.update', ['user' => $user->id]) }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                {{-- Nombre --}}
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

                {{-- Contraseña (oculta si es Entra) --}}
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
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        {!! __('users.alerts.entra_managed_html') !!}
                    </div>
                @endif

                {{-- Rol --}}
                <div class="form-group">
                    <label for="role">{{ __('users.form.role') }}</label>
                    <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" @selected(old('role', $user->roles->pluck('name')->first()) === $r)>
                                {{ ucfirst($r) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Info adicional (solo Entra) --}}
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

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">{{ __('users.form.buttons.save') }}</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('users.form.buttons.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@stop
