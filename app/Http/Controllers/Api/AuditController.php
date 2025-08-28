<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuditController extends Controller
{
    public function __construct(private readonly AuditService $service)
    {
        // Middleware de auth se aplica en routes/api.php
    }

    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'report_uuid'    => 'required|uuid',
            'action'         => 'required|string|max:32',
            'workspace_uuid' => 'nullable|uuid',
            'dataset_uuid'   => 'nullable|uuid',
            'rls_role'       => 'nullable|string|max:128',
            'filters'        => 'nullable|array',
            'page_name'      => 'nullable|string|max:128',
            'bookmark'       => 'nullable|string|max:128',
            'duration_ms'    => 'nullable|integer|min:0|max:86400000',
            'success'        => 'nullable|boolean',
            'error_message'  => 'nullable|string',
            'occurred_at'    => 'nullable|date',
        ]);

        if ($v->fails()) {
            return response()->json([
                'ok'     => false,
                'errors' => $v->errors(),
            ], 422);
        }

        $audit = $this->service->log($request, $v->validated());

        return response()->json([
            'ok' => true,
            'id' => $audit->id,
        ]);
    }
}
