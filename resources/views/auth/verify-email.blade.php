@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('auth_header', __('Verify Email'))

@section('auth_body')
    <p class="text-muted">{{ __('Thanks for signing up! Before getting started, please verify your email by clicking the link we just emailed to you.') }}</p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success small">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mb-2">
        @csrf
        <button type="submit" class="btn btn-primary btn-block">{{ __('Resend Verification Email') }}</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-link p-0">{{ __('Log Out') }}</button>
    </form>
@endsection
