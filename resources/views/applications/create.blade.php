@extends('adminlte::page')

@section('title', __('applications.create.title'))

@section('content_header')
    <x-page-header :title="__('applications.create.title')" icon="fas fa-plug" />
@stop

@section('content')
    <form action="{{ route('applications.store') }}" method="POST" id="appForm">
        @csrf
        @include('applications._form')

        <x-adminlte-card theme="lightblue" theme-mode="outline">
            <div class="d-flex flex-column flex-sm-row justify-content-end gap-3">
                <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary">
                    {{ __('applications.actions.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> {{ __('applications.actions.save') }}
                </button>
            </div>
        </x-adminlte-card>
    </form>
@stop
