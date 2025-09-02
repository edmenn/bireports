@extends('adminlte::page')

@section('title', 'Carpetas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Carpetas</h1>
        <a href="{{ route('folders.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva carpeta</a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card"><div class="card-body">
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Buscar por nombre">
                <div class="input-group-append"><button class="btn btn-outline-secondary">Buscar</button></div>
            </div>
        </form>

        <table class="table table-hover">
            <thead><tr>
                <th>Nombre</th>
                <th>Usuarios</th>
                <th>Grupos</th>
                <th>Reportes</th>
                <th class="text-right">Acciones</th>
            </tr></thead>
            <tbody>
            @forelse(($folders ?? collect()) as $f)
                <tr>
                    <td>{{ $f->name }}</td>
                    <td>{{ $f->users()->count() }}</td>
                    <td>{{ $f->groups()->count() }}</td>
                    <td>{{ $f->reports()->count() }}</td>
                    <td class="text-right">
                        <a href="{{ route('folders.edit', $f) }}" class="btn btn-sm btn-info">Editar</a>
                        <form action="{{ route('folders.destroy', $f) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar carpeta?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center p-4 text-muted">Sin carpetas.</td></tr>
            @endforelse
            </tbody>
        </table>

        {{ ($folders ?? collect())->links() }}
    </div></div>
@stop
