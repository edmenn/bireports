@extends('layouts.app')

@section('title', __('portal.most_accessed.title'))

@section('css')
    @include('portal.partials.base-styles')
    @include('portal.partials.home-card-styles')
    <style>
        .portal-table thead th {
            background-color: var(--app-card-alt-bg);
            font-weight: 600;
            font-size: .9rem;
            color: var(--app-card-text);
            border-bottom: none;
        }

        .portal-table tbody tr:hover {
            background-color: var(--app-card-alt-bg);
        }

        .portal-table th,
        .portal-table td {
            vertical-align: middle !important;
            border-top: 1px solid rgba(15, 34, 58, .08);
        }

        .portal-table td {
            font-size: .9rem;
        }

        .portal-table .actions-group {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .5rem;
        }

        .portal-table .actions-group .btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: .35rem;
            height: 34px !important;
            line-height: 1 !important;
            padding: 0 .75rem !important;
            font-size: .8125rem !important;
            border-radius: .35rem;
        }

        .portal-table .actions-group .btn i {
            font-size: .85rem;
            margin: 0;
        }
    </style>
@endsection

@section('content_header')
    <x-page-header :title="__('portal.most_accessed.title')" icon="fas fa-chart-bar" />
@stop

@section('content')
    <div class="portal-card">
        <div class="card-header">
            <span class="portal-icon">
                <i class="fas fa-chart-line"></i>
            </span>
            <h5 class="mb-0">{{ __('portal.most_accessed.title') }}</h5>
        </div>
        <div class="card-body">
            @if($items->isEmpty())
                <div class="portal-empty-state">
                    <i class="fas fa-chart-line"></i>
                    <div>{{ __('portal.most_accessed.empty') }}</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table portal-table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('portal.most_accessed.columns.report') }}</th>
                                <th class="text-right">{{ __('portal.most_accessed.columns.views') }}</th>
                                <th class="text-right" style="width:120px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $i)
                                <tr>
                                    <td>{{ $i['name'] }}</td>
                                    <td class="text-right">{{ $i['total'] }}</td>
                                    <td class="text-right">
                                        <div class="actions-group">
                                            @if(!empty($i['workspace_id']))
                                                <a href="{{ route('reports.show', [$i['workspace_id'], $i['id']]) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-external-link-alt"></i>
                                                    <span>{{ __('portal.most_accessed.actions.open') }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop
