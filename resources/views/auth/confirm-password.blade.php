@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', __('Confirm Password'))

@section('auth_body')
    <p class="text-muted">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="input-group mb-3">
            <input id="password" type="password" name="password" class="form-control" placeholder="{{ __('Password') }}" required autocomplete="current-password">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
        </div>
        @error('password')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

        <div class="row mb-0">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">{{ __('Confirm') }}</button>
            </div>
        </div>
    </form>
@endsection
