@extends('adminlte::page')

@section('title', __('portal.most_accessed.title'))

@section('content_header')
    <h1>{{ __('portal.most_accessed.title') }}</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        @if($items->isEmpty())
            <div class="text-center text-muted">{{ __('portal.most_accessed.empty') }}</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr>
                        <th>{{ __('portal.most_accessed.columns.report') }}</th>
                        <th class="text-right">{{ __('portal.most_accessed.columns.views') }}</th>
                        <th></th>
                    </tr></thead>
                    <tbody>
                    @foreach($items as $i)
                        <tr>
                            <td>{{ $i['name'] }}</td>
                            <td class="text-right">{{ $i['total'] }}</td>
                            <td class="text-right">
                                @if(!empty($i['workspace_id']))
                                    <a href="{{ route('reports.show', [$i['workspace_id'], $i['id']]) }}" class="btn btn-sm btn-outline-primary">
                                        {{ __('portal.most_accessed.actions.open') }}
                                    </a>
                                @endif
                            </td>
                        </td>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div></div>
@stop
