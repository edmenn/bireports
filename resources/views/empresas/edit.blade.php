@extends('adminlte::page')

@section('title', 'Editar empresa')

@section('content_header')
    <h1>Editar empresa</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('empresas.update', $company) }}" method="POST">
            @csrf @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $company->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Subdominio</label>
                    <input type="text" name="subdomain" class="form-control @error('subdomain') is-invalid @enderror"
                           value="{{ old('subdomain', $company->subdomain) }}">
                    @error('subdomain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $company->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Activa</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', (int)$company->is_active) === 1 ? 'selected' : '' }}>Sí</option>
                        <option value="0" {{ old('is_active', (int)$company->is_active) === 0 ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('empresas.index') }}" class="btn btn-secondary">Volver</a>
                <button class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@stop
