@extends('adminlte::page')

@section('title', 'Mis favoritos')

@section('content_header')
    <h1>Mis favoritos</h1>
@stop

@section('content')
    @include('portal.partials.report-card-styles')

    <div class="card">
        <div class="card-body">
            @if($reports->isEmpty())
                <div class="text-center text-muted py-4">Todavía no marcaste favoritos.</div>
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
