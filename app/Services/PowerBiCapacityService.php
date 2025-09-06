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
    private string $baseMyOrg = 'https://api.powerbi.com/v1.0/myorg';

    private string $tenantId;
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        // Leemos SIEMPRE desde configuraciones (no del .env),
        // para gestionar las credenciales centralizadas por instancia.
        $cfg = Configuracion::query()->first();
        if (!$cfg) {
            throw new RuntimeException('No existe fila en "configuraciones". Abre /configuraciones para crearla.');
        }

        $this->tenantId     = (string) ($cfg->powerbi_tenant_id ?? '');
        $this->clientId     = (string) ($cfg->powerbi_client_id ?? '');
        $this->clientSecret = (string) ($cfg->powerbi_client_secret ?? '');

        if ($this->tenantId === '' || $this->clientId === '' || $this->clientSecret === '') {
            throw new RuntimeException('Power BI credentials missing (tenant/client/secret) en configuraciones.');
        }

        $this->http = new Client([
            'timeout'     => 30,
            'http_errors' => false,
        ]);
    }

    /* ================== TOKENS ================== */

    private function getPbiToken(): string
    {
        return $this->issueToken('https://analysis.windows.net/powerbi/api/.default');
    }

    private function getArmToken(): string
    {
        return $this->issueToken('https://management.azure.com/.default');
    }

    private function issueToken(string $scope): string
    {
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";

        $resp = $this->http->post($url, [
            'form_params' => [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type'    => 'client_credentials',
                'scope'         => $scope,
            ],
        ]);

        if ($resp->getStatusCode() !== 200) {
            Log::error('OAuth failed', [
                'scope' => $scope,
                'code'  => $resp->getStatusCode(),
                'body'  => (string) $resp->getBody(),
            ]);
            throw new RuntimeException('OAuth failed for scope ' . $scope);
        }

        $json  = json_decode((string) $resp->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $token = (string) ($json['access_token'] ?? '');
        if ($token === '') {
            throw new RuntimeException('OAuth returned empty token for ' . $scope);
        }
        return $token;
    }

    /* ================== HTTP WRAPPERS ================== */

    /**
     * Llama a Power BI (myorg).
     * @return array<string,mixed>|null
     */
    private function pbiApi(string $method, string $path, array $opts = []): ?array
    {
        $token = $this->getPbiToken();
        $opts['headers']['Authorization'] = "Bearer {$token}";
        $opts['headers']['Content-Type']  = $opts['headers']['Content-Type'] ?? 'application/json';

        $attempts = 0;

        while (true) {
            $attempts++;
            try {
                $resp = $this->http->request(
                    $method,
                    rtrim($this->baseMyOrg, '/') . '/' . ltrim($path, '/'),
                    $opts
                );

                $code = $resp->getStatusCode();

                if ($code >= 200 && $code < 300) {
                    $body = (string) $resp->getBody();
                    return $body !== '' ? json_decode($body, true) : null;
                }

                if (in_array($code, [429, 500, 502, 503, 504], true) && $attempts < 4) {
                    sleep(2 ** $attempts);
                    continue;
                }

                Log::error('PowerBi myorg API error', [
                    'method' => $method,
                    'path'   => $path,
                    'code'   => $code,
                    'body'   => (string) $resp->getBody(),
                ]);
                throw new RuntimeException("PowerBi myorg API error {$code}");
            } catch (GuzzleException $e) {
                if ($attempts < 4) {
                    sleep(2 ** $attempts);
                    continue;
                }
                Log::error('PowerBi myorg exception', ['path' => $path, 'ex' => $e->getMessage()]);
                throw new RuntimeException('PowerBi myorg exception: ' . $e->getMessage(), 0, $e);
            }
        }
    }

    /**
     * Llama a ARM.
     * Devuelve el código HTTP y el cuerpo parseado si aplica.
     * @return array{code:int, json:array<string,mixed>|null, raw:string}
     */
    private function armRequest(string $method, string $url, array $opts = []): array
    {
        $token = $this->getArmToken();
        $opts['headers']['Authorization'] = "Bearer {$token}";
        $opts['headers']['Content-Type']  = $opts['headers']['Content-Type'] ?? 'application/json';

        try {
            $resp = $this->http->request($method, $url, $opts);
            $code = $resp->getStatusCode();
            $raw  = (string) $resp->getBody();
            $json = null;
            if ($raw !== '') {
                try {
                    $json = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                } catch (\Throwable $e) {
                    // cuerpo no JSON -> ignoramos parseo
                }
            }
            return ['code' => $code, 'json' => is_array($json) ? $json : null, 'raw' => $raw];
        } catch (GuzzleException $e) {
            Log::error('ARM request exception', ['url' => $url, 'ex' => $e->getMessage()]);
            throw new RuntimeException('ARM request exception: ' . $e->getMessage(), 0, $e);
        }
    }

    /* ================== Capacidades (listar/importar) ================== */

    /**
     * @return array<int, array<string,mixed>>
     */
    public function listCapacities(): array
    {
        $json = $this->pbiApi('GET', 'capacities');
        return (array) ($json['value'] ?? []);
    }

    /**
     * @return array<string,mixed>
     */
    public function getCapacity(string $capacityId): array
    {
        return (array) $this->pbiApi('GET', "capacities/{$capacityId}");
    }

    /**
     * Importa/actualiza capacidades desde myorg en BD.
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
                $cap->fill($payload)->save();
                if ($cap->activity()->doesntExist()) {
                    $cap->activity()->create([]);
                }
                $updated++;
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }

    public function markActivity(Capacity $capacity): void
    {
        $now = Carbon::now();
        $activity = $capacity->activity()->firstOrCreate([]);
        $activity->last_seen_at = $now;
        $activity->hits_last_window = (int) ($activity->hits_last_window ?? 0) + 1;
        $activity->save();
    }

    /* ================== Pausa / Reanudar (ARM) ================== */

    /**
     * Detecta provider ARM según SKU.
     */
    private function detectProviderBySku(string $sku): string
    {
        $u = strtoupper($sku);
        if (str_starts_with($u, 'F')) {
            return 'Microsoft.Fabric';
        }
        if (str_starts_with($u, 'A') || str_starts_with($u, 'EM')) {
            return 'Microsoft.PowerBIDedicated';
        }
        // por defecto Fabric
        return 'Microsoft.Fabric';
    }

    /**
     * Devuelve api-version apropiada por provider.
     */
    private function providerApiVersion(string $provider): string
    {
        return $provider === 'Microsoft.Fabric' ? '2023-11-01' : '2021-01-01';
    }

    /**
     * Construye la URL de ARM para action (suspend/resume) sobre la capacidad.
     */
    private function buildArmActionUrl(Capacity $cap, string $action): string
    {
        /** @var Configuracion|null $cfg */
        $cfg = Configuracion::query()->first();
        if (!$cfg) {
            throw new RuntimeException('No existe fila de configuraciones.');
        }

        $subId = (string) ($cfg->azure_subscription_id ?? '');
        $rg    = (string) ($cfg->azure_resource_group ?? '');

        if ($subId === '' || $rg === '') {
            throw new RuntimeException('Falta azure_subscription_id o azure_resource_group en configuraciones.');
        }

        $provider = $this->detectProviderBySku((string) $cap->sku);
        $api      = $this->providerApiVersion($provider);

        // ARM usa el nombre del recurso, no el display ni el GUID de myorg.
        $name = trim((string) ($cap->arm_resource_name ?: $cap->display_name));
        if ($name === '') {
            throw new RuntimeException('arm_resource_name/display_name vacío en la capacidad.');
        }

        // Acciones:
        // POST .../capacities/{name}/suspend?api-version=...
        // POST .../capacities/{name}/resume?api-version=...
        return sprintf(
            'https://management.azure.com/subscriptions/%s/resourceGroups/%s/providers/%s/capacities/%s/%s?api-version=%s',
            rawurlencode($subId),
            rawurlencode($rg),
            $provider,
            rawurlencode($name),
            $action,
            $api
        );
    }

    /**
     * Pausa vía ARM.
     */
    public function pause(Capacity $cap): void
    {
        if (!$cap->supports_pause) {
            throw new RuntimeException('Esta capacidad no soporta pausa (SKU PPU u otra restricción).');
        }

        $url = $this->buildArmActionUrl($cap, 'suspend');
        $res = $this->armRequest('POST', $url);

        // ARM suele responder 202 (accepted) o 200/204.
        if (!in_array($res['code'], [200, 202, 204], true)) {
            Log::error('ARM suspend error', ['url' => $url, 'code' => $res['code'], 'raw' => $res['raw']]);
            if ($res['code'] === 404) {
                throw new RuntimeException('404 ARM: nombre de recurso / RG / suscripción / provider incorrecto.');
            }
            if ($res['code'] === 403) {
                throw new RuntimeException('403 ARM: permisos insuficientes (RBAC) en el scope.');
            }
            throw new RuntimeException("ARM suspend HTTP {$res['code']}");
        }
    }

    /**
     * Reanuda vía ARM.
     */
    public function resume(Capacity $cap): void
    {
        $url = $this->buildArmActionUrl($cap, 'resume');
        $res = $this->armRequest('POST', $url);

        if (!in_array($res['code'], [200, 202, 204], true)) {
            Log::error('ARM resume error', ['url' => $url, 'code' => $res['code'], 'raw' => $res['raw']]);
            if ($res['code'] === 404) {
                throw new RuntimeException('404 ARM: nombre de recurso / RG / suscripción / provider incorrecto.');
            }
            if ($res['code'] === 403) {
                throw new RuntimeException('403 ARM: permisos insuficientes (RBAC) en el scope.');
            }
            throw new RuntimeException("ARM resume HTTP {$res['code']}");
        }
    }

    /* ================== Auditoría (Activity Logs) ================== */

    /**
     * Obtiene eventos de Activity Logs (ARM) para una capacidad específica.
     * Filtra por operaciones suspend/resume y por el resourceId de la capacidad.
     *
     * @return array<int, array<string,mixed>>
     */
    public function getCapacityActivityLogs(Capacity $cap, ?Carbon $from = null, ?Carbon $to = null, int $limit = 200): array
    {
        /** @var Configuracion|null $cfg */
        $cfg = Configuracion::query()->first();
        if (!$cfg) {
            throw new RuntimeException('No existe fila de configuraciones.');
        }

        $subId = (string) ($cfg->azure_subscription_id ?? '');
        $rg    = (string) ($cfg->azure_resource_group ?? '');
        if ($subId === '' || $rg === '') {
            throw new RuntimeException('Falta azure_subscription_id o azure_resource_group en configuraciones.');
        }

        $provider = $this->detectProviderBySku((string) $cap->sku); // Microsoft.Fabric | Microsoft.PowerBIDedicated
        $name     = trim((string) ($cap->arm_resource_name ?: $cap->display_name));
        if ($name === '') {
            throw new RuntimeException('arm_resource_name/display_name vacío en la capacidad.');
        }

        $resourceId = sprintf(
            '/subscriptions/%s/resourceGroups/%s/providers/%s/capacities/%s',
            $subId,
            $rg,
            $provider,
            $name
        );

        $from = $from ?: Carbon::now()->subDays(14);
        $to   = $to   ?: Carbon::now();

        // Filtro: por ventana de tiempo, resourceUri y operaciones (suspend/resume) para ambos providers.
        $ops = [
            "{$provider}/capacities/suspend/action",
            "{$provider}/capacities/resume/action",
            // En caso que el SKU detectado no coincida, incluimos ambos providers por compatibilidad
            'Microsoft.Fabric/capacities/suspend/action',
            'Microsoft.Fabric/capacities/resume/action',
            'Microsoft.PowerBIDedicated/capacities/suspend/action',
            'Microsoft.PowerBIDedicated/capacities/resume/action',
        ];

        $opFilter = '(' . implode(' or ', array_map(fn($v) => "operationName/value eq '$v'", array_unique($ops))) . ')';

        $filter = sprintf(
            "eventTimestamp ge '%s' and eventTimestamp le '%s' and resourceUri eq '%s' and %s",
            $from->copy()->utc()->toIso8601String(),
            $to->copy()->utc()->toIso8601String(),
            $resourceId,
            $opFilter
        );

        $url = sprintf(
            'https://management.azure.com/subscriptions/%s/providers/microsoft.insights/eventtypes/management/values?api-version=2015-04-01&$filter=%s',
            rawurlencode($subId),
            rawurlencode($filter)
        );

        $res = $this->armRequest('GET', $url);
        if ($res['code'] < 200 || $res['code'] >= 300) {
            Log::warning('ActivityLogs query failed', ['code' => $res['code'], 'raw' => $res['raw']]);
            return [];
        }

        $value = (array) ($res['json']['value'] ?? []);

        // Ordenar desc por eventTimestamp y recortar al límite
        usort($value, function ($a, $b) {
            $ta = strtotime((string) ($a['eventTimestamp'] ?? '')) ?: 0;
            $tb = strtotime((string) ($b['eventTimestamp'] ?? '')) ?: 0;
            return $tb <=> $ta;
        });

        if ($limit > 0 && count($value) > $limit) {
            $value = array_slice($value, 0, $limit);
        }

        return $value;
    }
}
