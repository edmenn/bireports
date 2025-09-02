@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', __('Log in'))

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
