{{-- resources/views/folders/workspaces/show.blade.php --}}
@extends('layouts.app')

@section('title', __('folders.workspaces.show.title', ['name' => $workspace->name]))

@section('content_header')
    {{-- Unified header --}}
    <x-page-header :title="__('folders.workspaces.show.title', ['name' => $workspace->name])" icon="fas fa-chart-bar">
        <x-slot name="subtitle">
            <small class="text-muted">
                {{ __('folders.workspaces.show.subtitle', [
                    'id' => $workspace->id,
                    'capacity' => $workspace->capacity_id ?? __('folders.workspaces.show.no_capacity')
                ]) }}
            </small>
        </x-slot>
    </x-page-header>
@stop

@section('content')
    @if($reports->isEmpty())
        {{-- Empty State --}}
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="mb-2">{{ __('folders.workspaces.show.empty') }}</h5>
                <p class="text-muted">{{ __('folders.workspaces.show.empty_hint') }}</p>
            </div>
        </div>
    @else
        {{-- Reports list --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-bottom">
                <h6 class="mb-0 fw-semibold text-secondary">
                    <i class="fas fa-table me-1 text-muted"></i> {{ __('folders.workspaces.show.list_title') }}
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table report-table mb-0">
                    <thead class="sticky-thead">
                        <tr>
                            <th class="sortable" data-sort="title">
                                {{ __('folders.workspaces.show.table.title') }}
                                <i class="fas fa-sort text-muted ms-1"></i>
                            </th>
                            <th>
                                {{ __('folders.workspaces.show.table.dataset') }}
                            </th>
                            <th class="text-end" style="width:160px;">
                                {{ __('folders.workspaces.show.table.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $r)
                            <tr data-title="{{ Str::lower($r->name ?? '') }}">
                                <td class="align-middle fw-semibold">
                                    <i class="fas fa-file-alt text-primary me-2"></i> {{ $r->name }}
                                </td>
                                <td class="align-middle">
                                    <code>{{ $r->dataset_id }}</code>
                                </td>
                                <td class="align-middle text-end">
                                    <a class="btn btn-sm btn-primary rounded-pill px-3"
                                       href="{{ route('reports.show', [$workspace->id, $r->id]) }}">
                                        <i class="fas fa-eye me-1"></i> {{ __('folders.workspaces.show.table.open') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@stop

@push('css')
<style>
/* Header actions (same as capacities) */
.header-actions {
    display: flex; flex-wrap: wrap;
    align-items: center; justify-content: flex-end;
    gap: .5rem;
}
.header-actions .btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 38px !important;
    font-size: .875rem !important;
    padding: 0 .9rem !important;
}
.header-actions i { font-size: .9rem; margin-right: .4rem; }

/* Table styling */
.report-table { font-size: 1rem; }
.report-table thead th {
    position: sticky; top: 0; z-index: 2;
    background: #f8fafc; border-bottom: 1px solid rgba(0,0,0,.06);
    font-weight: 700; color: #334155;
    padding: .85rem .75rem;
}
.report-table tbody td { padding: .85rem .75rem; vertical-align: middle; }
.report-table tbody tr:hover { background: #f9fafb; transition: background-color .12s ease; }
.report-table code { font-size: .9rem; color: #2563eb; }

/* Sorting icon style */
.sortable { cursor: pointer; user-select: none; }
.sortable .fa-sort { opacity: .6; }

/* Buttons */
.btn { border-radius: .75rem; }

/* Responsive */
@media (max-width: 768px) {
    .report-table { font-size: .95rem; }
}
</style>
@endpush

@push('js')
<script>
(function(){
    const table = document.querySelector('.report-table');
    if(!table) return;
    const rows = Array.from(table.querySelectorAll('tbody tr'));
    let sortKey = null;
    let sortDir = 'asc';

    function applySort(){
        if(!sortKey) return;
        const tbody = table.querySelector('tbody');
        const visible = rows.filter(tr => tr.style.display !== 'none');

        visible.sort((a,b)=>{
            const va = a.dataset[sortKey] || '';
            const vb = b.dataset[sortKey] || '';
            if(va < vb) return sortDir === 'asc' ? -1 : 1;
            if(va > vb) return sortDir === 'asc' ? 1 : -1;
            return 0;
        });

        visible.forEach(tr => tbody.appendChild(tr));
    }

    table.querySelectorAll('thead th.sortable').forEach(th=>{
        th.addEventListener('click', ()=>{
            const key = th.dataset.sort;
            if(sortKey === key){ sortDir = (sortDir === 'asc' ? 'desc' : 'asc'); }
            else { sortKey = key; sortDir = 'asc'; }

            table.querySelectorAll('thead th.sortable i')
                .forEach(i=> i.className = 'fas fa-sort text-muted ms-1');
            th.querySelector('i').className = (sortDir === 'asc'
                ? 'fas fa-sort-up ms-1' : 'fas fa-sort-down ms-1');

            applySort();
        });
    });
})();
</script>
@endpush
