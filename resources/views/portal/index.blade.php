@extends('adminlte::page')

@section('title', 'Mi Inicio')

@section('content_header')
    <h1>Mi Inicio</h1>
@stop

@section('content')
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
                <div class="row">
                    @foreach($reports as $r)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3 h-100 d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="mb-0">{{ $r->name }}</h5>
                                    <form action="{{ route('portal.fav.toggle', $r) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-sm {{ in_array($r->id, $favIds) ? 'btn-warning' : 'btn-outline-secondary' }}" title="Favorito">
                                            <i class="fas fa-star"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-auto">
                                    @if($r->workspace_id)
                                        <a href="{{ route('reports.show', [$r->workspace_id, $r->id]) }}" class="btn btn-primary btn-block">
                                            <i class="fas fa-chart-line mr-1"></i> Abrir
                                        </a>
                                    @else
                                        <span class="text-muted">Workspace no asignado</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@stop

