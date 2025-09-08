@extends('adminlte::page')

@section('title', __('schedule.title'))

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">{{ __('schedule.title') }}</h1>
            <small class="text-muted">Workspace: {{ $workspace->name ?? $workspace->id }} - Dataset: {{ $datasetId }}</small>
        </div>
        <a href="{{ route('datasets.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> {{ __('schedule.back') }}</a>
    </div>
@stop

@section('content')
    @if(session('status'))
        <x-adminlte-alert theme="success" title="{{ __('datasets.alert_ok') }}">{{ session('status') }}</x-adminlte-alert>
    @endif
    @if(session('error'))
        <x-adminlte-alert theme="danger" title="{{ __('datasets.alert_error') }}">{{ session('error') }}</x-adminlte-alert>
    @endif

    {{-- Fabric schedule summary --}}
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ __('schedule.fabric_summary') }}</h3>
            
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
                    <div class="small text-muted">{{ __('schedule.status') }}</div>
                    <div>
                        <span class="badge {{ $sumEnabled ? 'bg-success' : 'bg-secondary' }}">{{ $sumEnabled ? __('common.active') : __('common.paused') }}</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">{{ __('schedule.timezone') }}</div>
                    <div class="text-monospace small">{{ $sumTz }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">{{ __('schedule.days') }}</div>
                    <div class="small">{{ $sumDays ?: '-' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="small text-muted">{{ __('schedule.times') }}</div>
                    <div class="small">{{ $sumTimes ?: '-' }}</div>
                </div>
                @if($sumNotify !== '')
                <div class="col-md-3">
                    <div class="small text-muted">{{ __('schedule.notification') }}</div>
                    <div class="small">{{ $sumNotify }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Local schedules --}}
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">{{ __('schedule.local_schedules') }}</h3>
        </div>
        <div class="card-body">
            @if(session('warning'))
                <x-adminlte-alert theme="warning" title="{{ __('common.warning') }}">{{ session('warning') }}</x-adminlte-alert>
            @endif

            @php $localSchedules = $localSchedules ?? collect(); @endphp
            @if($localSchedules->isEmpty())
                <div class="text-muted">{{ __('schedule.none') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('schedule.status') }}</th>
                                <th>{{ __('schedule.last_result') }}</th>
                                <th>{{ __('schedule.days') }}</th>
                                <th>{{ __('schedule.times') }}</th>
                                <th class="text-end">{{ __('schedule.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($localSchedules as $s)
                                <tr>
                                    <td>
                                        <span class="badge {{ $s->enabled ? 'bg-success' : 'bg-secondary' }}">{{ $s->enabled ? __('schedule.state_active') : __('schedule.state_inactive') }}</span>
                                    </td>
                                    <td class="small">
                                        {{ $s->last_result ?: '-' }}
                                    </td>
                                    <td class="small">{{ $s->days }}</td>
                                    <td class="small">{{ $s->times }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('datasets.local.toggle', [$workspace->id, $datasetId, $s->id]) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-xs btn-outline-primary">{{ __('schedule.toggle') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('datasets.local.delete', [$workspace->id, $datasetId, $s->id]) }}" class="d-inline" onsubmit="return confirm('{{ __('schedule.delete_q') }}')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger">{{ __('schedule.delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <hr>
            <h5 class="mb-2">{{ __('schedule.new_schedule') }}</h5>
            <form method="POST" action="{{ route('datasets.local.store', [$workspace->id, $datasetId]) }}" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">{{ __('schedule.days') }}</label>
                    @php $dayOpts = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; @endphp
                    <div class="form-check mb-1">
                        <input id="days-all" class="form-check-input" type="checkbox">
                        <label class="form-check-label" for="days-all">{{ __('schedule.select_all') }}</label>
                    </div>
                    <select id="days-select" name="days[]" class="form-control" multiple size="7">
                        @foreach($dayOpts as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('schedule.times') }} (HH:mm)</label>
                    <input type="text" id="ds-times" name="times" class="form-control" placeholder="08:00,12:30,18:00">
                    <small id="times-warning" class="text-muted" style="display:none">{{ __('schedule.times_hint') }}</small>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary"><i class="fas fa-plus"></i> {{ __('schedule.add') }}</button>
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
