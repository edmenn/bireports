{{-- resources/views/folders/workspaces.blade.php --}}
@extends('adminlte::page')

@section('title', __('folders.workspaces.page.title'))

@section('content_header')
    {{-- Unified header with title, icon, and aligned actions (same style as capacities) --}}
    <x-page-header :title="__('folders.workspaces.page.title')" icon="fas fa-briefcase">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('powerbi.import.run') }}" class="btn btn-outline-primary btn-sm header-btn">
                <i class="fas fa-cloud-download-alt"></i>
                <span>{{ __('folders.workspaces.actions.import_now') }}</span>
            </a>
        </div>
        <x-slot name="subtitle">
            <small class="text-muted">{{ __('folders.workspaces.page.subtitle') }}</small>
        </x-slot>
    </x-page-header>
@stop

@section('content')
    @php
        // Métricas rápidas (seguras incluso si vienen nulls)
        $total   = $workspaces->count();
        $premium = $workspaces->where('is_on_premium_capacity', true)->count();
        $sinCap  = $workspaces->where('capacity_id', null)->count();
    @endphp

    @if($total === 0)
        {{-- Empty state (manteniendo acciones y texto original) --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-inbox fa-3x text-muted"></i>
                </div>
                <h5 class="mb-2">{{ __('folders.workspaces.empty.title') }}</h5>
                <p class="text-muted mb-4">{{ __('folders.workspaces.empty.hint') }}</p>
                <a href="{{ route('powerbi.import.run') }}" class="btn btn-primary rounded-pill px-3">
                    <i class="fas fa-sync"></i> {{ __('folders.workspaces.actions.import_now') }}
                </a>
            </div>
        </div>
    @else
        {{-- ===== KPIs modernas (mismo patrón que capacities) ===== --}}
        <div class="row kpi-row">
            <div class="col-md-4 mb-3">
                <div class="kpi-card kpi-primary">
                    <div>
                        <div class="kpi-label">{{ __('folders.workspaces.kpis.total') }}</div>
                        <div class="kpi-value">{{ $total }}</div>
                    </div>
                    <i class="fas fa-layer-group kpi-icon"></i>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="kpi-card kpi-success">
                    <div>
                        <div class="kpi-label">{{ __('folders.workspaces.kpis.premium') }}</div>
                        <div class="kpi-value">{{ $premium }}</div>
                    </div>
                    <i class="fas fa-bolt kpi-icon"></i>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="kpi-card kpi-warning">
                    <div>
                        <div class="kpi-label">{{ __('folders.workspaces.kpis.no_capacity') }}</div>
                        <div class="kpi-value">{{ $sinCap }}</div>
                    </div>
                    <i class="fas fa-exclamation-circle kpi-icon"></i>
                </div>
            </div>
        </div>

        {{-- ===== Filtros (alineados y alturas iguales como capacities) ===== --}}
        <div class="card mb-3 shadow-sm border-0">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label for="ws-search" class="form-label mb-1">{{ __('folders.workspaces.filters.search_label') }}</label>
                        <input id="ws-search" type="text" class="form-control form-control-lg"
                               placeholder="{{ __('folders.workspaces.filters.search_placeholder') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="ws-premium" class="form-label mb-1">{{ __('folders.workspaces.filters.premium_label') }}</label>
                        <select id="ws-premium" class="form-control form-control-lg">
                            <option value="all" selected>{{ __('folders.workspaces.filters.premium_all') }}</option>
                            <option value="yes">{{ __('folders.workspaces.filters.premium_yes') }}</option>
                            <option value="no">{{ __('folders.workspaces.filters.premium_no') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="ws-type" class="form-label mb-1">{{ __('folders.workspaces.filters.type_label') }}</label>
                        <select id="ws-type" class="form-control form-control-lg">
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

        {{-- ===== Tabla moderna (sticky thead + acciones) ===== --}}
        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table id="ws-table" class="table ws-table mb-0">
                    <thead class="sticky-thead">
                        <tr>
                            <th style="width: 44px;"></th>
                            <th class="sortable" data-sort="name">
                                {{ __('folders.workspaces.table.headers.name') }}
                                <i class="fas fa-sort text-muted ms-1"></i>
                            </th>
                            <th class="sortable" data-sort="type">
                                {{ __('folders.workspaces.table.headers.type') }}
                                <i class="fas fa-sort text-muted ms-1"></i>
                            </th>
                            <th class="sortable" data-sort="premium">
                                {{ __('folders.workspaces.table.headers.premium') }}
                                <i class="fas fa-sort text-muted ms-1"></i>
                            </th>
                            <th class="sortable" data-sort="capacity">
                                {{ __('folders.workspaces.table.headers.capacity') }}
                                <i class="fas fa-sort text-muted ms-1"></i>
                            </th>
                            <th class="text-end" style="width: 180px;">{{ __('folders.workspaces.table.headers.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workspaces as $w)
                            @php
                                $name = (string)($w->name ?? '');
                                $initials = collect(explode(' ', trim($name)))
                                    ->filter(fn($p) => $p !== '')
                                    ->take(2)
                                    ->map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)))
                                    ->implode('');
                                if ($initials === '') $initials = 'WS';
                                $typeVal  = $w->type ?? '';
                                $cap      = $w->capacity_id ?? '';
                                $isPremium = (bool)$w->is_on_premium_capacity;
                            @endphp
                            <tr
                                data-name="{{ Str::lower($name) }}"
                                data-type="{{ $typeVal !== '' ? $typeVal : '_vacio_' }}"
                                data-premium="{{ $isPremium ? 'yes' : 'no' }}"
                                data-capacity="{{ Str::lower($cap) }}"
                            >
                                <td class="align-middle">
                                    <div class="avatar-initial text-primary">
                                        {{ $initials }}
                                    </div>
                                </td>

                                <td class="align-middle">
                                    <div class="fw-bold text-dark h6 mb-0">{{ $name }}</div>
                                    @if(!empty($w->id))
                                        <small class="text-muted">{{ __('folders.workspaces.table.id_label') }} <code>{{ $w->id }}</code></small>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    @if($typeVal)
                                        <span class="badge bg-info">{{ $typeVal }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('folders.workspaces.badges.no_type') }}</span>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    @if($isPremium)
                                        <span class="badge bg-success">{{ __('folders.workspaces.badges.yes') }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ __('folders.workspaces.badges.no') }}</span>
                                    @endif
                                </td>

                                <td class="align-middle">
                                    @if($cap)
                                        <code class="me-2">{{ Str::limit($cap, 22) }}</code>
                                        <button type="button"
                                                class="btn btn-xs btn-outline-secondary rounded-pill js-copy"
                                                data-copy="{{ $cap }}"
                                                title="{{ __('folders.workspaces.actions.copy_capacity') }}">
                                            <i class="far fa-copy"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="align-middle text-end">
                                    <a href="{{ route('workspaces.show', $w->id) }}" class="btn btn-sm btn-primary rounded-pill px-3">
                                        <i class="fas fa-chart-bar me-1"></i> {{ __('folders.workspaces.actions.view_reports') }}
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
                    <div class="d-flex justify-content-center align-items-center w-100 py-2">
                        {{ $workspaces->links() }}
                    </div>
                </div>
            @endif
        </div>
    @endif
@stop

@push('css')
<style>
/* ===== Header buttons (same sizing as capacities) ===== */
.header-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: .5rem;
}
.header-actions a.btn,
.header-actions button.btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 38px !important;
    line-height: 1 !important;
    padding: 0 .9rem !important;
    font-size: .875rem !important;
}
.header-actions i { font-size: .9rem; margin-right: .4rem; }

