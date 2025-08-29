@extends('adminlte::page')

@section('title', 'Auditoría de Métricas de Uso')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="mb-2">Auditoría de Métricas de Uso</h1>
        <a class="btn btn-success mb-2" href="{{ route('auditoria.export', request()->query()) }}">
            <i class="fas fa-file-export"></i> Exportar
        </a>
    </div>

    @php($active = $tab ?? 'dashboard')
    <ul class="nav nav-tabs mt-2" role="tablist">
        <li class="nav-item"><a class="nav-link {{ $active==='dashboard'?'active':'' }}" href="{{ route('auditoria.index', array_merge(request()->query(), ['tab'=>'dashboard'])) }}">DashBoard</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='users-most'?'active':'' }}" href="{{ route('auditoria.index', array_merge(request()->query(), ['tab'=>'users-most'])) }}">Usuarios más activos</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='users-least'?'active':'' }}" href="{{ route('auditoria.index', array_merge(request()->query(), ['tab'=>'users-least'])) }}">Usuarios menos activos</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='users-inactive'?'active':'' }}" href="{{ route('auditoria.index', array_merge(request()->query(), ['tab'=>'users-inactive'])) }}">Usuarios no activos</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='reports-most'?'active':'' }}" href="{{ route('auditoria.index', array_merge(request()->query(), ['tab'=>'reports-most'])) }}">Reportes más accesados</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='reports-least'?'active':'' }}" href="{{ route('auditoria.index', array_merge(request()->query(), ['tab'=>'reports-least'])) }}">Reportes menos accesados</a></li>
        <li class="nav-item"><a class="nav-link {{ $active==='reports-inactive'?'active':'' }}" href="{{ route('auditoria.index', array_merge(request()->query(), ['tab'=>'reports-inactive'])) }}">Reportes no accesados</a></li>
    </ul>
@stop

@section('content')
<form method="get" class="mb-3">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <div class="card">
        <div class="card-body row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Desde</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Hasta</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Report UUID</label>
                <input type="text" name="report_uuid" class="form-control" value="{{ $reportUuid }}">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">User ID</label>
                <input type="number" name="user_id" class="form-control" value="{{ $userId }}">
            </div>
            <div class="col-12">
                <button class="btn btn-primary">Aplicar filtros</button>
                <a href="{{ route('auditoria.index', ['tab'=>$tab]) }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </div>
    </div>
</form>

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
@endif
@stop

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
</style>
@endpush

@push('js')
@if($tab==='dashboard')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    const months  = @json($months);
    const users   = @json($topUsers);
    const reports = @json($topReports);
    const heatmap = @json($heatmap);
    const dow     = @json(array_values($dowLabels));
    const common = {responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}}};

    new Chart(document.getElementById('chartMonths').getContext('2d'), {
        type:'bar',
        data:{labels:months.map(m=>m.label), datasets:[{label:'Accesos', data:months.map(m=>m.total)}]},
        options:common
    });

    new Chart(document.getElementById('chartUsers').getContext('2d'), {
        type:'bar',
        data:{labels:users.map(u=>u.name), datasets:[{label:'Accesos', data:users.map(u=>u.total)}]},
        options:common
    });

    new Chart(document.getElementById('chartReports').getContext('2d'), {
        type:'bar',
        data:{labels:reports.map(r=>r.label), datasets:[{label:'Accesos', data:reports.map(r=>r.total)}]},
        options:common
    });

    function bucket(v){if(v===0)return 0;if(v<=20)return 1;if(v<=40)return 2;if(v<=60)return 3;if(v<=80)return 4;if(v<=100)return 5;if(v<=300)return 6;return 7}
    const tbody=document.getElementById('heatBody');
    heatmap.forEach(row=>{
        const tr=document.createElement('tr');
        const th=document.createElement('th'); th.textContent=row.hour; tr.appendChild(th);
        for(let i=0;i<dow.length;i++){
            const col=(i+1).toString();
            const v=row[col]??0;
            const td=document.createElement('td'); td.className='hm-'+bucket(v); td.textContent=v; tr.appendChild(td);
        }
        tbody.appendChild(tr);
    });
})();
</script>
@endif
@endpush
