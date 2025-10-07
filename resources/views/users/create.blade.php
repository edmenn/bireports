@extends('adminlte::page')

@section('title', __('users.page.create'))

@section('content_header')
    <h1>{{ __('users.page.create') }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">{{ __('users.form.name') }}</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        required
                    >
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="email">{{ __('users.form.email') }}</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        required
                    >
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="password">{{ __('users.form.password') }}</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                    >
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="role">{{ __('users.form.role') }}</label>
                    <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" @selected(old('role') === $r)>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary">{{ __('users.form.buttons.save') }}</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">{{ __('users.form.buttons.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@stop
