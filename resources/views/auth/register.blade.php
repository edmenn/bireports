@extends('layouts.auth')

@section('title', __('Register'))

@section('auth_header','')

@php($appCfg = \App\Models\Configuration::find(1))

@push('css')
<style>
    /* ======================================== */
    /* Background                               */
    /* ======================================== */
    body.login-page {
        min-height: 100vh;
        position: relative;
        background:
            linear-gradient(180deg, rgba(2,6,23,.35), rgba(2,6,23,.45)),
            url('{{ optional($appCfg)->login_bg_path ? Storage::disk('public')->url($appCfg->login_bg_path) : '' }}') center/cover no-repeat fixed;
    }
    body.login-page::before {
        content:""; position:fixed; inset:0;
        background: linear-gradient(135deg, rgba(79,70,229,.25), rgba(14,165,233,.15));
        pointer-events:none;
    }

    /* ======================================== */
    /* Card layout                              */
    /* ======================================== */
    .login-box { max-width: 380px; width: 100%; }
    .login-logo { display: none !important; }

    .login-card {
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(148,163,184,.22);
        border-radius: 14px;
        box-shadow: 0 10px 40px rgba(2,6,23,.18);
    }

    /* ======================================== */
    /* Unified input border                     */
    /* ======================================== */
    .login-card .input-group {
        border: 1px solid #ced4da;
        border-radius: .5rem;
        overflow: hidden;
    }
    .login-card .input-group:focus-within {
        border-color: #93c5fd;
        box-shadow: 0 0 0 0.15rem rgba(147,197,253,.25);
    }
    .login-card .input-group-text {
        background: #f8fafc;
        border: none;
    }
    .login-card .form-control {
        border: none;
        box-shadow: none;
    }
    .login-card .form-control:focus {
        outline: none;
        box-shadow: none;
    }

    /* ======================================== */
    /* Buttons                                  */
    /* ======================================== */
    .btn-brand {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: .5rem;
        padding: .6rem;
        font-weight: 500;
    }
    .btn-brand:hover {
        background: #1d4ed8;
        color: #fff;
    }

    /* ======================================== */
    /* Logo inside card                         */
    /* ======================================== */
    .brand-logo {
        display: block;
        margin: 0 auto .75rem;
        max-height: 48px;
        object-fit: contain;
    }
</style>
@endpush

@section('auth_body')
    @php($appCfg = \App\Models\Configuration::find(1))

    <div class="login-card p-3 p-md-4">
        <div class="text-center mb-3">
            @if($appCfg && $appCfg->login_logo_path)
                <img src="{{ Storage::disk('public')->url($appCfg->login_logo_path) }}" alt="Logo" class="brand-logo">
            @elseif($appCfg && $appCfg->site_logo_path)
                <img src="{{ Storage::disk('public')->url($appCfg->site_logo_path) }}" alt="Logo" class="brand-logo">
            @endif
        </div>

        <h5 class="text-center mb-3">{{ __('Register') }}</h5>
        <p class="text-muted small text-center mb-4">
            {{ __('Create your account to get started.') }}
        </p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Name --}}
            <div class="mb-3">
                <label for="name" class="form-label small text-muted mb-1">{{ __('Name') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input id="name" type="text" name="name" class="form-control"
                           value="{{ old('name') }}" placeholder="{{ __('Your name') }}"
                           required autofocus autocomplete="name">
                </div>
                @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label for="email" class="form-label small text-muted mb-1">{{ __('Email') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input id="email" type="email" name="email" class="form-control"
                           value="{{ old('email') }}" placeholder="you@company.com"
                           required autocomplete="username">
                </div>
                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label for="password" class="form-label small text-muted mb-1">{{ __('Password') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input id="password" type="password" name="password" class="form-control"
                           placeholder="********" required autocomplete="new-password">
                </div>
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-3">
                <label for="password_confirmation" class="form-label small text-muted mb-1">{{ __('Confirm Password') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control"
                           placeholder="********" required autocomplete="new-password">
                </div>
                @error('password_confirmation')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-brand w-100">
                {{ __('Register') }}
            </button>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="small">
                    <i class="fas fa-arrow-left me-1"></i> {{ __('Already registered?') }}
                </a>
            </div>
        </form>
    </div>
@endsection

@section('auth_footer') @endsection
