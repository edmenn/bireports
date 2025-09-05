@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php($isDark = optional(Auth::user())->theme === 'dark' || session('theme') === 'dark')
<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ $isDark ? 'navbar-dark bg-dark' : 'navbar-light bg-white' }}">

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

        {{-- Theme switcher --}}
        <li class="nav-item">
            <form method="POST" action="{{ route('theme.switch') }}" class="d-inline">
                @csrf
                <input type="hidden" name="theme" value="{{ $isDark ? 'light' : 'dark' }}">
                <button type="submit" class="nav-link p-0 px-2 bg-transparent border-0" style="line-height:1" title="{{ $isDark ? 'Light' : 'Dark' }} theme">
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
