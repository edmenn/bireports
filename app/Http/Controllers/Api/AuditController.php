<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Models\Report;
use App\Services\ReportAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

final class AuditController extends Controller
{
    /**
     * Dependency-inject the audit service.
     * The route is defined in web.php with ['auth'] middleware.
     */
    public function __construct(
        private readonly AuditService $service,
        private readonly ReportAccessService $access,
    ) {}

    /**
     * Register an audit event originating from the report viewer.
     *
     * Accepts JSON or form-data. Supported fields:
     * - report_uuid (uuid, required)
     * - action (string, required; e.g. open|view|filter|click|close|error)
     * - workspace_uuid (uuid, optional)
     * - dataset_uuid (uuid, optional)
     * - rls_role (string, optional)
     * - filters (array, optional) — serialised to JSON by the service/model
     * - page_name (string, optional)
     * - bookmark (string, optional)
     * - duration_ms (int >=0, optional)
     * - success (bool, optional)
     * - error_message (string, optional)
     * - occurred_at (date/ISO-8601, optional)
     */
    public function store(Request $request): JsonResponse
    {
        // Validate with default messages (customise as needed)
        $validator = Validator::make($request->all(), [
            'report_uuid'    => ['required','uuid'],
            'action'         => ['required','string','max:32'],
            'workspace_uuid' => ['nullable','uuid'],
            'dataset_uuid'   => ['nullable','uuid'],
            'rls_role'       => ['nullable','string','max:128'],
            'filters'        => ['nullable','array'],
            'page_name'      => ['nullable','string','max:128'],
            'bookmark'       => ['nullable','string','max:128'],
            'duration_ms'    => ['nullable','integer','min:0','max:86400000'],
            'success'        => ['nullable','boolean'],
            'error_message'  => ['nullable','string','max:1000'],
            'occurred_at'    => ['nullable','date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok'     => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $report = Report::query()->findOrFail($data['report_uuid']);
        $user = $request->user();
        abort_unless($user && ($user->hasRole('admin') || $this->access->userCanAccessReport($user, $report)), 403);
        if (! empty($data['workspace_uuid'])) {
            abort_unless(hash_equals((string) $report->workspace_id, (string) $data['workspace_uuid']), 422);
        }
        $data['workspace_uuid'] = (string) $report->workspace_id;
        $data['dataset_uuid'] = $report->dataset_id ? (string) $report->dataset_id : null;

        // Light normalisation:
        // - Force 'action' to lowercase
        // - Cast 'success' to a strict boolean when provided as "1"/"0"/"true"/"false"
        if (isset($data['action'])) {
            $data['action'] = mb_strtolower((string) $data['action']);
        }
        if (array_key_exists('success', $data)) {
            // Validator already casts booleans; enforce conversion explicitly for safety
            $data['success'] = filter_var($data['success'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        try {
            // The service can use $request to read user(), ip(), user-agent, etc.
            $audit = $this->service->log($request, $data);

            return response()->json([
                'ok' => true,
                'id' => $audit->id,
            ], 201);
        } catch (Throwable $e) {
            // Internal log for diagnostics
            Log::error('Audit.store failed', [
                'exception' => get_class($e),
                'report_uuid' => $data['report_uuid'] ?? null,
                'action' => $data['action'] ?? null,
                'user_id' => optional($request->user())->id,
            ]);

            // Generic response to avoid leaking internal details
            return response()->json([
                'ok'      => false,
                'message' => __('audit.api.store_failed'),
            ], 500);
        }
    }
}
