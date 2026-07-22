<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackAudit
{
    public function __construct(private readonly AuditService $service) {}

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $reportUuid = $request->route('report_uuid')
            ?? $request->query('report_uuid')
            ?? $request->headers->get('X-Report-Id');

        if ($reportUuid) {
            try {
                $this->service->log($request, [
                    'report_uuid' => (string)$reportUuid,
                    'workspace_uuid' => $request->query('workspace_uuid') ?? $request->headers->get('X-Workspace-Id'),
                    'dataset_uuid'   => $request->query('dataset_uuid') ?? $request->headers->get('X-Dataset-Id'),
                    'action'      => 'view',
                    // Registrar SIEMPRE en UTC para evitar desfases por tz del host/BD
                    'occurred_at' => now()->utc(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Automatic audit tracking failed', [
                    'report_uuid' => (string) $reportUuid,
                    'user_id' => $request->user()?->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $response;
    }
}
