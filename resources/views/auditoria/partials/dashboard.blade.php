{{-- resources/views/auditoria/partials/dashboard.blade.php --}}
<div class="row mt-3">
    <div class="col-12 col-xl-6 mb-3">
        <div class="dash-card h-100 d-flex flex-column">
            <div class="dash-head">
                <strong class="dash-title">Accesos por mes</strong>
                <span class="dash-sub">Distribución mensual</span>
            </div>
            <div class="dash-body d-flex flex-column">
                <div class="chart-wrap flex-grow-1"><canvas id="chartMonths"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6 mb-3">
        <div class="dash-card h-100 d-flex flex-column">
            <div class="dash-head violet">
                <strong class="dash-title">Accesos por día (tendencia)</strong>
                <span class="dash-sub">Últimos registros</span>
            </div>
            <div class="dash-body d-flex flex-column">
                <div class="chart-wrap flex-grow-1"><canvas id="chartDaily"></canvas></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-6 mb-3">
        <div class="dash-card h-100 d-flex flex-column">
            <div class="dash-head green">
                <strong class="dash-title">Accesos por usuario (Top 10)</strong>
                <span class="dash-sub">Usuarios más activos</span>
            </div>
            <div class="dash-body d-flex flex-column">
                <div class="chart-wrap flex-grow-1"><canvas id="chartUsers"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6 mb-3">
        <div class="dash-card h-100 d-flex flex-column">
            <div class="dash-head orange">
                <strong class="dash-title">Accesos por reporte (Top 10)</strong>
                <span class="dash-sub">Dashboards más consultados</span>
            </div>
            <div class="dash-body d-flex flex-column">
                <div class="chart-wrap flex-grow-1"><canvas id="chartReports"></canvas></div>
            </div>
        </div>
    </div>
</div>

{{-- HEATMAP: sin scroll, sin sticky, totales con color y hover fila/columna --}}
<div class="row mt-3">
    <div class="col-12 mb-3">
        {{-- sin h-100/d-flex aquí para no forzar overflow --}}
        <div class="dash-card">
            <div class="dash-head slate">
                <strong class="dash-title">Accesos por día y horario</strong>
                <span class="dash-sub">Mapa de calor semanal (1=lun … 7=dom)</span>
            </div>

            <div class="dash-body">
                <div class="heat-wrap">
                    <div class="heat-guard">
                        <table class="hm-table w-100" id="heatTable">
                            <thead>
                                <tr>
                                    <th data-col="H">Hora</th>
                                    @foreach($dowLabels as $i => $lbl)
                                        <th data-col="{{ $i+1 }}">{{ $lbl }}</th>
                                    @endforeach
                                    <th class="total-col" data-col="T">Total</th>
                                </tr>
                            </thead>
                            <tbody id="heatBody"></tbody>
                            <tfoot>
                                <tr id="heatTotalsRow" class="total-row">
                                    <th data-col="H">Total</th>
                                    @for($d=1; $d<=7; $d++)
                                        <th class="total-row" data-col="{{ $d }}" data-day="{{ $d }}">0</th>
                                    @endfor
                                    <th class="total-row total-col" data-col="T" id="heatGrandTotal">0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="mt-3 hm-legend d-flex align-items-center flex-wrap gap-2">
                    <span class="text-muted small">Intensidad</span>
                    <span class="box hm-0" title="0"></span>
                    <span class="box hm-1" title="1–20"></span>
                    <span class="box hm-2" title="21–40"></span>
                    <span class="box hm-3" title="41–60"></span>
                    <span class="box hm-4" title="61–80"></span>
                    <span class="box hm-5" title="81–100"></span>
                    <span class="box hm-6" title="101–300"></span>
                    <span class="box hm-7" title="300+"></span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
