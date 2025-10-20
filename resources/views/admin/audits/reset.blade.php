@extends('adminlte::page')

@section('title', __('audit.reset.title'))

@section('css')
    @include('portal.partials.base-styles')
    @include('portal.partials.home-card-styles')
@endsection

@section('content_header')
    <x-page-header :title="__('audit.reset.title')" icon="fas fa-trash-alt">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-chart-line"></i>
                <span>{{ __('audit.reset.back_to_usage') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    @if (session('status'))
        <x-adminlte-alert theme="success" title="{{ __('settings.alerts.success') }}" dismissable>
            {{ session('status') }}
        </x-adminlte-alert>
    @endif
    @if (session('error'))
        <x-adminlte-alert theme="danger" title="{{ __('settings.alerts.error') }}" dismissable>
            {{ session('error') }}
        </x-adminlte-alert>
    @endif

    <form method="post"
          action="{{ route('admin.audit.reset.run') }}"
          onsubmit="return confirm('{{ __('audit.reset.confirm_prompt') }}');">
        @csrf

        <div class="portal-card mt-3">
            <div class="card-header">
                <span class="portal-icon text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </span>
                <div>
                    <h5 class="mb-0 text-danger">{{ __('audit.reset.card_title') }}</h5>
                    <small class="text-muted">{{ __('audit.reset.subtitle') }}</small>
                </div>
            </div>
            <div class="card-body">
                <p class="portal-reset-text">
                    {{ __('audit.reset.description') }}
                </p>

                <ul class="portal-reset-stats list-unstyled text-muted my-4">
                    <li><strong>{{ __('audit.reset.stats_current') }}:</strong> {{ number_format($total) }}</li>
                    <li><strong>{{ __('audit.reset.stats_first') }}:</strong> {{ $first_at ?? '-' }}</li>
                    <li><strong>{{ __('audit.reset.stats_last') }}:</strong> {{ $last_at ?? '-' }}</li>
                </ul>

                <x-adminlte-input name="confirm"
                                  label="{{ __('audit.reset.confirm_label') }}"
                                  placeholder="RESET"
                                  required>
                    <x-slot name="prependSlot">
                        <div class="input-group-text text-danger"><i class="fas fa-keyboard"></i></div>
                    </x-slot>
                    <x-slot name="bottom">
                        <small class="text-muted">{{ __('audit.reset.confirm_help') }}</small>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <div class="portal-card">
            <div class="card-body d-flex justify-content-center">
                <div class="d-flex flex-column flex-sm-row gap-3" style="min-width: 260px;">
                    <x-adminlte-button type="submit" theme="danger" icon="fas fa-trash-alt"
                                       label="{{ __('audit.reset.submit') }}" class="btn-eq"/>
                    <x-adminlte-button :href="route('audit.index')" theme="secondary" icon="fas fa-times"
                                       label="{{ __('audit.reset.cancel') }}" class="btn-eq"/>
                </div>
            </div>
        </div>
    </form>
@stop

@push('css')
<style>
    .portal-reset-text {
        font-size: 1.05rem;
        color: #4a4a4a;
    }
    .portal-reset-stats li {
        font-size: .95rem;
        padding-bottom: .35rem;
    }
</style>
@endpush
