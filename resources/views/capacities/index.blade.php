{{-- resources/views/capacities/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Capacidades')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">Capacidades</h1>
            <small class="text-muted">Fabric / Power BI Capacities</small>
        </div>
        <form action="{{ route('capacities.refresh') }}" method="POST" class="m-0">
            @csrf
            <button class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fas fa-sync-alt mr-1"></i> Refrescar
            </button>
        </form>
    </div>
@stop

@section('content')
    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @php
        $total    = $caps->count();
        $activos  = $caps->where('is_active', true)->count();
        $pausados = $total - $activos;

        $regiones = $caps->pluck('region')->filter()->unique()->sort()->values();
        $skus     = $caps->pluck('sku')->filter()->unique()->sort()->values();
    @endphp

    @if($total === 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-server fa-3x text-muted mb-3"></i>
                <h5 class="mb-2">Sin capacidades</h5>
                <p class="text-muted">Ejecuta “Refrescar” para traer las capacidades desde Fabric.</p>
                <form action="{{ route('capacities.refresh') }}" method="POST">
                    @csrf
                    <button class="btn btn-primary rounded-pill px-3">
                        <i class="fas fa-sync-alt mr-1"></i> Refrescar
                    </button>
                </form>
            </div>
        </div>
    @else
        {{-- KPIs modernos --}}
        <div class="row kpi-row">
            <div class="col-md-4 mb-3">
                <div class="kpi-card kpi-primary">
                    <div>
                        <div class="kpi-label">Capacidades totales</div>
                        <div class="kpi-value">{{ $total }}</div>
                    </div>
                    <i class="fas fa-layer-group kpi-icon"></i>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="kpi-card kpi-success">
                    <div>
                        <div class="kpi-label">Activas</div>
                        <div class="kpi-value">{{ $activos }}</div>
                    </div>
                    <i class="fas fa-play kpi-icon"></i>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="kpi-card kpi-warning">
                    <div>
                        <div class="kpi-label">Pausadas</div>
                        <div class="kpi-value">{{ $pausados }}</div>
                    </div>
                    <i class="fas fa-pause kpi-icon"></i>
                </div>
            </div>
        </div>

        {{-- Filtros rápidos (cliente) --}}
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="mb-1">Buscar</label>
                        <input id="cap-search" type="text" class="form-control form-control-lg"
                               placeholder="Nombre, GUID, región, SKU…">
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">Estado</label>
                        <select id="cap-state" class="form-control form-control-lg">
                            <option value="all" selected>Todos</option>
                            <option value="active">Activos</option>
                            <option value="paused">Pausados</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="mb-1">SKU</label>
                        <select id="cap-sku" class="form-control form-control-lg">
                            <option value="all" selected>Todos</option>
                            @foreach($skus as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="mb-1">Región</label>
                        <select id="cap-region" class="form-control form-control-lg">
                            <option value="all" selected>Todas</option>
                            @foreach($regiones as $r)
                                <option value="{{ $r }}">{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex align-items-center mt-2 small text-muted">
                    <i class="far fa-lightbulb mr-1"></i> Los filtros se aplican en el navegador (sin recarga).
                    <span id="cap-visible-counter" class="ml-2"></span>
                </div>
            </div>
        </div>

        {{-- Tabla moderna --}}
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table id="cap-table" class="table cap-table mb-0">
                    <thead class="sticky-thead">
                    <tr>
                        <th class="sortable" data-sort="name">Nombre <i class="fas fa-sort text-muted ml-1"></i></th>
                        <th>GUID</th>
                        <th class="sortable" data-sort="sku">SKU <i class="fas fa-sort text-muted ml-1"></i></th>
                        <th class="sortable" data-sort="region">Región <i class="fas fa-sort text-muted ml-1"></i></th>
                        <th class="sortable" data-sort="state">Estado <i class="fas fa-sort text-muted ml-1"></i></th>
                        <th class="text-right">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($caps as $c)
                        @php
                            $isActive  = (bool) $c->is_active;
                            $name      = (string) ($c->display_name ?? '');
                            $guid      = (string) ($c->capacity_uuid ?? '');
                            $skuVal    = (string) ($c->sku ?? '');
                            $regionVal = (string) ($c->region ?? '');
                            $searchBlob = \Illuminate\Support\Str::lower(trim($name.' '.$guid.' '.$skuVal.' '.$regionVal));
                        @endphp
                        <tr
                            data-name="{{ \Illuminate\Support\Str::lower($name) }}"
                            data-guid="{{ \Illuminate\Support\Str::lower($guid) }}"
                            data-sku="{{ \Illuminate\Support\Str::lower($skuVal) }}"
                            data-region="{{ \Illuminate\Support\Str::lower($regionVal) }}"
                            data-state="{{ $isActive ? 'active' : 'paused' }}"
                            data-search="{{ $searchBlob }}"
                        >
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark h6 mb-0">{{ $c->display_name }}</div>
                                <small class="text-muted">{{ $c->type }}</small>
                            </td>
                            <td class="align-middle">
                                @if($guid)
                                    <code class="mr-2">{{ \Illuminate\Support\Str::limit($guid, 22) }}</code>
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill js-copy"
                                            data-copy="{{ $guid }}" title="Copiar GUID">
                                        <i class="far fa-copy"></i>
                                    </button>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="align-middle">{{ $c->sku }}</td>
                            <td class="align-middle">{{ $c->region }}</td>
                            <td class="align-middle">
                                @if ($isActive)
                                    <span class="badge badge-pill badge-success px-3 py-2">
                                        <i class="fas fa-play mr-1"></i> Activa
                                    </span>
                                @else
                                    <span class="badge badge-pill badge-warning px-3 py-2">
                                        <i class="fas fa-pause mr-1"></i> Pausada
                                    </span>
                                @endif
                                @if ($c->last_state_change_at)
                                    <div><small class="text-muted">Último cambio: {{ \Illuminate\Support\Carbon::parse($c->last_state_change_at)->diffForHumans() }}</small></div>
                                @endif
                            </td>
                            <td class="align-middle text-right">
                                <div class="btn-group" role="group">
                                    @if ($isActive)
                                        <form action="{{ route('capacities.pause', $c) }}" method="POST" onsubmit="return confirm('¿Pausar esta capacidad?');" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                                <i class="fas fa-pause mr-1"></i> Pausar
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('capacities.resume', $c) }}" method="POST" onsubmit="return confirm('¿Reanudar esta capacidad?');" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                <i class="fas fa-play mr-1"></i> Reanudar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if ($caps->hasPages())
                <div class="card-footer">
                    {{ $caps->links() }}
                </div>
            @endif
        </div>
    @endif
@stop

@push('css')
<style>
/* ===== KPIs modernas ===== */
.kpi-card{
    display:flex; align-items:center; justify-content:space-between;
    border-radius:16px; padding:1rem 1.25rem; color:#fff;
    box-shadow:0 .6rem 1.2rem rgba(0,0,0,.08);
}
.kpi-label{ font-size:.95rem; opacity:.9; margin-bottom:.25rem; }
.kpi-value{ font-size:2rem; font-weight:800; line-height:1; }
.kpi-icon{ font-size:2rem; opacity:.9; }
.kpi-primary{ background:linear-gradient(135deg,#3b82f6,#06b6d4); }
.kpi-success{ background:linear-gradient(135deg,#10b981,#34d399); }
.kpi-warning{ background:linear-gradient(135deg,#f59e0b,#f97316); }

/* ===== Tabla moderna ===== */
.cap-table{ font-size:1rem; }
.cap-table thead th{
    position: sticky; top: 0; z-index: 2;
    background: #f8fafc; border-bottom: 1px solid rgba(0,0,0,.06);
    font-weight:700; color:#334155; padding: .85rem .75rem;
}
.cap-table tbody td, .cap-table tbody th{ padding: .9rem .75rem; vertical-align: middle; }
.cap-table tbody tr{ transition: background-color .12s ease, transform .06s ease; }
.cap-table tbody tr:hover{ background: #fbfdff; }
.cap-table code{ font-size:.9rem; }
.badge-pill{ border-radius: 999px; }

/* Botones redondos */
.btn{ border-radius: .75rem; }
.btn-xs{ padding: .15rem .45rem; border-radius: 999px; }

/* Ordenamiento */
.sortable{ cursor:pointer; user-select:none; }
.sortable .fa-sort{ opacity:.6; }

/* Ajustes responsive */
@media (max-width: 768px){
    .cap-table{ font-size:.95rem; }
    .kpi-value{ font-size:1.6rem; }
}
</style>
@endpush

@push('js')
<script>
(function(){
    const table  = document.getElementById('cap-table');
    if(!table) return;

    const rows   = Array.from(table.querySelectorAll('tbody tr'));
    const search = document.getElementById('cap-search');
    const state  = document.getElementById('cap-state');
    const sku    = document.getElementById('cap-sku');
    const region = document.getElementById('cap-region');
    const counter= document.getElementById('cap-visible-counter');

    let sortKey = null;
    let sortDir = 'asc';

    function norm(s){ return (s||'').toString().trim().toLowerCase(); }

    function applyFilters(){
        const q  = norm(search?.value || '');
        const st = state?.value || 'all';   // all|active|paused
        const sk = norm(sku?.value || 'all');
        const rg = norm(region?.value || 'all');

        let visible = 0;
        rows.forEach(tr=>{
            const sb   = tr.dataset.search || '';
            const ds   = tr.dataset.state  || 'paused';
            const dsku = tr.dataset.sku    || '';
            const drg  = tr.dataset.region || '';

            const okText  = (q === '' || sb.includes(q));
            const okState = (st === 'all') || (st === ds);
            const okSku   = (sk === 'all') || (sk === dsku);
            const okReg   = (rg === 'all') || (rg === drg);

            const show = okText && okState && okSku && okReg;
            tr.style.display = show ? '' : 'none';
            if(show) visible++;
        });

        if(counter){ counter.textContent = `— ${visible} visibles`; }
    }

    function getKey(tr, key){
        switch(key){
            case 'name':   return tr.dataset.name || '';
            case 'sku':    return tr.dataset.sku || '';
            case 'region': return tr.dataset.region || '';
            case 'state':  return tr.dataset.state || 'paused';
            default:       return '';
        }
    }

    function applySort(){
        if(!sortKey) return;
        const tbody = table.querySelector('tbody');
        const visible = rows.filter(tr => tr.style.display !== 'none');

        visible.sort((a,b)=>{
            const va = getKey(a, sortKey);
            const vb = getKey(b, sortKey);
            if(va < vb) return sortDir === 'asc' ? -1 : 1;
            if(va > vb) return sortDir === 'asc' ? 1 : -1;
            return 0;
        });

        visible.forEach(tr => tbody.appendChild(tr));
    }

    // Eventos filtros
    [search, state, sku, region].forEach(el=>{
        if(!el) return;
        const evt = el.tagName === 'INPUT' ? 'input' : 'change';
        el.addEventListener(evt, ()=>{ applyFilters(); applySort(); });
    });

    // Orden por columnas
    table.querySelectorAll('thead th.sortable').forEach(th=>{
        th.addEventListener('click', ()=>{
            const key = th.dataset.sort;
            if(sortKey === key){ sortDir = (sortDir === 'asc' ? 'desc' : 'asc'); }
            else { sortKey = key; sortDir = 'asc'; }

            table.querySelectorAll('thead th.sortable i').forEach(i=> i.className = 'fas fa-sort text-muted ml-1');
            th.querySelector('i').className = (sortDir === 'asc' ? 'fas fa-sort-up ml-1' : 'fas fa-sort-down ml-1');

            applySort();
        });
    });

    // Copiar GUID
    table.addEventListener('click', (e)=>{
        const btn = e.target.closest('.js-copy');
        if(!btn) return;
        const text = btn.dataset.copy || '';
        if(!text) return;
        navigator.clipboard.writeText(text).then(()=>{
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            setTimeout(()=>{ btn.classList.add('btn-outline-secondary'); btn.classList.remove('btn-success'); }, 900);
        }).catch(()=> alert('No se pudo copiar'));
    });

    // Inicial
    applyFilters();
})();
</script>
@endpush
