<!doctype html>
@php
    $appCfg ??= \App\Models\Configuration::query()->find(1);
    $isDark = optional(auth()->user())->theme === 'dark' || session('theme') === 'dark';
    $theme = is_array($appCfg?->theme_config) ? $appCfg->theme_config : (json_decode((string) $appCfg?->theme_config, true) ?: []);
    $title = $appCfg?->site_title ?: config('app.name', 'BI Portal');
    $logo = $appCfg?->site_logo_path ? Storage::disk('public')->url($appCfg->site_logo_path) : asset('favicon.ico');
    $compactLogo = $appCfg?->site_logo_compact_path ? Storage::disk('public')->url($appCfg->site_logo_compact_path) : $logo;
    $logoWidth = max(16, (int) ($appCfg?->site_logo_width ?: 120));
    $logoHeight = max(16, (int) ($appCfg?->site_logo_height ?: 40));
    $compactLogoWidth = max(16, (int) ($appCfg?->site_logo_compact_width ?: 40));
    $compactLogoHeight = max(16, (int) ($appCfg?->site_logo_compact_height ?: 40));
    $favicon = $appCfg?->site_favicon_path ? Storage::disk('public')->url($appCfg->site_favicon_path) : asset('favicon.ico');
    $nextTheme = $isDark ? 'light' : 'dark';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ $isDark ? 'dark' : 'light' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') — @endif{{ $title }}</title>
    <link rel="icon" href="{{ $favicon }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ @filemtime(public_path('css/custom.css')) ?: '1' }}">
    <link rel="stylesheet" href="{{ asset('css/tabler-bridge.css') }}?v={{ @filemtime(public_path('css/tabler-bridge.css')) ?: '1' }}">
    @include('partials.theme-styles', ['theme' => $theme])
    @stack('css')
    @yield('css')
</head>
<body class="app-shell {{ $isDark ? 'dark-mode' : '' }}">
<div class="page">
    <aside class="navbar navbar-vertical navbar-expand-lg app-sidebar main-sidebar" aria-label="{{ __('menu.navigation_label') }}">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#app-sidebar-menu" aria-controls="app-sidebar-menu" aria-expanded="false" aria-label="{{ __('menu.toggle_navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand navbar-brand-autodark brand-link" href="{{ route('portal.index') }}">
                <img src="{{ $logo }}" alt="{{ $title }}" class="navbar-brand-image brand-image brand-logo-full" style="--brand-width:{{ $logoWidth }}px;--brand-height:{{ $logoHeight }}px">
                <img src="{{ $compactLogo }}" alt="" class="navbar-brand-image brand-image brand-logo-compact" style="--brand-width:{{ $compactLogoWidth }}px;--brand-height:{{ $compactLogoHeight }}px">
                <span class="brand-text">{{ $title }}</span>
            </a>
            <div class="collapse navbar-collapse" id="app-sidebar-menu">
                <ul class="navbar-nav pt-lg-3">
                    @foreach(config('navigation', []) as $item)
                        @include('layouts.partials.sidebar-item', ['item' => $item])
                    @endforeach
                </ul>
            </div>
        </div>
    </aside>

    <div class="page-wrapper content-wrapper">
        <header class="navbar navbar-expand-md app-header main-header d-print-none">
            <div class="container-fluid">
                <button class="btn btn-icon d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#app-sidebar-menu" aria-label="{{ __('menu.toggle_navigation') }}"><i class="fas fa-bars"></i></button>
                <button class="btn btn-icon d-none d-lg-inline-flex" id="sidebar-size-toggle" type="button" aria-label="{{ __('menu.toggle_sidebar_size') }}" aria-pressed="false"><i class="fas fa-bars"></i></button>
                <div class="navbar-nav flex-row order-md-last ms-auto align-items-center gap-1">
                    <span id="navbar-current-datetime" class="nav-link d-none d-xl-flex" data-locale="{{ str_replace('_', '-', app()->getLocale()) }}"></span>
                    <div class="nav-item dropdown">
                        <button class="nav-link btn btn-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-language me-1"></i><span class="d-none d-sm-inline">{{ __('language.menu') }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            @foreach(config('app.supported_locales', ['en', 'es', 'pt']) as $code)
                                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('lang.switch', $code) }}">
                                    <img src="{{ asset('lang/'.$code.'.svg') }}" alt="" width="24" height="16"><span>{{ __('language.'.$code) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <form method="POST" action="{{ route('theme.switch') }}">
                        @csrf
                        <input type="hidden" name="theme" value="{{ $nextTheme }}">
                        <button class="nav-link btn btn-link" type="submit" aria-label="{{ __('theme.switch_to', ['theme' => __('theme.'.$nextTheme)]) }}"><i class="fas fa-{{ $isDark ? 'sun' : 'moon' }}"></i></button>
                    </form>
                    <div class="nav-item dropdown">
                        <button class="nav-link d-flex align-items-center gap-2 btn btn-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar avatar-sm"><i class="fas fa-user"></i></span><span class="d-none d-md-inline">{{ auth()->user()?->name }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user-cog me-2"></i>{{ __('Profile') }}</a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item" type="submit"><i class="fas fa-sign-out-alt me-2"></i>{{ __('Log Out') }}</button></form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        @hasSection('content_header')
            <div class="page-header d-print-none content-header"><div class="container-fluid">@yield('content_header')</div></div>
        @endif
        <main class="page-body content"><div class="container-fluid">@yield('content')</div></main>
        <footer class="footer footer-transparent main-footer">
            <div class="container-fluid justify-content-center text-center">
                @hasSection('footer')
                    @yield('footer')
                @else
                    @include('partials.footer-fixed', ['text' => $appCfg?->footer_text, 'text_key' => 'footer.signature', 'replacements' => ['app' => $title, 'year' => date('Y')]])
                @endif
            </div>
        </footer>
    </div>
</div>
<script src="{{ asset('js/menu-state.js') }}?v={{ @filemtime(public_path('js/menu-state.js')) ?: '1' }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const target = document.getElementById('navbar-current-datetime');
    if (!target) return;
    const formatter = new Intl.DateTimeFormat(target.dataset.locale || 'es', {dateStyle: 'full', timeStyle: 'short'});
    const update = () => target.textContent = formatter.format(new Date());
    update(); setInterval(update, 60000);
});
</script>
@stack('js')
@yield('js')
</body>
</html>
