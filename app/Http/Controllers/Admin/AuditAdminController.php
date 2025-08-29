<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

final class AuditAdminController extends Controller
{
    /**
     * Pantalla de confirmación para resetear auditoría.
     * Ruta protegida por middleware ['auth', 'role:super_admin'] en web.php.
     */
    public function showReset(): View
    {
        $stats = DB::table('audits')
            ->selectRaw('COUNT(*) AS total, MIN(occurred_at) AS first_at, MAX(occurred_at) AS last_at')
            ->first();

        return view('admin.audits.reset', [
            'total'    => (int) ($stats->total ?? 0),
            'first_at' => $stats->first_at,
            'last_at'  => $stats->last_at,
        ]);
    }

    /**
     * Ejecuta el TRUNCATE de la tabla audits.
     * Usa TRUNCATE ... RESTART IDENTITY para reiniciar el autoincremental.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm' => ['required', 'in:RESET'],
        ], [
            'confirm.in' => 'Debes escribir exactamente RESET para confirmar.',
        ]);

        try {
            // Transacción por prolijidad (aunque TRUNCATE es DDL)
            DB::beginTransaction();

            // Importante: PostgreSQL
            // - RESTART IDENTITY reinicia la secuencia del bigserial
            // - CASCADE no es necesario si no hay FKs hacia audits.
            DB::statement('TRUNCATE TABLE audits RESTART IDENTITY');

            DB::commit();

            return redirect()
                ->route('admin.audit.reset.show')
                ->with('status', 'Se borraron todos los registros de auditoría y se reinició el contador de IDs.');
        } catch (Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('admin.audit.reset.show')
                ->with('error', 'No se pudo resetear la auditoría: ' . $e->getMessage());
        }
    }
}
