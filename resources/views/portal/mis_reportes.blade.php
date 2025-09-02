@extends('adminlte::page')

@section('title', 'Mis reportes')

@section('content_header')
    <h1>Mis reportes</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        @if($reports->isEmpty())
            <div class="text-center text-muted">No tenés reportes asignados.</div>
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
            {{ $reports->links() }}
        @endif
    </div></div>
@stop

