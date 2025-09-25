@extends('adminlte::page')

@section('title', __('applications.edit.title', ['name' => $application->name]))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">{{ __('applications.edit.title', ['name' => $application->name]) }}</h1>
            <small class="text-muted">{{ __('applications.edit.subtitle') }}</small>
        </div>
        <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> {{ __('applications.actions.back') }}
        </a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('applications.actions.close_alert') }}"></button>
        </div>
    @endif

    <form action="{{ route('applications.update', $application) }}" method="POST" id="application-update-form">
        @csrf
        @method('PUT')

        @include('applications._form')

        <x-adminlte-card theme="lightblue" theme-mode="outline">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <button type="submit" form="application-delete-form" class="btn btn-outline-danger" onclick="return confirm('{{ __('applications.confirm.delete') }}');">
                    <i class="fas fa-trash-alt me-1"></i> {{ __('applications.actions.delete') }}
                </button>
                <div class="d-flex" style="gap: 0.75rem;">
                    <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary">
                        {{ __('applications.actions.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> {{ __('applications.actions.update') }}
                    </button>
                </div>
            </div>
        </x-adminlte-card>
    </form>

    <form action="{{ route('applications.destroy', $application) }}" method="POST" id="application-delete-form" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@stop