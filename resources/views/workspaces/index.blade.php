@extends('adminlte::page')

@section('title', 'Espacios de trabajo')

@section('content_header')
    <h1>Espacios de trabajo importados</h1>
@stop

@section('content')
    @if($workspaces->isEmpty())
        <div class="alert alert-info">
            No hay workspaces importados todavía.  
            <a href="{{ route('powerbi.import.run') }}" class="btn btn-sm btn-secondary">
                Importar ahora
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Premium</th>
                    <th>Capacity ID</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($workspaces as $w)
                    <tr>
                        <td>{{ $w->name }}</td>
                        <td>{{ $w->type ?? '-' }}</td>
                        <td>
                            @if($w->is_on_premium_capacity)
                                <span class="badge bg-success">Sí</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td><code>{{ $w->capacity_id ?? '-' }}</code></td>
                        <td class="text-right">
                            <a href="{{ route('workspaces.show', $w->id) }}" class="btn btn-sm btn-primary">
                                Ver reportes
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
@stop
