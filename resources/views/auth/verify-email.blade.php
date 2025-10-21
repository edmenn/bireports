@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('title', __('Verify Email'))

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

    .btn-outline-brand {
        background: transparent;
        border: 1px solid #2563eb;
        color: #2563eb;
        border-radius: .5rem;
        padding: .6rem;
        font-weight: 500;
    }
    .btn-outline-brand:hover {
        background: #2563eb;
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

    <div class="login-card p-3 p-md-4 text-center">
        <div class="mb-3">
            @if($appCfg && $appCfg->login_logo_path)
                <img src="{{ Storage::disk('public')->url($appCfg->login_logo_path) }}" alt="Logo" class="brand-logo">
            @elseif($appCfg && $appCfg->site_logo_path)
                <img src="{{ Storage::disk('public')->url($appCfg->site_logo_path) }}" alt="Logo" class="brand-logo">
            @endif
        </div>

        <h5 class="mb-3">{{ __('Verify Email') }}</h5>

        <p class="text-muted small mb-4">
            {{ __('Thanks for signing up! Before getting started, please verify your email by clicking the link we just emailed to you.') }}
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success small mb-4">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
            @csrf
            <button type="submit" class="btn btn-brand w-100">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-brand w-100">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
@endsection

@section('auth_footer') @endsection
