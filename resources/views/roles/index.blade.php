@extends('adminlte::page')

@section('title', __('roles.page.title'))

@section('content_header')
    <x-page-header :title="__('roles.page.title')" icon="fas fa-user-shield">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-plus"></i>
                <span>{{ __('roles.index.header_button') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

@section('content')
    {{-- Flash alerts --}}
    @if (session('success'))
        <x-adminlte-alert theme="success" title="{{ __('roles.alerts.success') }}" dismissable class="mb-2">{{ session('success') }}</x-adminlte-alert>
    @endif
    @if (session('warning'))
        <x-adminlte-alert theme="warning" title="{{ __('roles.alerts.warning') }}" dismissable class="mb-2">{{ session('warning') }}</x-adminlte-alert>
    @endif
    @if (session('error'))
        <x-adminlte-alert theme="danger" title="{{ __('roles.alerts.error') }}" dismissable class="mb-2">{{ session('error') }}</x-adminlte-alert>
    @endif

    <x-adminlte-card title="{{ __('roles.index.list_title') }}" icon="fas fa-user-shield text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3" body-class="p-0">

        {{-- Search bar --}}
        <div class="p-3">
            <form method="GET" action="{{ route('roles.index') }}" class="w-100" role="search" aria-label="{{ __('roles.index.search') }}">
                <div class="d-flex flex-column flex-lg-row align-items-stretch gap-2" style="max-width:760px;">
                    <div class="input-group flex-grow-1">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light text-secondary">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <input type="text" name="q" value="{{ $q }}" class="form-control"
                               placeholder="{{ __('roles.index.search_placeholder') }}" autocomplete="off">
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2 pl-sm-1">
                        <button type="submit" class="btn btn-primary px-4 filter-btn">
                            <i class="fas fa-search mr-1"></i>{{ __('roles.index.search') }}
                        </button>
                        @if(!empty(trim($q)))
                            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary px-4 filter-btn">
                                {{ __('roles.index.clear') }}
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Roles Table --}}
        <div class="table-responsive shadow-sm rounded-4">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('roles.index.table.name') }}</th>
                        <th class="text-center">{{ __('roles.index.table.users') }}</th>
                        <th class="text-center">{{ __('roles.index.table.permissions') }}</th>
                        <th class="text-center">{{ __('roles.index.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td class="py-3 font-weight-semibold">{{ $role->name }}</td>
                        <td class="text-center py-3">{{ $userCounts[$role->id] ?? 0 }}</td>
                        <td class="text-center py-3">{{ $role->permissions->count() }}</td>
                        <td class="text-center py-3">
                            <div class="actions-group">
                                {{-- Edit --}}
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline-info btn-icon" title="{{ __('common.edit') }}">
                                    <i class="fas fa-pen"></i>
                                </a>
                                {{-- Delete / Locked --}}
                                @if (strtolower($role->name) !== 'admin')
                                    @php $count = (int)($userCounts[$role->id] ?? 0); @endphp
                                    @if ($count === 0)
                                        <form action="{{ route('roles.destroy', $role) }}" method="POST" class="m-0 js-confirm-delete-form"
                                              onsubmit="return confirm('{{ __('roles.index.delete_confirm', ['name' => $role->name]) }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-icon" title="{{ __('roles.form.buttons.delete') }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary btn-icon" disabled
                                                title="{{ __('roles.index.table.cant_delete', ['count' => $count]) }}">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center p-4 text-muted">
                            <i class="fas fa-user-slash fa-2x mb-2"></i>
                            <p class="mb-0">{{ __('roles.index.table.empty') }}</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($roles->hasPages())
            <x-slot name="footerSlot">
                <div class="d-flex justify-content-center align-items-center w-100 py-2">
                    {{ $roles->appends(['q' => $q])->links() }}
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

/* Compact icon buttons (same as groups/folders) */
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
