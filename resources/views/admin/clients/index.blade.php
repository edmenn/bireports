@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <h1>Clientes</h1>
@stop

@section('content')
    @if(session('success')) <x-adminlte-alert theme="success" title="OK">{{ session('success') }}</x-adminlte-alert> @endif

    <a href="{{ route('admin.clients.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Nuevo cliente
    </a>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Dominio</th>
                    <th>Vista por defecto</th>
                    <th>Idioma email</th>
                    <th style="width: 160px;"></th>
                </tr>
                </thead>
                <tbody>
                @forelse($clients as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->slug }}</td>
                        <td>{{ $c->custom_domain }}</td>
                        <td>{{ $c->default_view_type }}</td>
                        <td>{{ $c->default_email_lang }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.clients.edit', $c) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.clients.destroy', $c) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('¿Eliminar cliente?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center p-4">Sin clientes</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $clients->links() }}</div>
    </div>
@stop
