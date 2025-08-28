<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\PowerBiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $reports = Report::query()
            ->with('workspace')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    // Cambia a 'like' si usas MySQL
                    $w->where('name', 'ilike', "%{$q}%")
                      ->orWhere('id', 'ilike', "%{$q}%")
                      ->orWhere('workspace_id', 'ilike', "%{$q}%")
                      ->orWhere('dataset_id', 'ilike', "%{$q}%");
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

    public function show(string $workspaceId, string $reportId, PowerBiService $svc, Request $request): View
    {
        $rlsUser  = $request->query('rls_user');
        $rlsRoles = array_filter(array_map('trim', explode(',', (string)$request->query('rls_roles', ''))));

        $report = Report::where('id', $reportId)
            ->where('workspace_id', $workspaceId)
            ->firstOrFail();

        $embed = $svc->generateReportEmbedToken(
            workspaceId: $workspaceId,
            reportId:    $reportId,
            datasetId:   $report->dataset_id,
            rlsUser:     $rlsUser ?: null,
            rlsRoles:    $rlsRoles
        );

        return view('reports.show', [
            'report' => $report,
            'embed'  => $embed,
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
                // Creamos directorio si no existe
                Storage::disk('public')->makeDirectory('report-covers');
                // Copiamos el recién subido como predeterminado (en PNG manteniendo nombre fijo)
                // Nota: no convertimos formato; copiamos bytes tal cual y nombre .png por consistencia de ruta pública
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

        $report->save();

        return redirect()
            ->route('reports.edit', $report)
            ->with('status', $makeDefault
                ? 'Reporte actualizado y portada establecida como predeterminada.'
                : 'Reporte actualizado correctamente.'
            );
    }
}
