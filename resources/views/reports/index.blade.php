@extends('adminlte::page')

@include('portal.partials.report-card-styles')

@section('title', __('reports.index.title'))

@section('content_header')
    {{-- Unified header with title, icon, and aligned actions --}}
    <x-page-header :title="__('reports.index.title')" icon="fas fa-chart-bar">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('powerbi.import.run') }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-cloud-download-alt"></i>
                <span>{{ __('reports.index.import_now') }}</span>
            </a>
            @can('report_subscriptions.manage')
                <a href="{{ route('subscriptions.index') }}" class="btn btn-primary btn-sm header-btn ml-2">
                    <i class="fas fa-list"></i>
                    <span>{{ __('menu.nav.reports_subscriptions') }}</span>
                </a>
            @endcan
        </div>
        <x-slot name="subtitle">
            <small class="text-muted">{{ __('reports.index.subtitle') }}</small>
        </x-slot>
    </x-page-header>
@stop

@section('content')
    @php
        $reports = $reports ?? collect();

        $total = $reports->count();
        $conCover = $reports->filter(function($r){
            $c = trim((string)($r->effective_cover_url ?? ''));
            return $c !== '';
        })->count();
        $sinCover = $total - $conCover;

        $wsSet = $reports->map(function($r){
            $name = $r->workspace?->name ?? null;
            $wid  = $r->workspace_id ?? null;
            return ['key' => $name ? $name : (string)$wid, 'text' => $name ? $name : (string)$wid];
        })->filter(fn($x)=>!empty($x['key']))->unique('key')->sortBy('text')->values();
    @endphp

    @if($total === 0)
        {{-- Empty state --}}
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-inbox fa-3x text-muted" aria-hidden="true"></i>
                </div>
                <h5 class="font-weight-bold mb-2">{{ __('reports.index.empty.title') }}</h5>
                <p class="text-muted mb-4">{{ __('reports.index.empty.hint') }}</p>
                <a href="{{ route('powerbi.import.run') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-sync"></i> {{ __('reports.index.empty.action') }}
                </a>
            </div>
        </div>
    @else

        {{-- Summary metrics --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="info-box bg-primary text-white shadow-sm">
                    <span class="info-box-icon"><i class="fas fa-chart-bar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('reports.index.metrics.total') }}</span>
                        <span class="info-box-number h3 mb-0">{{ $total }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="info-box bg-success text-white shadow-sm">
                    <span class="info-box-icon"><i class="fas fa-image"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('reports.index.metrics.with_cover') }}</span>
                        <span class="info-box-number h3 mb-0">{{ $conCover }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="info-box bg-secondary text-white shadow-sm">
                    <span class="info-box-icon"><i class="fas fa-image-slash"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('reports.index.metrics.without_cover') }}</span>
                        <span class="info-box-number h3 mb-0">{{ $sinCover }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-6 mb-3">
                        <label for="rpt-search" class="font-weight-bold">{{ __('reports.index.filters.search_label') }}</label>
                        <input id="rpt-search" type="text" class="form-control"
                               placeholder="{{ __('reports.index.filters.search_placeholder') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="rpt-workspace" class="font-weight-bold">{{ __('reports.index.filters.workspace_label') }}</label>
                        <select id="rpt-workspace" class="custom-select">
                            <option value="all" selected>{{ __('reports.index.filters.workspace_all') }}</option>
                            @foreach($wsSet as $ws)
                                <option value="{{ $ws['key'] }}">{{ $ws['text'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="rpt-cover" class="font-weight-bold">{{ __('reports.index.filters.cover_label') }}</label>
                        <select id="rpt-cover" class="custom-select">
                            <option value="all" selected>{{ __('reports.index.filters.cover_all') }}</option>
                            <option value="yes">{{ __('reports.index.filters.cover_yes') }}</option>
                            <option value="no">{{ __('reports.index.filters.cover_no') }}</option>
                        </select>
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label for="rpt-order" class="font-weight-bold">{{ __('reports.index.filters.order_label') }}</label>
                        <select id="rpt-order" class="custom-select">
                            <option value="name_asc" selected>{{ __('reports.index.filters.order_options.name_asc') }}</option>
                            <option value="name_desc">{{ __('reports.index.filters.order_options.name_desc') }}</option>
                            <option value="ws_asc">{{ __('reports.index.filters.order_options.workspace_asc') }}</option>
                            <option value="ws_desc">{{ __('reports.index.filters.order_options.workspace_desc') }}</option>
                        </select>
                    </div>
                    <div class="col-md-9 small text-muted">
                        <i class="far fa-lightbulb mr-1"></i>
                        {{ __('reports.index.filters.hint') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Reports grid --}}
        <div class="report-grid" id="rpt-grid">
            @foreach($reports as $r)
                @php
                    $title = (string)($r->name ?? '');
                    $cover = trim((string)($r->effective_cover_url ?? ''));
                    $scaleX = max(0.5, min((float)($r->cover_scale_x ?? $r->cover_scale ?? 1.0), 1.5));
                    $scaleY = max(0.5, min((float)($r->cover_scale_y ?? $r->cover_scale ?? 1.0), 1.5));
                    $wsName = $r->workspace->name ?? '';
                    $wsKey  = $wsName !== '' ? $wsName : (string)($r->workspace_id ?? '');
                    $datasetId = $r->dataset_id ?? '';
                    $rid = (string)($r->id ?? '');
                    $hasCover = $cover !== '' ? 'yes' : 'no';
                    $searchBlob = \Illuminate\Support\Str::lower($title.' '.$rid.' '.$wsKey.' '.$datasetId);
                @endphp

                <div class="report-card"
                     data-title="{{ \Illuminate\Support\Str::lower($title) }}"
                     data-ws="{{ \Illuminate\Support\Str::lower($wsKey) }}"
                     data-cover="{{ $hasCover }}"
                     data-search="{{ $searchBlob }}">

                    {{-- Cover --}}
                    <div class="report-cover">
                        <a href="{{ route('reports.show', [$r->workspace_id, $r->id]) }}"
                           class="cover-link"
                           title="{{ __('reports.index.cards.open_title') }}">
                            @if($cover !== '')
                                @php
                                    $isDefault = \Illuminate\Support\Str::contains($cover, 'report-covers/default');
                                    $scaleXFormatted = number_format($scaleX, 3, '.', '');
                                    $scaleYFormatted = number_format($scaleY, 3, '.', '');
                                @endphp
                                <img src="{{ $cover }}" alt="{{ __('reports.index.cards.cover_alt', ['name' => $r->name]) }}"
                                     class="cover-scale-target {{ $isDefault ? 'cover-default' : '' }}"
                                     style="transform: scale({{ $scaleXFormatted }}, {{ $scaleYFormatted }});">
                            @else
                                <i class="fas fa-chart-bar fa-3x text-warning opacity-50"></i>
                            @endif
                        </a>
                    </div>

                    {{-- Title --}}
                    <div class="px-3 pt-3 pb-2 text-center">
                        <h3 class="h6 font-weight-bold text-dark mb-2 text-truncate" title="{{ $r->name }}">
                            <a href="{{ route('reports.show', [$r->workspace_id, $r->id]) }}"
                               class="text-dark text-decoration-none report-title-link">
                                {{ $r->name }}
                            </a>
                        </h3>

                        {{-- Centered buttons --}}
                        <div class="d-flex justify-content-center flex-wrap">
                            <a href="{{ route('reports.show', [$r->workspace_id, $r->id]) }}"
                               class="btn btn-sm btn-primary mx-1"
                               title="{{ __('reports.index.cards.open_title') }}">
                                <i class="fas fa-chart-line mr-1"></i> {{ __('reports.index.actions.open') }}
                            </a>
                            @role('admin')
                                <a href="{{ route('reports.edit', ['report' => $r->id]) }}"
                                   class="btn btn-sm btn-outline-warning mx-1"
                                   title="{{ __('reports.index.cards.edit_title') }}">
                                    <i class="fas fa-edit mr-1"></i> {{ __('reports.index.actions.edit') }}
                                </a>
                            @endrole
                        </div>
                    </div>

                    {{-- Description --}}
                    @if(!empty($r->description))
                        <div class="px-3 pb-2 small text-muted description-clamp text-center">
                            {{ $r->description }}
                        </div>
                    @endif

                    {{-- Metadata --}}
                    <div class="px-3 pb-3 text-center">
                        <div class="d-flex flex-wrap justify-content-center align-items-center">
                            <span class="badge badge-light border mr-2 mb-1 text-truncate" style="max-width:220px;"
                                  title="{{ $wsName !== '' ? $wsName : $r->workspace_id }}">
                                <i class="fas fa-layer-group mr-1"></i>
                                {{ $wsName !== '' ? $wsName : __('reports.index.badges.workspace') }}
                            </span>

                            @if(!empty($r->dataset_id))
                                <button type="button" class="badge badge-secondary border-0 copy-pill mr-2 mb-1"
                                        data-copy="{{ $r->dataset_id }}" title="{{ __('reports.index.copy.dataset_title') }}">
                                    {{ __('reports.index.copy.dataset_label') }}
                                </button>
                            @endif

                            <button type="button" class="badge badge-primary border-0 copy-pill mb-1"
                                    data-copy="{{ $r->id }}" title="{{ __('reports.index.copy.report_title') }}">
                                {{ __('reports.index.copy.report_label') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(method_exists($reports,'links'))
            <div class="mt-4">
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
        --card-hover: rgba(0,123,255,.4);
        --cover-height: 190px;
        --cover-bg: #f8f9fa;
    }
    .report-grid{
        display:grid;
        grid-template-columns: repeat(1, minmax(0,1fr));
        gap:1.25rem;
    }
    @media (min-width:576px){ .report-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); } }
    @media (min-width:992px){ .report-grid{ grid-template-columns: repeat(3, minmax(0,1fr)); } }
    @media (min-width:1400px){ .report-grid{ grid-template-columns: repeat(4, minmax(0,1fr)); } }
    .report-title-link:hover{ text-decoration:underline; }
    .description-clamp{ display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:2.4em; }
    .copy-pill{ cursor:pointer; user-select:none; transition:all .15s ease; }
    .copy-pill:hover{ filter:brightness(1.08); }
    .copy-pill.copied{ outline:2px solid rgba(40,167,69,.6); outline-offset:2px; }
    .info-box{ border-radius: var(--card-radius); padding:.75rem 1rem; display:flex; align-items:center; min-height:78px; }
    .info-box-icon{ font-size:1.75rem; margin-right:.75rem; opacity:.95; }
</style>
@endpush

@push('js')
<script>
(function(){
    const grid  = document.getElementById('rpt-grid');
    if(!grid) return;

    const cards = Array.from(grid.querySelectorAll('.report-card'));
    const search = document.getElementById('rpt-search');
    const wsSel  = document.getElementById('rpt-workspace');
    const cover  = document.getElementById('rpt-cover');
    const order  = document.getElementById('rpt-order');
    const copySuccess = @json(__('reports.index.copy.success'));
    const copyError = @json(__('reports.index.copy.error'));

    const norm = (s)=> (s||'').toString().trim().toLowerCase();

    function applyFilters(){
        const q  = norm(search?.value || '');
        const ws = wsSel?.value || 'all';
        const cv = cover?.value || 'all';

        cards.forEach(card=>{
            const sb = card.dataset.search || '';
            const w  = card.dataset.ws || '';
            const c  = card.dataset.cover || 'no';

            const okText  = (q === '' || sb.includes(q));
            const okWs    = (ws === 'all') || (w === norm(ws));
            const okCover = (cv === 'all') || (cv === c);

            card.style.display = (okText && okWs && okCover) ? '' : 'none';
        });
    }

    function applyOrder(){
        if(!order) return;
        const val = order.value;
        const [key, dir] = val.split('_');
        const visible = cards.filter(c => c.style.display !== 'none');
        const parent  = grid;

        visible.sort((a,b)=>{
            const av = key === 'name' ? (a.dataset.title||'') : (a.dataset.ws||'');
            const bv = key === 'name' ? (b.dataset.title||'') : (b.dataset.ws||'');
            if(av < bv) return dir === 'asc' ? -1 : 1;
            if(av > bv) return dir === 'asc' ? 1 : -1;
            return 0;
        });

        visible.forEach(el => parent.appendChild(el));
    }

    let t=null;
    function debounceFilters(){
        if(t) clearTimeout(t);
        t = setTimeout(()=>{ applyFilters(); applyOrder(); }, 120);
    }

    search && search.addEventListener('input', debounceFilters);
    wsSel  && wsSel.addEventListener('change', ()=>{ applyFilters(); applyOrder(); });
    cover  && cover.addEventListener('change', ()=>{ applyFilters(); applyOrder(); });
    order  && order.addEventListener('change', applyOrder);

    document.addEventListener('click', function (e) {
        const pill = e.target.closest('.copy-pill');
        if (!pill) return;
        const value = pill.getAttribute('data-copy') || '';
        if (!value) return;

        navigator.clipboard.writeText(value).then(() => {
            pill.classList.add('copied');
            const original = pill.textContent;
            pill.textContent = copySuccess;
            setTimeout(() => {
                pill.classList.remove('copied');
                pill.textContent = original;
            }, 900);
        }).catch(() => alert(copyError));
    }, {passive:true});

    applyFilters(); applyOrder();
})();

</script>
@endpush
