@extends('adminlte::page')

@section('title', __('applications.portal.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-0">{{ __('applications.portal.title') }}</h1>
            <small class="text-muted">{{ __('applications.portal.subtitle') }}</small>
        </div>
    </div>
@stop

@section('content')
    @if ($applications->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-th-large fa-3x text-muted mb-3" aria-hidden="true"></i>
                <h5 class="mb-2">{{ __('applications.portal.empty_title') }}</h5>
                <p class="text-muted mb-0">{{ __('applications.portal.empty_description') }}</p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($applications as $application)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card h-100 shadow-sm application-card">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <h2 class="h5 mb-0 text-truncate" title="{{ $application->name }}">{{ $application->name }}</h2>
                                @if($application->is_active)
                                    <span class="badge bg-success">{{ __('applications.status.active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('applications.status.inactive') }}</span>
                                @endif
                            </div>

                            @if(!empty($application->description))
                                <p class="text-muted small mb-3 application-card__description">{{ $application->description }}</p>
                            @endif

                            <dl class="row row-cols-1 g-1 small mb-3 text-muted">
                                <div class="col">
                                    <span class="fw-semibold">{{ __('applications.portal.reports_count') }}:</span>
                                    {{ $application->reports_count }}
                                </div>
                                <div class="col">
                                    <span class="fw-semibold">{{ __('applications.portal.sections_count') }}:</span>
                                    {{ $application->sections_count }}
                                </div>
                            </dl>

                            <div class="mt-auto">
                                <a href="{{ route('portal.applications.show', $application) }}"
                                   class="btn btn-primary w-100">
                                    <i class="fas fa-play me-1"></i> {{ __('applications.actions.open') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@stop

@push('css')
<style>
.application-card__description {
    min-height: 3rem;
}
</style>
@endpush