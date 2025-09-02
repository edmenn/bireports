@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', __('Forgot your password?'))

@section('auth_body')
    <p class="text-muted">{{ __('No problem. Enter your email and we will email you a reset link.') }}</p>

    @if (session('status'))
        <div class="alert alert-success small">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="input-group mb-3">
            <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="{{ __('Email') }}" required autofocus>
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
            </div>
        </div>
        @error('email')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

        <div class="row mb-0">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">{{ __('Email Password Reset Link') }}</button>
            </div>
        </div>
    </form>
@endsection

@section('auth_footer')
    <p class="my-0"><a href="{{ route('login') }}">{{ __('Back to login') }}</a></p>
@endsection
