@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php($isDark = (bool) config('adminlte.layout_dark_mode', false))
@php($nextTheme = $isDark ? 'light' : 'dark')
@php($nextThemeLabel = __('theme.' . $nextTheme))
<nav class="main-header navbar shadow-sm border-0 {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }} {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Navbar left links --}}
    <ul class="navbar-nav">
        {{-- Left sidebar toggler link --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Configured left links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Custom left links --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Navbar right links --}}
    <ul class="navbar-nav ml-auto">
        {{-- Custom right links --}}
        @yield('content_top_nav_right')

        {{-- Language switcher --}}
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                <i class="fas fa-language"></i> {{ __('language.menu') }}
            </a>
            <ul class="dropdown-menu dropdown-menu-right border-0 shadow">
                @foreach(config('app.supported_locales', ['en','es','pt']) as $code)
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('lang.switch', $code) }}">
                            <img src="{{ asset('storage/lang/'.$code.'.png') }}" alt="{{ $code }}" width="18" height="12" class="mr-2"/>
                            <span>{{ __('language.'.$code) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>

        {{-- Theme switcher --}}
        <li class="nav-item">
            <form method="POST" action="{{ route('theme.switch') }}" class="d-inline">
                @csrf
                <input type="hidden" name="theme" value="{{ $nextTheme }}">
                @php($switchLabel = __('theme.switch_to', ['theme' => $nextThemeLabel]))
                <button type="submit" class="nav-link p-0 px-2 bg-transparent border-0" title="{{ $switchLabel }}" aria-label="{{ $switchLabel }}">
                    @if($isDark)
                        <i class="fas fa-sun"></i>
                    @else
                        <i class="fas fa-moon"></i>
                    @endif
                </button>
            </form>
        </li>

        {{-- Configured right links --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- User menu link --}}
        @if(Auth::user())
            @if(config('adminlte.usermenu_enabled'))
                @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
            @else
                @include('adminlte::partials.navbar.menu-item-logout-link')
            @endif
        @endif

        {{-- Right sidebar toggler link --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
        @endif
    </ul>

</nav>
