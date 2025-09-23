@extends('adminlte::page')

@section('title', 'Mi Inicio')

@section('content_header')
    <h1>Mi Inicio</h1>
@stop

@section('content')
    @include('portal.partials.report-card-styles')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="card mb-3">
        <div class="card-header">Carpetas</div>
        <div class="card-body p-2">
            <div class="nav nav-pills flex-wrap">
                @forelse($folders as $f)
                    <a class="nav-link {{ ($selected && $selected->id===$f->id) ? 'active' : '' }}"
                       href="{{ route('portal.index', ['carpeta_id'=>$f->id]) }}">
                        <i class="fas fa-folder mr-1"></i> {{ $f->name }}
                        <span class="badge badge-light ml-2">{{ $f->reports_count }}</span>
                    </a>
                @empty
                    <span class="text-muted p-2">Sin carpetas asignadas.</span>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                Reportes @if($selected) <small class="text-muted">en {{ $selected->name }}</small>@endif
            </div>
        </div>
        <div class="card-body">
            @if(($reports ?? collect())->isEmpty())
                <div class="text-center text-muted py-4">No hay reportes para mostrar.</div>
            @else
                <div class="report-grid">
                    @foreach($reports as $report)
                        @include('portal.partials.report-card', ['report' => $report, 'favIds' => $favIds])
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @include('portal.partials.fav-toggle-script')
@stop