/* --- Cards modernas --- */
.dash-card{ background:#fff; border:1px solid rgba(0,0,0,.06); border-radius:16px; overflow:hidden; box-shadow:0 .35rem 1rem rgba(0,0,0,.05); }
.dash-head{
    display:flex; align-items:baseline; justify-content:space-between; gap:.5rem;
    padding:.9rem 1rem; color:#fff;
    background: radial-gradient(900px 400px at 90% -10%, rgba(255,255,255,.25), transparent 60%),
                linear-gradient(135deg, #4f46e5, #22d3ee);
    border-bottom:1px solid rgba(255,255,255,.25);
}
.dash-head.violet{ background: linear-gradient(135deg, #7c3aed, #06b6d4); }
.dash-head.green { background: linear-gradient(135deg, #059669, #10b981); }
.dash-head.orange{ background: linear-gradient(135deg, #ea580c, #f59e0b); }
.dash-head.slate { background: linear-gradient(135deg, #334155, #64748b); }
.dash-title{ font-weight:800; letter-spacing:.2px; }
.dash-sub{ font-size:.85rem; opacity:.9; }
.dash-body{ padding:1rem; }

/* Charts */
.chart-wrap{ position:relative; min-height: 300px; }
@media (max-width:576px){ .chart-wrap{ min-height: 260px; } }

/* --- Heatmap (sin sticky y sin scroll interno) --- */
.heat-wrap{ border:1px solid rgba(0,0,0,.06); border-radius:12px; padding:1rem; background:#fff; }
.heat-guard{ position: relative; overflow: hidden; } /* evita fugas hacia los bordes */

.hm-table{
    border-collapse:separate; border-spacing:6px;
    table-layout: fixed; width:100%;
}
.hm-table thead th, .hm-table tfoot th{
    text-align:center; color:#334155; font-weight:800; font-size:1rem; white-space:nowrap;
}
.hm-table th, .hm-table td{
    text-align:center; padding:.6rem; border-radius:10px;
    border:1px solid rgba(0,0,0,.06);
    font-size:1rem; line-height:1.15;
}

/* Desactiva cualquier sticky previo (clave para AdminLTE) */
.hm-table th[data-col="H"],
.hm-table th[data-col="T"],
.hm-table td[data-col="T"]{
    position: static !important;
}

/* Paleta 0..7 para celdas de datos */
.hm-0{ background:#f8fafc; color:#94a3b8; }
.hm-1{ background:#e6f0ff; color:#1f3d70; }
.hm-2{ background:#d6e8ff; color:#1d3767; }
.hm-3{ background:#c6e0ff; color:#1a325e; }
.hm-4{ background:#b6d8ff; color:#172d55; }
.hm-5{ background:#a6d0ff; color:#14284d; }
.hm-6{ background:#96c8ff; color:#122443; }
.hm-7{ background:#86c0ff; color:#101f3a; }

/* === Estilos de totales (fila y columna) === */
.hm-table .total-col,
.hm-table .total-row{
    background:#eef2ff;            /* violeta azulado muy suave */
    border-color:#c7d2fe;
    color:#3730a3;                  /* violeta oscuro */
    font-weight:800;
}
#heatGrandTotal.total-col{ background:#e0e7ff; } /* gran total levemente distinto */

/* === Hover de fila y de columna === */
.hm-table tr.is-hover-row > th,
.hm-table tr.is-hover-row > td{
    box-shadow: inset 0 0 0 9999px rgba(99,102,241,.10); /* luz suave */
}
.hm-table.hover-col-1 [data-col="1"],
.hm-table.hover-col-2 [data-col="2"],
.hm-table.hover-col-3 [data-col="3"],
.hm-table.hover-col-4 [data-col="4"],
.hm-table.hover-col-5 [data-col="5"],
.hm-table.hover-col-6 [data-col="6"],
.hm-table.hover-col-7 [data-col="7"],
.hm-table.hover-col-T [data-col="T"]{
    box-shadow: inset 0 0 0 9999px rgba(99,102,241,.10);
}
/* Hover fuerte para la intersección (celda bajo cursor) */
.hm-table td.is-focus,
.hm-table th.is-focus{
    box-shadow: inset 0 0 0 9999px rgba(99,102,241,.18);
}
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    if (!window.Chart) return;

    // === Datos del backend (sin cambios) ===
    const months  = @json($months);
    const users   = @json($topUsers);
    const reports = @json($topReports);
    const heatmap = @json($heatmap);
    const daily   = @json($daily);

    // === Charts (tus opciones base) ===
    const common = {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{ legend:{ display:false } },
        scales:{ y:{ beginAtZero:true } }
    };

    new Chart(document.getElementById('chartMonths').getContext('2d'), {
        type: 'bar',
        data: { labels: months.map(m => m.label), datasets: [{ label: 'Accesos', data: months.map(m => m.total) }] },
        options: common
    });

    new Chart(document.getElementById('chartDaily').getContext('2d'), {
        type: 'line',
        data: { labels: daily.map(d => d.date), datasets: [{ label: 'Accesos', data: daily.map(d => d.total), tension: .2 }] },
        options: common
    });

    new Chart(document.getElementById('chartUsers').getContext('2d'), {
        type: 'bar',
        data: { labels: users.map(u => u.name), datasets: [{ label: 'Accesos', data: users.map(u => u.total) }] },
        options: common
    });

    new Chart(document.getElementById('chartReports').getContext('2d'), {
        type: 'bar',
        data: { labels: reports.map(r => r.label), datasets: [{ label: 'Accesos', data: reports.map(r => r.total) }] },
        options: common
    });

    // === Heatmap (sin sticky) con totales + hover ===
    function bucket(v){
        if(v===0) return 0;
        if(v<=20) return 1;
        if(v<=40) return 2;
        if(v<=60) return 3;
        if(v<=80) return 4;
        if(v<=100) return 5;
        if(v<=300) return 6;
        return 7;
    }
    const fmt = n => (n ?? 0).toLocaleString(undefined);

    const table     = document.getElementById('heatTable');
    const tbody     = document.getElementById('heatBody');
    const totalsRow = document.getElementById('heatTotalsRow');
    const grandCell = document.getElementById('heatGrandTotal');

    const colTotals = {1:0,2:0,3:0,4:0,5:0,6:0,7:0};
    let grandTotal = 0;

    (heatmap || []).forEach(row => {
        const tr = document.createElement('tr');

        // Hora
        const th = document.createElement('th');
        th.textContent = row.hour;
        th.setAttribute('data-col','H');
        tr.appendChild(th);

        // Celdas por día
        let rowSum = 0;
        for(let d=1; d<=7; d++){
            const v = Number(row[d] ?? 0);
            rowSum += v;
            colTotals[d] += v;
            grandTotal += v;

            const td = document.createElement('td');
            td.className = 'hm-' + bucket(v);
            td.textContent = fmt(v);
            td.setAttribute('data-col', String(d));
            tr.appendChild(td);
        }

        // Total por hora (última columna)
        const tdTotal = document.createElement('th');
        tdTotal.className = 'total-col';
        tdTotal.textContent = fmt(rowSum);
        tdTotal.setAttribute('data-col','T');
        tr.appendChild(tdTotal);

        tbody.appendChild(tr);
    });

    // Totales por día y gran total
    for(let d=1; d<=7; d++){
        const th = totalsRow.querySelector(`th[data-day="${d}"]`);
        if(th) th.textContent = fmt(colTotals[d]);
    }
    if(grandCell) grandCell.textContent = fmt(grandTotal);

    // === Hover fila/columna (incluye columna Total) ===
    let currentCol = null, currentRowEl = null, currentFocus = null;

    function clearHover(){
        if (table) {
            table.classList.remove('hover-col-1','hover-col-2','hover-col-3','hover-col-4','hover-col-5','hover-col-6','hover-col-7','hover-col-T');
        }
        document.querySelectorAll('.hm-table tr.is-hover-row').forEach(el => el.classList.remove('is-hover-row'));
        document.querySelectorAll('.hm-table .is-focus').forEach(el => el.classList.remove('is-focus'));
        currentCol = null; currentRowEl = null; currentFocus = null;
    }

    table.addEventListener('mouseover', (e) => {
        const cell = e.target.closest('td,th');
        if(!cell || !table.contains(cell)) return;

        const col = cell.getAttribute('data-col'); // '1'..'7', 'T', 'H'
        const row = cell.parentElement;

        clearHover();

        // Marca fila (excepto header/footer cuando corresponda)
        if (row && row.parentElement === tbody) {
            row.classList.add('is-hover-row');
            currentRowEl = row;
        }

        // Marca columna si es 1..7 o T (Total)
        if (col && col !== 'H') {
            table.classList.add('hover-col-' + col);
            currentCol = col;
        }

        // Marca foco exacto (celda bajo cursor)
        cell.classList.add('is-focus');
        currentFocus = cell;
    }, {passive:true});

    table.addEventListener('mouseleave', clearHover, {passive:true});
});
</script>
@endpush
