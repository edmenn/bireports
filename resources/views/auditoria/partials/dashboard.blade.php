{{-- resources/views/auditoria/partials/dashboard.blade.php --}}
@php
    $auditHeatmapLegend = __('audit.dashboard.heatmap.legend.buckets');
    if (! is_array($auditHeatmapLegend)) {
        $auditHeatmapLegend = [];
    }
    $auditChartLabels = [
        'accesses' => __('audit.dashboard.labels.accesses'),
    ];
@endphp
<div class="row mt-3">
    <div class="col-12 col-xl-6 mb-3">
        <div class="dash-card h-100 d-flex flex-column">
            <div class="dash-head">
                <strong class="dash-title">{{ __('audit.dashboard.cards.access_by_month.title') }}</strong>
                <span class="dash-sub">{{ __('audit.dashboard.cards.access_by_month.subtitle') }}</span>
            </div>
            <div class="dash-body d-flex flex-column">
                <div class="chart-wrap flex-grow-1">
                    <canvas id="chartMonths" role="img" aria-label="{{ __('audit.dashboard.cards.access_by_month.aria') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6 mb-3">
        <div class="dash-card h-100 d-flex flex-column">
            <div class="dash-head violet">
                <strong class="dash-title">{{ __('audit.dashboard.cards.access_by_day.title') }}</strong>
                <span class="dash-sub">{{ __('audit.dashboard.cards.access_by_day.subtitle') }}</span>
            </div>
            <div class="dash-body d-flex flex-column">
                <div class="chart-wrap flex-grow-1">
                    <canvas id="chartDaily" role="img" aria-label="{{ __('audit.dashboard.cards.access_by_day.aria') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-xl-6 mb-3">
        <div class="dash-card h-100 d-flex flex-column">
            <div class="dash-head green">
                <strong class="dash-title">{{ __('audit.dashboard.cards.access_by_user.title') }}</strong>
                <span class="dash-sub">{{ __('audit.dashboard.cards.access_by_user.subtitle') }}</span>
            </div>
            <div class="dash-body d-flex flex-column">
                <div class="chart-wrap flex-grow-1">
                    <canvas id="chartUsers" role="img" aria-label="{{ __('audit.dashboard.cards.access_by_user.aria') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6 mb-3">
        <div class="dash-card h-100 d-flex flex-column">
            <div class="dash-head orange">
                <strong class="dash-title">{{ __('audit.dashboard.cards.access_by_report.title') }}</strong>
                <span class="dash-sub">{{ __('audit.dashboard.cards.access_by_report.subtitle') }}</span>
            </div>
            <div class="dash-body d-flex flex-column">
                <div class="chart-wrap flex-grow-1">
                    <canvas id="chartReports" role="img" aria-label="{{ __('audit.dashboard.cards.access_by_report.aria') }}"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Heatmap area: no inner scroll, totals highlighted, accessible hover/focus --}}
<div class="row mt-3">
    <div class="col-12 mb-3">
        <div class="dash-card">
            <div class="dash-head slate">
                <strong class="dash-title">{{ __('audit.dashboard.heatmap.title') }}</strong>
                <span class="dash-sub">{{ __('audit.dashboard.heatmap.subtitle') }}</span>
            </div>

            <div class="dash-body">
                <div class="heat-wrap">
                    <div class="heat-guard">
                        <table class="hm-table w-100" id="heatTable">
                            <thead>
                                <tr>
                                    <th scope="col" data-col="H">{{ __('audit.dashboard.heatmap.hour') }}</th>
                                    @foreach($dowLabels as $i => $lbl)
                                        <th scope="col" data-col="{{ $i+1 }}">{{ $lbl }}</th>
                                    @endforeach
                                    <th scope="col" class="total-col" data-col="T">{{ __('audit.dashboard.heatmap.total') }}</th>
                                </tr>
                            </thead>
                            <tbody id="heatBody"></tbody>
                            <tfoot>
                                <tr id="heatTotalsRow" class="total-row">
                                    <th scope="row" data-col="H">{{ __('audit.dashboard.heatmap.total') }}</th>
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
                    <span class="text-muted small">{{ __('audit.dashboard.heatmap.legend.label') }}</span>
                    @foreach($auditHeatmapLegend as $bucket => $label)
                        <span class="box hm-{{ $bucket }}" title="{{ $label }}"></span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
