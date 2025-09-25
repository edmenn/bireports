<?php

declare(strict_types=1);

namespace App\Http\Controllers; 

use App\Models\Report; 
use App\Models\Capacity; // [Minimalista] Para mapear workspace.capacity_id (UUID) a la fila local (PK)
use App\Models\Workspace;
use App\Services\PowerBiService;
use App\Models\Configuracion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $reports = Report::query()
            ->with('workspace')
            ->when($q !== '', function ($query) use ($q) {
                $needle = mb_strtolower($q);
                $query->where(function ($w) use ($needle) {
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]) 
                      ->orWhereRaw('LOWER(id) LIKE ?', ["%{$needle}%"]) 
                      ->orWhereRaw('LOWER(workspace_id) LIKE ?', ["%{$needle}%"]) 
                      ->orWhereRaw('LOWER(dataset_id) LIKE ?', ["%{$needle}%"]);
                });
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('reports.index', [
            'reports' => $reports,
            'q'       => $q,
        ]);
    }

    /**
     * Muestra el reporte embebido asegurando que pertenece al workspace de la URL.
     * En caso de error al obtener el embed token, NO rompemos: devolvemos la vista con 'error' y 'error_debug'.
     */
    public function show(string $workspaceId, string $reportId, Request $request): View
    {
        // 1) Cargar workspace (404 si no existe)
        $workspace = Workspace::findOrFail($workspaceId);

        // 2) Cargar reporte que pertenezca a ese workspace (404 si no coincide)
        $report = Report::where('id', $reportId)
            ->where('workspace_id', $workspace->id)
            ->firstOrFail();

        // [Minimalista] Resolver la capacidad asociada al workspace
        // - workspace.capacity_id almacena el UUID de Power BI/Fabric
        // - La API /api/capacities/{capacity}/ping usa RouteModelBinding por PK local (entero)
        //   Mapeamos aquí para que la vista pueda hacer ping/resume on-demand.
        $capacityModelId = null;
        $capacityUuid    = (string) ($workspace->capacity_id ?? '');
        if ($capacityUuid !== '') {
            $capModel = Capacity::query()->where('capacity_uuid', $capacityUuid)->first();
            $capacityModelId = $capModel?->id;
        }

        // 3) Parámetros opcionales de RLS desde querystring
        $rlsUser  = $request->query('rls_user');
        $rlsRoles = array_values(array_filter(array_map('trim', explode(',', (string) $request->query('rls_roles', '')))));

        $embedArr  = [];
        $errShort  = null;
        $errDetail = null;

        // 4) Si no hay configuración completa, devolvemos vista con error amigable
        $hasConfig = Configuracion::query()
            ->whereNotNull('powerbi_tenant_id')->where('powerbi_tenant_id', '!=', '')
            ->whereNotNull('powerbi_client_id')->where('powerbi_client_id', '!=', '')
            ->whereNotNull('powerbi_client_secret')->where('powerbi_client_secret', '!=', '')
            ->exists();

        if (!$hasConfig) {
            $errShort  = 'Faltan credenciales de Power BI en Configuraciones.';
            $embedArr  = [];
        } else {
            try {
                // 4) Generar token de embed
                $svc = app(PowerBiService::class);
                $embed = $svc->generateReportEmbedToken(
                workspaceId: $workspace->id,
                reportId:    $report->id,
                datasetId:   $report->dataset_id,
                rlsUser:     $rlsUser ?: null,
                rlsRoles:    $rlsRoles
            );

                // 4.1) Normalización de claves para la vista
                $embedArr = is_array($embed) ? $embed : (array) $embed;

                $embedArr['accessToken'] = $embedArr['accessToken']
                    ?? ($embedArr['token'] ?? null)
                    ?? ($embedArr['access_token'] ?? null);

                $embedArr['embedUrl']    = $embedArr['embedUrl']    ?? ($embedArr['embed_url'] ?? null);
                $embedArr['reportId']    = $embedArr['reportId']    ?? $report->id;
                $embedArr['tokenType']   = $embedArr['tokenType']   ?? 'Embed';
                $embedArr['datasetId']   = $embedArr['datasetId']   ?? $report->dataset_id;
                $embedArr['workspaceId'] = $embedArr['workspaceId'] ?? $workspace->id;
            } catch (Throwable $e) {
                // 5) Fast-fail: NO lanzamos a la página de error; mostramos banner en la misma vista
                $errShort  = 'No se pudo preparar el embed del reporte. Verifica credenciales/capacidad/permisos.';
                // Detalle seguro para copiar/pegar (sin exponer secrets del env; mostramos clase, mensaje y stack)
                $errDetail = sprintf(
                    "%s: %s\nCode: %s\nFile: %s:%d\n\nTrace:\n%s",
                    get_class($e),
                    $e->getMessage(),
                    (string) ($e->getCode() ?? '0'),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTraceAsString()
                );
                // Devolvemos con $embedArr vacío; la vista mostrará el diagnóstico y no romperá
                $embedArr = [];
            }
        }

        // 5.5) Parámetros minimalistas para warmup on-demand desde la vista
        $cfg = Configuracion::first();
        $capacityOnDemandEnable = (bool) ($cfg?->capacity_on_demand_enable ?? true);
        $capacityOnDemandMessage = (string) ($cfg?->capacity_on_demand_message ?? 'Encendiendo la capacidad, aguarde unos segundos…');
        $capacityStartTimeoutSec = (int) ($cfg?->capacity_start_timeout_sec ?? 120);
        $capacityStartPollSec    = (int) ($cfg?->capacity_start_poll_interval_sec ?? 5);
        $reportLoadTimeoutSec    = (int) ($cfg?->report_load_timeout_sec ?? 10);

        // 6) Vista
        return view('reports.show', [
            'report'       => $report,
            'workspace'    => $workspace,
            'embedded'     => $request->boolean('embedded'),
            'embed'        => $embedArr,
            'error'        => $errShort,   // mensaje corto arriba
            'error_debug'  => $errDetail,  // detalle dentro de <details> para copiar
            // [Minimalista] IDs de capacidad para warmup on-demand desde la vista
            'capacityModelId' => $capacityModelId, // PK local (entero) para /api/capacities/{id}/ping
            'capacityUuid'    => $capacityUuid,    // UUID de Fabric/PBI (informativo/eventos)
            // [Minimalista] Parámetros on-demand
            'capacityOnDemandEnable' => $capacityOnDemandEnable,
            'capacityOnDemandMessage' => $capacityOnDemandMessage,
            'capacityStartTimeoutSec' => $capacityStartTimeoutSec,
            'capacityStartPollSec'    => $capacityStartPollSec,
            'reportLoadTimeoutSec'    => $reportLoadTimeoutSec,
        ]);
    }

    public function edit(Report $report): View
    {
        return view('reports.edit', [
            'report' => $report,
        ]);
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'description'  => ['nullable', 'string', 'max:2000'],
            'cover_url'    => ['nullable', 'url', 'max:2048'],
            'cover_file'   => ['nullable','file','max:2048','mimetypes:image/jpeg,image/png,image/webp,image/gif,image/svg+xml'],
            'make_default' => ['nullable','boolean'],
        ], [
            'cover_url.url'        => 'La imagen debe ser una URL válida.',
            'cover_file.max'       => 'La imagen no debe superar 2MB.',
            'cover_file.mimetypes' => 'Formatos permitidos: JPG, PNG, WEBP, GIF o SVG.',
        ]);

        $makeDefault = (bool) ($validated['make_default'] ?? false);

        // --- Imagen subida (tiene prioridad) ---
        if ($request->hasFile('cover_file')) {
            $file = $request->file('cover_file');

            // Guarda en storage/app/public/reports
            $path = $file->store('reports', 'public');
            $publicUrl = asset('storage/'.$path);

            $report->cover_url = $publicUrl;

            // Si marcó "Usar como predeterminada", copiamos a report-covers/default.png
            if ($makeDefault) {
                $defaultRelPath = 'report-covers/default.png';
                Storage::disk('public')->makeDirectory('report-covers');
                Storage::disk('public')->put($defaultRelPath, Storage::disk('public')->get($path));
            }
        }
        // --- Si no sube archivo pero envía URL ---
        elseif (array_key_exists('cover_url', $validated)) {
            $url = trim((string) ($validated['cover_url'] ?? ''));
            $report->cover_url = $url !== '' ? $url : null;
        }

        // --- Descripción ---
        if (array_key_exists('description', $validated)) {
            $desc = trim((string) ($validated['description'] ?? ''));
            $report->description = $desc !== '' ? $desc : null;
        }

        // --- Permitir encendido fuera de ventana ---
        if ($request->has('allow_resume_outside_window')) {
            $report->allow_resume_outside_window = $request->boolean('allow_resume_outside_window');
        }

        $report->save();

        return redirect()
            ->route('reports.edit', $report)
            ->with('status', $makeDefault
                ? 'Reporte actualizado y portada establecida como predeterminada.'
                : 'Reporte actualizado correctamente.'
            );
    }
}
