@extends('adminlte::page')

@section('title', __('groups.page.title'))

@section('content_header')
    <x-page-header :title="__('groups.page.title')" icon="fas fa-users">
        <a href="{{ route('groups.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            <span>{{ __('groups.index.create_button') }}</span>
        </a>
    </x-page-header>
@stop

@section('content')
    @if (session('success'))
        <x-adminlte-alert theme="success" title="{{ __('groups.alerts.success_title') }}" dismissable>
            {{ session('success') }}
        </x-adminlte-alert>
    @endif

    @php
        $q = $q ?? request('q', '');
        $hasQuery = strlen(trim($q)) > 0;
    @endphp

    <x-adminlte-card title="{{ __('groups.index.card_title') }}" icon="fas fa-users text-lightblue"
                     theme="primary" theme-mode="outline" class="mt-3" body-class="p-0">

        <div class="p-3">
            {{-- Buscador con input + botones alineados --}}
            <form method="GET" action="{{ route('groups.index') }}" class="w-100" role="search" aria-label="{{ __('groups.search.submit') }}">
                <div class="d-flex flex-column flex-lg-row align-items-stretch gap-3">
                    <div class="input-group input-group-lg flex-grow-1">
                        <span class="input-group-text bg-body-tertiary text-secondary">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text"
                               name="q"
                               value="{{ $q }}"
                               id="groupsSearch"
                               class="form-control"
                               placeholder="{{ __('groups.search.placeholder') }}"
                               aria-label="{{ __('groups.search.placeholder') }}">
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <button type="submit" class="btn btn-primary btn-lg px-4">
                            {{ __('groups.search.submit') }}
                        </button>
                        @if($hasQuery)
                            <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                                {{ __('groups.search.clear') }}
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive shadow-sm rounded-4">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="w-50">{{ __('groups.table.name_description') }}</th>
                        <th class="text-center table-col-sm">{{ __('groups.table.members') }}</th>
                        <th class="text-end table-col-lg">{{ __('groups.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse(($groups ?? collect()) as $g)
                    <tr>
                        <td>
                            <div class="fw-semibold text-body">{{ $g->name }}</div>
                            @if(!empty($g->description))
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($g->description, 120) }}</small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $membersCount = isset($g->users_count) ? (int) $g->users_count : (int) $g->users()->count();
                            @endphp
                            <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2">{{ $membersCount }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                <a href="{{ route('groups.edit', $g) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-2"></i> {{ __('groups.table.edit') }}
                                </a>
                                <form action="{{ route('groups.destroy', $g) }}" method="POST" class="m-0"
                                      onsubmit="return confirm('{{ __('groups.table.delete_confirm', ['name' => $g->name]) }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash me-2"></i> {{ __('groups.table.delete') }}
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

        @if(($groups ?? collect())->hasPages())
            <x-slot name="footerSlot">
                <div class="d-flex justify-content-center">
                    {{ ($groups ?? collect())->appends(['q' => $q])->links() }}
                </div>
            </x-slot>
        @endif
    </x-adminlte-card>
@stop

