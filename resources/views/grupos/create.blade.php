@extends('adminlte::page')

@section('title', 'Nuevo grupo')

@section('content_header')
    <h1>Nuevo grupo</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        <form action="{{ route('grupos.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Usuarios</label>
                <select name="users[]" class="form-control" multiple size="8">
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} &lt;{{ $u->email }}&gt;</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary">Guardar</button>
                <a href="{{ route('grupos.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div></div>
@stop

