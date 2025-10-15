@extends('adminlte::page')

@section('title', __('groups.page.title'))

@section('content_header')
    <x-page-header :title="__('groups.page.title')" icon="fas fa-users">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('groups.create') }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-plus"></i>
                <span>{{ __('groups.index.create_button') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    @if (session('success'))
        <x-adminlte-alert theme="success" title="{{ __('groups.alerts.success_title') }}" dismissable class="mb-2">
            {{ session('success') }}
        </x-adminlte-alert>
    @endif

    @php
        $q = $q ?? request('q', '');
        $hasQuery = strlen(trim($q)) > 0;
    @endphp

    <x-adminlte-card title="{{ __('groups.index.card_title') }}" icon="fas fa-users text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3" body-class="p-0">

        {{-- Search form --}}
        <div class="p-3">
            <form method="GET" action="{{ route('groups.index') }}" class="w-100" role="search" aria-label="{{ __('groups.search.submit') }}">
                <div class="row align-items-end">
                    <div class="col-12 col-lg-8 mb-2">
                        <label for="groupsSearch" class="form-label mb-1 font-weight-semibold small">{{ __('groups.search.placeholder') }}</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-body-tertiary text-secondary">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                            <input type="text"
                                   name="q"
                                   value="{{ $q }}"
                                   id="groupsSearch"
                                   class="form-control form-control-sm"
                                   placeholder="{{ __('groups.search.placeholder') }}"
                                   aria-label="{{ __('groups.search.placeholder') }}">
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 mb-2">
                        <div class="d-flex flex-wrap justify-content-start justify-content-lg-end filter-actions">
                            <button type="submit" class="btn btn-primary filter-btn">
                                <i class="fas fa-search mr-1"></i>{{ __('groups.search.submit') }}
                            </button>
                            @if($hasQuery)
                                <a href="{{ route('groups.index') }}" class="btn btn-secondary filter-btn">
                                    {{ __('groups.search.clear') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Groups table --}}
        <div class="table-responsive shadow-sm rounded-4">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="w-50">{{ __('groups.table.name_description') }}</th>
                        <th class="text-center table-col-sm">{{ __('groups.table.members') }}</th>
                        <th class="text-center table-col-lg">{{ __('groups.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse(($groups ?? collect()) as $g)
                    <tr>
                        <td class="py-3">
                            <div class="font-weight-semibold text-body">{{ $g->name }}</div>
                            @if(!empty($g->description))
                                <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($g->description, 120) }}</small>
                            @else
                                <small class="text-muted d-block">-</small>
                            @endif
                        </td>
                        <td class="text-center py-3">
                            @php
                                $membersCount = isset($g->users_count) ? (int) $g->users_count : (int) $g->users()->count();
                            @endphp
                            <span class="badge badge-primary px-3 py-2">{{ $membersCount }}</span>
                        </td>
                        <td class="text-center py-3">
                            <div class="actions-group">
                                <a href="{{ route('groups.edit', $g) }}" class="btn btn-sm btn-info action-btn">
                                    <i class="fas fa-edit"></i> {{ __('groups.table.edit') }}
                                </a>
                                <form action="{{ route('groups.destroy', $g) }}" method="POST" class="m-0 js-confirm-delete-form"
                                      onsubmit="return confirm('{{ __('groups.table.delete_confirm', ['name' => $g->name]) }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger action-btn">
                                        <i class="fas fa-trash"></i> {{ __('groups.table.delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center p-4 text-muted">
                            <i class="fas fa-users-slash fa-2x mb-2"></i>
                            <p class="mb-0">{{ __('groups.empty.list') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(($groups ?? collect())->hasPages())
            <x-slot name="footerSlot">
                <div class="d-flex justify-content-center">
                    {{ ($groups ?? collect())->appends(['q' => $q])->links() }}
                </div>
            </x-slot>
        @endif
    </x-adminlte-card>
@stop

@push('css')
<style>
/* ===== Header buttons ===== */
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
.header-actions i {
    font-size: .9rem;
    margin-right: .4rem;
}

/* ===== Filters ===== */
.filter-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: .5rem;
}
.filter-actions .filter-btn {
    height: 38px;
    min-width: 120px;
    font-size: .875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.filter-actions .filter-btn i {
    font-size: .9rem;
    margin-right: .3rem;
}
@media (max-width: 992px) {
    .filter-actions {
        justify-content: center !important;
    }
}

/* ===== Tables ===== */
table th, table td {
    vertical-align: middle !important;
}
.actions-group {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    margin: -4px;
}
.actions-group .action-btn {
    margin: 4px;
    height: 34px;
    padding: .25rem .6rem;
    min-width: 120px;
    line-height: 1.2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    font-size: .8rem;
}
.actions-group .action-btn i {
    font-size: .9rem;
    margin-right: .35rem;
}
.badge {
    font-size: .8rem;
    font-weight: 600;
}
</style>
@endpush
