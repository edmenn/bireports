<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @yield('head') {{-- Por si alguna vista necesita CSS/JS extra en <head> --}}
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            {{-- Encabezado opcional de página (las vistas pueden definir @section('header')) --}}
            @hasSection('header')
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        @yield('header')
                    </div>
                </header>
            @endif

            {{-- Contenido principal de cada vista --}}
            <main class="py-4">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {{-- Mensajes flash globales opcionales --}}
                    @if (session('success'))
                        <div class="alert alert-success mb-3">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

        @yield('scripts') {{-- Scripts extra al final del <body> --}}
    </body>
</html>
