@extends('layouts.app')

@section('title', __('applications.index.title'))

{{-- =========================
     HEADER
     ========================= --}}
@section('content_header')
    {{-- Header with title on the left and compact, evenly spaced actions on the right --}}
    <x-page-header :title="__('applications.index.title')" icon="fas fa-th-large">
        <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-lg-end header-actions">
            {{-- Create application --}}
            <a href="{{ route('applications.create') }}" class="btn btn-primary btn-sm header-btn">
                <i class="fas fa-plus"></i>
                <span>{{ __('applications.actions.create') }}</span>
            </a>
        </div>
    </x-page-header>
@stop

{{-- =========================
     CONTENT
     ========================= --}}
@section('content')
    {{-- Flash messages --}}
    @if (session('success'))
        <x-ui-alert theme="success" title="{{ __('applications.alerts.success') }}" dismissable class="mb-2">
            {{ session('success') }}
        </x-ui-alert>
    @endif

    <x-ui-card icon="fas fa-th-large text-lightblue"
                     theme="primary" theme-mode="outline"
                     class="mt-3" body-class="p-0">

        {{-- =========================
             APPLICATIONS TABLE
             ========================= --}}
        <div class="table-responsive shadow-sm rounded-4">
            <table class="table table-striped table-hover align-middle mb-0" id="applicationsTable">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('applications.fields.name') }}</th>
                        <th>{{ __('applications.fields.slug') }}</th>
                        <th class="text-center">{{ __('applications.fields.status') }}</th>
                        <th class="text-center">{{ __('applications.fields.sections') }}</th>
                        <th class="text-center">{{ __('applications.fields.reports') }}</th>
                        <th class="text-center">{{ __('applications.fields.users') }}</th>
                        <th class="text-center">{{ __('applications.fields.groups') }}</th>
                        <th class="text-center">{{ __('applications.fields.roles') }}</th>
                        <th class="text-right">{{ __('applications.fields.updated_at') }}</th>
                        <th class="text-center">{{ __('applications.fields.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $app)
                        <tr>
                            <td class="py-3">
                                <div class="font-weight-semibold">{{ $app->name }}</div>
                                <div class="text-muted small">#{{ $app->uuid }}</div>
                            </td>
                            <td class="py-3"><code>{{ $app->slug }}</code></td>
                            <td class="py-3 text-center">
                                @if ($app->is_active)
                                    <span class="badge u-badge-fixed badge-success">{{ __('applications.status.active') }}</span>
                                @else
                                    <span class="badge u-badge-fixed badge-secondary">{{ __('applications.status.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $app->sections->count() }}</td>
                            <td class="text-center">{{ $app->reports_count }}</td>
                            <td class="text-center">{{ $app->users_count }}</td>
                            <td class="text-center">{{ $app->groups_count }}</td>
                            <td class="text-center">{{ $app->roles_count }}</td>
                            <td class="text-right text-muted small">{{ optional($app->updated_at)->format('Y-m-d H:i') }}</td>
                            <td class="py-3 text-center">
                                <div class="actions-group">
                                    {{-- Edit --}}
                                    <a href="{{ route('applications.edit', $app) }}"
                                       class="btn btn-outline-info btn-icon"
                                       title="{{ __('applications.actions.edit') }}">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    {{-- Delete --}}
                                    <form action="{{ route('applications.destroy', $app) }}" method="POST"
                                          onsubmit="return confirm('{{ __('applications.confirm.delete') }}');"
                                          class="m-0 js-confirm-delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-icon"
                                                title="{{ __('applications.actions.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center p-4 text-muted">
                                <i class="far fa-folder-open mr-2"></i> {{ __('applications.index.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- =========================
             FOOTER (pagination)
             ========================= --}}
        <x-slot name="footerSlot">
            <div class="d-flex justify-content-between align-items-center w-100">
                <small class="text-muted mb-0">
                    @if($applications->total() > 0)
                        {{ __('applications.index.showing', ['from' => $applications->firstItem(), 'to' => $applications->lastItem(), 'total' => $applications->total()]) }}
                    @endif
                </small>
                <div class="mb-0">{{ $applications->appends(request()->except('page'))->links() }}</div>
            </div>
        </x-slot>
    </x-ui-card>
@stop

{{-- =========================
     VIEW-SPECIFIC CSS
     ========================= --}}
@push('css')
<style>
/* Header actions alignment */
.header-actions { display:flex; flex-wrap:wrap; align-items:center; justify-content:flex-end; gap:.5rem; }
.header-actions form { display:flex; align-items:center; margin:0; padding:0; }
.header-actions a.btn,
.header-actions button.btn,
.header-actions form button.btn {
    display:inline-flex; align-items:center; justify-content:center;
    height:38px; line-height:1; padding:0 .9rem; font-size:.875rem;
}
.header-actions i { font-size:.9rem; margin-right:.4rem; }
@media (max-width:992px){ .header-actions{ justify-content:center !important; } }

/* Table alignment + actions */
table th, table td { vertical-align:middle !important; }
.actions-group {
    display:flex; flex-wrap:wrap; justify-content:center; align-items:center; gap:.35rem;
}

/* Compact icon buttons (uniform size) */
.table .btn-icon {
    width:28px !important; height:28px !important; min-width:auto !important;
    border-radius:4px !important; padding:0 !important;
    display:inline-flex !important; align-items:center; justify-content:center;
    line-height:1 !important; font-size:.8rem !important;
}
.table .btn-icon i { margin:0 !important; font-size:.8rem !important; }
.table .btn-icon:hover { transform:scale(1.1); transition:all .15s ease-in-out; }

/* Badge style consistency */
.u-badge-fixed { font-size:.75rem; padding:.35rem .55rem; border-radius:8px; font-weight:600; }

.pagination { margin-bottom:0 !important; }
</style>
@endpush
