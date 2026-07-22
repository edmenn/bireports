@extends('layouts.app')

@section('title', __('audit.title'))

@section('css')
    @include('portal.partials.base-styles')
    @include('portal.partials.home-card-styles')
@endsection

@section('content_header')
    <x-page-header :title="__('audit.title')" icon="fas fa-chart-area">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a class="btn btn-primary btn-sm" href="{{ route('audit.export', request()->query()) }}">
                <i class="fas fa-file-export me-1" aria-hidden="true"></i>
                <span>{{ __('audit.actions.export') }}</span>
            </a>
        </div>
    </x-page-header>

    @php($tab = $tab ?? 'dashboard')

    {{-- Scrollable tabs --}}
    <div class="portal-tabs-wrapper mt-3">
        <ul class="portal-tabs" role="tablist">
            <li>
                <a class="{{ $tab==='dashboard'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'dashboard']) }}">
                    <i class="fas fa-chart-line"></i>{{ __('audit.tabs.dashboard') }}
                </a>
            </li>
            <li>
                <a class="{{ $tab==='users-most'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'users-most']) }}">
                    <i class="fas fa-user-plus"></i>{{ __('audit.tabs.users_most') }}
                </a>
            </li>
            <li>
                <a class="{{ $tab==='users-least'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'users-least']) }}">
                    <i class="fas fa-user-minus"></i>{{ __('audit.tabs.users_least') }}
                </a>
            </li>
            <li>
                <a class="{{ $tab==='users-inactive'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'users-inactive']) }}">
                    <i class="fas fa-user-clock"></i>{{ __('audit.tabs.users_inactive') }}
                </a>
            </li>
            <li>
                <a class="{{ $tab==='reports-most'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'reports-most']) }}">
                    <i class="fas fa-file-alt"></i>{{ __('audit.tabs.reports_most') }}
                </a>
            </li>
            <li>
                <a class="{{ $tab==='reports-least'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'reports-least']) }}">
                    <i class="fas fa-file-medical-alt"></i>{{ __('audit.tabs.reports_least') }}
                </a>
            </li>
            <li>
                <a class="{{ $tab==='reports-inactive'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'reports-inactive']) }}">
                    <i class="fas fa-file-excel"></i>{{ __('audit.tabs.reports_inactive') }}
                </a>
            </li>
            <li>
                <a class="{{ $tab==='user-profile'?'active':'' }}"
                   href="{{ route('audit.index', ['tab'=>'user-profile']) }}">
                    <i class="fas fa-id-badge"></i>{{ __('audit.tabs.user_profile') }}
                </a>
            </li>
        </ul>
    </div>
@stop

@section('content')

{{-- Filter form only for "User profile" --}}
@if($tab === 'user-profile')
<form method="get" class="portal-filter-form" autocomplete="off" novalidate>
    <input type="hidden" name="tab" value="user-profile">
    <div class="portal-card mt-3">
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
            <div class="col-12">
                <div class="nav portal-theme-tabs portal-filter-actions border-0" aria-label="{{ __('audit.filters.apply') }} / {{ __('audit.filters.clear') }}">
                <button class="nav-link active" type="submit">
                    <i class="fas fa-filter me-1" aria-hidden="true"></i> {{ __('audit.filters.apply') }}
                </button>
                <a href="{{ route('audit.index', ['tab'=>'user-profile']) }}" class="nav-link">
                    <i class="fas fa-eraser me-1" aria-hidden="true"></i> {{ __('audit.filters.clear') }}
                </a>
                </div>
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
.portal-tabs-wrapper{
    width:100%;
    max-width:100%;
    min-width:0;
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
    padding-bottom:.25rem;
}
.portal-tabs{
    display:flex;
    gap:.5rem;
    list-style:none;
    padding:0;
    margin:0;
    width:max-content;
    min-width:100%;
}
.portal-tabs li a{
    display:inline-flex;
    align-items:center;
    gap:.45rem;
    padding:.55rem 1rem;
    border-radius:.65rem;
    background:var(--app-card-alt-bg);
    color:var(--app-card-text);
    font-weight:600;
    border:1px solid transparent;
    transition:all .15s ease-in-out;
    text-decoration:none;
    white-space:nowrap;
}
.portal-tabs li a i{font-size:.9rem;}
.portal-tabs li a:hover{
    background:var(--app-card-alt-bg);
    color:var(--app-card-text);
}
.portal-tabs li a.active{
    background:var(--app-highlight-bg);
    color:var(--app-highlight-text);
    border-color:var(--app-highlight-bg);
    box-shadow:0 .9rem 1.6rem rgba(13,110,253,.18);
}

.portal-filter-form .form-control{
    font-size:.95rem;
    padding:.55rem .75rem;
    height: auto;
}
.portal-filter-form label.form-label{
    font-weight:600;
    color:var(--app-card-text);
}

.portal-table thead th{
    background:var(--app-card-alt-bg);
    font-weight:600;
    font-size:.9rem;
    color:var(--app-card-text);
    border-bottom:none;
}
.portal-table tbody td{
    border-top:1px solid var(--app-card-alt-bg);
    font-size:.9rem;
}
.portal-table tbody tr:hover{
    background:var(--app-card-alt-bg);
}

.portal-audit-card .card-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:.75rem;
    padding:1rem 1.25rem;
}
.portal-audit-card .card-header small{
    color:var(--app-card-text);
    opacity:.72;
    font-weight:500;
}

.portal-icon.icon-indigo,
.portal-icon.icon-sky,
.portal-icon.icon-emerald,
.portal-icon.icon-amber{background:var(--app-icon-bg);color:var(--app-icon-text);}

.portal-dashboard-card .card-header{
    border-bottom:1px solid var(--app-card-alt-bg);
    padding:1rem 1.25rem;
}
.portal-dashboard-card .card-header h5{
    margin:0;
    font-size:1rem;
    font-weight:600;
    color:var(--app-card-text);
}
.portal-dashboard-card .card-header small{
    color:var(--app-card-text);
    opacity:.72;
}

/* Charts */
.chart-wrap{position:relative;height:360px}

/* Heatmap */
.hm-table{border-collapse:collapse;width:100%}
.hm-table th,.hm-table td{
    border:1px solid #e5e7eb;
    padding:.3rem .4rem;
    text-align:center;
    font-size:.78rem;
    line-height:1;
    vertical-align:middle;
    height:2rem;
}
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
</style>
@endpush
