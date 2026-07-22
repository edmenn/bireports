{{-- resources/views/layouts/app-shell.blade.php --}}
@extends('adminlte::page')

{{-- Título de la pestaña (usa el de la vista si existe) --}}
@section('title', trim($__env->yieldContent('title')) ?: config('app.name'))

{{-- Header opcional: se muestra solo si la vista define "page_title" --}}
@section('content_header')
    @hasSection('page_title')
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h1 class="m-0">@yield('page_title')</h1>
            <div>@yield('page_actions')</div>
        </div>
    @endif

    @hasSection('page_toolbar')
        <div class="mt-3">
            @yield('page_toolbar')
        </div>
    @endif
@endsection

{{-- Contenido dentro de un contenedor centrado (anti desbordes) --}}
@section('content')
    <div class="app-page-container">
        @yield('page')
    </div>
@endsection

@push('css')
<style>
/* Contenedor principal centrado para TODAS las páginas que usen este layout */
.app-page-container{
    max-width: 1200px;  /* ancho fijo y bonito */
    margin: 0 auto;     /* centra */
    padding: .75rem;    /* aire */
}

/* Anti desborde de imágenes/tablas dentro del contenedor */
.app-page-container img,
.app-page-container table{
    max-width: 100%;
}

/* Una toolbar simple si la usas (opcional) */
.app-page-toolbar{
    display:flex;
    gap:.5rem;
    flex-wrap:wrap;
    align-items:center;
    background:var(--app-card-bg, #fff);
    color:var(--app-card-text, inherit);
    padding:.75rem;
    border:1px solid rgba(148,163,184,.15);
    border-radius:12px;
}
</style>
@endpush
