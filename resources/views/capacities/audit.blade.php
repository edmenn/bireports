@extends('adminlte::page')

@section('title', __('capacities.audit.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">{{ __('capacities.audit.header', ['name' => $capacity->display_name]) }}</h1>
            <small class="text-muted">{{ __('capacities.shared.sku_region', ['sku' => $capacity->sku, 'region' => $capacity->region]) }}</small>
        </div>
        <div class="d-flex" style="gap: .5rem;">
            <form action="{{ route('capacities.audit.refresh', $capacity) }}" method="POST" onsubmit="return confirm('{{ __('capacities.audit.refresh_confirm') }}');">
                @csrf
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-sync-alt"></i> {{ __('common.refresh') }}</button>
            </form>
            <a href="{{ route('capacities.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> {{ __('common.back') }}</a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('capacities.audit.table.date') }}</th>
                        <th>{{ __('capacities.audit.table.action') }}</th>
                        <th>{{ __('capacities.audit.table.result') }}</th>
                        <th>{{ __('capacities.audit.table.source') }}</th>
                        <th>{{ __('capacities.audit.table.detail') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $l)
                        <tr>
                            <td class="text-nowrap text-monospace small">{{ optional($l->created_at)->format('d-m-Y H:i:s') }}</td>
                            @php
                                $actionKey = strtolower($l->action ?? 'other');
                                $actionLabelKey = 'capacities.audit.actions.' . ($actionKey !== '' ? $actionKey : 'other');
                                $actionLabel = app('translator')->has($actionLabelKey)
                                    ? __($actionLabelKey)
                                    : ($l->action ?? __('capacities.audit.actions.other'));
                                $resultKey = strtolower($l->result ?? '');
                                $resultLabelKey = 'capacities.audit.results.' . ($resultKey !== '' ? $resultKey : 'unknown');
                                $resultLabel = app('translator')->has($resultLabelKey)
                                    ? __($resultLabelKey)
                                    : ($l->result ?? __('capacities.audit.results.unknown'));
                            @endphp
                            <td>{{ $actionLabel }}</td>
                            <td><span class="badge {{ ($resultKey==='success') ? 'bg-success' : (($resultKey==='error')?'bg-danger':'bg-secondary') }}">{{ $resultLabel }}</span></td>
                            <td>
                                @php
                                    $src = strtolower($l->source ?? 'local');
                                    $sourceLabelKey = 'capacities.audit.sources.' . $src;
                                    $sourceLabel = app('translator')->has($sourceLabelKey)
                                        ? __($sourceLabelKey)
                                        : strtoupper($src);
                                @endphp
                                <span class="badge {{ $src==='api' ? 'bg-primary' : 'bg-secondary' }}">{{ $sourceLabel }}</span>
                            </td>
                            <td class="small">{{ $l->message }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('capacities.audit.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