/* ===== KPIs modernas (same tokens as capacities) ===== */
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

/* ===== Tabla moderna (match capacities) ===== */
.ws-table{ font-size:1rem; }
.ws-table thead th{
    position: sticky; top: 0; z-index: 2;
    background: #f8fafc; border-bottom: 1px solid rgba(0,0,0,.06);
    font-weight:700; color:#334155; padding: .85rem .75rem;
}
.ws-table tbody td, .ws-table tbody th{ padding: .9rem .75rem; vertical-align: middle; }
.ws-table tbody tr{ transition: background-color .12s ease, transform .06s ease; }
.ws-table tbody tr:hover{ background: #fbfdff; }
.ws-table code{ font-size:.9rem; }

/* Avatar (iniciales), mismo patrón global del proyecto */
.avatar-initial {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background-color: rgba(148,163,184,.18);
    color: #334155; font-weight: 700; font-size: .9rem;
}

/* Compact copy button */
.btn.btn-xs{ padding: .15rem .45rem; border-radius: 999px; }

/* Sort affordance */
.sortable{ cursor:pointer; user-select:none; }
.sortable .fa-sort{ opacity:.6; }

/* Responsive tweaks */
@media (max-width: 768px){
    .ws-table{ font-size:.95rem; }
    .kpi-value{ font-size:1.6rem; }
}
</style>
@endpush

@push('js')
<script>
(function(){
    const table   = document.getElementById('ws-table');
    if(!table) return;

    const rows    = Array.from(table.querySelectorAll('tbody tr'));
    const search  = document.getElementById('ws-search');
    const premium = document.getElementById('ws-premium');
    const type    = document.getElementById('ws-type');
    const copyErrorMessage = @json(__('folders.workspaces.js.copy_failed'));

    let sortKey = null;
    let sortDir = 'asc';

    function normalize(s){ return (s||'').toString().trim().toLowerCase(); }

    function applyFilters(){
        const q = normalize(search?.value || '');
        const p = premium?.value || 'all';  // all|yes|no
        const t = type?.value || 'all';     // all|_vacio_|<tipo>

        rows.forEach(tr=>{
            const name = tr.dataset.name || '';
            const cap  = tr.dataset.capacity || '';
            const typ  = tr.dataset.type || '';
            const pre  = tr.dataset.premium || 'no';

            const matchText    = (name.includes(q) || cap.includes(q));
            const matchPremium = (p === 'all') || (p === pre);
            const matchType    = (t === 'all') || (t === typ);

            tr.style.display = (matchText && matchPremium && matchType) ? '' : 'none';
        });
    }

    function getCellValue(tr, key){
        switch(key){
            case 'name':     return tr.dataset.name || '';
            case 'type':     return tr.dataset.type || '';
            case 'premium':  return tr.dataset.premium || 'no';
            case 'capacity': return tr.dataset.capacity || '';
            default:         return '';
        }
    }

    function applySort(){
        if(!sortKey) return;
        const tbody   = table.querySelector('tbody');
        const visible = rows.filter(tr => tr.style.display !== 'none');

        visible.sort((a,b)=>{
            const va = getCellValue(a, sortKey);
            const vb = getCellValue(b, sortKey);
            if(va < vb) return sortDir === 'asc' ? -1 : 1;
            if(va > vb) return sortDir === 'asc' ? 1 : -1;
            return 0;
        });

        visible.forEach(tr => tbody.appendChild(tr));
    }

    // Filter & sort events
    [search, premium, type].forEach(el=>{
        if(!el) return;
        const evt = el.tagName === 'INPUT' ? 'input' : 'change';
        el.addEventListener(evt, ()=>{ applyFilters(); applySort(); });
    });

    // Click-to-sort
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

    // Copy capacity id
    table.addEventListener('click', (e)=>{
        const btn = e.target.closest('.js-copy');
        if(!btn) return;
        const text = btn.dataset.copy || '';
        if(!text) return;
        navigator.clipboard.writeText(text).then(()=>{
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            setTimeout(()=>{ btn.classList.add('btn-outline-secondary'); btn.classList.remove('btn-success'); }, 900);
        }).catch(()=> alert(copyErrorMessage));
    });

    // Initial
    applyFilters();
})();
</script>
@endpush
