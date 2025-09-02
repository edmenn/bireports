@extends('adminlte::page')

@section('title', 'Mis favoritos')

@section('content_header')
    <h1>Mis favoritos</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        @if($reports->isEmpty())
            <div class="text-center text-muted">Todavía no marcaste favoritos.</div>
        @else
            <div class="row">
                @foreach($reports as $r)
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100 d-flex flex-column">
                            <h5>{{ $r->name }}</h5>
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

