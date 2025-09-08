@extends('adminlte::page')

@section('title', __('schedules.title'))

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">{{ __('schedules.title') }}</h1>
            <small class="text-muted">{{ __('schedules.subtitle') }}</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('datasets.schedules.index', array_merge(request()->query(), ['type'=>'local'])) }}" class="btn btn-outline-secondary">
                <i class="fas fa-filter"></i> {{ __('schedules.show_only_local') }}
            </a>
            <a href="{{ route('datasets.schedules.index', array_merge(request()->query(), ['type'=>'all'])) }}" class="btn btn-outline-secondary">
                <i class="fas fa-database"></i> {{ __('schedules.load_fabric') }}
            </a>
            <a href="{{ route('datasets.schedules.export', request()->query()) }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-export"></i> {{ __('schedules.export_csv') }}
            </a>
        </div>
    </div>
@stop

@section('content')
    @php
        $type = $type ?? 'all';
        $q    = $q ?? '';
        $day  = $day ?? '';
        $workspace = $workspace ?? '';
    @endphp

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('datasets.schedules.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">{{ __('schedules.filters.type') }}</label>
                    <select name="type" class="form-control">
                        <option value="all" {{ $type==='all'?'selected':'' }}>{{ __('schedules.filters.all') }}</option>
                        <option value="local" {{ $type==='local'?'selected':'' }}>{{ __('schedules.filters.local') }}</option>
                        <option value="fabric" {{ $type==='fabric'?'selected':'' }}>{{ __('schedules.filters.fabric') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">{{ __('schedules.filters.workspace') }}</label>
                    <select name="workspace" class="form-control">
                        <option value="">{{ __('schedules.filters.all') }}</option>
                        @foreach($workspaces as $ws)
                            <option value="{{ $ws->id }}" {{ $workspace===$ws->id?'selected':'' }}>{{ $ws->name ?? $ws->id }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">{{ __('schedules.filters.day') }}</label>
                    @php $dayOpts = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; @endphp
                    <select name="day" class="form-control">
                        <option value="">{{ __('schedules.filters.all') }}</option>
                        @foreach($dayOpts as $d)
                            <option value="{{ $d }}" {{ $day===$d?'selected':'' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">{{ __('schedules.filters.search') }}</label>
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="{{ __('schedules.filters.placeholder') }}">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary"><i class="fas fa-filter"></i> {{ __('schedules.filters.apply') }}</button>
                    <a href="{{ route('datasets.schedules.index') }}" class="btn btn-outline-secondary">{{ __('schedules.filters.clear') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('schedules.th.source') }}</th>
                        <th>{{ __('schedules.th.workspace') }}</th>
                        <th>{{ __('schedules.th.dataset') }}</th>
                        <th class="text-nowrap">{{ __('schedules.th.status') }}</th>
                        <th>{{ __('schedules.th.tz') }}</th>
                        <th>{{ __('schedules.th.days') }}</th>
                        <th>{{ __('schedules.th.times') }}</th>
                        <th class="text-end">{{ __('schedules.th.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        @php
                            $wsId = $r['workspace_id'];
                            $dsId = $r['dataset_id'];
                        @endphp
                        <tr>
                            <td>
                                <span class="badge {{ $r['source']==='local' ? 'bg-primary' : 'bg-secondary' }}">
                                    {{ $r['source']==='local' ? __('schedules.filters.local') : 'Fabric' }}
                                </span>
                            </td>
                            <td class="small">{{ $r['workspace'] }}</td>
                            <td class="small">
                                <div class="fw-semibold">{{ $r['dataset_name'] }}</div>
                                <div class="text-muted text-monospace">{{ $r['dataset_id'] }}</div>
                            </td>
                            <td class="small">
                                <span class="badge {{ ($r['enabled']??false) ? 'bg-success':'bg-secondary' }}">{{ ($r['enabled']??false) ? __('schedules.enabled') : __('schedules.disabled') }}</span>
                                @if(!empty($r['last_status']))
                                    <div class="text-muted">{{ __('schedules.last') }} {{ $r['last_status'] }} {{ $r['last_at'] ? '(' . $r['last_at'] . ')' : '' }}</div>
                                @endif
                            </td>
                            <td class="small text-monospace">{{ $r['tz'] }}</td>
                            <td class="small">{{ implode(', ', (array)$r['days']) }}</td>
                            <td class="small">{{ implode(', ', (array)$r['times']) }}</td>
                            <td class="text-end">
                                <form action="{{ route('datasets.refresh', [$wsId, $dsId]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('schedules.refresh_now_q') }}')">
                                    @csrf
                                    <button class="btn btn-xs btn-outline-primary"><i class="fas fa-sync"></i> {{ __('schedules.refresh_now') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">{{ __('schedules.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($datasets ?? null, 'links'))
            <div class="card-footer">
                {{ $datasets->links() }}
            </div>
        @endif
    </div>
@stop
