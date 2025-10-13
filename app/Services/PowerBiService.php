<?php

namespace App\Services;

use App\Models\Configuration;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class PowerBiService
{
    private Client $http;
    private Configuration $config;
    private string $base = 'https://api.powerbi.com/v1.0/myorg';

    public function __construct()
    {
        $this->http   = new Client(['timeout' => 30]);
        // No forzar existencia aquí; controlamos en getAccessToken
        $this->config = Configuration::query()->first() ?? new Configuration();
    }

    /* =========================================================
     * Auth
     * ========================================================= */

    /** Access token (Client Credentials / Service Principal). */
    public function getAccessToken(): string
    {
        $tenant = trim((string) $this->config->powerbi_tenant_id);
        $client = trim((string) $this->config->powerbi_client_id);
        $secret = trim((string) $this->config->powerbi_client_secret);

        if ($tenant === '' || $client === '' || $secret === '') {
            throw new RuntimeException('Power BI credentials missing (tenant/client/secret) en configurations.');
        }
        // Cache rápido del token por tenant+client
        $cacheKey = 'pbi:token:' . sha1($tenant . '|' . $client);
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $url    = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";

        $resp = $this->http->post($url, [
            'form_params' => [
                'client_id'     => $this->config->powerbi_client_id,
                'client_secret' => $this->config->powerbi_client_secret,
                'scope'         => env('POWERBI_SCOPE', 'https://analysis.windows.net/powerbi/api/.default'),
                'grant_type'    => 'client_credentials',
            ],
            'headers' => ['Accept' => 'application/json'],
        ]);

        $data = json_decode((string) $resp->getBody(), true);
        if (!isset($data['access_token'])) {
            Log::error('Azure token sin access_token', ['response' => $data]);
            throw new RuntimeException('No se recibió access_token de Azure.');
        }
        // Guardar en cache con margen de 60s
        $ttl = (int) ($data['expires_in'] ?? 3600);
        $ttl = max(60, $ttl - 60);
        \Illuminate\Support\Facades\Cache::put($cacheKey, $data['access_token'], now()->addSeconds($ttl));
        return $data['access_token'];
    }

    private function authHeaders(string $token): array
    {
        return [
            'Authorization' => "Bearer {$token}",
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /* =========================================================
     * Diagnóstico
     * ========================================================= */

    /** Ping rápido contra la API para validar credenciales. */
    public function ping(): array
    {
        $token = $this->getAccessToken();
        try {
            $resp = $this->http->get($this->base . '/availableFeatures', [
                'headers' => $this->authHeaders($token),
            ]);
            return [
                'status' => $resp->getStatusCode(),
                'body'   => json_decode((string) $resp->getBody(), true),
            ];
        } catch (ClientException $e) {
            $this->logHttpError('GET /availableFeatures', $e);
            throw $e;
        }
    }

    /* =========================================================
     * Lectura (Workspaces / Reports / Dashboards)
     * ========================================================= */

    /** @return array<int, array<string,mixed>> */
    public function listWorkspaces(): array
    {
        $token = $this->getAccessToken();
        try {
            $resp = $this->http->get($this->base . '/groups?$top=5000', [
                'headers' => $this->authHeaders($token),
            ]);
            $data = json_decode((string) $resp->getBody(), true);
            return $data['value'] ?? [];
        } catch (ClientException $e) {
            $this->logHttpError('GET /groups', $e);
            throw $e;
        }
    }

    /** @return array<int, array<string,mixed>> */
    public function listReports(string $workspaceId): array
    {
        $token = $this->getAccessToken();
        try {
            $resp = $this->http->get("{$this->base}/groups/{$workspaceId}/reports?\$top=5000", [
                'headers' => $this->authHeaders($token),
            ]);
            $data = json_decode((string) $resp->getBody(), true);
            return $data['value'] ?? [];
        } catch (ClientException $e) {
            $this->logHttpError("GET /groups/{$workspaceId}/reports", $e);
            throw $e;
        }
    }

    /** @return array<int, array<string,mixed>> */
    public function listDashboards(string $workspaceId): array
    {
        $token = $this->getAccessToken();
        try {
            $resp = $this->http->get("{$this->base}/groups/{$workspaceId}/dashboards?\$top=5000", [
                'headers' => $this->authHeaders($token),
            ]);
            $data = json_decode((string) $resp->getBody(), true);
            return $data['value'] ?? [];
        } catch (ClientException $e) {
            $this->logHttpError("GET /groups/{$workspaceId}/dashboards", $e);
            throw $e;
        }
    }

    /** @return array<int, array<string,mixed>> */
    public function listDatasets(string $workspaceId): array
    {
        $token = $this->getAccessToken();
        try {
            $resp = $this->http->get("{$this->base}/groups/{$workspaceId}/datasets?\$top=5000", [
                'headers' => $this->authHeaders($token),
            ]);
            $data = json_decode((string) $resp->getBody(), true);
            return $data['value'] ?? [];
        } catch (ClientException $e) {
            $this->logHttpError("GET /groups/{$workspaceId}/datasets", $e);
            throw $e;
        }
    }

    public function getDataset(string $workspaceId, string $datasetId): array
    {
        $token = $this->getAccessToken();
        try {
            $resp = $this->http->get("{$this->base}/groups/{$workspaceId}/datasets/{$datasetId}", [
                'headers' => $this->authHeaders($token),
            ]);
            return json_decode((string) $resp->getBody(), true) ?? [];
        } catch (ClientException $e) {
            $this->logHttpError("GET /groups/{$workspaceId}/datasets/{$datasetId}", $e);
            throw $e;
        }
    }

    public function refreshDataset(string $workspaceId, string $datasetId): array
    {
        $token = $this->getAccessToken();
        try {
            $resp = $this->http->post("{$this->base}/groups/{$workspaceId}/datasets/{$datasetId}/refreshes", [
                'headers' => $this->authHeaders($token),
                'json'    => new \stdClass(),
            ]);
            return [
                'status' => $resp->getStatusCode(),
            ];
        } catch (ClientException $e) {
            $this->logHttpError("POST /groups/{$workspaceId}/datasets/{$datasetId}/refreshes", $e);
            throw $e;
        }
    }

    /** @return array<int, array<string,mixed>> */
    public function getDatasetRefreshHistory(string $workspaceId, string $datasetId): array
    {
        $token = $this->getAccessToken();
        try {
            $resp = $this->http->get("{$this->base}/groups/{$workspaceId}/datasets/{$datasetId}/refreshes", [
                'headers' => $this->authHeaders($token),
            ]);
            $data = json_decode((string) $resp->getBody(), true);
            return $data['value'] ?? [];
        } catch (ClientException $e) {
            $this->logHttpError("GET /groups/{$workspaceId}/datasets/{$datasetId}/refreshes", $e);
            throw $e;
        }
    }

    public function getDatasetRefreshSchedule(string $workspaceId, string $datasetId): array
    {
        $token = $this->getAccessToken();
        try {
            $resp = $this->http->get("{$this->base}/groups/{$workspaceId}/datasets/{$datasetId}/refreshSchedule", [
                'headers' => $this->authHeaders($token),
            ]);
            $data = json_decode((string) $resp->getBody(), true) ?? [];
            // Normaliza: para esta API, el schedule viene en el nivel raíz (sin 'value').
            // La UI espera ['value' => {...}] para unificar con otros endpoints.
            return isset($data['value']) ? $data : ['value' => $data];
        } catch (ClientException $e) {
            $this->logHttpError("GET /groups/{$workspaceId}/datasets/{$datasetId}/refreshSchedule", $e);
            throw $e;
        }
    }

    // updateDatasetRefreshSchedule eliminado: mantenemos solo lectura del schedule de Fabric.

    /* =========================================================
     * Info puntual
     * ========================================================= */

    public function getReportInfo(string $workspaceId, string $reportId): array
    {
        $token = $this->getAccessToken();
        $resp  = $this->http->get("{$this->base}/groups/{$workspaceId}/reports/{$reportId}", [
            'headers' => $this->authHeaders($token),
        ]);
        return json_decode((string) $resp->getBody(), true) ?? [];
    }

    /* =========================================================
     * GenerateToken: REPORT (con RLS/cross-workspace)
     * ========================================================= */

    /**
     * Genera un Embed Token universal para REPORT.
     * - Soporta dataset explícito y targetWorkspaces (cross-workspace).
     * - Soporta RLS opcional (identities).
     *
     * Devuelve: ['embedUrl' => string, 'token' => string, 'expiration' => string|null, ...]
     */
    public function generateReportEmbedToken(
        string $workspaceId,
        string $reportId,
        ?string $datasetId = null,
        ?string $rlsUser   = null,
        array $rlsRoles    = []
    ): array {
        $access = $this->getAccessToken();
        $H      = $this->authHeaders($access);

        // 1) Info del reporte (embedUrl + datasetId si no vino)
        $info               = $this->getReportInfo($workspaceId, $reportId);
        $embedUrl           = $info['embedUrl'] ?? '';
        $reportDatasetId    = $info['datasetId'] ?? null;
        $datasetWorkspaceId = $info['datasetWorkspaceId'] ?? $workspaceId;

        if (!$embedUrl) {
            throw new RuntimeException('Power BI no devolvió embedUrl del reporte.');
        }

        $finalDatasetId = $datasetId ?: $reportDatasetId;

        // 2) Payload GenerateToken
        $payload = [
            'accessLevel' => 'View',
            'allowSaveAs' => false,
            'reports'     => [['id' => $reportId]],
        ];

        if ($finalDatasetId) {
            $payload['datasets'] = [['id' => $finalDatasetId]];
        }

        // cross-workspace (reporte en un WS y dataset en otro)
        $tw = [];
        if ($workspaceId) {
            $tw[] = ['id' => $workspaceId];
        }
        if ($datasetWorkspaceId && $datasetWorkspaceId !== $workspaceId) {
            $tw[] = ['id' => $datasetWorkspaceId];
        }
        if (!empty($tw)) {
            $payload['targetWorkspaces'] = $tw;
        }

        // RLS opcional
        $roles = array_values(array_filter(array_unique(array_map('trim', $rlsRoles))));
        if ($finalDatasetId && ($rlsUser || !empty($roles))) {
            $ident = [
                'username' => $rlsUser ?: 'anonymous@local',
                'datasets' => [['id' => $finalDatasetId]],
            ];
            if (!empty($roles)) {
                $ident['roles'] = $roles;
            }
            $payload['identities'] = [$ident];
        }

        // 3) GenerateToken (modern endpoint + legacy fallback)
        $tok = [];
        try {
            $tokResp = $this->http->post("{$this->base}/GenerateToken", [
                'headers' => $H,
                'json'    => $payload,
            ]);
            $tok = json_decode((string) $tokResp->getBody(), true) ?? [];
            if (empty($tok['token'])) {
                Log::warning('GenerateToken sin token en endpoint moderno, se intentará fallback', [
                    'payload'     => $payload,
                    'workspaceId' => $workspaceId,
                    'reportId'    => $reportId,
                ]);
                throw new RuntimeException('No se obtuvo token en endpoint moderno.');
            }
        } catch (ClientException|RuntimeException $modernException) {
            $tok = $this->generateReportEmbedTokenLegacy(
                workspaceId: $workspaceId,
                reportId: $reportId,
                headers: $H,
                datasetId: $finalDatasetId,
                rlsUser: $rlsUser,
                rlsRoles: $roles,
                previous: $modernException,
                modernPayload: $payload
            );
        }

        return [
            'embedUrl'   => $embedUrl,
            'token'      => $tok['token'],
            'expiration' => $tok['expiration'] ?? null,
            'reportId'   => $reportId,
            'workspaceId'=> $workspaceId,
            'datasetId'  => $finalDatasetId,
        ];
    }

    private function generateReportEmbedTokenLegacy(
        string $workspaceId,
        string $reportId,
        array $headers,
        ?string $datasetId,
        ?string $rlsUser,
        array $rlsRoles,
        Throwable $previous,
        array $modernPayload
    ): array {
        if ($workspaceId === '' || $reportId === '') {
            throw new RuntimeException('Faltan identificadores para el fallback de GenerateToken.', 0, $previous);
        }

        Log::warning('PowerBI GenerateToken moderno falló, se intentará endpoint legacy', [
            'workspaceId' => $workspaceId,
            'reportId'    => $reportId,
            'datasetId'   => $datasetId,
            'previous'    => [
                'type'    => get_class($previous),
                'message' => $previous->getMessage(),
                'status'  => $previous instanceof ClientException ? ($previous->getResponse()?->getStatusCode() ?? null) : null,
            ],
            'modern_payload' => $modernPayload,
        ]);

        $payload = [
            'accessLevel' => 'View',
        ];

        if ($datasetId && ($rlsUser || !empty($rlsRoles))) {
            $identity = [
                'username' => $rlsUser ?: 'anonymous@local',
                'datasets' => [['id' => $datasetId]],
            ];
            if (!empty($rlsRoles)) {
                $identity['roles'] = $rlsRoles;
            }
            $payload['identities'] = [$identity];
        }

        try {
            $resp = $this->http->post("{$this->base}/groups/{$workspaceId}/reports/{$reportId}/GenerateToken", [
                'headers' => $headers,
                'json'    => $payload,
            ]);
            $tok = json_decode((string) $resp->getBody(), true) ?? [];
        } catch (ClientException $legacyException) {
            $this->logHttpError("POST /groups/{$workspaceId}/reports/{$reportId}/GenerateToken (legacy)", $legacyException);
            throw new RuntimeException('No se pudo obtener el embed token (fallback legacy).', 0, $legacyException);
        }

        if (empty($tok['token'])) {
            Log::error('GenerateToken (legacy) sin token', [
                'payload'     => $payload,
                'workspaceId' => $workspaceId,
                'reportId'    => $reportId,
                'response'    => $tok,
            ]);
            throw new RuntimeException('No se pudo obtener el embed token (fallback legacy sin token).', 0, $previous);
        }

        Log::info('PowerBI GenerateToken legacy fallback exitoso', [
            'workspaceId' => $workspaceId,
            'reportId'    => $reportId,
        ]);

        return $tok;
    }
    /* =========================================================
     * Helpers
     * ========================================================= */

    private function logHttpError(string $where, ClientException $e, array $extra = []): void
    {
        $status = $e->getResponse()->getStatusCode() ?? 0;
        $body   = (string) $e->getResponse()->getBody();
        Log::error("PowerBI {$where} error", array_merge([
            'status' => $status,
            'body'   => $body,
        ], $extra));
    }
}

