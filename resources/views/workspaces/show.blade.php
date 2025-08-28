@extends('adminlte::page')

@section('title', 'Workspace: '.$workspace->name)

@section('content_header')
    <h1>Workspace: {{ $workspace->name }}</h1>
@stop

@section('content')
    @if($reports->isEmpty())
        <div class="alert alert-info">Este workspace no tiene reportes importados.</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Título</th>
                    <th>Dataset ID</th>
                    <th class="text-right">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($reports as $r)
                    <tr>
                        <td>{{ $r->name }}</td>
                        <td><code>{{ $r->dataset_id }}</code></td>
                        <td class="text-right">
                            <a class="btn btn-sm btn-primary"
                               href="{{ route('reports.show', [$workspace->id, $r->id]) }}">
                                Abrir
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
@stop
