@extends('adminlte::page')

@section('title', 'Todos los reportes')

@section('content_header')
    <h1>Todos los reportes</h1>
@stop

@section('content')
    <form method="get" class="mb-3">
        <div class="row g-2">
            <div class="col-md-6">
                <input
                    type="text"
                    class="form-control"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Buscar por título, Report ID, Workspace ID o Dataset ID">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary">Buscar</button>
                @if($q !== '')
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    @if($reports->isEmpty())
        <div class="alert alert-info">
            No hay reportes importados.
            <a href="{{ route('powerbi.import.run') }}" class="btn btn-sm btn-secondary ms-2">
                Importar ahora
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Título</th>
                    <th>Workspace</th>
                    <th>Dataset ID</th>
                    <th>Report ID</th>
                    <th class="text-end"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($reports as $r)
                    <tr>
                        <td>{{ $r->name }}</td>
                        <td>
                            @if($r->workspace)
                                {{ $r->workspace->name }}
                                <div><code>{{ $r->workspace_id }}</code></div>
                            @else
                                <code>{{ $r->workspace_id }}</code>
                            @endif
                        </td>
                        <td><code>{{ $r->dataset_id ?? '-' }}</code></td>
                        <td><code>{{ $r->id }}</code></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-primary"
                               href="{{ route('reports.show', [$r->workspace_id, $r->id]) }}">
                                Abrir
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{ $reports->links() }}
    @endif
@stop
