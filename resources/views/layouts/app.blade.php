<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel'))</title>

    {{-- Fuente moderna --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 + AdminLTE 4 (CDN, gratis) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css">

    {{-- Iconos minimalistas --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.21.0/tabler-icons.min.css">

    {{-- CSS propio centralizado --}}
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v={{ @filemtime(public_path('css/custom.css')) ?: '1' }}">

    {{-- Vite (si tu proyecto ya lo usa para otros assets) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('head') {{-- CSS/JS extra en <head> --}}
</head>
<body class="hold-transition layout-fixed sidebar-mini">
<div class="wrapper">

    {{-- NAVBAR --}}
    <nav class="main-header navbar navbar-expand border-0 shadow-sm">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link px-2" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
                        <i class="ti ti-layout-sidebar-left fs-4"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-md-block">
                    <a href="{{ url('/') }}" class="nav-link fw-semibold">{{ config('app.name','Laravel') }}</a>
                </li>
            </ul>

            <form class="d-none d-md-flex ms-2 flex-grow-1" role="search">
                <div class="input-group input-group-sm modern-search">
                    <span class="input-group-text border-0 bg-transparent text-light"><i class="ti ti-search"></i></span>
                    <input class="form-control border-0 bg-transparent text-light" type="search" placeholder="Buscar…" aria-label="Buscar">
                </div>
            </form>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a id="notifDropdown" href="#" data-bs-toggle="dropdown" class="nav-link position-relative">
                        <i class="ti ti-bell fs-5"></i>
                        <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle p-1 border border-light"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg rounded-3 overflow-hidden">
                        <div class="p-3 small">
                            <div class="fw-semibold mb-2">Notificaciones</div>
                            <div class="text-secondary">Sin novedades por ahora.</div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    {{-- SIDEBAR --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-0">
        <a href="{{ route('dashboard') ?? url('/') }}" class="brand-link text-decoration-none py-3">
            <div class="d-flex align-items-center gap-2 px-3">
                <img src="https://dummyimage.com/36x36/4f46e5/ffffff.png&text=P" class="brand-image img-circle elevation-0" alt="Logo">
                <span class="brand-text fw-bold">PowerBIDash</span>
            </div>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column gap-1" data-lte-toggle="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') ?? url('/') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon ti ti-dashboard"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->is('reports*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-report-analytics"></i>
                            <p>Reportes<i class="end ti ti-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview ps-4">
                            <li class="nav-item"><a href="#" class="nav-link"><i class="ti ti-chart-bar nav-icon"></i><p>Ventas</p></a></li>
                            <li class="nav-item"><a href="#" class="nav-link"><i class="ti ti-chart-pie nav-icon"></i><p>Operaciones</p></a></li>
                        </ul>
                    </li>

                    <li class="nav-header text-uppercase small text-secondary px-3 mt-3">Sistema</li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon ti ti-settings"></i>
                            <p>Configuración</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    {{-- CONTENT WRAPPER --}}
    <div class="content-wrapper">
        {{-- Header opcional de página --}}
        @hasSection('header')
            <div class="content-header py-3">
                <div class="container-fluid">
                    @yield('header')
                </div>
            </div>
        @else
            <div class="content-header py-3">
                <div class="container-fluid">
                    <h1 class="m-0 fw-bold">@yield('page-title', 'Panel')</h1>
                    <p class="text-secondary mb-0">@yield('page-subtitle', 'Resumen general')</p>
                </div>
            </div>
        @endif

        {{-- Contenido principal --}}
        <section class="content">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success mb-3">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger mb-3">{{ session('error') }}</div>
                @endif

                @yield('content')
            </div>
        </section>
    </div>

    {{-- FOOTER --}}
    <footer class="main-footer border-0 py-3">
        <div class="container-fluid d-flex justify-content-between small">
            <span>© {{ date('Y') }} — {{ config('app.name', 'Laravel') }}</span>
            <span class="fw-semibold">Desarrollado por Edgar Menna — 2025</span>
        </div>
    </footer>
</div>

{{-- JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/js/adminlte.min.js"></script>
<script src="{{ asset('js/layout-offset.js') }}"></script>

@yield('scripts')
</body>
</html>
