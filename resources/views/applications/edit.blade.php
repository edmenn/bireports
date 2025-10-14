@extends('adminlte::page')

@section('title', __('applications.edit.title', ['name' => $application->name]))

@section('content_header')
    <x-page-header :title="__('applications.edit.title', ['name' => $application->name])"
                   :subtitle="__('applications.edit.subtitle')" icon="fas fa-plug">
        <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> {{ __('applications.actions.back') }}
        </a>
    </x-page-header>
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
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                <button type="submit" form="application-delete-form" class="btn btn-outline-danger" onclick="return confirm('{{ __('applications.confirm.delete') }}');">
                    <i class="fas fa-trash-alt me-1"></i> {{ __('applications.actions.delete') }}
                </button>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary">
                        {{ __('applications.actions.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> {{ __('applications.actions.update') }}
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
