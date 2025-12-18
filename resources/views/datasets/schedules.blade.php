@extends('adminlte::page')

@section('title', __('datasets.schedules.title'))

@section('content_header')
    <x-page-header :title="__('datasets.schedules.title')" icon="fas fa-calendar-alt">
        <x-slot name="subtitle">
            <small class="text-muted">{{ __('datasets.schedules.subtitle') }}</small>
        </x-slot>
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('datasets.index') }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('datasets.schedules.back') }}</span>
            </a>
            <a href="{{ route('datasets.schedules.index', array_merge(request()->query(), ['type'=>'local'])) }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-filter"></i>
                <span>{{ __('datasets.schedules.show_only_local') }}</span>
            </a>
            <a href="{{ route('datasets.schedules.index', array_merge(request()->query(), ['type'=>'all'])) }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-database"></i>
                <span>{{ __('datasets.schedules.load_fabric') }}</span>
            </a>
            <a href="{{ route('datasets.schedules.export', request()->query()) }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-file-excel"></i>
                <span>{{ __('datasets.schedules.export_excel') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    @php
        $type = $type ?? 'all';
        $q    = $q ?? '';
        $day  = $day ?? '';
        $workspace = $workspace ?? '';
        $dayOpts = [
            'Sunday' => __('common.days.sunday'),
            'Monday' => __('common.days.monday'),
            'Tuesday' => __('common.days.tuesday'),
            'Wednesday' => __('common.days.wednesday'),
            'Thursday' => __('common.days.thursday'),
            'Friday' => __('common.days.friday'),
            'Saturday' => __('common.days.saturday'),
        ];
    @endphp

    {{-- ======================== FILTERS CARD ======================== --}}
    <x-adminlte-card title="{{ __('datasets.schedules.filters.title') }}"
                     icon="fas fa-sliders-h text-lightblue"
                     theme="primary" theme-mode="outline"
                     class="mb-3">
        <form method="GET" action="{{ route('datasets.schedules.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('datasets.schedules.filters.type') }}</label>
                <select name="type" class="form-control">
                    <option value="all" {{ $type==='all'?'selected':'' }}>{{ __('datasets.schedules.filters.all') }}</option>
                    <option value="local" {{ $type==='local'?'selected':'' }}>{{ __('datasets.schedules.filters.local') }}</option>
                    <option value="fabric" {{ $type==='fabric'?'selected':'' }}>{{ __('datasets.schedules.filters.fabric') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('datasets.schedules.filters.workspace') }}</label>
                <select name="workspace" class="form-control">
                    <option value="">{{ __('datasets.schedules.filters.all') }}</option>
                    @foreach($workspaces as $ws)
                        <option value="{{ $ws->id }}" {{ $workspace===$ws->id?'selected':'' }}>
                            {{ $ws->name ?? $ws->id }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('datasets.schedules.filters.day') }}</label>
                <select name="day" class="form-control">
                    <option value="">{{ __('datasets.schedules.filters.all') }}</option>
                    @foreach($dayOpts as $key => $label)
                        <option value="{{ $key }}" {{ $day===$key?'selected':'' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('datasets.schedules.filters.search') }}</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control"
                       placeholder="{{ __('datasets.schedules.filters.placeholder') }}">
            </div>
            <div class="col-12 d-flex flex-wrap gap-2 mt-2">
                <button class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i>{{ __('datasets.schedules.filters.apply') }}
                </button>
                <a href="{{ route('datasets.schedules.index') }}" class="btn btn-primary">
                    <i class="fas fa-times me-1"></i>{{ __('datasets.schedules.filters.clear') }}
                </a>
            </div>
        </form>
    </x-adminlte-card>

    {{-- ======================== TABLE CARD ======================== --}}
    <x-adminlte-card title="{{ __('datasets.schedules.table_title') }}"
                     icon="fas fa-list-alt text-lightblue"
                     theme="primary" theme-mode="outline"
                     body-class="p-0">
        @if(empty($rows) || count($rows) === 0)
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-times fa-2x mb-3"></i>
                <p class="mb-0">{{ __('datasets.schedules.empty') }}</p>
            </div>
        @else
            <div class="table-responsive shadow-sm rounded-3">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('datasets.schedules.table.source') }}</th>
                            <th>{{ __('datasets.schedules.table.workspace') }}</th>
                            <th>{{ __('datasets.schedules.table.dataset') }}</th>
                            <th>{{ __('datasets.schedules.table.status') }}</th>
                            <th>{{ __('datasets.schedules.table.tz') }}</th>
                            <th>{{ __('datasets.schedules.table.days') }}</th>
                            <th>{{ __('datasets.schedules.table.times') }}</th>
                            <th class="text-center">{{ __('datasets.schedules.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $r)
                            @php
                                $wsId = $r['workspace_id'];
                                $dsId = $r['dataset_id'];
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge {{ $r['source']==='local' ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ $r['source']==='local' ? __('datasets.schedules.badges.local') : __('datasets.schedules.badges.fabric') }}
                                    </span>
                                </td>
                                <td class="small">{{ $r['workspace'] }}</td>
                                <td class="small">
                                    <div class="fw-semibold">{{ $r['dataset_name'] }}</div>
                                    <div class="text-muted text-monospace small">{{ $r['dataset_id'] }}</div>
                                </td>
                                <td class="small">
                                    <span class="badge {{ ($r['enabled']??false) ? 'bg-success' : 'bg-secondary' }}">
                                        {{ ($r['enabled']??false) ? __('datasets.schedules.enabled') : __('datasets.schedules.disabled') }}
                                    </span>
                                    @if(!empty($r['last_status']))
                                        <div class="text-muted small mt-1">
                                            {{ __('datasets.schedules.last') }} {{ $r['last_status'] }}
                                            {{ $r['last_at'] ? '(' . $r['last_at'] . ')' : '' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-monospace small">{{ $r['tz'] }}</td>
                                <td class="small">{{ implode(', ', (array)$r['days']) }}</td>
                                <td class="small">{{ implode(', ', (array)$r['times']) }}</td>
                                <td class="text-center">
                                    <form action="{{ route('datasets.refresh', [$wsId, $dsId]) }}" method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('{{ __('datasets.schedules.refresh_confirm') }}')">
                                        @csrf
                                        <button class="btn btn-primary btn-icon"
                                                title="{{ __('datasets.schedules.refresh_now') }}">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-adminlte-card>
@stop

@push('css')
<style>
/* Header buttons */
.header-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}
.header-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    line-height: 1;
    font-size: .875rem;
    padding: 0 .8rem;
}
.header-btn i { font-size: .9rem; margin-right: .4rem; }

/* Compact filters */
.card form select.form-control,
.card form input.form-control {
    height: calc(1.9rem + 2px) !important;
    padding: .25rem .5rem !important;
    font-size: .8125rem !important;
}
.card form label.form-label {
    font-size: .8125rem !important;
    font-weight: 600;
    margin-bottom: .25rem;
}
.card form .btn {
    height: calc(1.9rem + 2px);
    padding: 0 .9rem;
    font-size: .8125rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.card form .btn i {
    font-size: .8rem;
    margin-right: .3rem;
}

/* Table visuals */
.text-monospace {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}
.btn-icon {
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .85rem;
    border-radius: 6px;
}
</style>
@endpush
