@extends('layouts.app')

@section('title', __('portal.favorites.title'))

@section('css')
    @include('portal.partials.base-styles')
    @include('portal.partials.home-card-styles')
    @include('portal.partials.report-card-styles')
@endsection

@section('content_header')
    <x-page-header :title="__('portal.favorites.title')" icon="fas fa-star">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('portal.index') }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-home"></i>
                <span>{{ __('applications.actions.go_home') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    <div class="portal-card">
        <div class="card-header">
            <span class="portal-icon">
                <i class="fas fa-star"></i>
            </span>
            <h5 class="mb-0">{{ __('portal.favorites.title') }}</h5>
        </div>
        <div class="card-body">
            @if($reports->isEmpty())
                <div class="portal-empty-state">
                    <i class="fas fa-star"></i>
                    <div>{{ __('portal.favorites.empty') }}</div>
                </div>
            @else
                <div class="report-grid">
                    @foreach($reports as $report)
                        @include('portal.partials.report-card', ['report' => $report, 'favIds' => $favIds])
                    @endforeach
                </div>

                @if(method_exists($reports, 'links'))
                    <div class="mt-3">
                        {{ $reports->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    @include('portal.partials.fav-toggle-script')
@stop
