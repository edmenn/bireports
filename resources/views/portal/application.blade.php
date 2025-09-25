@extends('adminlte::page')

@section('title', $application->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-0">{{ $application->name }}</h1>
            @if(!empty($application->description))
                <small class="text-muted">{{ $application->description }}</small>
            @else
                <small class="text-muted">{{ __('applications.runtime.subtitle') }}</small>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('portal.apps') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('applications.actions.exit_application') }}
            </a>
            <a href="{{ route('portal.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-home me-1"></i> {{ __('applications.actions.go_home') }}
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(!$currentReport)
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-info-circle me-1"></i> {{ __('applications.runtime.no_reports') }}
        </div>
    @else
        <div class="row g-3 application-layout">
            <div class="col-lg-3 col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header fw-semibold">
                        <i class="fas fa-stream me-1"></i>{{ __('applications.runtime.sidebar_title') }}
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush application-sidebar" role="navigation">
                            @foreach($sections as $section)
                                <div class="list-group-item fw-semibold text-uppercase small text-muted">
                                    <i class="fas fa-folder-open me-1"></i>{{ $section->name }}
                                </div>
                                @foreach($section->reports as $report)
                                    <a href="{{ route('portal.applications.show', ['application' => $application, 'report' => $report->id]) }}"
                                       class="list-group-item list-group-item-action application-sidebar__item {{ $currentReport && $currentReport->id === $report->id ? 'active' : '' }}"
                                       aria-current="{{ $currentReport && $currentReport->id === $report->id ? 'true' : 'false' }}">
                                        <span class="ms-3">{{ $report->name }}</span>
                                    </a>
                                @endforeach
                            @endforeach

                            @if($sections->isNotEmpty() && $looseReports->isNotEmpty())
                                <div class="list-group-item fw-semibold text-uppercase small text-muted">
                                    <i class="fas fa-layer-group me-1"></i>{{ __('applications.runtime.other_reports') }}
                                </div>
                            @endif

                            @foreach($looseReports as $report)
                                <a href="{{ route('portal.applications.show', ['application' => $application, 'report' => $report->id]) }}"
                                   class="list-group-item list-group-item-action application-sidebar__item {{ $currentReport && $currentReport->id === $report->id ? 'active' : '' }}"
                                   aria-current="{{ $currentReport && $currentReport->id === $report->id ? 'true' : 'false' }}">
                                    {{ $report->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9 col-md-8">
                <div class="card shadow-sm h-100">
                    <div class="card-body p-0 application-viewer">
                        <iframe
                            src="{{ $iframeUrl }}"
                            title="{{ $currentReport->name }}"
                            class="application-viewer__frame"
                            allowfullscreen
                            frameborder="0"></iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif
@stop

@push('css')
<style>
.application-layout { min-height: 70vh; }
.application-sidebar { max-height: 70vh; overflow-y: auto; }
.application-sidebar__item.active { background-color: #0d6efd; color: #fff; font-weight: 600; }
.application-sidebar__item.active:hover { color: #fff; }
.application-sidebar__item { font-size: 0.95rem; }
.application-viewer__frame { width: 100%; min-height: 75vh; border: 0; }
@media (max-width: 991.98px) {
    .application-sidebar { max-height: none; }
    .application-viewer__frame { min-height: 60vh; }
}
</style>
@endpush