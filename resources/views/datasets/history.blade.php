@extends('layouts.app')

@section('title', __('datasets.history.title'))

{{-- =========================
     HEADER
     ========================= --}}
@section('content_header')
    <x-page-header :title="__('datasets.history.title')" 
                   :subtitle="__('datasets.history.subtitle', ['workspace' => $workspace->name ?? $workspace->id, 'dataset' => $datasetId])" 
                   icon="fas fa-history">
    </x-page-header>
@stop

{{-- =========================
     CONTENT
     ========================= --}}
@section('content')
    @php $history = is_array($history ?? null) ? $history : []; @endphp

    <x-ui-card theme="primary" theme-mode="outline" class="shadow-sm">
        <div class="table-responsive datasets-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('datasets.history.table.date') }}</th>
                        <th>{{ __('datasets.history.table.status') }}</th>
                        <th>{{ __('datasets.history.table.duration') }}</th>
                        <th>{{ __('datasets.history.table.requested_by') }}</th>
                        <th>{{ __('datasets.history.table.details') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $h)
                        <tr>
                            {{-- Date --}}
                            <td class="text-nowrap text-monospace small" title="{{ $h['startTime'] ?? '' }}">
                                @php
                                    $tz = \App\Support\AppTimezone::current();
                                    $startFmt = '';
                                    try {
                                        if (!empty($h['startTime'])) {
                                            $startFmt = \Carbon\Carbon::parse($h['startTime'])->setTimezone($tz)->format('d-m-Y H:i:s');
                                        }
                                    } catch (\Throwable $e) { 
                                        $startFmt = (string)($h['startTime'] ?? ''); 
                                    }
                                @endphp
                                {{ $startFmt }}
                            </td>

                            {{-- Status --}}
                            <td>
                                @php $st = strtolower((string)($h['status'] ?? '')); @endphp
                                <span class="badge {{ 
                                    $st === 'completed' ? 'bg-success' : 
                                    ($st === 'failed' ? 'bg-danger' : 
                                    ($st === 'inprogress' ? 'bg-warning text-dark' : 'bg-secondary')) 
                                }}">
                                    {{ ucfirst($h['status'] ?? '') }}
                                </span>
                            </td>

                            {{-- Duration --}}
                            <td class="text-monospace small">
                                @php
                                    $dur = '';
                                    if (!empty($h['startTime']) && !empty($h['endTime'])) {
                                        try {
                                            $s = \Carbon\Carbon::parse($h['startTime']);
                                            $e = \Carbon\Carbon::parse($h['endTime']);
                                            $dur = $e->diff($s)->format('%H:%I:%S');
                                        } catch (\Throwable $e) { $dur = ''; }
                                    }
                                @endphp
                                {{ $dur }}
                            </td>

                            {{-- Requested by --}}
                            <td class="small">{{ $h['requestedBy'] ?? '' }}</td>

                            {{-- Details --}}
                            <td class="small text-muted">
                                @if(!empty($h['serviceExceptionJson']))
                                    <pre class="mb-0 small text-wrap" style="white-space: pre-wrap; word-break: break-word;">
                                        {{ $h['serviceExceptionJson'] }}
                                    </pre>
                                @else
                                    <span class="text-muted">{{ __('datasets.history.no_details') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                {{ __('datasets.history.empty') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui-card>
@stop

{{-- =========================
     STYLES
     ========================= --}}
@push('css')
<style>
.header-actions {
    display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:.5rem;
}
.header-btn {
    display:inline-flex;align-items:center;justify-content:center;
    height:38px;line-height:1;padding:0 .9rem;font-size:.875rem;
}
.header-btn i {font-size:.9rem;margin-right:.4rem;}
.text-monospace {
    font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;
}
.table thead th {font-weight:600;}
pre {background:transparent;border:none;padding:0;margin:0;}
</style>
@endpush
