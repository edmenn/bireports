@extends('adminlte::page')

@section('title', __('portal.most_accessed.title'))

@section('css')
<style>
    /* Compact and consistent header actions (AdminLTE 3 + Bootstrap 4) */
    .header-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: .5rem;
    }

    .header-actions a.btn,
    .header-actions button.btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 38px !important;
        line-height: 1 !important;
        padding: 0 .9rem !important;
        font-size: .875rem !important;
    }

    .header-actions i {
        font-size: .9rem;
        margin-right: .4rem;
    }

    @media (max-width: 480px) {
        .header-actions {
            justify-content: center;
            text-align: center;
        }
        .header-actions a.btn,
        .header-actions button.btn {
            flex: 1 1 auto;
        }
    }

    /* Table consistency */
    .card {
        box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,.05);
        border-radius: .5rem;
    }

    table th, table td {
        vertical-align: middle !important;
    }

    .table thead th {
        background-color: #e9ecef;
        font-weight: 600;
        font-size: .9rem;
    }

    /* Centered "Open" button inside table */
    .actions-group {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    .actions-group .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: .4rem;
        height: 34px !important;
        line-height: 1 !important;
        padding: 0 .75rem !important;
        font-size: .8125rem !important;
        border-radius: .35rem;
    }

    .actions-group .btn i {
        font-size: .85rem;
        margin: 0;
    }

    .text-center.text-muted.py-4 i {
        opacity: .5;
    }
</style>
@endsection

@section('content_header')
    {{-- Centered title and cleaner header --}}
    <x-page-header :title="__('portal.most_accessed.title')" icon="fas fa-chart-bar" />
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            @if($items->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                    <div>{{ __('portal.most_accessed.empty') }}</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
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
                                                <a href="{{ route('reports.show', [$i['workspace_id'], $i['id']]) }}" class="btn btn-outline-primary btn-sm">
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
