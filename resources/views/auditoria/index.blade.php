@extends('adminlte::page')

@section('title', 'Auditoría de Métricas de Uso')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="mb-2">Auditoría de Métricas de Uso</h1>
        <div class="d-flex gap-2">
            <a class="btn btn-success" href="{{ route('auditoria.export', request()->query()) }}">
                <i class="fas fa-file-export"></i> Exportar
            </a>
        </div>
    </div>

    @php($active = $tab ?? 'dashboard')
    <ul class="nav nav-tabs mt-2" role="tablist">
        <li class="nav-item"><a class="nav-link {{ $active==='dashboard'?'active':'' }}" href="{{ route('auditoria.index', ['tab'=>'dashboard']) }}">DashBoard</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='users-most'?'active':'' }}" href="{{ route('auditoria.index', ['tab'=>'users-most']) }}">Usuarios más activos</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='users-least'?'active':'' }}" href="{{ route('auditoria.index', ['tab'=>'users-least']) }}">Usuarios menos activos</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='users-inactive'?'active':'' }}" href="{{ route('auditoria.index', ['tab'=>'users-inactive']) }}">Usuarios no activos</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='reports-most'?'active':'' }}" href="{{ route('auditoria.index', ['tab'=>'reports-most']) }}">Reportes más accesados</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='reports-least'?'active':'' }}" href="{{ route('auditoria.index', ['tab'=>'reports-least']) }}">Reportes menos accesados</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='reports-inactive'?'active':'' }}" href="{{ route('auditoria.index', ['tab'=>'reports-inactive']) }}">Reportes no accesados</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='user-profile'?'active':'' }}" href="{{ route('auditoria.index', ['tab'=>'user-profile']) }}">Perfil de usuario</a></li>
    </ul>
@stop

@section('content')

{{-- Formulario de filtros SOLO para "Perfil de usuario" --}}
@if($tab === 'user-profile')
<form method="get" class="mb-3">
    <input type="hidden" name="tab" value="user-profile">
    <div class="card">
        <div class="card-body row">
            <div class="col-md-2 mb-3">
                <label class="form-label">Desde</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Hasta</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="col-md-8 mb-3">
                <label class="form-label">Usuario</label>
                <select name="user_id" class="form-control">
                    <option value="">— Selecciona un usuario —</option>
                    @foreach($usersList as $u)
                        <option value="{{ $u->id }}" {{ (string)$userId === (string)$u->id ? 'selected' : '' }}>
                            {{ $u->name }} {{ $u->email ? ' - '.$u->email : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">El perfil y sus métricas se filtran por el usuario y rango seleccionados.</small>
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Aplicar filtros</button>
                <a href="{{ route('auditoria.index', ['tab'=>'user-profile']) }}" class="btn btn-outline-secondary">Limpiar</a>
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
.chart-wrap{position:relative;height:360px}
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
.timeline{list-style:none;padding-left:0}
.timeline li{padding:8px 0;border-bottom:1px solid #eee}
.timeline .when{min-width:170px;font-family:monospace}
.badge-ok{background:#10b981}
.badge-fail{background:#ef4444}
</style>
@endpush
