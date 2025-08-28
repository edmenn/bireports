@extends('adminlte::page')

@section('title', 'Empresas')

@section('content_header')
    <h1>Empresas</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('empresas.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva empresa
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Subdominio</th>
                        <th>Activa</th>
                        <th class="text-right" style="width:160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empresas as $c)
                        <tr>
                            <td>{{ $c->name }}</td>
                            <td>{{ $c->subdomain ?? '—' }}</td>
                            <td>
                                @if($c->is_active)
                                    <span class="badge badge-success">Sí</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a class="btn btn-sm btn-info" href="{{ route('empresas.edit', $c) }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('empresas.destroy', $c) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Eliminar esta empresa?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center p-4">Sin empresas aún.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($empresas->hasPages())
            <div class="card-footer">
                {{ $empresas->links() }}
            </div>
        @endif
    </div>
@stop
