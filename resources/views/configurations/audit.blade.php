@extends('layouts.app')

@section('title', __('settings.audit.page.title'))

@section('content_header')
    <x-page-header :title="__('settings.audit.page.title')" :subtitle="__('settings.audit.page.subtitle')" icon="fas fa-history" />
@stop

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label mb-1">{{ __('settings.audit.form.search_label') }}</label>
                    <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="{{ __('settings.audit.form.search_placeholder') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">{{ __('settings.audit.form.user_label') }}</label>
                    <input type="number" name="user" value="{{ $user ?? '' }}" class="form-control" min="1">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary"><i class="fas fa-filter"></i> {{ __('settings.audit.form.submit') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>{{ __('settings.audit.table.date') }}</th>
                    <th>{{ __('settings.audit.table.user') }}</th>
                    <th>{{ __('settings.audit.table.ip') }}</th>
                    <th>{{ __('settings.audit.table.changes') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($audits as $a)
                    <tr>
                        <td class="text-nowrap text-monospace small">{{ optional($a->created_at)->format('d-m-Y H:i:s') }}</td>
                        <td>{{ $a->user_id ?? '-' }}</td>
                        <td class="text-monospace small">{{ $a->ip ?? '-' }}</td>
                        <td>
                            <details>
                                <summary class="small text-muted">{{ __('settings.audit.table.summary', ['count' => is_countable($a->changes ?? []) ? count($a->changes) : 0]) }}</summary>
                                <div class="mt-2">
                                    <table class="table table-sm">
                                        <thead><tr><th>{{ __('settings.audit.table.field') }}</th><th>{{ __('settings.audit.table.before') }}</th><th>{{ __('settings.audit.table.after') }}</th></tr></thead>
                                        <tbody>
                                        @foreach(($a->changes ?? []) as $ch)
                                            <tr>
                                                <td class="text-monospace small">{{ $ch['field'] ?? '' }}</td>
                                                <td class="small">{{ $ch['old'] ?? '' }}</td>
                                                <td class="small">{{ $ch['new'] ?? '' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('settings.audit.table.empty') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($audits,'links'))
            <div class="card-footer">{{ $audits->links() }}</div>
        @endif
    </div>
@stop
