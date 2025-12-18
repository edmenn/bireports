@extends('adminlte::page')

@section('title', __('datasets.schedule.title'))

@section('content_header')
    <x-page-header :title="__('datasets.schedule.title')"
                   :subtitle="__('datasets.schedule.subtitle', ['workspace' => $workspace->name ?? $workspace->id, 'dataset' => $datasetId])"
                   icon="fas fa-clock">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('datasets.index') }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-arrow-left"></i>
                <span>{{ __('datasets.schedule.back') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    {{-- Flash alerts --}}
    @if(session('status'))
        <x-adminlte-alert theme="success" icon="fas fa-check-circle">{{ session('status') }}</x-adminlte-alert>
    @endif
    @if(session('error'))
        <x-adminlte-alert theme="danger" icon="fas fa-times-circle">{{ session('error') }}</x-adminlte-alert>
    @endif

    {{-- ====================== ROW: Fabric summary + Add schedule ====================== --}}
    <div class="row mt-3 align-bottom-cards">
        {{-- Fabric Schedule Summary --}}
        <div class="col-lg-6 mb-3 d-flex">
            <x-adminlte-card title="{{ __('datasets.schedule.fabric.title') }}"
                             icon="fas fa-calendar-alt text-lightblue"
                             theme="primary" theme-mode="outline"
                             class="flex-fill d-flex flex-column"
                             body-class="pb-3 d-flex flex-column justify-content-between">
                @php
                    $val = (array)($schedule['value'] ?? []);
                    $sumEnabled = (bool)($val['enabled'] ?? false);
                    $sumTz = (string)($val['localTimeZoneId'] ?? ($defaultTz ?? 'UTC'));
                    $sumDays = implode(', ', (array)($val['days'] ?? []));
                    $sumTimes = implode(', ', (array)($val['times'] ?? []));
                    $sumNotify = (string)($val['notifyOption'] ?? '');
                @endphp

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">{{ __('datasets.schedule.fabric.status') }}</div>
                        <span class="badge {{ $sumEnabled ? 'bg-success' : 'bg-secondary' }}">
                            {{ $sumEnabled ? __('common.active') : __('common.paused') }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">{{ __('datasets.schedule.fabric.timezone') }}</div>
                        <div class="text-monospace small">{{ $sumTz }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">{{ __('datasets.schedule.fabric.days') }}</div>
                        <div class="small">{{ $sumDays ?: '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">{{ __('datasets.schedule.fabric.times') }}</div>
                        <div class="small">{{ $sumTimes ?: '-' }}</div>
                    </div>
                    @if($sumNotify !== '')
                        <div class="col-md-12">
                            <div class="small text-muted">{{ __('datasets.schedule.fabric.notification') }}</div>
                            <div class="small">{{ $sumNotify }}</div>
                        </div>
                    @endif
                </div>
            </x-adminlte-card>
        </div>

        {{-- Add Local Schedule --}}
        <div class="col-lg-6 mb-3 d-flex">
            <x-adminlte-card title="{{ __('datasets.schedule.local.new_title') }}"
                             icon="fas fa-plus-circle text-lightblue"
                             theme="primary" theme-mode="outline"
                             class="flex-fill d-flex flex-column"
                             body-class="pb-3 d-flex flex-column justify-content-between">
                <form method="POST" action="{{ route('datasets.local.store', [$workspace->id, $datasetId]) }}"
                      class="flex-grow-1 d-flex flex-column justify-content-between">
                    @csrf

                    <div>
                        <label class="form-label fw-semibold mb-2">{{ __('datasets.schedule.local.days_label') }}</label>
                        @php
                            $days = [
                                'Sunday' => __('common.days.sunday'),
                                'Monday' => __('common.days.monday'),
                                'Tuesday' => __('common.days.tuesday'),
                                'Wednesday' => __('common.days.wednesday'),
                                'Thursday' => __('common.days.thursday'),
                                'Friday' => __('common.days.friday'),
                                'Saturday' => __('common.days.saturday'),
                            ];
                        @endphp
                        <div class="days-selector p-3 rounded border bg-light mb-3">
                            <div class="days-grid">
                                @foreach($days as $key => $label)
                                    <div class="form-check day-item">
                                        <input type="checkbox" class="form-check-input" name="days[]" id="day{{ $loop->index }}" value="{{ $key }}">
                                        <label class="form-check-label" for="day{{ $loop->index }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ __('datasets.schedule.local.times_label') }}</label>
                            <input type="text" id="ds-times" name="times" class="form-control"
                                   placeholder="{{ __('datasets.schedule.local.times_placeholder') }}">
                            <small id="times-warning" class="text-muted d-none">
                                {{ __('datasets.schedule.local.times_hint') }}
                            </small>
                        </div>
                    </div>

                    <div class="text-center mt-3 align-btn-bottom">
                        <button class="btn btn-primary px-4">
                            <i class="fas fa-plus me-1"></i>{{ __('datasets.schedule.local.add') }}
                        </button>
                    </div>
                </form>
            </x-adminlte-card>
        </div>
    </div>

    {{-- ====================== CARD: Local Schedule List ====================== --}}
    <x-adminlte-card title="{{ __('datasets.schedule.local.title') }}"
                     icon="fas fa-list-alt text-lightblue"
                     theme="primary" theme-mode="outline"
                     class="mt-3 mb-3"
                     body-class="pb-2">

        @if(session('warning'))
            <x-adminlte-alert theme="warning" icon="fas fa-exclamation-triangle">{{ session('warning') }}</x-adminlte-alert>
        @endif

        @php $localSchedules = $localSchedules ?? collect(); @endphp

        {{-- No schedules --}}
        @if($localSchedules->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-times fa-2x mb-3"></i>
                <p class="mb-0">{{ __('datasets.schedule.local.empty') }}</p>
            </div>
        @else
            {{-- Table --}}
            <div class="table-responsive shadow-sm rounded-3 mb-4">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('datasets.schedule.local.days') }}</th>
                            <th>{{ __('datasets.schedule.local.times') }}</th>
                            <th>{{ __('datasets.schedule.local.last_result') }}</th>
                            <th>{{ __('datasets.schedule.local.status') }}</th>
                            <th class="text-center">{{ __('datasets.schedule.local.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($localSchedules as $s)
                            <tr>
                                <td class="fw-semibold">{{ $s->days }}</td>
                                <td class="small text-monospace">{{ $s->times }}</td>
                                <td class="small">{{ $s->last_result ?: '-' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $s->enabled ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $s->enabled ? __('datasets.schedule.local.state_active') : __('datasets.schedule.local.state_inactive') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="actions-group">
                                        <form method="POST" action="{{ route('datasets.local.toggle', [$workspace->id, $datasetId, $s->id]) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-primary btn-icon"
                                                    title="{{ $s->enabled ? __('datasets.schedule.local.deactivate') : __('datasets.schedule.local.activate') }}">
                                                <i class="fas {{ $s->enabled ? 'fa-ban' : 'fa-play' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('datasets.local.delete', [$workspace->id, $datasetId, $s->id]) }}" class="d-inline"
                                              onsubmit="return confirm('{{ __('datasets.schedule.local.delete_confirm') }}')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-icon" title="{{ __('datasets.schedule.local.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
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
.align-bottom-cards { display:flex; align-items:stretch; }
.align-bottom-cards .card { display:flex; flex-direction:column; justify-content:space-between; }
.align-btn-bottom { margin-top:auto !important; padding-top:1rem; display:flex; justify-content:center; }
.align-btn-bottom .btn { min-width:160px; height:40px; font-weight:600; border-radius:.4rem; }

.days-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(120px,1fr)); gap:.5rem 1rem; }
.day-item { display:flex; align-items:center; gap:.35rem; }

.actions-group { display:flex; justify-content:center; align-items:center; gap:.35rem; }
.btn-icon { width:28px; height:28px; display:inline-flex; justify-content:center; align-items:center; border-radius:4px; font-size:.8rem; }

.text-monospace {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}
</style>
@endpush

@push('js')
<script>
(function(){
    const input=document.getElementById('ds-times');
    const warn=document.getElementById('times-warning');
    if(!input||!warn)return;
    function checkDup(){
        const raw=(input.value||'').split(',').map(s=>s.trim()).filter(Boolean);
        const seen=new Set();let dup=false;
        for(const t of raw){if(seen.has(t)){dup=true;break;}seen.add(t);}
        warn.classList.toggle('d-none',!dup);
    }
    input.addEventListener('input',checkDup);
})();
</script>
@endpush
