<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Capacity;
use App\Models\Configuracion;
use App\Services\PowerBiService;
use App\Services\PowerBiCapacityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DatasetsController extends Controller
{
    public function index(): View
    {
        $datasets = \App\Models\Dataset::with('workspace')
            ->orderBy('name')
            ->paginate(25);

        return view('datasets.index', compact('datasets'));
    }

    public function refresh(string $workspaceId, string $datasetId, PowerBiService $svc, PowerBiCapacityService $capSvc): RedirectResponse
    {
        try {
            // 0) Si el workspace tiene capacidad asociada y est3 pausada, reanudar antes del refresh
            $ws = Workspace::find($workspaceId);
            if ($ws && !empty($ws->capacity_id)) {
                /** @var Capacity|null $cap */
                $cap = Capacity::query()->where('capacity_uuid', $ws->capacity_id)->first();
                if ($cap && $cap->isPausable() && $cap->is_active === false) {
                    $capSvc->resume($cap);
                    // Nota: opcionalmente podramos esperar unos segundos o reintentar si el refresh falla por estado.
                }
            }
            $svc->refreshDataset($workspaceId, $datasetId);
            return back()->with('status', 'Actualización iniciada para el dataset.');
        } catch (Throwable $e) {
            return back()->with('error', 'No se pudo iniciar la actualización: ' . $e->getMessage());
        }
    }

    public function history(string $workspaceId, string $datasetId, PowerBiService $svc): View
    {
        $ws = Workspace::findOrFail($workspaceId);
        $history = [];
        try {
            $history = $svc->getDatasetRefreshHistory($workspaceId, $datasetId);
        } catch (Throwable $e) {
            $history = [];
        }
        return view('datasets.history', [
            'workspace' => $ws,
            'datasetId' => $datasetId,
            'history'   => $history,
        ]);
    }

    public function schedule(string $workspaceId, string $datasetId, PowerBiService $svc): View
    {
        $ws = Workspace::findOrFail($workspaceId);
        $dataset = \App\Models\Dataset::findOrFail($datasetId);
        $schedule = [];
        try {
            $schedule = $svc->getDatasetRefreshSchedule($workspaceId, $datasetId);
        } catch (Throwable $e) {
            $schedule = [];
        }
        $cfg = Configuracion::query()->first();
        return view('datasets.schedule', [
            'workspace' => $ws,
            'dataset'   => $dataset,
            'datasetId' => $datasetId,
            'schedule'  => $schedule,
            'defaultTz' => $cfg?->app_timezone_db ?: config('app.timezone'),
            'localSchedules' => $dataset->schedules()->orderByDesc('created_at')->get(),
        ]);
    }


    public function metadataRefresh(string $workspaceId, string $datasetId, PowerBiService $svc): RedirectResponse
    {
        try {
            $d = $svc->getDataset($workspaceId, $datasetId);
            if (!empty($d['id'])) {
                \App\Models\Dataset::updateOrCreate(
                    ['id' => (string)$d['id']],
                    [
                        'name'                  => (string)($d['name'] ?? '(sin nombre)'),
                        'workspace_id'          => (string)$workspaceId,
                        'configured_by'         => isset($d['configuredBy']) ? (string)$d['configuredBy'] : null,
                        'is_refreshable'        => (bool)  ($d['isRefreshable'] ?? false),
                        'add_rows_api_enabled'  => (bool)  ($d['addRowsAPIEnabled'] ?? false),
                    ]
                );
            }
            return back()->with('status', 'Metadatos recargados para el dataset.');
        } catch (Throwable $e) {
            return back()->with('error', 'No se pudieron recargar los metadatos: ' . $e->getMessage());
        }
    }

    /** Lista combinada de programaciones (local + Fabric) con filtros. */
    public function schedulesIndex(Request $request, PowerBiService $svc): View
    {
        $type      = $request->query('type', 'local'); // default: local
        $workspace = trim((string) $request->query('workspace', ''));
        $day       = trim((string) $request->query('day', ''));
        $q         = trim((string) $request->query('q', ''));

        $datasets = \App\Models\Dataset::with('workspace','schedules')
            ->when($workspace !== '', fn($q2)=>$q2->where('workspace_id',$workspace))
            ->when($q !== '', function($q2) use ($q) {
                $needle = mb_strtolower($q);
                $q2->where(function($w) use ($needle){
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]) 
                      ->orWhereRaw('LOWER(id) LIKE ?', ["%{$needle}%"]);
                });
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $rows = [];
        foreach ($datasets as $d) {
            // Local schedules
            if ($type === 'all' || $type === 'local') {
                foreach ($d->schedules as $s) {
                    $rows[] = [
                        'source'       => 'local',
                        'dataset_id'   => $d->id,
                        'dataset_name' => $d->name,
                        'workspace_id' => $d->workspace_id,
                        'workspace'    => $d->workspace?->name ?: $d->workspace_id,
                        'enabled'      => (bool)$s->enabled,
                        'days'         => (array)$s->days,
                        'times'        => (array)$s->times,
                        'tz'           => \App\Support\AppTimezone::current(),
                        'last_status'  => (string)($s->last_run_status ?? ''),
                        'last_at'      => optional($s->last_run_at)->format('Y-m-d H:i:s'),
                    ];
                }
            }
            // Fabric schedule (solo lectura)
            if ($type === 'all' || $type === 'fabric') {
                try {
                    $cacheKey = 'pbi:schedule:' . $d->workspace_id . ':' . $d->id;
                    $sch = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(5), function() use ($svc, $d) {
                        return $svc->getDatasetRefreshSchedule($d->workspace_id, $d->id);
                    });
                    $val = (array)($sch['value'] ?? []);
                    if (!empty($val)) {
                        $rows[] = [
                            'source'       => 'fabric',
                            'dataset_id'   => $d->id,
                            'dataset_name' => $d->name,
                            'workspace_id' => $d->workspace_id,
                            'workspace'    => $d->workspace?->name ?: $d->workspace_id,
                            'enabled'      => (bool)($val['enabled'] ?? false),
                            'days'         => (array)($val['days'] ?? []),
                            'times'        => (array)($val['times'] ?? []),
                            'tz'           => (string)($val['localTimeZoneId'] ?? ''),
                            'last_status'  => '',
                            'last_at'      => '',
                        ];
                    }
                } catch (\Throwable $e) {
                    // ignorar fallas puntuales
                }
            }
        }

        // Filtros adicionales
        if ($day !== '') {
            $rows = array_values(array_filter($rows, function($r) use ($day){ return in_array($day, (array)$r['days'], true); }));
        }
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $rows = array_values(array_filter($rows, function($r) use ($needle){
                $blob = mb_strtolower(($r['dataset_name'] ?? '').' '.($r['dataset_id'] ?? '').' '.($r['workspace'] ?? ''));
                return str_contains($blob, $needle);
            }));
        }

        // Workspaces para filtro
        $workspaces = \App\Models\Workspace::orderBy('name')->get(['id','name']);

        return view('datasets.schedules', [
            'rows'       => $rows,
            'type'       => $type,
            'workspace'  => $workspace,
            'day'        => $day,
            'q'          => $q,
            'workspaces' => $workspaces,
            'datasets'   => $datasets,
        ]);
    }

    /** Exportación CSV de la lista combinada. */
    public function schedulesExport(Request $request, PowerBiService $svc): StreamedResponse
    {
        $type      = $request->query('type', 'local');
        $workspace = trim((string) $request->query('workspace', ''));
        $day       = trim((string) $request->query('day', ''));
        $q         = trim((string) $request->query('q', ''));

        $datasetsAll = \App\Models\Dataset::with('workspace','schedules')
            ->when($workspace !== '', fn($q2)=>$q2->where('workspace_id',$workspace))
            ->when($q !== '', function($q2) use ($q) {
                $needle = mb_strtolower($q);
                $q2->where(function($w) use ($needle){
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]) 
                      ->orWhereRaw('LOWER(id) LIKE ?', ["%{$needle}%"]);
                });
            })
            ->orderBy('name')
            ->get();

        $rows = [];
        foreach ($datasetsAll as $d) {
            if ($type === 'all' || $type === 'local') {
                foreach ($d->schedules as $s) {
                    $rows[] = [
                        'source'       => 'local',
                        'dataset_id'   => $d->id,
                        'dataset_name' => $d->name,
                        'workspace_id' => $d->workspace_id,
                        'workspace'    => $d->workspace?->name ?: $d->workspace_id,
                        'enabled'      => (bool)$s->enabled,
                        'days'         => (array)$s->days,
                        'times'        => (array)$s->times,
                        'tz'           => \App\Support\AppTimezone::current(),
                        'last_status'  => (string)($s->last_run_status ?? ''),
                        'last_at'      => optional($s->last_run_at)->format('Y-m-d H:i:s'),
                    ];
                }
            }
            if ($type === 'all' || $type === 'fabric') {
                try {
                    $cacheKey = 'pbi:schedule:' . $d->workspace_id . ':' . $d->id;
                    $sch = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(5), function() use ($svc, $d) {
                        return $svc->getDatasetRefreshSchedule($d->workspace_id, $d->id);
                    });
                    $val = (array)($sch['value'] ?? []);
                    if (!empty($val)) {
                        $rows[] = [
                            'source'       => 'fabric',
                            'dataset_id'   => $d->id,
                            'dataset_name' => $d->name,
                            'workspace_id' => $d->workspace_id,
                            'workspace'    => $d->workspace?->name ?: $d->workspace_id,
                            'enabled'      => (bool)($val['enabled'] ?? false),
                            'days'         => (array)($val['days'] ?? []),
                            'times'        => (array)($val['times'] ?? []),
                            'tz'           => (string)($val['localTimeZoneId'] ?? ''),
                            'last_status'  => '',
                            'last_at'      => '',
                        ];
                    }
                } catch (\Throwable $e) {}
            }
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="dataset_schedules.csv"',
        ];

        return response()->streamDownload(function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['source','workspace','dataset_name','dataset_id','enabled','tz','days','times','last_status','last_at']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['source'] ?? '',
                    $r['workspace'] ?? '',
                    $r['dataset_name'] ?? '',
                    $r['dataset_id'] ?? '',
                    ($r['enabled'] ?? false) ? '1' : '0',
                    $r['tz'] ?? '',
                    implode(',', (array)($r['days'] ?? [])),
                    implode(',', (array)($r['times'] ?? [])),
                    $r['last_status'] ?? '',
                    $r['last_at'] ?? '',
                ]);
            }
            fclose($out);
        }, 'dataset_schedules.csv', $headers);
    }

    public function localScheduleStore(string $workspaceId, string $datasetId, Request $request): RedirectResponse
    {
        $dataset = \App\Models\Dataset::findOrFail($datasetId);
        $validated = $request->validate([
            'days'  => ['required','array','min:1'],
            'days.*'=> ['string','in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'],
            'times' => ['required','string','max:500'],
        ]);

        $rawTimes = array_values(array_filter(array_map('trim', explode(',', (string)$validated['times']))));
        $times = [];
        foreach ($rawTimes as $t) {
            if (preg_match('/^\d{2}:\d{2}$/', $t)) { $times[] = $t; }
        }
        $times = array_values(array_unique($times));
        if (empty($times)) {
            return back()->with('error','Debes indicar al menos un horario válido (HH:mm).')->withInput();
        }

        $days = array_values(array_unique(array_intersect($validated['days'], ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'])));

        // Detección de solapamientos con existentes
        $warning = null;
        $existing = $dataset->schedules()->get();
        foreach ($existing as $ex) {
            if (!($ex->enabled)) continue;
            $dInt = array_intersect($days, (array)$ex->days);
            $tInt = array_intersect($times, (array)$ex->times);
            if (!empty($dInt) && !empty($tInt)) {
                $warning = 'Atención: hay solapamientos con otra programación local.';
                break;
            }
        }

        $dataset->schedules()->create([
            'days'    => $days,
            'times'   => $times,
            'enabled' => true,
        ]);

        return redirect()->route('datasets.schedule', [$workspaceId, $datasetId])
            ->with($warning ? 'warning' : 'status', $warning ?: 'Programación local creada.');
    }

    public function localScheduleToggle(string $workspaceId, string $datasetId, int $scheduleId): RedirectResponse
    {
        $dataset = \App\Models\Dataset::findOrFail($datasetId);
        $sch = $dataset->schedules()->where('id',$scheduleId)->firstOrFail();
        $sch->enabled = ! $sch->enabled;
        $sch->save();
        return back()->with('status', $sch->enabled ? 'Programación activada.' : 'Programación desactivada.');
    }

    public function localScheduleDelete(string $workspaceId, string $datasetId, int $scheduleId): RedirectResponse
    {
        $dataset = \App\Models\Dataset::findOrFail($datasetId);
        $sch = $dataset->schedules()->where('id',$scheduleId)->firstOrFail();
        $sch->delete();
        return back()->with('status', 'Programación eliminada.');
    }
}