/* --- Cards modernas con contraste mejorado en títulos --- */
.dash-card{
    background:#fff; border:1px solid rgba(0,0,0,.06);
    border-radius:16px; overflow:hidden;
    box-shadow:0 .35rem 1rem rgba(0,0,0,.05);
}
.dash-head{
    display:flex; align-items:baseline; justify-content:space-between; gap:.5rem;
    padding:.9rem 1rem; color:#fff;
    background: radial-gradient(900px 400px at 90% -10%, rgba(255,255,255,.2), transparent 60%),
                linear-gradient(135deg, var(--bs-primary), #22d3ee);
    border-bottom:1px solid rgba(255,255,255,.25);
}
.dash-head.violet{ background: linear-gradient(135deg, #7c3aed, #06b6d4); }
.dash-head.green { background: linear-gradient(135deg, #059669, #10b981); }
.dash-head.orange{ background: linear-gradient(135deg, #ea580c, #f59e0b); }
.dash-head.slate { background: linear-gradient(135deg, #334155, #64748b); }
.dash-title{ font-weight:800; letter-spacing:.2px; }
.dash-sub{ font-size:.85rem; opacity:.9; }
.dash-body{ padding:1rem; }

/* Charts: altura fluida, sin “saltos” al cargar */
.chart-wrap{ position:relative; min-height: 300px; }
@media (max-width:576px){ .chart-wrap{ min-height: 260px; } }

/* --- Heatmap --- */
.heat-wrap{ border:1px solid rgba(0,0,0,.06); border-radius:12px; padding:1rem; background:#fff; }
.heat-guard{ position: relative; overflow: hidden; }

.hm-table{
    border-collapse:separate; border-spacing:6px; table-layout: fixed; width:100%;
}
.hm-table thead th, .hm-table tfoot th{
    text-align:center; color:#334155; font-weight:800; font-size:1rem; white-space:nowrap;
}
.hm-table th, .hm-table td{
    text-align:center; padding:.6rem; border-radius:10px;
    border:1px solid rgba(0,0,0,.06);
    font-size:1rem; line-height:1.15;
}

/* Desactiva cualquier sticky heredado */
.hm-table [data-col="H"], .hm-table [data-col="T"]{ position: static !important; }

/* Paleta 0..7 */
.hm-0{ background:#f8fafc; color:#94a3b8; }
.hm-1{ background:#e6f0ff; color:#1f3d70; }
.hm-2{ background:#d6e8ff; color:#1d3767; }
.hm-3{ background:#c6e0ff; color:#1a325e; }
.hm-4{ background:#b6d8ff; color:#172d55; }
.hm-5{ background:#a6d0ff; color:#14284d; }
.hm-6{ background:#96c8ff; color:#122443; }
.hm-7{ background:#86c0ff; color:#101f3a; }

/* Totales */
.hm-table .total-col,
.hm-table .total-row{
    background:#eef2ff; border-color:#c7d2fe; color:#3730a3; font-weight:800;
}
#heatGrandTotal.total-col{ background:#e0e7ff; }

/* Hover fila/columna */
.hm-table tr.is-hover-row > th, .hm-table tr.is-hover-row > td{
    box-shadow: inset 0 0 0 9999px rgba(99,102,241,.10);
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
.hm-table td.is-focus, .hm-table th.is-focus{
    box-shadow: inset 0 0 0 9999px rgba(99,102,241,.18);
    outline: 0; /* evitamos doble foco junto al .is-focus */
}

/* Accesibilidad: foco por teclado visible en celdas */
.hm-table td:focus, .hm-table th:focus{
    outline: 2px solid rgba(79,70,229,.6);
    outline-offset: 2px;
}
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    if (!window.Chart) return;

    // === Datos del backend ===
    const months  = @json($months);
    const users   = @json($topUsers);
    const reports = @json($topReports);
    const heatmap = @json($heatmap);
    const daily   = @json($daily);
    const auditLabels = @json($auditChartLabels);

    // === Tema Chart.js usando variables Bootstrap/AdminLTE ===
    const styles = getComputedStyle(document.documentElement);
    const cPrimary  = styles.getPropertyValue('--bs-primary').trim() || '#4f46e5';
    const cInfo     = styles.getPropertyValue('--bs-info').trim()    || '#0ea5e9';
    const cGray700  = '#334155';
    const gridColor = 'rgba(148,163,184,.35)';

    // Utilidad: degradado para líneas
    function makeGradient(ctx, from, to){
        const g = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height || 300);
        g.addColorStop(0, from);
        g.addColorStop(1, to);
        return g;
    }

    // Opciones comunes
    const common = {
        responsive:true,
        maintainAspectRatio:false,
        animation: (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) ? false : { duration: 300 },
        plugins:{
            legend:{ display:false },
            tooltip:{ mode:'index', intersect:false, callbacks:{
                label: (item)=> `${item.dataset.label || auditLabels.accesses}: ${Number(item.parsed.y ?? item.parsed).toLocaleString()}`
            }}
        },
        scales:{
            x:{ grid:{ color:gridColor }, ticks:{ color:cGray700, maxRotation:0 } },
            y:{ grid:{ color:gridColor }, ticks:{ color:cGray700, callback:(v)=> Number(v).toLocaleString() }, beginAtZero:true }
        }
    };

    // --- Chart: Accesos por mes (bar) ---
    (function(){
        const el = document.getElementById('chartMonths');
        const ctx = el.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months.map(m => m.label),
                datasets: [{
                    label: auditLabels.accesses,
                    data: months.map(m => m.total),
                    backgroundColor: cPrimary,
                    borderRadius: 6,
                    maxBarThickness: 36
                }]
            },
            options: common
        });
    })();

    // --- Chart: Accesos diarios (line) ---
    (function(){
        const el = document.getElementById('chartDaily');
        const ctx = el.getContext('2d');
        const grad = makeGradient(ctx, cInfo, 'rgba(14,165,233,0.08)');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: daily.map(d => d.date),
                datasets: [{
                    label: auditLabels.accesses,
                    data: daily.map(d => d.total),
                    borderColor: cInfo,
                    backgroundColor: grad,
                    fill: true,
                    tension: .25,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    borderWidth: 2
                }]
            },
            options: common
        });
    })();

    // --- Chart: Top usuarios (bar) ---
    (function(){
        const el = document.getElementById('chartUsers');
        const ctx = el.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: users.map(u => u.name),
                datasets: [{
                    label: auditLabels.accesses,
                    data: users.map(u => u.total),
                    backgroundColor: cPrimary,
                    borderRadius: 6,
                    maxBarThickness: 36
                }]
            },
            options: common
        });
    })();

    // --- Chart: Top reportes (bar) ---
    (function(){
        const el = document.getElementById('chartReports');
        const ctx = el.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: reports.map(r => r.label),
                datasets: [{
                    label: auditLabels.accesses,
                    data: reports.map(r => r.total),
                    backgroundColor: cPrimary,
                    borderRadius: 6,
                    maxBarThickness: 36
                }]
            },
            options: common
        });
    })();

    // === Heatmap con totales + hover/foco accesibles ===
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
            td.setAttribute('tabindex', '0'); // foco por teclado
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

    // Hover/foco fila/columna (incluye Total)
    let currentCol = null;

    function clearHover(){
        table.classList.remove('hover-col-1','hover-col-2','hover-col-3','hover-col-4','hover-col-5','hover-col-6','hover-col-7','hover-col-T');
        tbody.querySelectorAll('tr.is-hover-row').forEach(el => el.classList.remove('is-hover-row'));
        table.querySelectorAll('.is-focus').forEach(el => el.classList.remove('is-focus'));
        currentCol = null;
    }

    function setHover(cell){
        const col = cell.getAttribute('data-col');
        const row = cell.parentElement;

        clearHover();

        if (row && row.parentElement === tbody) row.classList.add('is-hover-row');
        if (col && col !== 'H') {
            table.classList.add('hover-col-' + col);
            currentCol = col;
        }
        cell.classList.add('is-focus');
    }

    table.addEventListener('mouseover', (e) => {
        const cell = e.target.closest('td,th');
        if(!cell || !table.contains(cell)) return;
        setHover(cell);
    }, {passive:true});

    table.addEventListener('mouseleave', clearHover, {passive:true});

    // Accesible: navegación con TAB y flechas
    table.addEventListener('focusin', (e)=>{
        const cell = e.target.closest('td,th');
        if(!cell) return;
        setHover(cell);
    });
    table.addEventListener('keydown', (e)=>{
        const focus = document.activeElement;
        if (!focus || focus.tagName !== 'TD') return;
        const tr = focus.parentElement;
        const tds = Array.from(tr.querySelectorAll('td'));
        const colIdx = tds.indexOf(focus);

        // Flechas: izquierda/derecha (horizontal)
        if (e.key === 'ArrowRight' && colIdx < tds.length - 1) {
            tds[colIdx + 1].focus(); e.preventDefault();
        } else if (e.key === 'ArrowLeft' && colIdx > 0) {
            tds[colIdx - 1].focus(); e.preventDefault();
        }
        // Flechas: arriba/abajo (vertical)
        else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const rowIdx = rows.indexOf(tr);
            const nextIdx = e.key === 'ArrowDown' ? rowIdx + 1 : rowIdx - 1;
            if (nextIdx >= 0 && nextIdx < rows.length) {
                const nextRowTds = Array.from(rows[nextIdx].querySelectorAll('td'));
                const target = nextRowTds[colIdx] || nextRowTds[0];
                if (target) { target.focus(); e.preventDefault(); }
            }
        }
    });

    // Re-render pequeño al cambiar tamaño para mantener gradientes y grosores
    const ro = ('ResizeObserver' in window) ? new ResizeObserver(() => {
        document.querySelectorAll('#chartMonths,#chartDaily,#chartUsers,#chartReports').forEach(c => {
            if (c && c.chart && c.chart.resize) c.chart.resize();
        });
    }) : null;
    document.querySelectorAll('#chartMonths,#chartDaily,#chartUsers,#chartReports').forEach(c => {
        if (ro) ro.observe(c);
    });
});
</script>
@endpush
