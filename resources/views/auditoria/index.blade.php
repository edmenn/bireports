@extends('adminlte::page')

@section('title', 'Auditoría BI')

@section('content_header')
    <h1>Auditoría de Uso de Reportes</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <form method="get" class="mb-3">
            <div class="card">
                <div class="card-body row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Desde</label>
                        <input type="date" name="from" class="form-control" value="{{ $from }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="to" class="form-control" value="{{ $to }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Report UUID (opcional)</label>
                        <input type="text" name="report_uuid" class="form-control" value="{{ $reportUuid }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">User ID (opcional)</label>
                        <input type="number" name="user_id" class="form-control" value="{{ $userId }}">
                    </div>
                    <div class="col-12 mt-2">
                        <button class="btn btn-primary">Aplicar filtros</button>
                        <a href="{{ route('auditoria.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Top reportes --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Top 10 reportes por accesos</strong></div>
            <div class="card-body">
                <canvas id="chartTopReports" height="180"></canvas>
            </div>
        </div>
    </div>

    {{-- Top usuarios --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><strong>Top 10 usuarios por accesos</strong></div>
            <div class="card-body">
                <canvas id="chartTopUsers" height="180"></canvas>
            </div>
        </div>
    </div>

    {{-- Serie temporal --}}
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><strong>Accesos por día ({{ $from }} → {{ $to }})</strong></div>
            <div class="card-body">
                <canvas id="chartDaily" height="120"></canvas>
            </div>
        </div>
    </div>

    {{-- Día de la semana --}}
    <div class="col-md-12">
        <div class="card">
            <div class="card-header"><strong>Distribución por día de la semana</strong></div>
            <div class="card-body">
                <canvas id="chartDOW" height="90"></canvas>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    // Datos del servidor
    const topReports = @json($topReports);
    const topUsers   = @json($topUsers);
    const daily      = @json($daily);
    const dow        = @json($dow);

    // Helpers
    function toBarConfig(labels, values, title) {
        return {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ label: title, data: values }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { ticks: { autoSkip: false } }, y: { beginAtZero: true } }
            }
        };
    }

    // Top Reports
    (function(){
        const labels = topReports.map(r => r.report_uuid);
        const values = topReports.map(r => r.total);
        const ctx = document.getElementById('chartTopReports').getContext('2d');
        new Chart(ctx, toBarConfig(labels, values, 'Accesos'));
    })();

    // Top Users
    (function(){
        const labels = topUsers.map(u => (u.name ?? ('User #'+u.user_id)));
        const values = topUsers.map(u => u.total);
        const ctx = document.getElementById('chartTopUsers').getContext('2d');
        new Chart(ctx, toBarConfig(labels, values, 'Accesos'));
    })();

    // Serie diaria
    (function(){
        const labels = daily.map(d => d.date);
        const values = daily.map(d => d.total);
        const ctx = document.getElementById('chartDaily').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: { labels, datasets: [{ label: 'Accesos', data: values, tension: 0.2, fill: false }] },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    })();

    // Día de semana
    (function(){
        const labels = dow.map(d => d.label);
        const values = dow.map(d => d.total);
        const ctx = document.getElementById('chartDOW').getContext('2d');
        new Chart(ctx, toBarConfig(labels, values, 'Accesos'));
    })();
})();
</script>
@endpush
