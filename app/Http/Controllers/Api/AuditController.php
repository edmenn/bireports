<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

final class AuditController extends Controller
{
    /**
     * Usamos inyección de dependencias para el servicio de auditoría.
     * La ruta está definida en web.php con middleware ['auth'].
     */
    public function __construct(private readonly AuditService $service)
    {
        // Si algún día quisieras forzar aquí: $this->middleware(['auth']);
    }

    /**
     * Registra un evento de auditoría proveniente del visor de reportes.
     *
     * Acepta JSON o form-data. Campos soportados:
     * - report_uuid (uuid, requerido)
     * - action (string, requerido; ej: open|view|filter|click|close|error)
     * - workspace_uuid (uuid, opcional)
     * - dataset_uuid (uuid, opcional)
     * - rls_role (string, opcional)
     * - filters (array, opcional) → se castea a JSON por el servicio/model
     * - page_name (string, opcional)
     * - bookmark (string, opcional)
     * - duration_ms (int >=0, opcional)
     * - success (bool, opcional)
     * - error_message (string, opcional)
     * - occurred_at (date/ISO-8601, opcional)
     */
    public function store(Request $request): JsonResponse
    {
        // Validación con mensajes por defecto (podrías personalizarlos si quieres)
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
            'error_message'  => ['nullable','string'],
            'occurred_at'    => ['nullable','date'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok'     => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Normalizaciones ligeras:
        // - 'action' en minúsculas
        // - 'success' a bool estricto si viene como "1"/"0"/"true"/"false"
        if (isset($data['action'])) {
            $data['action'] = mb_strtolower((string) $data['action']);
        }
        if (array_key_exists('success', $data)) {
            // Validator boolean ya convierte, pero hacemos cast explícito por seguridad
            $data['success'] = filter_var($data['success'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        try {
            // El servicio puede usar $request para obtener user(), ip(), user-agent, etc.
            $audit = $this->service->log($request, $data);

            return response()->json([
                'ok' => true,
                'id' => $audit->id,
            ], 201);
        } catch (Throwable $e) {
            // Log interno para diagnóstico
            Log::error('Audit.store failed', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'payload' => $data,
                'user_id' => optional($request->user())->id,
                'ip'      => $request->ip(),
                'agent'   => $request->header('User-Agent'),
            ]);

            // Respuesta genérica para no filtrar detalles internos
            return response()->json([
                'ok'      => false,
                'message' => 'No se pudo registrar la auditoría.',
            ], 500);
        }
    }
}
