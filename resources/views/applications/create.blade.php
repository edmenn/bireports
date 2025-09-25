@extends('adminlte::page')

@section('title', __('applications.create.title'))

@section('content_header')
    <h1 class="h3">{{ __('applications.create.title') }}</h1>
@stop

@section('content')
    <form action="{{ route('applications.store') }}" method="POST">
        @csrf
        @include('applications._form')

        <x-adminlte-card theme="lightblue" theme-mode="outline">
            <div class="d-flex justify-content-end" style="gap: 0.75rem;">
                <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary">
                    {{ __('applications.actions.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> {{ __('applications.actions.save') }}
                </button>
            </div>
        </x-adminlte-card>
    </form>
@stop