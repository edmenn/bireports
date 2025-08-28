<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditService
{
    public function log(Request $request, array $data): Audit
    {
        $data['report_uuid']   = (string) ($data['report_uuid'] ?? '');
        $data['action']        = strtolower((string) ($data['action'] ?? 'view'));
        $data['occurred_at']   = $data['occurred_at'] ?? now();
        $data['success']       = array_key_exists('success', $data) ? (bool) $data['success'] : true;
        $data['duration_ms']   = isset($data['duration_ms']) ? (int) $data['duration_ms'] : null;

        if ($data['report_uuid'] === '') {
            throw new \InvalidArgumentException('report_uuid es obligatorio');
        }

        $record = [
            'user_id'    => optional($request->user())->id,
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1024),
            'referer'    => substr((string) $request->headers->get('referer', ''), 0, 512),
        ];

        $payload = array_merge($data, $record);

        try {
            return Audit::create($payload);
        } catch (Throwable $e) {
            Log::error('Error registrando auditoría', [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);
            return Audit::create([
                'user_id'      => $record['user_id'],
                'report_uuid'  => $data['report_uuid'],
                'action'       => 'error',
                'success'      => false,
                'error_message'=> 'Audit write failed: ' . $e->getMessage(),
                'ip'           => $record['ip'],
                'user_agent'   => $record['user_agent'],
                'referer'      => $record['referer'],
                'occurred_at'  => now(),
            ]);
        }
    }
}
