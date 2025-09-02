@extends('adminlte::auth.auth-page', ['authType' => 'register'])

@section('auth_header', __('Register'))

@section('auth_body')
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="input-group mb-3">
            <input id="name" type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="{{ __('Name') }}" required autofocus autocomplete="name">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-user"></span></div>
            </div>
        </div>
        @error('name')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

        <div class="input-group mb-3">
            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="{{ __('Email') }}" required autocomplete="username">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
            </div>
        </div>
        @error('email')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

        <div class="input-group mb-3">
            <input id="password" type="password" name="password" class="form-control" placeholder="{{ __('Password') }}" required autocomplete="new-password">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
        </div>
        @error('password')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

        <div class="input-group mb-3">
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" placeholder="{{ __('Confirm Password') }}" required autocomplete="new-password">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
        </div>
        @error('password_confirmation')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

        <div class="row mb-0">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">{{ __('Register') }}</button>
            </div>
        </div>
    </form>
@endsection

@section('auth_footer')
    <p class="my-0">
        <a href="{{ route('login') }}">{{ __('Already registered?') }}</a>
    </p>
@endsection
