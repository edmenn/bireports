@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', __('Log in'))

@php($appCfg = \App\Models\Configuracion::find(1))
@if($appCfg && $appCfg->login_bg_path)
    @section('adminlte_css')
        <style>
            body.login-page {
                background-image: url('{{ Storage::disk('public')->url($appCfg->login_bg_path) }}');
                background-size: cover;
                background-position: center center;
                background-repeat: no-repeat;
            }
        </style>
    @endsection
@endif

@section('auth_body')
    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="input-group mb-3">
            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="{{ __('Email') }}" required autofocus autocomplete="username">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
            </div>
        </div>
        @error('email')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

        <div class="input-group mb-3">
            <input id="password" type="password" name="password" class="form-control" placeholder="{{ __('Password') }}" required autocomplete="current-password">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
        </div>
        @error('password')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

        <div class="row mb-3">
            <div class="col-8">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">{{ __('Remember me') }}</label>
                </div>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">{{ __('Log in') }}</button>
            </div>
        </div>
    </form>

    @php($msCfg = \App\Models\Configuracion::find(1))
    @if($msCfg && $msCfg->microsoft_login_enabled && $msCfg->powerbi_client_id && $msCfg->powerbi_client_secret && $msCfg->powerbi_tenant_id)
        <div class="text-center my-3 text-muted">— o —</div>
        <a href="{{ route('auth.microsoft.redirect') }}" class="btn btn-block btn-outline-primary">
            <i class="fab fa-microsoft mr-1"></i> Continuar con Microsoft
        </a>
    @endif
@endsection

@section('auth_footer')
    <p class="my-0">
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
        @endif
    </p>
    @if (Route::has('register'))
        <p class="my-0">
            <a href="{{ route('register') }}">{{ __('Register') }}</a>
        </p>
    @endif
@endsection
