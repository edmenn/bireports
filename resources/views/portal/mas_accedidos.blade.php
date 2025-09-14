@extends('adminlte::page')

@section('title', 'Más accedidos')

@section('content_header')
    <h1>Más accedidos</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        @if($items->isEmpty())
            <div class="text-center text-muted">Sin accesos registrados aún.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>Reporte</th>
                        <th class="text-right">Accesos</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                    @foreach($items as $i)
                        <tr>
                            <td>{{ $i['name'] }}</td>
                            <td class="text-right">{{ $i['total'] }}</td>
                            <td class="text-right">
                                @if(!empty($i['workspace_id']))
                                    <a href="{{ route('reports.show', [$i['workspace_id'], $i['id']]) }}" class="btn btn-sm btn-outline-primary">
                                        Abrir
                                    </a>
                                @endif
                            </td>
                        </td>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div></div>
@stop
