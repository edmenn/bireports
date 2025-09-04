{{-- resources/views/auth/login.blade.php --}}
@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('title', __('Log in'))

@section('auth_header')
    <div class="text-center w-100">
        @php($appCfg = \App\Models\Configuracion::find(1))
        @if($appCfg && $appCfg->site_logo_path)
            <img src="{{ Storage::disk('public')->url($appCfg->site_logo_path) }}"
                 alt="Logo" style="max-height:48px" class="mb-2">
        @endif
        <h1 class="h5 mb-0">{{ __('Log in') }}</h1>
        <small class="text-muted">{{ config('app.name', 'PowerBIDash') }}</small>
    </div>
@endsection

{{-- Estilos del fondo + card (compatibles AdminLTE 4 / BS5) --}}
@section('adminlte_css')
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
        .login-card .input-group-text{
            background: #f8fafc;
            border-right: 0;
        }
        .login-card .form-control{
            border-left: 0;
        }
        .login-card .form-control:focus{
            box-shadow: none;
            border-color: #93c5fd;
        }
        .btn-brand{
            background: #2563eb; color:#fff; border: 0;
        }
        .btn-brand:hover{ background:#1d4ed8; color:#fff; }
        .btn-microsoft{
            border-color:#2563eb;
        }
        /* Centrado y ancho cómodo */
        .login-box{ max-width: 420px; width: 100%; }
    </style>
@endsection

@section('auth_body')
    <div class="login-card p-3 p-md-4">
        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label for="email" class="form-label small text-muted mb-1">{{ __('Email') }}</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input id="email" type="email" name="email" class="form-control"
                           value="{{ old('email') }}" placeholder="you@company.com"
                           required autofocus autocomplete="username">
                </div>
                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label for="password" class="form-label small text-muted mb-1">{{ __('Password') }}</label>
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
                    <label class="form-check-label small" for="remember">{{ __('Remember me') }}</label>
                </div>
                @if (Route::has('password.request'))
                    <a class="small" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                @endif
            </div>

            <button type="submit" class="btn btn-brand w-100">
                {{ __('Log in') }}
            </button>
        </form>

        @php($msCfg = \App\Models\Configuracion::find(1))
        @if($msCfg && $msCfg->microsoft_login_enabled && $msCfg->powerbi_client_id && $msCfg->powerbi_client_secret && $msCfg->powerbi_tenant_id)
            <div class="text-center my-3 text-muted small">— o —</div>
            <a href="{{ route('auth.microsoft.redirect') }}" class="btn btn-outline-primary w-100 btn-microsoft">
                <i class="fab fa-microsoft me-1"></i> {{ __('Continue with Microsoft') }}
            </a>
        @endif
    </div>
@endsection

@section('auth_footer')
    @if (Route::has('register'))
        <p class="my-2 text-center">
            <span class="text-muted small">{{ __('Don’t have an account?') }}</span>
            <a href="{{ route('register') }}">{{ __('Register') }}</a>
        </p>
    @endif
@endsection
