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
        /** @var Configuracion|null $cfg */
        $cfg = Configuracion::query()->first();

        // Si preferís, podés forzar que exista:
        // $cfg = Configuracion::query()->firstOrFail();

        $this->tenantId     = trim((string) ($cfg->powerbi_tenant_id     ?? ''));
        $this->clientId     = trim((string) ($cfg->powerbi_client_id     ?? ''));
        $this->clientSecret = trim((string) ($cfg->powerbi_client_secret ?? ''));

        if ($this->tenantId === '' || $this->clientId === '' || $this->clientSecret === '') {
            throw new RuntimeException('Faltan credenciales de Power BI en Configuración (tenant/client/secret).');
        }

        $this->http = new Client([
            'timeout'     => 30,
            'http_errors' => false, // manejamos manualmente
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
                'scope'         => 'https://analysis.windows.net/powerbi/api/.default',
            ],
        ]);

        $code = $resp->getStatusCode();
        if ($code !== 200) {
            Log::error('PowerBi OAuth failed', [
                'code' => $code,
                'body' => (string) $resp->getBody(),
            ]);
            throw new RuntimeException("PowerBi OAuth failed: HTTP {$code}");
        }

        $json  = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $token = (string) ($json['access_token'] ?? '');
        if ($token === '') {
            throw new RuntimeException('PowerBi OAuth failed: empty token');
        }

        return $token;
    }

    /**
     * Llamada genérica a la API con reintentos exponenciales para 429/5xx.
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
                $resp = $this->http->request(
                    $method,
                    rtrim($this->base, '/') . '/' . ltrim($path, '/'),
                    $opts
                );

                $code = $resp->getStatusCode();

                if ($code >= 200 && $code < 300) {
                    $body = (string) $resp->getBody();
                    return $body !== '' ? json_decode($body, true) : null;
                }

                if (in_array($code, [429, 500, 502, 503, 504], true) && $attempts < 4) {
                    sleep(2 ** $attempts); // 2,4,8
                    continue;
                }

                Log::error('PowerBi API error', [
                    'method' => $method,
                    'path'   => $path,
                    'code'   => $code,
                    'body'   => (string) $resp->getBody(),
                ]);
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
     * @return array<int, array<string,mixed>>
     */
    public function listCapacities(): array
    {
        $json = $this->api('GET', 'capacities');
        return (array) ($json['value'] ?? []);
    }

    /**
     * @return array<string,mixed>
     */
    public function getCapacity(string $capacityId): array
    {
        return (array) $this->api('GET', "capacities/{$capacityId}");
    }

    /** Pausar (suspend) una capacidad. */
    public function pause(string $capacityId): void
    {
        $this->api('POST', "capacities/{$capacityId}/suspend");
    }

    /** Reanudar (resume) una capacidad. */
    public function resume(string $capacityId): void
    {
        $this->api('POST', "capacities/{$capacityId}/resume");
    }

    /**
     * Importa/actualiza capacidades en la BD.
     *
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
     * Marca actividad cuando alguien usa la capacidad (abrir embed, etc.).
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
