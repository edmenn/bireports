@extends('layouts.auth')

@section('title', __('Log in'))

@section('auth_header','')

@php($appCfg = \App\Models\Configuration::find(1))

@push('css')
    <style>
        body.login-page{
            min-height: 100vh;
            position: relative;
            background:
                linear-gradient(180deg, rgba(2,6,23,.35), rgba(2,6,23,.45)),
                url('{{ optional($appCfg)->login_bg_path ? Storage::disk('public')->url($appCfg->login_bg_path) : '' }}') center/cover no-repeat fixed;
        }
        /* Capa de refuerzo por si no hay imagen */
        body.login-page::before{
            content:""; position:fixed; inset:0;
            background: linear-gradient(135deg, rgba(79,70,229,.25), rgba(14,165,233,.15));
            pointer-events:none;
        }
        /* Caja del formulario */
        .login-card{
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            background: rgba(255,255,255,.86);
            border: 1px solid rgba(148,163,184,.22);
            border-radius: 14px;
            box-shadow: 0 10px 40px rgba(2,6,23,.18);
        }

        /* Input group unified border */
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

        .btn-brand{
            background: #2563eb; color:#fff; border: 0;
        }
        .btn-brand:hover{ background:#1d4ed8; color:#fff; }
        .btn-microsoft{
            border-color:#2563eb;
        }

        /* Centrado y ancho cómodo */
        .login-box{ max-width: 380px; width: 100%; }
        .login-logo{ display:none; }

@php($loginLogoW = max(16, (int) ($theme['login_logo_width'] ?? 180)))
@php($loginLogoH = max(16, (int) ($theme['login_logo_height'] ?? 64)))

        .brand-logo{
@if($loginLogoW > 0)
            max-width: {{ $loginLogoW }}px;
@endif
@if($loginLogoH > 0)
            max-height: {{ $loginLogoH }}px;
@else
            max-height: 48px;
@endif
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
    </style>
@endpush

@section('auth_body')
    @php($appCfg = \App\Models\Configuration::find(1))

    <div class="login-card p-3 p-md-4">
        <div class="text-center mb-2">
            @if($appCfg && $appCfg->login_logo_path)
                <img src="{{ Storage::disk('public')->url($appCfg->login_logo_path) }}" alt="{{ __('auth.logo_alt') }}" class="mb-1 brand-logo">
            @elseif($appCfg && $appCfg->site_logo_path)
                <img src="{{ Storage::disk('public')->url($appCfg->site_logo_path) }}" alt="Logo" class="mb-1 brand-logo">
            @endif
        </div>

        @if (session('status'))
            <div class="alert alert-warning small">{{ session('status') }}</div>
        @endif

        @if($allowLocal)
        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label for="email" class="form-label small text-muted mb-1">{{ __('auth.email') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input id="email" type="email" name="email" class="form-control"
                           value="{{ old('email') }}" placeholder="{{ __('auth.email_placeholder') }}"
                           required autofocus autocomplete="username">
                </div>
                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label for="password" class="form-label small text-muted mb-1">{{ __('auth.password_label') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input id="password" type="password" name="password" class="form-control"
                           placeholder="********" required autocomplete="current-password">
                </div>
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">{{ __('auth.remember') }}</label>
                </div>
                @if (Route::has('password.request'))
                    <a class="small" href="{{ route('password.request') }}">{{ __('auth.forgot_password') }}</a>
                @endif
            </div>

            <button type="submit" class="btn btn-brand w-100">
                {{ __('auth.login') }}
            </button>
        </form>
        @endif

        @if($allowMicrosoft)
            @if($allowLocal)
                <div class="text-center my-3 text-muted small">{{ __('auth.or') }}</div>
            @endif
            <a href="{{ route('auth.microsoft.redirect') }}" class="btn btn-outline-primary w-100 btn-microsoft">
                <i class="fab fa-microsoft me-1"></i> {{ __('auth.continue_microsoft') }}
            </a>
        @endif
    </div>
@endsection

@section('auth_footer') @endsection
