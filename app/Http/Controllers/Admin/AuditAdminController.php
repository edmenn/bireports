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
     * Confirmation screen for wiping audit data.
     * Route protected by ['auth', 'role:admin'] middleware.
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
     * Truncates the audits table and restarts the identity sequence.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm' => ['required', 'in:RESET'],
        ], [
            'confirm.in' => __('audit.admin.confirm_exact'),
        ]);

        try {
            // Transaction for neatness (even though TRUNCATE is DDL)
            DB::beginTransaction();

            // PostgreSQL specifics:
            // - RESTART IDENTITY resets the bigserial sequence
            // - CASCADE is unnecessary when no FKs target audits.
            DB::statement('TRUNCATE TABLE audits RESTART IDENTITY');

            DB::commit();

            return redirect()
                ->route('admin.audit.reset.show')
                ->with('status', __('audit.admin.reset_success'));
        } catch (Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('admin.audit.reset.show')
                ->with('error', __('audit.admin.reset_error', ['message' => $e->getMessage()]));
        }
    }
}
