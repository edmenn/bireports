<!doctype html>
@php
    $appCfg ??= \App\Models\Configuration::query()->find(1);
    $theme = is_array($appCfg?->theme_config) ? $appCfg->theme_config : (json_decode((string) $appCfg?->theme_config, true) ?: []);
    $isDark = session('theme') === 'dark';
    $title = $appCfg?->site_title ?: config('app.name', 'BI Portal');
    $bg = $appCfg?->login_bg_path ? Storage::disk('public')->url($appCfg->login_bg_path) : null;
    $logo = $appCfg?->login_logo_path ? Storage::disk('public')->url($appCfg->login_logo_path) : ($appCfg?->site_logo_path ? Storage::disk('public')->url($appCfg->site_logo_path) : null);
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ $isDark ? 'dark' : 'light' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ @filemtime(public_path('css/custom.css')) ?: '1' }}">
    <link rel="stylesheet" href="{{ asset('css/tabler-bridge.css') }}?v={{ @filemtime(public_path('css/tabler-bridge.css')) ?: '1' }}">
    @include('partials.theme-styles', ['theme' => $theme])
    @stack('css') @yield('css')
</head>
<body class="border-top-wide border-primary d-flex flex-column auth-page login-page {{ $isDark ? 'dark-mode' : '' }}" @if($bg) style="--auth-background:url('{{ $bg }}')" @endif>
<main class="page page-center"><div class="container container-tight py-4">
    @if($logo)<div class="text-center mb-4"><img src="{{ $logo }}" alt="{{ $title }}" class="auth-logo-image"></div>@endif
    @hasSection('auth_header')<h1 class="h2 text-center mb-3">@yield('auth_header')</h1>@endif
    @yield('auth_body')
    @hasSection('auth_footer')<div class="text-center text-secondary mt-3">@yield('auth_footer')</div>@endif
</div></main>
@stack('js') @yield('js')
</body></html>
