@extends('adminlte::page')

@section('title', 'Aplicaciones')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">Aplicaciones</h1>
            <small class="text-muted">Power BI / Fabric Apps</small>
        </div>
        @if(!empty($hasConfig) && $hasConfig)
            <form action="{{ route('apps.refresh') }}" method="POST" class="m-0">
                @csrf
                <button class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-sync-alt me-1"></i> Refrescar
                </button>
            </form>
        @else
            <a href="{{ route('configuraciones.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Configura credenciales para habilitar el refresco">
                <i class="fas fa-cogs me-1"></i> Configurar
            </a>
        @endif
    </div>
@stop

@section('content')
    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @php
        $apps      = $apps ?? collect(); // asegurar colección
        $total     = $apps->count();

        // Derivar listas únicas para filtros sin romper si vienen null
        $workspaces = $apps->pluck('workspace_name')->filter()->unique()->sort()->values();
        $owners     = $apps->pluck('owner_name')->filter()->unique()->sort()->values();
    @endphp

    @if($total === 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-th-large fa-3x text-muted mb-3"></i>
                <h5 class="mb-2">Sin aplicaciones</h5>
                <p class="text-muted">Usa “Refrescar” para traer las aplicaciones desde Fabric / Power BI.</p>
                @if(!empty($hasConfig) && $hasConfig)
                    <form action="{{ route('apps.refresh') }}" method="POST">
                        @csrf
                        <button class="btn btn-primary rounded-pill px-3">
                            <i class="fas fa-sync-alt me-1"></i> Refrescar
                        </button>
                    </form>
                @else
                    <a href="{{ route('configuraciones.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-cogs me-1"></i> Configurar
                    </a>
                @endif
            </div>
        </div>
    @else
        {{-- Filtros rápidos --}}
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="mb-1">Buscar</label>
                        <input id="app-search" type="text" class="form-control form-control-lg"
                               placeholder="Nombre, App ID, workspace, propietario…">
                    </div>
                    <div class="col-md-3">
                        <label class="mb-1">Workspace</label>
                        <select id="app-workspace" class="form-control form-control-lg">
                            <option value="all" selected>Todos</option>
                            @foreach($workspaces as $ws)
                                <option value="{{ \Illuminate\Support\Str::lower($ws) }}">{{ $ws }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="mb-1">Propietario</label>
                        <select id="app-owner" class="form-control form-control-lg">
                            <option value="all" selected>Todos</option>
                            @foreach($owners as $ow)
                                <option value="{{ \Illuminate\Support\Str::lower($ow) }}">{{ $ow }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex align-items-center mt-2 small text-muted">
                    <i class="far fa-lightbulb me-1"></i> Filtros aplicados en el navegador.
                    <span id="app-visible-counter" class="ms-2"></span>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table id="app-table" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="name">Nombre <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th>App ID</th>
                            <th class="sortable" data-sort="workspace">Workspace <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-sort="owner">Propietario <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="sortable" data-sort="updated">Actualizado <i class="fas fa-sort text-muted ms-1"></i></th>
                            <th class="text-end">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($apps as $a)
                            @php
                                $name   = (string) ($a->display_name ?? $a->name ?? '');
                                $id     = (string) ($a->app_id ?? $a->id ?? '');
                                $ws     = (string) ($a->workspace_name ?? '');
                                $owner  = (string) ($a->owner_name ?? '');
                                $updRaw = $a->updated_at ?? $a->modified_at ?? $a->last_modified ?? null;

                                // Normalizados para data-attrs y búsqueda
                                $blob = \Illuminate\Support\Str::lower(trim($name.' '.$id.' '.$ws.' '.$owner));

                                // Estado: published/unpublished/disabled (flexible)
                                $state = (string) ($a->state ?? $a->status ?? '');
                                $stateNorm = \Illuminate\Support\Str::of($state)->lower()->value();
                                $badgeClass = 'bg-secondary';
                                $stateLabel = $state ?: '—';
                                if (in_array($stateNorm, ['published','active','enabled'])) { $badgeClass = 'bg-success'; $stateLabel = 'Publicado'; }
                                elseif (in_array($stateNorm, ['unpublished','draft'])) { $badgeClass = 'bg-warning'; $stateLabel = 'Borrador'; }
                                elseif (in_array($stateNorm, ['disabled','archived'])) { $badgeClass = 'bg-secondary'; $stateLabel = 'Archivado'; }
                            @endphp
                            <tr
                                data-name="{{ \Illuminate\Support\Str::lower($name) }}"
                                data-id="{{ \Illuminate\Support\Str::lower($id) }}"
                                data-workspace="{{ \Illuminate\Support\Str::lower($ws) }}"
                                data-owner="{{ \Illuminate\Support\Str::lower($owner) }}"
                                data-updated="{{ $updRaw ? \Illuminate\Support\Carbon::parse($updRaw)->format('Y-m-d\TH:i:s') : '' }}"
                                data-search="{{ $blob }}"
                            >
                                <td>
                                    <div class="fw-semibold">{{ $name ?: '—' }}</div>
                                    @if(!empty($a->description))
                                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($a->description, 80) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($id)
                                        <code class="me-2">{{ \Illuminate\Support\Str::limit($id, 22) }}</code>
                                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill js-copy" data-copy="{{ $id }}" title="Copiar App ID" aria-label="Copiar App ID">
                                            <i class="far fa-copy"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $ws ?: '—' }}</td>
                                <td>{{ $owner ?: '—' }}</td>
                                <td>
                                    @if($updRaw)
                                        <span title="{{ \Illuminate\Support\Carbon::parse($updRaw)->toDateTimeString() }}">
                                            {{ \Illuminate\Support\Carbon::parse($updRaw)->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="badge rounded-pill {{ $badgeClass }} px-3 py-2">{{ $stateLabel }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if (method_exists($apps, 'hasPages') && $apps->hasPages())
                <div class="card-footer">
                    {{ $apps->links() }}
                </div>
            @endif
        </div>
    @endif
@stop

@push('css')
<style>
/* Tabla y controles */
#app-table thead th{ font-weight:700; color:#334155; }
#app-table tbody tr{ transition: background-color .12s ease; }
#app-table tbody tr:hover{ background:#fbfdff; }
.sortable{ cursor:pointer; user-select:none; }
.sortable .fa-sort{ opacity:.6; }
/* Botón chip */
.btn-xs{ padding:.15rem .45rem; border-radius:999px; }
/* Responsive */
@media (max-width: 768px){
    #app-table{ font-size:.95rem; }
}
</style>
@endpush

@push('js')
<script>
(function(){
    const table    = document.getElementById('app-table');
    if(!table) return;

    const rows     = Array.from(table.querySelectorAll('tbody tr'));
    const searchEl = document.getElementById('app-search');
    const wsEl     = document.getElementById('app-workspace');
    const ownerEl  = document.getElementById('app-owner');
    const counter  = document.getElementById('app-visible-counter');

    let sortKey = null, sortDir = 'asc';

    const norm = (s) => (s||'').toString().trim().toLowerCase();

    function applyFilters(){
        const q  = norm(searchEl?.value || '');
        const ws = wsEl?.value || 'all';
        const ow = ownerEl?.value || 'all';

        let visible = 0;
        rows.forEach(tr=>{
            const blob = tr.dataset.search || '';
            const dws  = tr.dataset.workspace || '';
            const dow  = tr.dataset.owner || '';

            const okText = (q === '' || blob.includes(q));
            const okWs   = (ws === 'all') || (ws === dws);
            const okOw   = (ow === 'all') || (ow === dow);

            const show = okText && okWs && okOw;
            tr.style.display = show ? '' : 'none';
            if(show) visible++;
        });

        if(counter){ counter.textContent = `— ${visible} visibles`; }
    }

    function getKey(tr, key){
        switch(key){
            case 'name':      return tr.dataset.name || '';
            case 'workspace': return tr.dataset.workspace || '';
            case 'owner':     return tr.dataset.owner || '';
            case 'updated':   return tr.dataset.updated || ''; // ISO string
            default:          return '';
        }
    }

    function applySort(){
        if(!sortKey) return;
        const tbody  = table.querySelector('tbody');
        const shown  = rows.filter(tr => tr.style.display !== 'none');

        shown.sort((a,b)=>{
            const va = getKey(a, sortKey);
            const vb = getKey(b, sortKey);
            if(va < vb) return sortDir === 'asc' ? -1 : 1;
            if(va > vb) return sortDir === 'asc' ? 1 : -1;
            return 0;
        });

        shown.forEach(tr => tbody.appendChild(tr));
    }

    // Eventos filtros
    [searchEl, wsEl, ownerEl].forEach(el=>{
        if(!el) return;
        el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', ()=>{ applyFilters(); applySort(); });
    });

    // Orden por columnas
    table.querySelectorAll('thead th.sortable').forEach(th=>{
        th.addEventListener('click', ()=>{
            const key = th.dataset.sort;
            if(sortKey === key){ sortDir = (sortDir === 'asc' ? 'desc' : 'asc'); }
            else { sortKey = key; sortDir = 'asc'; }

            table.querySelectorAll('thead th.sortable i').forEach(i=> i.className = 'fas fa-sort text-muted ms-1');
            th.querySelector('i').className = (sortDir === 'asc' ? 'fas fa-sort-up ms-1' : 'fas fa-sort-down ms-1');

            applySort();
        });
    });

    // Copiar App ID
    table.addEventListener('click', (e)=>{
        const btn = e.target.closest('.js-copy');
        if(!btn) return;
        const text = btn.dataset.copy || '';
        if(!text) return;
        navigator.clipboard.writeText(text).then(()=>{
            btn.classList.remove('btn-outline-secondary'); btn.classList.add('btn-success');
            setTimeout(()=>{ btn.classList.add('btn-outline-secondary'); btn.classList.remove('btn-success'); }, 900);
        }).catch(()=> alert('No se pudo copiar'));
    });

    // Inicial
    applyFilters();
})();
</script>
@endpush
