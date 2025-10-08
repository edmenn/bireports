@extends('adminlte::page')

@section('title', __('folders.workspaces.show.title', ['name' => $workspace->name]))

@section('content_header')
    <h1>{{ __('folders.workspaces.show.title', ['name' => $workspace->name]) }}</h1>
@stop

@section('content')
    @if($reports->isEmpty())
        <div class="alert alert-info">{{ __('folders.workspaces.show.empty') }}</div>
    @else
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{{ __('folders.workspaces.show.table.title') }}</th>
                    <th>{{ __('folders.workspaces.show.table.dataset') }}</th>
                    <th class="text-right">{{ __('folders.workspaces.show.table.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($reports as $r)
                    <tr>
                        <td>{{ $r->name }}</td>
                        <td><code>{{ $r->dataset_id }}</code></td>
                        <td class="text-right">
                            <a class="btn btn-sm btn-primary"
                               href="{{ route('reports.show', [$workspace->id, $r->id]) }}">
                                {{ __('folders.workspaces.show.table.open') }}
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
@stop
