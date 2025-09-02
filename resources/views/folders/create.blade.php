@extends('adminlte::page')

@section('title', 'Nueva carpeta')

@section('content_header')
    <h1>Nueva carpeta</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        <form action="{{ route('folders.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Usuarios con acceso</label>
                    <select name="users[]" class="form-control" multiple size="10">
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} &lt;{{ $u->email }}&gt;</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Grupos con acceso</label>
                    <select name="groups[]" class="form-control" multiple size="10">
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Reportes</label>
                    <select name="reports[]" class="form-control" multiple size="10">
                        @foreach($reports as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary">Guardar</button>
                <a href="{{ route('folders.index') }}" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div></div>
@stop

