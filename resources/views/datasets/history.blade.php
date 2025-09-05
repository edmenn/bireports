@extends('adminlte::page')

@section('title', 'Historial de actualización')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h1 class="m-0">Historial de actualización</h1>
            <small class="text-muted">Workspace: {{ $workspace->name ?? $workspace->id }} — Dataset: {{ $datasetId }}</small>
        </div>
        <a href="{{ route('datasets.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @php $history = is_array($history ?? null) ? $history : []; @endphp

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Duración</th>
                        <th>Iniciado por</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $h)
                        <tr>
                            <td class="text-nowrap text-monospace small" title="{{ $h['startTime'] ?? '' }}">
                                @php
                                    $tz = \App\Support\AppTimezone::current();
                                    $startFmt = '';
                                    try {
                                        if (!empty($h['startTime'])) {
                                            $startFmt = \Carbon\Carbon::parse($h['startTime'])->setTimezone($tz)->format('d-m-Y H:i:s');
                                        }
                                    } catch (\Throwable $e) { $startFmt = (string)($h['startTime'] ?? ''); }
                                @endphp
                                {{ $startFmt }}
                            </td>
                            <td>
                                @php $st = strtolower((string)($h['status'] ?? '')); @endphp
                                <span class="badge {{ $st === 'completed' ? 'bg-success' : ($st === 'failed' ? 'bg-danger' : 'bg-secondary') }}">{{ $h['status'] ?? '' }}</span>
                            </td>
                            <td>
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
                            <td>{{ $h['requestedBy'] ?? '' }}</td>
                            <td class="small text-muted">{{ $h['serviceExceptionJson'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
