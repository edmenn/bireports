<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Capacity;
use App\Models\Configuration;
use App\Services\PowerBiService;
use App\Services\PowerBiCapacityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
            // 0) If the workspace is linked to a paused capacity, resume it before refreshing
            $ws = Workspace::find($workspaceId);
            if ($ws && !empty($ws->capacity_id)) {
                /** @var Capacity|null $cap */
                $cap = Capacity::query()->where('capacity_uuid', $ws->capacity_id)->first();
                if ($cap && $cap->isPausable() && $cap->is_active === false) {
                    $capSvc->resume($cap);
                    // Optional: wait a few seconds or retry if the refresh fails due to state
                }
            }
            $svc->refreshDataset($workspaceId, $datasetId);
            return back()->with('status', __('datasets.flash.refresh_started'));
        } catch (Throwable $e) {
            return back()->with('error', __('datasets.flash.refresh_failed', ['message' => $e->getMessage()]));
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
        $cfg = Configuration::query()->first();
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
                        'name'                  => (string)($d['name'] ?? __('common.unnamed')),
                        'workspace_id'          => (string)$workspaceId,
                        'configured_by'         => isset($d['configuredBy']) ? (string)$d['configuredBy'] : null,
                        'is_refreshable'        => (bool)  ($d['isRefreshable'] ?? false),
                        'add_rows_api_enabled'  => (bool)  ($d['addRowsAPIEnabled'] ?? false),
                    ]
                );
            }
            return back()->with('status', __('datasets.flash.metadata_refreshed'));
        } catch (Throwable $e) {
            return back()->with('error', __('datasets.flash.metadata_failed', ['message' => $e->getMessage()]));
        }
    }

    /** Combined schedule list (local + Fabric) with filtering support. */
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

    /** Export the combined schedule list as an Excel workbook. */
    public function schedulesExport(Request $request, PowerBiService $svc): StreamedResponse
    {
        if (class_exists(\Debugbar::class)) {
            \Debugbar::disable();
        }
        @ini_set('zlib.output_compression', 'Off');
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

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

        $columns = [
            'source' => [
                'header' => __('datasets.schedules.export.columns.source'),
                'value'  => static function(array $row): string {
                    $source = (string)($row['source'] ?? '');
                    if ($source === 'local') {
                        return __('datasets.schedules.badges.local');
                    }
                    if ($source === 'fabric') {
                        return __('datasets.schedules.badges.fabric');
                    }
                    return $source;
                },
            ],
            'workspace' => [
                'header' => __('datasets.schedules.export.columns.workspace'),
                'value'  => static fn(array $row): string => (string)($row['workspace'] ?? ''),
            ],
            'dataset_name' => [
                'header' => __('datasets.schedules.export.columns.dataset_name'),
                'value'  => static fn(array $row): string => (string)($row['dataset_name'] ?? ''),
            ],
            'dataset_id' => [
                'header' => __('datasets.schedules.export.columns.dataset_id'),
                'value'  => static fn(array $row): string => (string)($row['dataset_id'] ?? ''),
            ],
            'status' => [
                'header' => __('datasets.schedules.export.columns.status'),
                'value'  => static fn(array $row): string => ($row['enabled'] ?? false)
                    ? __('datasets.schedules.enabled')
                    : __('datasets.schedules.disabled'),
            ],
            'tz' => [
                'header' => __('datasets.schedules.export.columns.tz'),
                'value'  => static fn(array $row): string => (string)($row['tz'] ?? ''),
            ],
            'days' => [
                'header' => __('datasets.schedules.export.columns.days'),
                'value'  => static fn(array $row): string => implode(', ', (array)($row['days'] ?? [])),
            ],
            'times' => [
                'header' => __('datasets.schedules.export.columns.times'),
                'value'  => static fn(array $row): string => implode(', ', (array)($row['times'] ?? [])),
            ],
            'last_status' => [
                'header' => __('datasets.schedules.export.columns.last_status'),
                'value'  => static fn(array $row): string => (string)($row['last_status'] ?? ''),
            ],
            'last_at' => [
                'header' => __('datasets.schedules.export.columns.last_at'),
                'value'  => static fn(array $row): string => (string)($row['last_at'] ?? ''),
            ],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheetName = (string)__('datasets.schedules.export.sheet_name');
        $sheet->setTitle(mb_substr($sheetName !== '' ? $sheetName : 'Sheet1', 0, 31));

        $colIndex = 1;
        foreach ($columns as $column) {
            $coord = Coordinate::stringFromColumnIndex($colIndex) . '1';
            $sheet->setCellValueExplicit($coord, (string)($column['header'] ?? ''), DataType::TYPE_STRING);
            $sheet->getStyle($coord)->getFont()->setBold(true);
            $colIndex++;
        }

        $rowIndex = 2;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach ($columns as $column) {
                $valueCallback = $column['value'] ?? static fn(array $r): string => '';
                $value = $valueCallback($row);
                $coord = Coordinate::stringFromColumnIndex($colIndex) . (string)$rowIndex;

                if (is_int($value) || is_float($value)) {
                    $sheet->setCellValueExplicit($coord, $value, DataType::TYPE_NUMERIC);
                } else {
                    $sheet->setCellValueExplicit($coord, (string)$value, DataType::TYPE_STRING);
                }
                $colIndex++;
            }
            $rowIndex++;
        }

        $totalColumns = count($columns);
        for ($i = 1; $i <= $totalColumns; $i++) {
            $columnLetter = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = __('datasets.schedules.export.filename') . '_' . now()->format('Ymd_His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->streamDownload(static function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
        ]);
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
            return back()->with('error', __('datasets.flash.local_invalid_times'))->withInput();
        }

        $days = array_values(array_unique(array_intersect($validated['days'], ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'])));

        // Detect overlapping schedules
        $warning = null;
        $existing = $dataset->schedules()->get();
        foreach ($existing as $ex) {
            if (!($ex->enabled)) continue;
            $dInt = array_intersect($days, (array)$ex->days);
            $tInt = array_intersect($times, (array)$ex->times);
            if (!empty($dInt) && !empty($tInt)) {
                $warning = __('datasets.flash.local_overlap');
                break;
            }
        }

        $dataset->schedules()->create([
            'days'    => $days,
            'times'   => $times,
            'enabled' => true,
        ]);

        return redirect()->route('datasets.schedule', [$workspaceId, $datasetId])
            ->with($warning ? 'warning' : 'status', $warning ?: __('datasets.flash.local_created'));
    }

    public function localScheduleToggle(string $workspaceId, string $datasetId, int $scheduleId): RedirectResponse
    {
        $dataset = \App\Models\Dataset::findOrFail($datasetId);
        $sch = $dataset->schedules()->where('id',$scheduleId)->firstOrFail();
        $sch->enabled = ! $sch->enabled;
        $sch->save();
        return back()->with('status', $sch->enabled ? __('datasets.flash.local_enabled') : __('datasets.flash.local_disabled'));
    }

    public function localScheduleDelete(string $workspaceId, string $datasetId, int $scheduleId): RedirectResponse
    {
        $dataset = \App\Models\Dataset::findOrFail($datasetId);
        $sch = $dataset->schedules()->where('id',$scheduleId)->firstOrFail();
        $sch->delete();
        return back()->with('status', __('datasets.flash.local_deleted'));
    }
}
