@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="fw-bold">Resumen Ejecutivo</h1>
            <p class="text-secondary mb-0">KPIs, actividad y datos clave</p>
        </div>
        <div class="d-flex gap-2">
            <a href="#" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nuevo
            </a>
            <a href="#" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download"></i> Exportar
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">

    {{-- KPI 1 --}}
    <div class="col-12 col-md-6 col-xl-3">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>$1.24M</h3>
                <p>Ingresos</p>
            </div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            <a href="#" class="small-box-footer">Más info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    {{-- KPI 2 --}}
    <div class="col-12 col-md-6 col-xl-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>$830k</h3>
                <p>Costos</p>
            </div>
            <div class="icon"><i class="fas fa-coins"></i></div>
            <a href="#" class="small-box-footer">Más info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    {{-- KPI 3 --}}
    <div class="col-12 col-md-6 col-xl-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>4,812</h3>
                <p>Órdenes</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <a href="#" class="small-box-footer">Más info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

    {{-- KPI 4 --}}
    <div class="col-12 col-md-6 col-xl-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>92%</h3>
                <p>Satisfacción</p>
            </div>
            <div class="icon"><i class="fas fa-smile"></i></div>
            <a href="#" class="small-box-footer">Más info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>

</div>

<div class="row">
    {{-- Gráfico / Placeholder --}}
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title fw-bold">Tendencia general</h3>
                <div class="card-tools">
                    <button class="btn btn-tool" aria-label="Filtrar"><i class="fas fa-filter"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="bg-body-tertiary rounded-3 d-flex align-items-center justify-content-center text-secondary"
                     style="height:250px;">
                    <i class="fas fa-chart-line me-2"></i> Aquí va tu gráfico (Power BI / Fabric)
                </div>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title fw-bold">Actividad reciente</h3>
            </div>
            <div class="card-body">
                <div class="timeline timeline-inverse">
                    <div>
                        <i class="fas fa-shopping-cart bg-primary"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> Hoy, 10:32</span>
                            <h3 class="timeline-header">Nueva orden #5481</h3>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-chart-bar bg-success"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> Ayer, 17:05</span>
                            <h3 class="timeline-header">Reporte ventas actualizado</h3>
                        </div>
                    </div>
                    <div>
                        <i class="fas fa-server bg-warning"></i>
                        <div class="timeline-item">
                            <span class="time"><i class="far fa-clock"></i> Ayer, 08:14</span>
                            <h3 class="timeline-header">Capacidad Fabric verificada</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabla --}}
<div class="card">
    <div class="card-header border-0 d-flex justify-content-between align-items-center">
        <h3 class="card-title fw-bold">Top clientes</h3>
        <div class="card-tools">
            <button class="btn btn-tool" aria-label="Descargar"><i class="fas fa-download"></i></button>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover align-middle text-nowrap">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Órdenes</th>
                    <th>Ingresos</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Agro SRL</td>
                    <td>124</td>
                    <td>$214,300</td>
                    <td><span class="badge bg-success">Activo</span></td>
                </tr>
                <tr>
                    <td>Campos SA</td>
                    <td>87</td>
                    <td>$148,900</td>
                    <td><span class="badge bg-warning">Pendiente</span></td>
                </tr>
                <tr>
                    <td>Graneros PY</td>
                    <td>52</td>
                    <td>$96,500</td>
                    <td><span class="badge bg-secondary">Inactivo</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@stop
