<div class="row row-cols-1 row-cols-md-3 g-3">
    <div class="col">
        <div class="small-box bg-primary h-100 d-flex align-items-center">
            <div class="inner">
                <h3 class="mb-1">{{ number_format($totalAccesos) }}</h3>
                <p class="mb-0">Total de accesos</p>
            </div>
            <div class="icon"><i class="fas fa-sign-in-alt"></i></div>
        </div>
    </div>
    <div class="col">
        <div class="small-box bg-secondary h-100 d-flex align-items-center">
            <div class="inner">
                <h3 class="mb-1">{{ number_format($totalUsuarios) }}</h3>
                <p class="mb-0">Total de usuarios</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col">
        <div class="small-box bg-success h-100 d-flex align-items-center">
            <div class="inner">
                <h3 class="mb-1">{{ number_format($totalReportes) }}</h3>
                <p class="mb-0">Total de reportes</p>
            </div>
            <div class="icon"><i class="fas fa-chart-bar"></i></div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12 col-xl-6 mb-3">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header"><strong>Accesos por mes</strong></div>
            <div class="card-body d-flex flex-column">
                <div class="chart-wrap flex-grow-1"><canvas id="chartMonths"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6 mb-3">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header"><strong>Accesos por día y horario</strong></div>
            <div class="card-body d-flex flex-column">
                <div class="heat-wrap flex-grow-1">
                    <table class="hm-table">
                        <thead>
                            <tr><th>Hora</th>
                                @foreach($dowLabels as $lbl)<th>{{ $lbl }}</th>@endforeach
                            </tr>
                        </thead>
                        <tbody id="heatBody"></tbody>
                    </table>
                </div>
                <div class="mt-2 hm-legend">
                    <span class="box hm-0"></span> 0
                    <span class="box hm-1"></span> 1–20
                    <span class="box hm-2"></span> 21–40
                    <span class="box hm-3"></span> 41–60
                    <span class="box hm-4"></span> 61–80
                    <span class="box hm-5"></span> 81–100
                    <span class="box hm-6"></span> 101–300
                    <span class="box hm-7"></span> 300+
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-6 mb-3">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header"><strong>Accesos por usuario (Top 10)</strong></div>
            <div class="card-body d-flex flex-column">
                <div class="chart-wrap flex-grow-1"><canvas id="chartUsers"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6 mb-3">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header"><strong>Accesos por reporte (Top 10)</strong></div>
            <div class="card-body d-flex flex-column">
                <div class="chart-wrap flex-grow-1"><canvas id="chartReports"></canvas></div>
            </div>
        </div>
    </div>
</div>
