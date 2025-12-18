@extends('adminlte::page')

@section('title', 'Importación Power BI')

@section('content_header')
    <h1>Importación desde Power BI</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <p class="mb-2">Resultado de la importación:</p>
            <ul>
                <li><strong>Workspaces importados/actualizados:</strong> {{ $wsCount }}</li>
                <li><strong>Reports importados/actualizados:</strong> {{ $rpCount }}</li>
                @isset($dsCount)
                    <li><strong>Datasets importados/actualizados:</strong> {{ $dsCount }}</li>
                @endisset
            </ul>

            <a href="{{ route('workspaces.index') }}" class="btn btn-primary">Ir a "Espacios de trabajo"</a>
            <a href="{{ route('reports.index') }}" class="btn btn-primary">Ir a "Informes"</a>
            @if(Route::has('datasets.index'))
                <a href="{{ route('datasets.index') }}" class="btn btn-primary">Ir a "Conjuntos de datos"</a>
            @endif
        </div>
    </div>
@stop
