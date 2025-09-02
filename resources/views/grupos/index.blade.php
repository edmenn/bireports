@extends('adminlte::page')

@section('title', 'Grupos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Grupos</h1>
        <a href="{{ route('grupos.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo grupo</a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card"><div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Buscar por nombre">
                <div class="input-group-append"><button class="btn btn-outline-secondary">Buscar</button></div>
            </div>
        </form>

        <table class="table table-hover">
            <thead><tr>
                <th>Nombre</th>
                <th>Miembros</th>
                <th class="text-right">Acciones</th>
            </tr></thead>
            <tbody>
            @forelse($grupos as $g)
                <tr>
                    <td>{{ $g->name }}</td>
                    <td>{{ $g->users()->count() }}</td>
                    <td class="text-right">
                        <a href="{{ route('grupos.edit', $g) }}" class="btn btn-sm btn-info">Editar</a>
                        <form action="{{ route('grupos.destroy', $g) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar grupo?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center p-4 text-muted">Sin grupos.</td></tr>
            @endforelse
            </tbody>
        </table>

        {{ $grupos->links() }}
    </div></div>
@stop
