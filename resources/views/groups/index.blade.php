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

        {{-- Search bar --}}
        <div class="p-3">
            <form method="GET" action="{{ route('groups.index') }}" class="w-100" role="search" aria-label="{{ __('groups.search.submit') }}">
                <div class="d-flex flex-column flex-lg-row align-items-stretch gap-2" style="max-width:760px;">
                    <div class="input-group flex-grow-1">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light text-secondary">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <input type="text"
                               id="groupsSearch"
                               name="q"
                               value="{{ $q }}"
                               class="form-control"
                               placeholder="{{ __('groups.search.placeholder') }}"
                               autocomplete="off">
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2 pl-sm-1">
                        <button type="submit" class="btn btn-primary px-4 filter-btn">
                            <i class="fas fa-search mr-1"></i>{{ __('groups.search.submit') }}
                        </button>
                        @if($hasQuery)
                            <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary px-4 filter-btn">
                                {{ __('groups.search.clear') }}
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Table --}}
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
                                <a href="{{ route('groups.edit', $g) }}" class="btn btn-outline-info btn-icon" title="{{ __('groups.table.edit_title') }}">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <form action="{{ route('groups.destroy', $g) }}" method="POST" class="m-0 js-confirm-delete-form"
                                      onsubmit="return confirm('{{ __('groups.table.delete_confirm', ['name' => $g->name]) }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-icon" title="{{ __('groups.table.delete_title') }}">
                                        <i class="fas fa-trash"></i>
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
                <div class="d-flex justify-content-center align-items-center w-100 py-2">
                    {{ ($groups ?? collect())->appends(['q' => $q])->links() }}
                </div>
            </x-slot>
        @endif
    </x-adminlte-card>
@stop

@push('css')
<style>
/* Header buttons */
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

/* Filters */
.input-group .form-control {
    height: calc(1.9rem + 2px);
    font-size: .875rem;
    line-height: 1.2;
}
.input-group-text {
    height: calc(1.9rem + 2px);
    display: flex;
    align-items: center;
    justify-content: center;
}
.filter-btn {
    height: calc(1.9rem + 2px);
    font-size: .875rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.filter-btn i { font-size: .9rem; margin-right: .3rem; }

/* Tables and actions */
.actions-group {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: .35rem;
}

/* Compact icon buttons (same as folders) */
.table .btn-icon {
    min-width: auto !important;
    width: 28px !important;
    height: 28px !important;
    border-radius: 4px !important;
    padding: 0 !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    line-height: 1 !important;
    font-size: .8rem !important;
}
.table .btn-icon i {
    margin: 0 !important;
    font-size: .8rem !important;
}
.table .btn-icon:hover {
    transform: scale(1.1);
    transition: all .15s ease-in-out;
}
</style>
@endpush
