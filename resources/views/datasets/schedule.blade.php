@extends('adminlte::page')

@section('title', __('datasets.schedule.title'))

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">{{ __('datasets.schedule.title') }}</h1>
            <small class="text-muted">{{ __('datasets.schedule.subtitle', ['workspace' => $workspace->name ?? $workspace->id, 'dataset' => $datasetId]) }}</small>
        </div>
        <a href="{{ route('datasets.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> {{ __('datasets.schedule.back') }}</a>
    </div>
@stop

@section('content')
    @if(session('status'))
        <x-adminlte-alert theme="success" title="{{ __('datasets.alerts.success') }}">{{ session('status') }}</x-adminlte-alert>
    @endif
    @if(session('error'))
        <x-adminlte-alert theme="danger" title="{{ __('datasets.alerts.error') }}">{{ session('error') }}</x-adminlte-alert>
    @endif

    {{-- Fabric schedule summary --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ __('datasets.schedule.fabric.title') }}</h3>
            
        </div>
        <div class="card-body">
            @php
                $val = (array)($schedule['value'] ?? []);
                $sumEnabled = (bool)($val['enabled'] ?? false);
                $sumTz = (string)($val['localTimeZoneId'] ?? ($defaultTz ?? 'UTC'));
                $sumDays = implode(', ', (array)($val['days'] ?? []));
                $sumTimes = implode(', ', (array)($val['times'] ?? []));
                $sumNotify = (string)($val['notifyOption'] ?? '');
            @endphp
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="small text-muted">{{ __('datasets.schedule.fabric.status') }}</div>
                    <div>
                        <span class="badge {{ $sumEnabled ? 'bg-success' : 'bg-secondary' }}">{{ $sumEnabled ? __('common.active') : __('common.paused') }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">{{ __('datasets.schedule.fabric.timezone') }}</div>
                    <div class="text-monospace small">{{ $sumTz }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">{{ __('datasets.schedule.fabric.days') }}</div>
                    <div class="small">{{ $sumDays ?: '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">{{ __('datasets.schedule.fabric.times') }}</div>
                    <div class="small">{{ $sumTimes ?: '-' }}</div>
                </div>
                @if($sumNotify !== '')
                <div class="col-md-3">
                    <div class="small text-muted">{{ __('datasets.schedule.fabric.notification') }}</div>
                    <div class="small">{{ $sumNotify }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Local schedules --}}
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ __('datasets.schedule.local.title') }}</h3>
        </div>
        <div class="card-body">
            @if(session('warning'))
                <x-adminlte-alert theme="warning" title="{{ __('common.warning') }}">{{ session('warning') }}</x-adminlte-alert>
            @endif

            @php $localSchedules = $localSchedules ?? collect(); @endphp
            @if($localSchedules->isEmpty())
                <div class="text-muted">{{ __('datasets.schedule.local.empty') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('datasets.schedule.local.status') }}</th>
                                <th>{{ __('datasets.schedule.local.last_result') }}</th>
                                <th>{{ __('datasets.schedule.local.days') }}</th>
                                <th>{{ __('datasets.schedule.local.times') }}</th>
                                <th class="text-end">{{ __('datasets.schedule.local.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localSchedules as $s)
                                <tr>
                                    <td>
                                        <span class="badge {{ $s->enabled ? 'bg-success' : 'bg-secondary' }}">{{ $s->enabled ? __('datasets.schedule.local.state_active') : __('datasets.schedule.local.state_inactive') }}</span>
                                    </td>
                                    <td class="small">
                                        {{ $s->last_result ?: '-' }}
                                    </td>
                                    <td class="small">{{ $s->days }}</td>
                                    <td class="small">{{ $s->times }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('datasets.local.toggle', [$workspace->id, $datasetId, $s->id]) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-xs btn-outline-primary">{{ __('datasets.schedule.local.toggle') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('datasets.local.delete', [$workspace->id, $datasetId, $s->id]) }}" class="d-inline" onsubmit="return confirm('{{ __('datasets.schedule.local.delete_confirm') }}')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger">{{ __('datasets.schedule.local.delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <hr>
            <h5 class="mb-2">{{ __('datasets.schedule.local.new_title') }}</h5>
            <form method="POST" action="{{ route('datasets.local.store', [$workspace->id, $datasetId]) }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">{{ __('datasets.schedule.local.days_label') }}</label>
                    @php $dayOpts = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; @endphp
                    <div class="form-check mb-1">
                        <input id="days-all" class="form-check-input" type="checkbox">
                        <label class="form-check-label" for="days-all">{{ __('datasets.schedule.local.select_all') }}</label>
                    </div>
                    <select id="days-select" name="days[]" class="form-control" multiple size="7">
                        @foreach($dayOpts as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('datasets.schedule.local.times_label') }}</label>
                    <input type="text" id="ds-times" name="times" class="form-control" placeholder="{{ __('datasets.schedule.local.times_placeholder') }}">
                    <small id="times-warning" class="text-muted" style="display:none">{{ __('datasets.schedule.local.times_hint') }}</small>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary"><i class="fas fa-plus"></i> {{ __('datasets.schedule.local.add') }}</button>
                </div>
            </form>
        </div>
    </div>
@stop

@push('js')
<script>
(function(){
  const input = document.getElementById('ds-times');
  const warn  = document.getElementById('times-warning');
  if(!input || !warn) return;
  function checkDup(){
    const raw = (input.value||'').split(',').map(s=>s.trim()).filter(Boolean);
    const norm = raw.map(s=> /^\d{2}:\d{2}$/.test(s) ? s : s);
    const seen = new Set(); let dup = false;
    for(const t of norm){ if(seen.has(t)){ dup = true; break; } seen.add(t); }
    warn.style.display = dup ? '' : 'none';
  }
  input.addEventListener('input', checkDup);
  checkDup();
})();
</script>
@endpush

@push('js')
<script>
(function(){
    const all = document.getElementById('days-all');
    const sel = document.getElementById('days-select');
    if(!all || !sel) return;
    all.addEventListener('change', function(){
        Array.from(sel.options).forEach(o => o.selected = all.checked);
    });
})();
</script>
@endpush
