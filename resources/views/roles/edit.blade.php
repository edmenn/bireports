@extends('adminlte::page')

@section('title', __('roles.page.edit'))

@section('content_header')
    <h1 class="m-0">{{ __('roles.page.edit') }}</h1>
@stop

@section('content')
    @if (session('success')) <x-adminlte-alert theme="success" title="{{ __('roles.alerts.success') }}" dismissable class="mb-2">{{ session('success') }}</x-adminlte-alert> @endif
    @if (session('warning')) <x-adminlte-alert theme="warning" title="{{ __('roles.alerts.warning') }}" dismissable class="mb-2">{{ session('warning') }}</x-adminlte-alert> @endif
    @if (session('error'))   <x-adminlte-alert theme="danger"  title="{{ __('roles.alerts.error') }}"    dismissable class="mb-2">{{ session('error') }}</x-adminlte-alert>   @endif

    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="{{ __('roles.alerts.error') }}" dismissable>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PUT')

        <x-adminlte-card title="{{ __('roles.form.card_title') }}" icon="fas fa-id-badge text-lightblue" theme="primary" theme-mode="outline">
            <div class="form-group">
                <label for="name">{{ __('roles.form.name_label') }}</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $role->name) }}" required maxlength="150">
            </div>
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="badge_bg">{{ __('roles.form.badge_bg_label') }}</label>
                    <input type="color" name="badge_bg" id="badge_bg" class="form-control" value="{{ old('badge_bg', $role->badge_bg ?: '#eef2ff') }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="badge_text">{{ __('roles.form.badge_text_label') }}</label>
                    <input type="color" name="badge_text" id="badge_text" class="form-control" value="{{ old('badge_text', $role->badge_text ?: '#4338ca') }}">
                </div>
            </div>
        </x-adminlte-card>

        @php $sel = old('permissions', $current ?? []); @endphp
        @if(!empty($isAdmin))
            <x-adminlte-alert theme="info" title="{{ __('roles.alerts.warning') }}" class="mb-2">
                {!! __('roles.alerts.protected') !!}
            </x-adminlte-alert>
            @include('roles._permissions', ['sections' => $sections, 'current' => $sel, 'disableInputs' => true])
        @else
            @include('roles._permissions', ['sections' => $sections, 'current' => $sel])
        @endif

        <x-adminlte-card theme="primary" theme-mode="outline">
            <x-adminlte-button type="submit" theme="primary" label="{{ __('roles.form.buttons.save') }}" icon="fas fa-save"/>
            <x-adminlte-button :href="route('roles.index')" theme="secondary" label="{{ __('roles.form.buttons.cancel') }}" icon="fas fa-times"/>
            @if (strtolower($role->name) !== 'admin')
                <x-adminlte-button theme="danger" label="{{ __('roles.form.buttons.delete') }}" icon="fas fa-trash" type="submit" form="roleDeleteForm"/>
            @endif
        </x-adminlte-card>
    </form>

    @if (strtolower($role->name) !== 'admin')
        <form id="roleDeleteForm" action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('roles.index.delete_confirm', ['name' => $role->name]) }}');">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
