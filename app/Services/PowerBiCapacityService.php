<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Capacity;
use App\Models\Configuracion;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PowerBiCapacityService
{
    private Client $http;
    private string $base = 'https://api.powerbi.com/v1.0/myorg';

    private string $tenantId;
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        // Leemos credenciales desde tu tabla configuraciones (o .env como fallback)
        /** @var Configuracion|null $cfg */
        $cfg = Configuracion::query()->first();

        $this->tenantId     = (string) ($cfg->powerbi_tenant_id ?? env('POWERBI_TENANT_ID', ''));
        $this->clientId     = (string) ($cfg->powerbi_client_id ?? env('POWERBI_CLIENT_ID', ''));
        $this->clientSecret = (string) ($cfg->powerbi_client_secret ?? env('POWERBI_CLIENT_SECRET', ''));

        if ($this->tenantId === '' || $this->clientId === '' || $this->clientSecret === '') {
            throw new RuntimeException('Power BI credentials missing (tenant/client/secret).');
        }

        $this->http = new Client([
            'timeout'     => 30,
            'http_errors' => false,
        ]);
    }

    /* ================== Auth ================== */

    private function getAccessToken(): string
    {
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";

        $resp = $this->http->post($url, [
            'form_params' => [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type'    => 'client_credentials',
                // Scope para API de Power BI (myorg)
                'scope'         => 'https://analysis.windows.net/powerbi/api/.default',
            ],
        ]);

        if ($resp->getStatusCode() !== 200) {
            Log::error('PowerBi OAuth failed', [
                'code' => $resp->getStatusCode(),
                'body' => (string) $resp->getBody(),
            ]);
            throw new RuntimeException('PowerBi OAuth failed');
        }

        $json  = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $token = (string) ($json['access_token'] ?? '');
        if ($token === '') {
            throw new RuntimeException('PowerBi OAuth failed: empty token');
        }

        return $token;
    }

    /**
     * Helper para llamar a la API Power BI (myorg).
     *
     * @return array<string,mixed>|null
     */
    private function api(string $method, string $path, array $opts = []): ?array
    {
        $token = $this->getAccessToken();
        $opts['headers']['Authorization'] = "Bearer {$token}";
        $opts['headers']['Content-Type']  = $opts['headers']['Content-Type'] ?? 'application/json';

        $attempts = 0;

        while (true) {
            $attempts++;

            try {
                $url = rtrim($this->base, '/') . '/' . ltrim($path, '/');
                $resp = $this->http->request($method, $url, $opts);
                $code = $resp->getStatusCode();

                if ($code >= 200 && $code < 300) {
                    $body = (string) $resp->getBody();
                    return $body !== '' ? json_decode($body, true) : null;
                }

                // Reintentos en errores transitorios
                if (in_array($code, [429, 500, 502, 503, 504], true) && $attempts < 4) {
                    sleep(2 ** $attempts); // 2,4,8
                    continue;
                }

                $bodyStr = (string) $resp->getBody();
                Log::error('PowerBi API error', [
                    'method' => $method,
                    'path'   => $path,
                    'url'    => $url,
                    'code'   => $code,
                    'body'   => $bodyStr,
                ]);

                if ($code === 405) {
                    // Mensaje claro para diagnóstico
                    throw new RuntimeException('PowerBi API 405 (Method Not Allowed). Para pausar/reanudar usa los endpoints /admin/capacities/{id}/(suspend|resume) y verifica que el Service Principal tenga rol de Power BI/Fabric Admin.');
                }
                if ($code === 403) {
                    throw new RuntimeException('PowerBi API 403 (Forbidden). El Service Principal no tiene permisos suficientes (requiere permisos de admin en el tenant y acceso a la capacidad).');
                }
                if ($code === 401) {
                    throw new RuntimeException('PowerBi API 401 (Unauthorized). Token inválido o sin alcance; revisa el scope del cliente app y los consentimientos.');
                }

                throw new RuntimeException("PowerBi API error {$code}");
            } catch (GuzzleException $e) {
                if ($attempts < 4) {
                    sleep(2 ** $attempts);
                    continue;
                }
                Log::error('PowerBi API exception', ['path' => $path, 'ex' => $e->getMessage()]);
                throw new RuntimeException('PowerBi API exception: ' . $e->getMessage(), 0, $e);
            }
        }
    }

    /* ================== Capacidades ================== */

    /**
     * Lista capacidades (myorg).
     * @return array<int, array<string,mixed>>
     */
    public function listCapacities(): array
    {
        $json = $this->api('GET', 'capacities');
        return (array) ($json['value'] ?? []);
    }

    /**
     * Obtiene una capacidad por ID (UUID de myorg).
     * @return array<string,mixed>
     */
    public function getCapacity(string $capacityId): array
    {
        return (array) $this->api('GET', "capacities/{$capacityId}");
    }

    /**
     * Pausa una capacidad usando los endpoints de admin.
     * Requiere que el Service Principal sea Power BI/Fabric Admin.
     */
    public function pause(string $capacityId): void
    {
        // /v1.0/myorg/admin/capacities/{id}/suspend
        $this->api('POST', "admin/capacities/{$capacityId}/suspend");
    }

    /**
     * Reanuda una capacidad usando los endpoints de admin.
     */
    public function resume(string $capacityId): void
    {
        // /v1.0/myorg/admin/capacities/{id}/resume
        $this->api('POST', "admin/capacities/{$capacityId}/resume");
    }

    /**
     * Importa/actualiza capacidades en DB desde myorg.
     * @return array{created:int, updated:int}
     */
    public function importCapacities(): array
    {
        $rows    = $this->listCapacities();
        $created = 0;
        $updated = 0;

        foreach ($rows as $r) {
            $uuid = (string) ($r['id'] ?? '');
            if ($uuid === '') {
                continue;
            }

            $display = (string) ($r['displayName'] ?? $r['name'] ?? $uuid);
            $sku     = (string) ($r['sku'] ?? ($r['administration']['sku'] ?? ''));
            $region  = (string) ($r['region'] ?? '');
            $state   = (string) ($r['state'] ?? ''); // Active | Suspended
            $type    = (string) ($r['type'] ?? 'Fabric');

            // PPU no soporta pause/resume
            $supportsPause = ! str_contains(strtoupper($sku), 'PPU');

            /** @var Capacity|null $cap */
            $cap = Capacity::query()->where('capacity_uuid', $uuid)->first();

            $payload = [
                'capacity_uuid'        => $uuid,
                'display_name'         => $display,
                'sku'                  => $sku,
                'type'                 => $type,
                'region'               => $region,
                'supports_pause'       => $supportsPause,
                'is_active'            => strtoupper($state) === 'ACTIVE',
                'last_state_change_at' => null,
            ];

            if ($cap === null) {
                $cap = Capacity::query()->create($payload);
                $cap->activity()->create([
                    'last_seen_at'     => null,
                    'hits_last_window' => 0,
                ]);
                $created++;
            } else {
                $cap->fill($payload);
                $cap->save();

                if ($cap->activity()->doesntExist()) {
                    $cap->activity()->create([]);
                }
                $updated++;
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }

    /**
     * Marca actividad de una capacidad.
     */
    public function markActivity(Capacity $capacity): void
    {
        $now = Carbon::now();
        $activity = $capacity->activity()->firstOrCreate([]);
        $activity->last_seen_at = $now;
        $activity->hits_last_window = (int) ($activity->hits_last_window ?? 0) + 1;
        $activity->save();
    }
}
