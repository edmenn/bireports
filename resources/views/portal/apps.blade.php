@extends('layouts.app')

@section('title', __('applications.portal.title'))

@section('content_header')
    <x-page-header :title="__('applications.portal.title')" :subtitle="__('applications.portal.subtitle')" icon="fas fa-th-large" />
@stop

@section('content')
    <x-ui-card title="{{ __('applications.portal.card_title') }}" icon="fas fa-th-large text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3" body-class="p-4">

        @if ($applications->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-th-large fa-3x text-muted mb-3" aria-hidden="true"></i>
                <h5 class="mb-2">{{ __('applications.portal.empty_title') }}</h5>
                <p class="text-muted mb-0">{{ __('applications.portal.empty_description') }}</p>
            </div>
        @else
            <div class="row g-4">
                @foreach ($applications as $application)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                        <div class="card h-100 border-0 shadow-sm application-card">
                            <div class="card-body d-flex flex-column p-3">
                                {{-- Application title and status --}}
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <h2 class="h6 fw-semibold mb-0 text-truncate" title="{{ $application->name }}">
                                        {{ $application->name }}
                                    </h2>
                                    <span class="badge {{ $application->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $application->is_active
                                            ? __('applications.status.active')
                                            : __('applications.status.inactive') }}
                                    </span>
                                </div>

                                {{-- Description --}}
                                @if(!empty($application->description))
                                    <p class="text-muted small mb-3 application-card__description">
                                        {{ \Illuminate\Support\Str::limit($application->description, 120) }}
                                    </p>
                                @else
                                    <p class="text-muted small mb-3 application-card__description">—</p>
                                @endif

                                {{-- Stats --}}
                                <div class="small text-muted mb-3">
                                    <div>
                                        <i class="fas fa-file-alt me-1 text-secondary"></i>
                                        <span class="fw-semibold">{{ __('applications.portal.reports_count') }}:</span>
                                        {{ $application->reports_count }}
                                    </div>
                                    <div>
                                        <i class="fas fa-layer-group me-1 text-secondary"></i>
                                        <span class="fw-semibold">{{ __('applications.portal.sections_count') }}:</span>
                                        {{ $application->sections_count }}
                                    </div>
                                </div>

                                {{-- Open button --}}
                                <div class="mt-auto pt-2">
                                    <a href="{{ route('portal.applications.show', $application) }}"
                                       class="btn btn-primary w-100 fw-semibold">
                                        {{ __('applications.actions.open') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui-card>
@stop

@push('css')
<style>
/* Cards layout */
.application-card {
    border-radius: .75rem !important;
    transition: all .15s ease-in-out;
    background-color: var(--app-card-bg);
}
.application-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* Description block */
.application-card__description {
    min-height: 2.6rem;
    line-height: 1.35;
}

/* Card title */
.application-card h2 {
    font-size: 1rem;
    color: var(--app-card-text);
}

/* Status badge */
.badge {
    font-size: .7rem;
    padding: .4em .6em;
    border-radius: .35rem;
    text-transform: uppercase;
}

/* Button */
.btn-primary {
    font-size: .875rem;
    height: 36px;
    border-radius: .5rem;
}

/* Responsive */
@media (max-width: 576px) {
    .application-card {
        margin-bottom: 1rem;
    }
}
</style>
@endpush
