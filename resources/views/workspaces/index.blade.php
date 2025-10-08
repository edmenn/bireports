@extends('adminlte::page')

@section('title', __('folders.workspaces.page.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="mb-0">{{ __('folders.workspaces.page.title') }}</h1>
            <small class="text-muted">{{ __('folders.workspaces.page.subtitle') }}</small>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('powerbi.import.run') }}" class="btn btn-outline-secondary">
                <i class="fas fa-cloud-download-alt"></i> {{ __('folders.workspaces.actions.import_now') }}
            </a>
        </div>
    </div>
@stop

@section('content')
    @php
        // Métricas rápidas (seguras incluso si vienen nulls)
        $total = $workspaces->count();
        $premium = $workspaces->where('is_on_premium_capacity', true)->count();
        $sinCap = $workspaces->where('capacity_id', null)->count();
    @endphp

    @if($total === 0)
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-inbox fa-3x text-muted"></i>
                </div>
                <h5 class="mb-2">{{ __('folders.workspaces.empty.title') }}
                <p class="text-muted mb-4">{{ __('folders.workspaces.empty.hint') }}
                <a href="{{ route('powerbi.import.run') }}" class="btn btn-primary">
                    <i class="fas fa-sync"></i> {{ __('folders.workspaces.actions.import_now') }}
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
                        <p class="mb-0">{{ __('folders.workspaces.kpis.total') }}</p>
                    </div>
                    <div class="icon"><i class="fas fa-layer-group"></i></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 class="mb-0">{{ $premium }}</h3>
                        <p class="mb-0">{{ __('folders.workspaces.kpis.premium') }}</p>
                    </div>
                    <div class="icon"><i class="fas fa-bolt"></i></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3 class="mb-0">{{ $sinCap }}</h3>
                        <p class="mb-0">{{ __('folders.workspaces.kpis.no_capacity') }}</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
                </div>
            </div>
        </div>

        {{-- Filtros y búsqueda --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label for="ws-search" class="form-label mb-1">{{ __('folders.workspaces.filters.search_label') }}</label>
                        <input id="ws-search" type="text" class="form-control" placeholder="{{ __('folders.workspaces.filters.search_placeholder') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="ws-premium" class="form-label mb-1">{{ __('folders.workspaces.filters.premium_label') }}</label>
                        <select id="ws-premium" class="form-control">
                            <option value="all" selected>{{ __('folders.workspaces.filters.premium_all') }}</option>
                            <option value="yes">{{ __('folders.workspaces.filters.premium_yes') }}</option>
                            <option value="no">{{ __('folders.workspaces.filters.premium_no') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="ws-type" class="form-label mb-1">{{ __('folders.workspaces.filters.type_label') }}</label>
                        <select id="ws-type" class="form-control">
                            <option value="all" selected>{{ __('folders.workspaces.filters.type_all') }}</option>
                            @foreach($workspaces->pluck('type')->filter()->unique()->values() as $tipo)
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endforeach
                            <option value="_vacio_">{{ __('folders.workspaces.filters.type_empty') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla principal --}}
        <div class="card">
            <div class="table-responsive">
                <table id="ws-table" class="table table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 38px;"></th>
                            <th class="sortable" data-sort="name">{{ __('folders.workspaces.table.headers.name') }} <i class="fas fa-sort ms-1 text-muted"></i></th>
                            <th class="sortable" data-sort="type">{{ __('folders.workspaces.table.headers.type') }} <i class="fas fa-sort ms-1 text-muted"></i></th>
                            <th class="sortable" data-sort="premium">{{ __('folders.workspaces.table.headers.premium') }} <i class="fas fa-sort ms-1 text-muted"></i></th>
                            <th class="sortable" data-sort="capacity">{{ __('folders.workspaces.table.headers.capacity') }} <i class="fas fa-sort ms-1 text-muted"></i></th>
                            <th class="text-right" style="width:160px;">{{ __('folders.workspaces.table.headers.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workspaces as $w)
                            @php
                                $name = (string)($w->name ?? '');
                                $initials = collect(explode(' ', trim($name)))
                                    ->filter(fn($p) => $p !== '')
                                    ->take(2)->map(fn($p) => mb_strtoupper(mb_substr($p,0,1)))->implode('');
                                if ($initials === '') $initials = 'WS';
                                $typeVal = $w->type ?? '';
                                $cap = $w->capacity_id ?? '';
                                $isPremium = (bool)$w->is_on_premium_capacity;
                            @endphp
                            <tr
                                data-name="{{ Str::lower($name) }}"
                                data-type="{{ $typeVal !== '' ? $typeVal : '_vacio_' }}"
                                data-premium="{{ $isPremium ? 'yes' : 'no' }}"
                                data-capacity="{{ Str::lower($cap) }}"
                            >
                                <td>
                                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center"
                                         style="width:32px;height:32px;font-weight:600;">
                                        {{ $initials }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-600">{{ $name }}</div>
                                    @if(!empty($w->id))
                                        <small class="text-muted">{{ __('folders.workspaces.table.id_label') }} <code>{{ $w->id }}</code></small>
                                    @endif
                                </td>
                                <td>
                                    @if($typeVal)
                                        <span class="badge bg-info">{{ $typeVal }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('folders.workspaces.badges.no_type') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isPremium)
                                        <span class="badge bg-success">{{ __('folders.workspaces.badges.yes') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('folders.workspaces.badges.no') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cap)
                                        <code class="me-2">{{ Str::limit($cap, 22) }}</code>
                                        <button type="button" class="btn btn-xs btn-outline-secondary js-copy" data-copy="{{ $cap }}" title="{{ __('folders.workspaces.actions.copy_capacity') }}">
                                            <i class="far fa-copy"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('workspaces.show', $w->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-chart-bar"></i> {{ __('folders.workspaces.actions.view_reports') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación si el objeto es paginador --}}
            @if(method_exists($workspaces, 'links'))
                <div class="card-footer">
                    {{ $workspaces->links() }}
                </div>
            @endif
        </div>
    @endif
@stop

@push('css')
<style>
    .fw-600{font-weight:600;}
    .gap-2{gap:.5rem;}
    .small-box .inner h3{font-weight:700;}
    .table thead th.sortable{cursor:pointer;}
    .table thead th.sortable .fa-sort{opacity:.6;}
    .table tbody tr{transition:background-color .12s ease;}
    .table tbody tr:hover{background-color:#fafafa;}
</style>
@endpush

@push('js')
<script>
(function(){
    const table = document.getElementById('ws-table');
    if(!table) return;

    const rows = Array.from(table.querySelectorAll('tbody tr'));
    const search = document.getElementById('ws-search');
    const premium = document.getElementById('ws-premium');
    const type = document.getElementById('ws-type');
    const copyErrorMessage = @json(__('folders.workspaces.js.copy_failed'));

    let sortKey = null;
    let sortDir = 'asc';

    function normalize(s){ return (s||'').toString().trim().toLowerCase(); }

    function applyFilters(){
        const q = normalize(search.value);
        const p = premium.value; // all|yes|no
        const t = type.value;    // all|_vacio_|<tipo>

        rows.forEach(tr=>{
            const name = tr.dataset.name || '';
            const cap  = tr.dataset.capacity || '';
            const typ  = tr.dataset.type || '';
            const pre  = tr.dataset.premium || 'no';

            const matchText = (name.includes(q) || cap.includes(q));
            const matchPremium = (p === 'all') || (p === pre);
            const matchType = (t === 'all') || (t === typ);

            tr.style.display = (matchText && matchPremium && matchType) ? '' : 'none';
        });
    }

    function getCellValue(tr, key){
        switch(key){
            case 'name':    return tr.dataset.name || '';
            case 'type':    return tr.dataset.type || '';
            case 'premium': return tr.dataset.premium || 'no';
            case 'capacity':return tr.dataset.capacity || '';
            default:        return '';
        }
    }

    function applySort(){
        if(!sortKey) return;
        const tbody = table.querySelector('tbody');
        const visible = rows.filter(tr => tr.style.display !== 'none');

        visible.sort((a,b)=>{
            const va = getCellValue(a, sortKey);
            const vb = getCellValue(b, sortKey);
            if(va < vb) return sortDir === 'asc' ? -1 : 1;
            if(va > vb) return sortDir === 'asc' ? 1 : -1;
            return 0;
        });

        // Reinsertar en orden
        visible.forEach(tr => tbody.appendChild(tr));
    }

    // Eventos filtros
    [search, premium, type].forEach(el=>{
        el && el.addEventListener('input', ()=>{ applyFilters(); applySort(); });
        el && el.addEventListener('change', ()=>{ applyFilters(); applySort(); });
    });

    // Orden por columnas
    table.querySelectorAll('thead th.sortable').forEach(th=>{
        th.addEventListener('click', ()=>{
            const key = th.dataset.sort;
            if(sortKey === key){
                sortDir = (sortDir === 'asc' ? 'desc' : 'asc');
            }else{
                sortKey = key;
                sortDir = 'asc';
            }
            // Actualizar iconos
            table.querySelectorAll('thead th.sortable i').forEach(i=> i.className = 'fas fa-sort ms-1 text-muted');
            th.querySelector('i').className = (sortDir === 'asc' ? 'fas fa-sort-up ms-1' : 'fas fa-sort-down ms-1');

            applySort();
        });
    });

    // Copiar al portapapeles
    table.addEventListener('click', (e)=>{
        const btn = e.target.closest('.js-copy');
        if(!btn) return;
        const text = btn.dataset.copy || '';
        if(!text) return;
        navigator.clipboard.writeText(text).then(()=>{
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-secondary');
            setTimeout(()=>{
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 900);
        }).catch(()=>{ alert(copyErrorMessage); });
    });

    // Inicial
    applyFilters();
})();
</script>
@endpush

