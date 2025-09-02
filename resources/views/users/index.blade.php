@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Usuarios</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo usuario</a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Buscar por nombre o email">
                    <div class="input-group-append"><button class="btn btn-outline-secondary">Buscar</button></div>
                </div>
            </form>

            <table class="table table-hover">
                <thead><tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th class="text-right">Acciones</th>
                </tr></thead>
                <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->roles->pluck('name')->join(', ') }}</td>
                        <td class="text-right">
                            <a href="{{ route('users.edit', ['usuario' => $u->id]) }}" class="btn btn-sm btn-info">Editar</a>
                            <form action="{{ route('users.destroy', ['usuario' => $u->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar usuario?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center p-4 text-muted">Sin usuarios.</td></tr>
                @endforelse
                </tbody>
            </table>

            {{ $users->links() }}
        </div>
    </div>
@stop

