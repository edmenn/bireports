<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

final class AuditService
{
    /**
     * Vocabulario aceptado + aliases comunes del visor.
     * (Puedes ampliar esta lista sin problema.)
     */
    private const CANONICAL_ACTIONS = [
        'view', 'render', 'export', 'error', 'filter_change', 'page_change', 'bookmark',
        'open', 'loaded', 'click', 'close', 'rendered', 'filters_applied', 'page_changed', 'bookmark_applied', 'data_selected',
    ];

    /**
     * Punto único de ingesta. Normaliza y persiste respetando tu migración.
     *
     * @param  Request $request  Para ip, user-agent, referer y usuario
     * @param  array   $data     Payload validado en el controlador
     * @return Audit
     */
    public function log(Request $request, array $data): Audit
    {
        // ===== 1) Validaciones mínimas y normalizaciones básicas =====
        $reportUuid = trim((string)($data['report_uuid'] ?? ''));
        if ($reportUuid === '') {
            throw new InvalidArgumentException('report_uuid es obligatorio');
        }

        $actionRaw = strtolower(trim((string)($data['action'] ?? 'view')));
        $action    = $this->normalizeAction($actionRaw);

        // Fecha/hora del evento (timestampTz). Si no llega, now()
        $occurredAt = isset($data['occurred_at'])
            ? Carbon::parse((string)$data['occurred_at'])
            : now();

        // success: si no llega, true excepto cuando action=error
        $success = array_key_exists('success', $data)
            ? (bool) $data['success']
            : ($action !== 'error');

        // duration_ms acotado (0 .. 1 día)
        $durationMs = $this->clampInt($data['duration_ms'] ?? null, 0, 86_400_000);

        // Strings con límites reales según tu migración
        $workspaceUuid = $this->uuidOrNull($data['workspace_uuid'] ?? null);
        $datasetUuid   = $this->uuidOrNull($data['dataset_uuid']   ?? null);

        $rlsRole   = $this->trimOrNull($data['rls_role']   ?? null, 128);
        $pageName  = $this->trimOrNull($data['page_name']  ?? null, 128);
        $bookmark  = $this->trimOrNull($data['bookmark']   ?? null, 128);

        // jsonb: aceptamos array u objeto; si llega string, lo ignoramos
        $filters = (isset($data['filters']) && (is_array($data['filters']) || is_object($data['filters'])))
            ? json_decode(json_encode($data['filters']), true) // normaliza a array asociativo
            : null;

        $errorMessage = $this->trimOrNull($data['error_message'] ?? null, 10000); // TEXT

        // Meta del request real
        $ip        = $this->trimOrNull((string) $request->ip(), 45);
        $userAgent = $this->trimOrNull((string) ($request->header('User-Agent') ?? $request->userAgent() ?? ''), 10000);
        $referer   = $this->trimOrNull((string) ($request->header('Referer') ?? $request->headers->get('referer', '')), 512);

        $userId = Auth::id(); // grupo de rutas usa auth

        // ===== 2) Persistencia =====
        $payload = [
            'user_id'        => $userId,
            'report_uuid'    => $reportUuid,
            'workspace_uuid' => $workspaceUuid,
            'dataset_uuid'   => $datasetUuid,

            'action'         => $action,
            'rls_role'       => $rlsRole,
            'filters'        => $filters,
            'page_name'      => $pageName,
            'bookmark'       => $bookmark,

            'ip'             => $ip,
            'user_agent'     => $userAgent,
            'referer'        => $referer,

            'duration_ms'    => $durationMs,
            'occurred_at'    => $occurredAt,
            'success'        => $success,
            'error_message'  => $errorMessage,
        ];

        try {
            return Audit::create($payload);
        } catch (Throwable $e) {
            // En caso de fallo de escritura, registramos un evento "error" para no perder señal.
            Log::error('Error registrando auditoría', [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);

            return Audit::create([
                'user_id'       => $userId,
                'report_uuid'   => $reportUuid,
                'workspace_uuid'=> $workspaceUuid,
                'dataset_uuid'  => $datasetUuid,

                'action'        => 'error',
                'success'       => false,
                'error_message' => 'Audit write failed: ' . $e->getMessage(),

                'ip'            => $ip,
                'user_agent'    => $userAgent,
                'referer'       => $referer,

                'occurred_at'   => now(),
            ]);
        }
    }

    /**
     * Normaliza aliases del visor a tu vocabulario.
     * - open|loaded         → view
     * - rendered            → render
     * - page_changed        → page_change
     * - filters_applied     → filter_change
     * - bookmark_applied    → bookmark
     * - data_selected       → click (si quieres rastrear selecciones)
     * - otros no listados   → se devuelven tal cual si están en CANONICAL_ACTIONS;
     *   si no, se degradan a 'view' para no romper la ingesta.
     */
    private function normalizeAction(string $action): string
    {
        $map = [
            'open'              => 'view',
            'loaded'            => 'view',
            'rendered'          => 'render',
            'page_changed'      => 'page_change',
            'filters_applied'   => 'filter_change',
            'bookmark_applied'  => 'bookmark',
            'data_selected'     => 'click',
        ];

        $a = $map[$action] ?? $action;

        // Si no forma parte del vocabulario permitido, degradamos a 'view'
        if (!in_array($a, self::CANONICAL_ACTIONS, true)) {
            $a = 'view';
        }

        // Longitud máxima para columna action (32)
        return mb_strimwidth($a, 0, 32, '', 'UTF-8');
    }

    /** Recorta string o devuelve null si queda vacío. */
    private function trimOrNull(?string $value, int $max): ?string
    {
        if ($value === null) return null;
        $v = trim($value);
        if ($v === '') return null;
        return mb_strimwidth($v, 0, $max, '', 'UTF-8');
    }

    /** Acota entero dentro de [min, max]; null si no viene. */
    private function clampInt(mixed $value, int $min, int $max): ?int
    {
        if ($value === null || $value === '') return null;
        $i = (int) $value;
        if ($i < $min) $i = $min;
        if ($i > $max) $i = $max;
        return $i;
    }

    /** Valida formato UUID v4 simple o devuelve null. */
    private function uuidOrNull(mixed $value): ?string
    {
        if ($value === null) return null;
        $v = trim((string)$value);
        if ($v === '') return null;
        // Permite guiones; no hacemos validación criptográfica, sólo formato.
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $v)) {
            return null;
        }
        return strtolower($v);
    }
}
