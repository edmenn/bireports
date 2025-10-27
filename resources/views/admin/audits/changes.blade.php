@extends('adminlte::page')

@section('title', __('changes.page.title'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="m-0">{{ __('changes.page.header') }}</h1>
        <a href="{{ route('audit.changes.index') }}" class="btn btn-outline-secondary">{{ __('changes.buttons.clear_filters') }}</a>
    </div>
@stop

@section('content')
    <x-adminlte-card title="{{ __('changes.cards.filters.title') }}" icon="fas fa-filter text-lightblue" theme="primary" theme-mode="outline">
        <form method="GET" class="form-inline" role="search">
            <div class="form-row w-100">
                <div class="form-group col-md-3 mb-2">
                    <label class="sr-only" for="f_action">{{ __('changes.filters.action_label') }}</label>
                    <select name="action" id="f_action" class="form-control w-100">
                        <option value="">{{ __('changes.filters.all_actions') }}</option>
                        @foreach(['created','updated','deleted','attached','detached','synced'] as $a)
                            <option value="{{ $a }}" @selected(request('action')===$a)>{{ __('changes.actions.' . $a) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-5 mb-2">
                    <label class="sr-only" for="f_model">{{ __('changes.filters.model_label') }}</label>
                    <input type="text" class="form-control w-100" name="model" id="f_model" value="{{ request('model') }}" placeholder="{{ __('changes.filters.model_placeholder') }}">
                </div>
                <div class="form-group col-md-2 mb-2">
                    <label class="sr-only" for="f_user">{{ __('changes.filters.user_label') }}</label>
                    <input type="number" class="form-control w-100" name="user" id="f_user" value="{{ request('user') }}" placeholder="{{ __('changes.filters.user_placeholder') }}">
                </div>
                <div class="form-group col-md-2 mb-2 d-flex align-items-stretch">
                    <button class="btn btn-primary w-100" type="submit"><i class="fas fa-search"></i> {{ __('changes.buttons.search') }}</button>
                </div>
            </div>
        </form>
    </x-adminlte-card>

    <x-adminlte-card title="{{ __('changes.cards.events.title') }}" icon="fas fa-list text-lightblue" theme="primary" theme-mode="outline" body-class="p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ __('changes.table.date') }}</th>
                        <th>{{ __('changes.table.user') }}</th>
                        <th>{{ __('changes.table.source') }}</th>
                        <th>{{ __('changes.table.action') }}</th>
                        <th>{{ __('changes.table.model') }}</th>
                        <th>{{ __('changes.table.entity') }}</th>
                        <th>{{ __('changes.table.details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>{{ $r['created_at'] }}</td>
                            <td>{{ $r['user_name'] }}</td>
                            <td><span class="badge {{ $r['source']==='sistema' ? 'badge-info' : 'badge-secondary' }}">{{ __('changes.sources.' . $r['source']) }}</span></td>
                            <td><span class="badge badge-primary">{{ __('changes.actions.' . $r['action']) }}</span></td>
                            <td><code>{{ $r['model'] }}</code></td>
                            <td>{{ $r['entity'] }}</td>
                            <td>
                                @if(!empty($r['before']) || !empty($r['after']))
                                    @php
                                        // Aplana arreglos para mostrar claves en notación punto
                                        $flatten = function($arr, $prefix = '') use (&$flatten) {
                                            $out = [];
                                            if (!is_array($arr)) return $out;
                                            foreach ($arr as $k => $v) {
                                                $key = $prefix === '' ? (string)$k : $prefix.'.'.$k;
                                                if (is_array($v)) {
                                                    $out += $flatten($v, $key);
                                                } else {
                                                    $out[$key] = $v;
                                                }
                                            }
                                            return $out;
                                        };
                                        $b = $flatten($r['before'] ?? []);
                                        $a = $flatten($r['after'] ?? []);
                                        $keys = array_values(array_unique(array_merge(array_keys($b), array_keys($a))));
                                        sort($keys);
                                    @endphp
                                    <details>
                                        <summary>{{ __('changes.table.view_changes') }}</summary>
                                        <div class="table-responsive mt-2">
                                            <table class="table table-sm table-striped mb-0 diff-table">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('changes.table.key') }}</th>
                                                        <th>{{ __('changes.table.before') }}</th>
                                                        <th>{{ __('changes.table.after') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($keys as $k)
                                                    @php $beforeVal = array_key_exists($k,$b) ? $b[$k] : null; $afterVal = array_key_exists($k,$a) ? $a[$k] : null; @endphp
                                                    <tr class="diff-row">
                                                        <td class="diff-key">{{ $k }}</td>
                                                        <td class="diff-before">{{ is_array($beforeVal) ? json_encode($beforeVal, JSON_UNESCAPED_UNICODE) : (is_bool($beforeVal) ? ($beforeVal?'true':'false') : (string)($beforeVal ?? '')) }}</td>
                                                        <td class="diff-after">{{ is_array($afterVal) ? json_encode($afterVal, JSON_UNESCAPED_UNICODE) : (is_bool($afterVal) ? ($afterVal?'true':'false') : (string)($afterVal ?? '')) }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </details>
                                @elseif(!empty($r['meta']))
                                    <pre class="mb-0 small">{{ json_encode($r['meta'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    {{ __('changes.table.no_details') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('changes.table.no_events') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $rows->links() }}</div>
    </x-adminlte-card>
@endsection

@push('css')
<style>
.diff-table .diff-key{ font-weight:600; color:#0ea5e9; }        /* clave en celeste */
.diff-table .diff-before{ color:#991b1b; }                      /* antes en rojo oscuro */
.diff-table .diff-after{ color:#065f46; }                       /* después en verde oscuro */
.diff-table .diff-row:hover{ background:#f8fafc; }              /* hover suave */
</style>
@endpush
