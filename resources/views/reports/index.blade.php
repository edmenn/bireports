@extends('adminlte::page')

@section('title', 'Todos los reportes')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">Todos los reportes</h1>
            <small class="text-muted">Listado importado desde Microsoft Fabric / Power BI</small>
        </div>
        <a href="{{ route('powerbi.import.run') }}" class="btn btn-outline-secondary">
            <i class="fas fa-cloud-download-alt"></i> Importar ahora
        </a>
    </div>
@stop

@section('content')
    @php
        $total = $reports->count();
        // Cover “no vacío”
        $conCover = $reports->filter(function($r){
            $c = trim((string)($r->effective_cover_url ?? ''));
            return $c !== '';
        })->count();
        $sinCover = $total - $conCover;

        // Workspace únicos (por nombre si existe, si no por ID)
        $wsSet = $reports->map(function($r){
            $name = $r->workspace?->name ?? null;
            $wid  = $r->workspace_id ?? null;
            return [
                'key'  => $name ? $name : (string)$wid,
                'text' => $name ? $name : (string)$wid
            ];
        })->filter(fn($x)=>!empty($x['key']))->unique('key')->sortBy('text')->values();
    @endphp

    @if($total === 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-inbox fa-3x text-muted"></i>
                </div>
                <h5 class="mb-2">No hay reportes importados</h5>
                <p class="text-muted mb-4">Ejecuta una importación para ver tus dashboards aquí.</p>
                <a href="{{ route('powerbi.import.run') }}" class="btn btn-primary">
                    <i class="fas fa-sync"></i> Importar ahora
                </a>
            </div>
        </div>
    @else

        {{-- Resumen superior --}}
        <div class="row">
            <div class="col-md-4">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3 class="mb-0">{{ $total }}</h3>
                        <p class="mb-0">Reportes totales</p>
                    </div>
                    <div class="icon"><i class="fas fa-chart-bar"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 class="mb-0">{{ $conCover }}</h3>
                        <p class="mb-0">Con portada</p>
                    </div>
                    <div class="icon"><i class="fas fa-image"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3 class="mb-0">{{ $sinCover }}</h3>
                        <p class="mb-0">Sin portada</p>
                    </div>
                    <div class="icon"><i class="fas fa-image-slash"></i></div>
                </div>
            </div>
        </div>

        {{-- Filtros / Orden --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label for="rpt-search" class="form-label mb-1">Buscar</label>
                        <input id="rpt-search" type="text" class="form-control" placeholder="Título, Report ID, Workspace o Dataset ID…">
                    </div>
                    <div class="col-md-3">
                        <label for="rpt-workspace" class="form-label mb-1">Workspace</label>
                        <select id="rpt-workspace" class="form-control">
                            <option value="all" selected>Todos</option>
                            @foreach($wsSet as $ws)
                                <option value="{{ $ws['key'] }}">{{ $ws['text'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="rpt-cover" class="form-label mb-1">Portada</label>
                        <select id="rpt-cover" class="form-control">
                            <option value="all" selected>Todas</option>
                            <option value="yes">Con portada</option>
                            <option value="no">Sin portada</option>
                        </select>
                    </div>
                </div>

                <div class="row g-2 align-items-end mt-2">
                    <div class="col-md-3">
                        <label for="rpt-order" class="form-label mb-1">Ordenar</label>
                        <select id="rpt-order" class="form-control">
                            <option value="name_asc" selected>Nombre (A→Z)</option>
                            <option value="name_desc">Nombre (Z→A)</option>
                            <option value="ws_asc">Workspace (A→Z)</option>
                            <option value="ws_desc">Workspace (Z→A)</option>
                        </select>
                    </div>
                    <div class="col-md-9 text-muted small">
                        <i class="far fa-lightbulb me-1"></i>
                        Los filtros y el orden se aplican en el navegador, sin recargar la página.
                    </div>
                </div>
            </div>
        </div>

        {{-- GRID DE TARJETAS --}}
        <div class="report-grid" id="rpt-grid">
            @foreach($reports as $r)
                @php
                    $title = (string)($r->name ?? '');
                    $cover = trim((string)($r->effective_cover_url ?? ''));
                    $wsName = $r->workspace->name ?? '';
                    $wsKey  = $wsName !== '' ? $wsName : (string)($r->workspace_id ?? '');
                    $datasetId = $r->dataset_id ?? '';
                    $rid = (string)($r->id ?? '');
                    $hasCover = $cover !== '' ? 'yes' : 'no';
                    $searchBlob = \Illuminate\Support\Str::lower(
                        $title.' '.$rid.' '.$wsKey.' '.$datasetId
                    );
                @endphp

                <div class="report-card shadow-sm"
                     data-title="{{ \Illuminate\Support\Str::lower($title) }}"
                     data-ws="{{ \Illuminate\Support\Str::lower($wsKey) }}"
                     data-cover="{{ $hasCover }}"
                     data-search="{{ $searchBlob }}">

                    {{-- Link clickeable (no tapa los botones gracias a z-index) --}}
                    <a class="stretched-link text-decoration-none card-link"
                       href="{{ route('reports.show', [$r->workspace_id, $r->id]) }}"
                       title="Abrir reporte"></a>

                    {{-- COVER --}}
                    <div class="report-cover">
                        @if($cover !== '')
                            @php $isDefault = \Illuminate\Support\Str::contains($cover, 'report-covers/default'); @endphp
                            <img src="{{ $cover }}"
                                 alt="cover {{ $r->name }}"
                                 loading="lazy"
                                 class="{{ $isDefault ? 'cover-default' : 'cover-full' }}">
                        @else
                            <svg class="bi-svg" viewBox="0 0 128 128" aria-hidden="true">
                                <defs>
                                    <linearGradient id="biYellow" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#FFD93D"/>
                                        <stop offset="100%" stop-color="#FFC107"/>
                                    </linearGradient>
                                </defs>
                                <rect x="8" y="28" width="112" height="80" rx="12" fill="url(#biYellow)"/>
                                <rect x="8" y="28" width="112" height="80" rx="12" fill="#000" opacity=".05"/>
                                <rect x="8" y="18" width="56" height="20" rx="6" fill="url(#biYellow)"/>
                                <rect x="8" y="18" width="56" height="20" rx="6" fill="#000" opacity=".05"/>
                                <g transform="translate(28,50)" fill="#fff">
                                    <rect x="0"   y="30" width="12" height="28" rx="2"/>
                                    <rect x="20"  y="18" width="12" height="40" rx="2"/>
                                    <rect x="40"  y="10" width="12" height="48" rx="2"/>
                                    <rect x="60"  y="4"  width="12" height="54" rx="2"/>
                                </g>
                            </svg>
                        @endif
                    </div>

                    {{-- Título + acciones --}}
                    <div class="px-3 pt-2">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <h3 class="h6 fw-semibold text-truncate mb-1" title="{{ $r->name }}">
                                {{ $r->name }}
                            </h3>

                            <div class="d-flex gap-1 position-relative" style="z-index:2">
                                <a href="{{ route('reports.show', [$r->workspace_id, $r->id]) }}"
                                   class="btn btn-sm btn-primary"
                                   title="Abrir dashboard"
                                   onclick="event.stopPropagation();">
                                    <i class="fas fa-chart-line"></i> Abrir
                                </a>
                                @role('admin')
                                    <a href="{{ route('reports.edit', ['report' => $r->id]) }}"
                                       class="btn btn-sm btn-outline-warning"
                                       title="Editar imagen y descripción"
                                       onclick="event.stopPropagation();">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                @endrole
                            </div>
                        </div>
                    </div>

                    {{-- Descripción opcional --}}
                    @if(!empty($r->description))
                        <div class="px-3 small text-muted description-clamp">
                            {{ $r->description }}
                        </div>
                    @endif

                    {{-- Chips / metadatos --}}
                    <div class="px-3 mt-2 mb-3">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-light text-dark border text-truncate"
                                  style="max-width: 260px"
                                  title="{{ $wsName !== '' ? $wsName : $r->workspace_id }}">
                                <i class="fas fa-layer-group me-1"></i>
                                {{ $wsName !== '' ? $wsName : 'Workspace' }}
                            </span>

                            @if(!empty($r->dataset_id))
                                <button type="button" class="badge bg-secondary border-0 copy-pill"
                                        data-copy="{{ $r->dataset_id }}" title="Copiar Dataset ID">
                                    Dataset
                                </button>
                            @endif

                            <button type="button" class="badge bg-primary border-0 copy-pill"
                                    data-copy="{{ $r->id }}" title="Copiar Report ID">
                                Report
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Si tu $reports es LengthAwarePaginator, puedes dejar la paginación del servidor aquí --}}
        @if(method_exists($reports,'links'))
            <div class="mt-3">
                {{ $reports->links() }}
            </div>
        @endif
    @endif
@stop

@push('css')
<style>
    :root{
        --card-radius: 14px;
        --card-border: rgba(0,0,0,.08);
        --card-hover: rgba(25,118,210,.35);
        --cover-height: 170px;
        --cover-bg: #f5f6fa;
    }
    .fw-600{font-weight:600;}
    .report-grid{
        display:grid;
        grid-template-columns: repeat(1, minmax(0,1fr));
        gap:1rem;
    }
    @media (min-width:576px){ .report-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
    @media (min-width:992px){ .report-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); } }
    @media (min-width:1400px){ .report-grid{ grid-template-columns: repeat(4, minmax(0,1fr)); } }

    .report-card{
        position:relative;
        border:1px solid var(--card-border);
        border-radius: var(--card-radius);
        background:#fff;
        overflow:hidden;
        transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }
    .report-card:hover{
        transform: translateY(-2px);
        box-shadow:0 .6rem 1.2rem rgba(0,0,0,.08)!important;
        border-color: var(--card-hover);
    }
    .report-card .card-link::after{ z-index: 0; }

    .report-cover{
        height: var(--cover-height);
        background: var(--cover-bg);
        display:flex; align-items:center; justify-content:center;
        border-bottom:1px solid rgba(0,0,0,.05);
    }
    .report-cover img.cover-full{
        width:100%;
        height:100%;
        object-fit:cover;
        display:block;
    }
    .report-cover img.cover-default{
        max-width:70%;
        max-height:70%;
        object-fit:contain;
        display:block;
        margin:auto;
    }
    .bi-svg{
        width: 60%;
        max-height: 80%;
        height: auto;
        filter: drop-shadow(0 8px 16px rgba(0,0,0,.12));
    }
    .description-clamp{
        display:-webkit-box;
        -webkit-line-clamp:2;
        -webkit-box-orient:vertical;
        overflow:hidden;
        min-height: 2.4em;
    }
    .copy-pill{ cursor:pointer; user-select:none; }
    .copy-pill.copied{
        filter: brightness(1.05);
        outline:2px solid rgba(25,135,84,.6);
        outline-offset:2px;
    }
</style>
@endpush

@push('js')
<script>
(function(){
    const grid = document.getElementById('rpt-grid');
    if(!grid) return;

    const cards = Array.from(grid.querySelectorAll('.report-card'));
    const search = document.getElementById('rpt-search');
    const wsSel  = document.getElementById('rpt-workspace');
    const cover  = document.getElementById('rpt-cover');
    const order  = document.getElementById('rpt-order');

    function norm(s){ return (s||'').toString().trim().toLowerCase(); }

    function applyFilters(){
        const q  = norm(search?.value || '');
        const ws = wsSel?.value || 'all';
        const cv = cover?.value || 'all';

        cards.forEach(card=>{
            const sb = card.dataset.search || '';
            const w  = card.dataset.ws || '';
            const c  = card.dataset.cover || 'no';

            const okText  = q === '' || sb.includes(q);
            const okWs    = (ws === 'all') || (w === norm(ws));
            const okCover = (cv === 'all') || (cv === c);

            card.style.display = (okText && okWs && okCover) ? '' : 'none';
        });
    }

    function applyOrder(){
        if(!order) return;
        const val = order.value;
        const [key, dir] = val.split('_'); // name|ws + asc|desc

        const visible = cards.filter(c => c.style.display !== 'none');
        const tbody = grid; // reutilizamos el grid

        visible.sort((a,b)=>{
            const av = key === 'name' ? (a.dataset.title||'') : (a.dataset.ws||'');
            const bv = key === 'name' ? (b.dataset.title||'') : (b.dataset.ws||'');
            if(av < bv) return dir === 'asc' ? -1 : 1;
            if(av > bv) return dir === 'asc' ? 1 : -1;
            return 0;
        });

        visible.forEach(el => tbody.appendChild(el));
    }

    // Debounce para búsqueda
    let t=null;
    function debounceFilters(){
        if(t) clearTimeout(t);
        t = setTimeout(()=>{ applyFilters(); applyOrder(); }, 120);
    }

    // Eventos
    search && search.addEventListener('input', debounceFilters);
    wsSel  && wsSel.addEventListener('change', ()=>{ applyFilters(); applyOrder(); });
    cover  && cover.addEventListener('change', ()=>{ applyFilters(); applyOrder(); });
    order  && order.addEventListener('change', applyOrder);

    // Copiar IDs con feedback
    document.addEventListener('click', function (e) {
        const pill = e.target.closest('.copy-pill');
        if (!pill) return;
        const value = pill.getAttribute('data-copy') || '';
        if (!value) return;

        navigator.clipboard.writeText(value).then(() => {
            pill.classList.add('copied');
            const original = pill.textContent;
            pill.textContent = 'Copiado';
            setTimeout(() => {
                pill.classList.remove('copied');
                pill.textContent = original;
            }, 900);
        }).catch(() => alert('No se pudo copiar al portapapeles.'));
    }, {passive:true});

    // Inicial
    applyFilters(); applyOrder();
})();
</script>
@endpush
