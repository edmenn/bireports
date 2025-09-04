@extends('adminlte::page')

@section('title')
    @yield('page-title', trim($__env->yieldContent('title')) ?: config('app.name'))
@endsection

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-baseline flex-wrap gap-2">
            <h1 class="m-0">@yield('page-title', trim($__env->yieldContent('title')) ?: '')</h1>
            @hasSection('page-subtitle')
                <small class="text-muted">@yield('page-subtitle')</small>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            @yield('header-actions')
        </div>
    </div>

    @hasSection('toolbar')
        <div class="page-toolbar mt-3">
            @yield('toolbar')
        </div>
    @endif
@endsection

@section('content')
    @if(session('success')) <x-adminlte-alert theme="success" title="OK" dismissable>{{ session('success') }}</x-adminlte-alert> @endif
    @if(session('warning')) <x-adminlte-alert theme="warning" title="Atención" dismissable>{{ session('warning') }}</x-adminlte-alert> @endif
    @if(session('error'))   <x-adminlte-alert theme="danger"  title="Error"    dismissable>{{ session('error') }}</x-adminlte-alert>   @endif
    @if($errors->any())
        <x-adminlte-alert theme="danger" title="Errores de validación" dismissable>
            <ul class="mb-0 ps-3">@foreach($errors->all() as $m)<li>{{ $m }}</li>@endforeach</ul>
        </x-adminlte-alert>
    @endif

    @yield('page')
@endsection
