@extends('adminlte::page')

@section('title', __('portal.home.title'))

@section('css')
    @include('portal.partials.base-styles')
    @include('portal.partials.home-card-styles')
    @include('portal.partials.report-card-styles')
    <style>
        .portal-folders {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }

        .portal-folder-link {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1rem;
            border-radius: .6rem;
            font-size: .875rem;
            color: var(--app-card-text);
            background-color: var(--app-card-alt-bg);
            border: 1px solid transparent;
            transition: all .15s ease-in-out;
            text-decoration: none;
        }

        .portal-folder-link i {
            font-size: .95rem;
        }

        .portal-folder-link .badge {
            font-size: .75rem;
            font-weight: 600;
            padding: .2rem .5rem;
            background-color: var(--app-icon-bg);
            color: inherit;
        }

        .portal-folder-name {
            font-weight: 600;
            max-width: 16rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .portal-folder-link.active {
            background-color: var(--app-hi-bg);
            border-color: var(--app-hi-bg);
            color: var(--app-hi-text);
            box-shadow: 0 .65rem 1.2rem color-mix(in srgb, var(--app-hi-bg) 24%, transparent);
        }

        .portal-folder-link.active .badge {
            background-color: rgba(255, 255, 255, .25);
            color: var(--app-hi-text);
        }

        .portal-folder-link:not(.active):hover {
            background-color: var(--app-icon-bg);
            color: var(--app-icon-text);
        }

        .portal-folder-link:not(.active):hover .badge {
            background-color: var(--app-card-bg);
        }

        .portal-folders-empty {
            color: var(--app-card-text);
            opacity: .72;
            padding: .5rem 0;
        }

        @media (max-width: 576px) {
            .portal-folder-link {
                flex: 1 1 calc(50% - .5rem);
                justify-content: center;
                text-align: center;
            }
        }
    </style>
@endsection

@section('content_header')
    <x-page-header :title="__('portal.home.title')" icon="fas fa-th-large" />
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Folder Section --}}
    <div class="portal-card">
        <div class="card-header">
            <span class="portal-icon">
                <i class="fas fa-folder-open"></i>
            </span>
            <h5 class="mb-0">{{ __('portal.home.folders.title') }}</h5>
        </div>
        <div class="card-body">
            <div class="portal-folders">
                @forelse($folders as $f)
                    <a class="portal-folder-link {{ ($selected && $selected->id === $f->id) ? 'active' : '' }}"
                       href="{{ route('portal.index', ['carpeta_id'=>$f->id]) }}">
                        <i class="fas fa-folder"></i>
                        <span class="portal-folder-name">{{ $f->name }}</span>
                        <span class="badge">{{ $f->reports_count }}</span>
                    </a>
                @empty
                    <span class="portal-folders-empty">{{ __('portal.home.folders.empty') }}</span>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Reports Section --}}
    <div class="portal-card">
        <div class="card-header">
            <span class="portal-icon">
                <i class="fas fa-chart-bar"></i>
            </span>
            <div>
                <h5 class="mb-0">{{ __('portal.home.reports.title') }}</h5>
                @if($selected)
                    <small>
                        {{ __('portal.home.reports.in_folder', ['name' => $selected->name]) }}
                    </small>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if(($reports ?? collect())->isEmpty())
                <div class="portal-empty-state">
                    <i class="fas fa-folder-open"></i>
                    <div>{{ __('portal.home.reports.empty') }}</div>
                </div>
            @else
                <div class="report-grid">
                    @foreach($reports as $report)
                        @include('portal.partials.report-card', ['report' => $report, 'favIds' => $favIds])
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    @include('portal.partials.fav-toggle-script')
@stop
