@extends('adminlte::page')

@section('title', __('roles.page.create'))

@section('content_header')
    <h1 class="m-0">{{ __('roles.page.create') }}</h1>
@stop

@section('content')
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="{{ __('roles.alerts.error') }}" dismissable>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('roles.store') }}">
        @csrf

        <x-adminlte-card title="{{ __('roles.form.card_title') }}" icon="fas fa-id-badge text-lightblue" theme="primary" theme-mode="outline">
            <div class="form-group">
                <label for="name">{{ __('roles.form.name_label') }}</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required maxlength="150" placeholder="{{ __('roles.form.name_placeholder') }}">
            </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="badge_bg">{{ __('roles.form.badge_bg_label') }}</label>
                    <input type="color" name="badge_bg" id="badge_bg" class="form-control" value="{{ old('badge_bg', '#eef2ff') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="badge_text">{{ __('roles.form.badge_text_label') }}</label>
                    <input type="color" name="badge_text" id="badge_text" class="form-control" value="{{ old('badge_text', '#4338ca') }}">
                </div>
            </div>
        </x-adminlte-card>

        @include('roles._permissions', ['sections' => $sections, 'current' => old('permissions', [])])

        <x-adminlte-card theme="primary" theme-mode="outline">
            <x-adminlte-button type="submit" theme="primary" label="{{ __('roles.form.buttons.save') }}" icon="fas fa-save"/>
            <x-adminlte-button :href="route('roles.index')" theme="secondary" label="{{ __('roles.form.buttons.cancel') }}" icon="fas fa-times"/>
        </x-adminlte-card>
    </form>
@endsection
