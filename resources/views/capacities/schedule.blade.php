{{-- resources/views/capacities/schedule.blade.php --}}
@extends('layouts.app')

@section('title', __('capacities.schedule.page_title'))

@section('content_header')
    <x-page-header :title="__('capacities.schedule.title', ['name' => $capacity->display_name])" icon="fas fa-clock">
        <x-slot name="subtitle">
            <small class="text-muted">
                {{ __('capacities.shared.sku_region', ['sku' => $capacity->sku, 'region' => $capacity->region]) }}
            </small>
        </x-slot>
    </x-page-header>
@stop

@section('content')
    {{-- Flash alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-2" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert" data-dismiss="alert" aria-label="{{ __('common.close') }}">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="close" data-bs-dismiss="alert" data-dismiss="alert" aria-label="{{ __('common.close') }}">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- ====================== ROW: Add Schedule + Options ====================== --}}
    <div class="row mt-3 align-bottom-cards">
        {{-- Add Schedule --}}
        <div class="col-lg-6 mb-3 d-flex">
            <x-ui-card title="{{ __('capacities.schedule.form.title') }}"
                             icon="fas fa-plus-circle text-lightblue"
                             theme="primary" theme-mode="outline"
                             class="flex-fill d-flex flex-column"
                             body-class="pb-3 d-flex flex-column justify-content-between">
                <form method="POST" action="{{ route('capacities.schedule.store', $capacity->id) }}" class="flex-grow-1 d-flex flex-column justify-content-between">
                    @csrf
                    <div>
                        <label class="form-label fw-semibold mb-2">{{ __('capacities.schedule.form.days') }}</label>
                        <div class="days-selector p-3 rounded border bg-light mb-3">
                            <div class="days-grid">
                                @foreach($dayNames as $idx => $name)
                                    <div class="form-check day-item">
                                        <input type="checkbox" class="form-check-input" name="days[]" id="d{{ $idx }}" value="{{ $idx }}">
                                        <label class="form-check-label" for="d{{ $idx }}">{{ $name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('capacities.schedule.form.from') }}</label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ __('capacities.schedule.form.to') }}</label>
                                <input type="time" class="form-control" name="end_time" required>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-3 align-btn-bottom">
                        <button class="btn btn-primary px-4">
                            <i class="fas fa-plus mr-1"></i>{{ __('capacities.schedule.form.submit') }}
                        </button>
                    </div>
                </form>
            </x-ui-card>
        </div>

        {{-- Options --}}
        <div class="col-lg-6 mb-3 d-flex">
            <x-ui-card title="{{ __('capacities.schedule.options.title') }}"
                             icon="fas fa-cogs text-lightblue"
                             theme="primary" theme-mode="outline"
                             class="flex-fill d-flex flex-column"
                             body-class="pb-3 d-flex flex-column justify-content-between">
                <form method="POST" action="{{ route('capacities.settings.update', $capacity->id) }}" class="flex-grow-1 d-flex flex-column justify-content-between">
                    @csrf @method('PUT')

                    <div>
                        <div class="form-check form-switch mb-3">
                            <input type="checkbox" class="form-check-input" id="automation_enabled"
                                   name="automation_enabled" value="1"
                                   {{ ($capacity->force_shutdown_on_schedule && $capacity->cost_optimization_enabled) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="automation_enabled">
                                {{ __('capacities.schedule.options.automation_switch') }}
                                <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip"
                                   title="{{ __('capacities.schedule.options.automation_hint') }}"></i>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                {{ __('capacities.schedule.options.inactivity_minutes') }}
                                <i class="far fa-question-circle ms-1 text-muted" data-bs-toggle="tooltip"
                                   title="{{ __('capacities.schedule.options.inactivity_hint') }}"></i>
                            </label>
                            <input type="number" class="form-control" name="inactivity_timeout_minutes"
                                   min="5" max="120" step="5"
                                   value="{{ old('inactivity_timeout_minutes', $capacity->inactivity_timeout_minutes) }}">
                            <small class="text-muted">{{ __('capacities.schedule.options.inactivity_help') }}</small>
                        </div>
                    </div>

                    <div class="text-center mt-3 align-btn-bottom">
                        <button class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i>{{ __('common.save') }}
                        </button>
                    </div>
                </form>
            </x-ui-card>
        </div>
    </div>

    {{-- ====================== CARD 2: Schedule List (BOTTOM) ====================== --}}
    <x-ui-card title="{{ __('capacities.schedule.list') }}"
                     icon="fas fa-calendar-alt text-lightblue"
                     theme="primary" theme-mode="outline"
                     class="mt-3 mb-3"
                     body-class="pb-2">

        @if($schedules->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-times fa-2x mb-3"></i>
                <p class="mb-0">{{ __('capacities.schedule.empty') }}</p>
            </div>
        @else
            <div class="table-responsive shadow-sm rounded-3">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('capacities.schedule.table.day') }}</th>
                            <th>{{ __('capacities.schedule.table.from') }}</th>
                            <th>{{ __('capacities.schedule.table.to') }}</th>
                            <th>{{ __('capacities.schedule.table.status') }}</th>
                            <th class="text-center">{{ __('capacities.schedule.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $s)
                            <tr>
                                <td class="py-2 fw-semibold">{{ $dayNames[$s->weekday] ?? $s->weekday }}</td>
                                <td>{{ $s->start_time->format('H:i') }}</td>
                                <td>{{ $s->end_time->format('H:i') }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $s->enabled ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $s->enabled ? __('capacities.schedule.state.active') : __('capacities.schedule.state.inactive') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="actions-group">
                                        <form method="POST" action="{{ route('capacities.schedule.toggle', [$capacity->id, $s->id]) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-primary btn-icon" title="{{ $s->enabled ? __('capacities.schedule.actions.deactivate') : __('capacities.schedule.actions.activate') }}">
                                                <i class="fas {{ $s->enabled ? 'fa-ban' : 'fa-play' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('capacities.schedule.delete', [$capacity->id, $s->id]) }}" class="d-inline"
                                              onsubmit="return confirm('{{ __('capacities.schedule.delete_confirm') }}')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-icon" title="{{ __('capacities.schedule.actions.delete') }}">
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
    </x-ui-card>
@stop

@push('css')
<style>
/* Align Add schedule & Save buttons */
.align-bottom-cards {
    display: flex;
    align-items: stretch;
}
.align-bottom-cards .card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.align-btn-bottom {
    margin-top: auto !important;
    padding-top: 1rem;
    display: flex;
    justify-content: center;
}
.align-btn-bottom .btn {
    min-width: 160px;
    height: 40px;
    font-weight: 600;
    border-radius: .4rem;
}

/* Days grid perfectly aligned and responsive */
.days-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: .5rem 1rem;
}
.days-grid .day-item {
    display: flex;
    align-items: center;
    gap: .35rem;
}

/* Table actions */
.actions-group {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: .35rem;
}
.table .btn-icon {
    width: 28px;
    height: 28px;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border-radius: 4px;
    font-size: .8rem;
}
</style>
@endpush

@push('js')
<script>
(function(){
    if (window.bootstrap && window.bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new window.bootstrap.Tooltip(el);
        });
    }
})();
</script>
@endpush
