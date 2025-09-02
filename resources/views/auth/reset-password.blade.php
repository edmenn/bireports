@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', __('Reset Password'))

@section('auth_body')
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="input-group mb-3">
            <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $request->email) }}" placeholder="{{ __('Email') }}" required autofocus autocomplete="username">
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
                <button type="submit" class="btn btn-primary btn-block">{{ __('Reset Password') }}</button>
            </div>
        </div>
    </form>
@endsection
