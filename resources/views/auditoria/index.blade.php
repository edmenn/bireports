@extends('adminlte::page')

@section('title', 'Auditoría de Métricas de Uso')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h1 class="mb-0">Auditoría de Métricas de Uso</h1>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-success btn-eq" href="{{ route('auditoria.export', request()->query()) }}">
                <i class="fas fa-file-export me-1" aria-hidden="true"></i> Exportar
            </a>
        </div>
    </div>

    @php($tab = $tab ?? 'dashboard')

    {{-- Tabs scrollables en móvil --}}
    <div class="nav-tabs-wrapper mt-2">
        <ul class="nav nav-tabs flex-nowrap text-nowrap" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $tab==='dashboard'?'active':'' }}"
                   href="{{ route('auditoria.index', ['tab'=>'dashboard']) }}">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='users-most'?'active':'' }}"
                   href="{{ route('auditoria.index', ['tab'=>'users-most']) }}">Usuarios más activos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='users-least'?'active':'' }}"
                   href="{{ route('auditoria.index', ['tab'=>'users-least']) }}">Usuarios menos activos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='users-inactive'?'active':'' }}"
                   href="{{ route('auditoria.index', ['tab'=>'users-inactive']) }}">Usuarios no activos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='reports-most'?'active':'' }}"
                   href="{{ route('auditoria.index', ['tab'=>'reports-most']) }}">Reportes más accesados</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='reports-least'?'active':'' }}"
                   href="{{ route('auditoria.index', ['tab'=>'reports-least']) }}">Reportes menos accesados</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='reports-inactive'?'active':'' }}"
                   href="{{ route('auditoria.index', ['tab'=>'reports-inactive']) }}">Reportes no accesados</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab==='user-profile'?'active':'' }}"
                   href="{{ route('auditoria.index', ['tab'=>'user-profile']) }}">Perfil de usuario</a>
            </li>
        </ul>
    </div>
@stop

@section('content')

{{-- Formulario de filtros SOLO para "Perfil de usuario" --}}
@if($tab === 'user-profile')
<form method="get" class="mb-3" autocomplete="off" novalidate>
    <input type="hidden" name="tab" value="user-profile">
    <div class="card">
        <div class="card-body row g-3">
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="col-md-8">
                <label class="form-label">Usuario</label>
                <select name="user_id" class="form-control">
                    <option value="">— Selecciona un usuario —</option>
                    @foreach($usersList as $u)
                        <option value="{{ $u->id }}" {{ (string)$userId === (string)$u->id ? 'selected' : '' }}>
                            {{ $u->name }}{{ $u->email ? ' - '.$u->email : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">El perfil y sus métricas se filtran por el usuario y rango seleccionados.</small>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <button class="btn btn-primary btn-eq" type="submit">
                    <i class="fas fa-filter me-1" aria-hidden="true"></i> Aplicar filtros
                </button>
                <a href="{{ route('auditoria.index', ['tab'=>'user-profile']) }}" class="btn btn-outline-secondary btn-eq">
                    <i class="fas fa-eraser me-1" aria-hidden="true"></i> Limpiar
                </a>
            </div>
        </div>
    </div>
</form>
@endif

{{-- Contenido por pestaña --}}
@if($tab === 'dashboard')
    @include('auditoria.partials.dashboard')
@elseif($tab === 'users-most')
    @include('auditoria.partials.users_table', ['rows'=>$usersMost, 'title'=>'Top 100 usuarios más activos (rango)'])
@elseif($tab === 'users-least')
    @include('auditoria.partials.users_table', ['rows'=>$usersLeast, 'title'=>'Top 100 usuarios menos activos (>=1 acceso en el rango)'])
@elseif($tab === 'users-inactive')
    @include('auditoria.partials.users_inactive', ['rows'=>$usersNoAct, 'title'=>'Usuarios sin accesos en el rango'])
@elseif($tab === 'reports-most')
    @include('auditoria.partials.reports_table', ['rows'=>$reportsMost, 'title'=>'Top 100 reportes más accesados'])
@elseif($tab === 'reports-least')
    @include('auditoria.partials.reports_table', ['rows'=>$reportsLeast, 'title'=>'Reportes menos accesados (>=1 acceso en el rango)'])
@elseif($tab === 'reports-inactive')
    @include('auditoria.partials.reports_inactive', ['rows'=>$reportsNoAcc, 'title'=>'Reportes no accesados en el rango'])
@elseif($tab === 'user-profile')
    @include('auditoria.partials.user_profile', ['profile'=>$userProfile, 'events'=>$userEvents, 'dowLabels'=>$dowLabels])
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

/* Tabs scrollables en móvil */
.nav-tabs-wrapper{
    overflow-x:auto; -webkit-overflow-scrolling:touch;
}
.nav-tabs-wrapper .nav-tabs .nav-link{
    padding:.5rem .85rem;      /* un poco más compactas */
}
</style>
@endpush
