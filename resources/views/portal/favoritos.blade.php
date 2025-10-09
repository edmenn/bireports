@extends('adminlte::page')

@section('title', __('portal.favorites.title'))

@section('content_header')
    <h1>{{ __('portal.favorites.title') }}</h1>
@stop

@section('content')
    @include('portal.partials.report-card-styles')

    <div class="card">
        <div class="card-body">
            @if($reports->isEmpty())
                <div class="text-center text-muted py-4">{{ __('portal.favorites.empty') }}</div>
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
