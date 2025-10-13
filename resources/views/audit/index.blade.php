@extends('adminlte::page')

@section('title', __('audit.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0">{{ __('audit.title') }}</h1>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-success btn-eq" href="{{ route('audit.export', request()->query()) }}">
                <i class="fas fa-file-export me-1" aria-hidden="true"></i> {{ __('audit.actions.export') }}
            </a>
        </div>
    </div>

    @php($tab = $tab ?? 'dashboard')

    {{-- Scrollable tabs on mobile --}}
    <div class="nav-tabs-wrapper mt-2">
        <ul class="nav nav-tabs flex-nowrap text-nowrap" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $tab==='dashboard'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'dashboard']) }}">{{ __('audit.tabs.dashboard') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='users-most'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'users-most']) }}">{{ __('audit.tabs.users_most') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='users-least'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'users-least']) }}">{{ __('audit.tabs.users_least') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='users-inactive'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'users-inactive']) }}">{{ __('audit.tabs.users_inactive') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='reports-most'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'reports-most']) }}">{{ __('audit.tabs.reports_most') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='reports-least'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'reports-least']) }}">{{ __('audit.tabs.reports_least') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='reports-inactive'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'reports-inactive']) }}">{{ __('audit.tabs.reports_inactive') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='user-profile'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'user-profile']) }}">{{ __('audit.tabs.user_profile') }}</a>
            </li>
        </ul>
    </div>
@stop

@section('content')

{{-- Filter form only for "User profile" --}}
@if($tab === 'user-profile')
<form method="get" class="mb-3" autocomplete="off" novalidate>
    <input type="hidden" name="tab" value="user-profile">
    <div class="card">
        <div class="card-body row g-3">
            <div class="col-md-2">
                <label class="form-label">{{ __('audit.filters.from') }}</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">{{ __('audit.filters.to') }}</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="col-md-8">
                <label class="form-label">{{ __('audit.filters.user') }}</label>
                <select name="user_id" class="form-control">
                    <option value="">{{ __('audit.filters.select_user') }}</option>
                    @foreach($usersList as $u)
                        <option value="{{ $u->id }}" {{ (string)$userId === (string)$u->id ? 'selected' : '' }}>
                            {{ $u->name }}{{ $u->email ? ' - '.$u->email : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">{{ __('audit.filters.hint') }}</small>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <button class="btn btn-primary btn-eq" type="submit">
                    <i class="fas fa-filter me-1" aria-hidden="true"></i> {{ __('audit.filters.apply') }}
                </button>
                <a href="{{ route('audit.index', ['tab'=>'user-profile']) }}" class="btn btn-outline-secondary btn-eq">
                    <i class="fas fa-eraser me-1" aria-hidden="true"></i> {{ __('audit.filters.clear') }}
                </a>
            </div>
        </div>
    </div>
</form>
@endif

{{-- Tab content --}}
@if($tab === 'dashboard')
    @include('audit.partials.dashboard')
@elseif($tab === 'users-most')
    @include('audit.partials.users_table', ['rows'=>$usersMost, 'title'=>__('audit.lists.users_most')])
@elseif($tab === 'users-least')
    @include('audit.partials.users_table', ['rows'=>$usersLeast, 'title'=>__('audit.lists.users_least')])
@elseif($tab === 'users-inactive')
    @include('audit.partials.users_inactive', ['rows'=>$usersNoAct, 'title'=>__('audit.lists.users_inactive')])
@elseif($tab === 'reports-most')
    @include('audit.partials.reports_table', ['rows'=>$reportsMost, 'title'=>__('audit.lists.reports_most')])
@elseif($tab === 'reports-least')
    @include('audit.partials.reports_table', ['rows'=>$reportsLeast, 'title'=>__('audit.lists.reports_least')])
@elseif($tab === 'reports-inactive')
    @include('audit.partials.reports_inactive', ['rows'=>$reportsNoAcc, 'title'=>__('audit.lists.reports_inactive')])
@elseif($tab === 'user-profile')
    @include('audit.partials.user_profile', ['profile'=>$userProfile, 'events'=>$userEvents, 'dowLabels'=>$dowLabels])
@endif
@stop

@section('js')
    @if(in_array($tab, ['dashboard','user-profile']))
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endif
@endsection

@push('css')
<style>
/* Charts */
.chart-wrap{position:relative;height:360px}

/* Heatmap */
.hm-table{border-collapse:collapse;width:100%}
.hm-table th,.hm-table td{border:1px solid #e5e7eb;padding:4px 6px;text-align:center}
.hm-0{background:#fff}
.hm-1{background:#eef2ff}
.hm-2{background:#dbeafe}
.hm-3{background:#bfdbfe}
.hm-4{background:#93c5fd}
.hm-5{background:#60a5fa}
.hm-6{background:#3b82f6;color:#fff}
.hm-7{background:#ef4444;color:#fff}
.hm-legend .box{display:inline-block;width:20px;height:12px;vertical-align:middle;margin-right:6px;border:1px solid #ddd}
.heat-wrap{max-height:360px;overflow:auto}

/* Timeline */
.timeline{list-style:none;padding-left:0}
.timeline li{padding:8px 0;border-bottom:1px solid #eee}
.timeline .when{min-width:170px;font-family:monospace}
.badge-ok{background:#10b981}
.badge-fail{background:#ef4444}

/* Scrollable tabs on mobile */
.nav-tabs-wrapper{
    overflow-x:auto; -webkit-overflow-scrolling:touch;
}
.nav-tabs-wrapper .nav-tabs .nav-link{
    padding:.5rem .85rem;      /* slightly more compact */
}
</style>
@endpush
