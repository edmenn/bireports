<?php

namespace App\Services;

use App\Models\Configuracion;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PowerBiService
{
    private Client $http;
    private Configuracion $config;
    private string $base = 'https://api.powerbi.com/v1.0/myorg';

    public function __construct()
    {
        $this->http   = new Client(['timeout' => 30]);
        $this->config = Configuracion::firstOrFail(); // fila única
    }

    /* ================== Auth ================== */

    /** Access token (Client Credentials / Service Principal). */
    public function getAccessToken(): string
    {
        $tenant = trim((string) $this->config->powerbi_tenant_id);
        $url    = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";

        $resp = $this->http->post($url, [
            'form_params' => [
                'client_id'     => $this->config->powerbi_client_id,
                'client_secret' => $this->config->powerbi_client_secret,
                'scope'         => $this->config->powerbi_scope ?: 'https://analysis.windows.net/powerbi/api/.default',
                'grant_type'    => 'client_credentials',
            ],
            'headers' => ['Accept' => 'application/json'],
        ]);

        $data = json_decode((string) $resp->getBody(), true);
        if (!isset($data['access_token'])) {
            Log::error('Azure token sin access_token', ['response' => $data]);
            throw new RuntimeException('No se recibió access_token de Azure.');
        }
        return $data['access_token'];
    }

    private function authHeaders(string $token): array
    {
        return ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json', 'Content-Type' => 'application/json'];
    }

    /* ================== Diagnóstico ================== */

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
            $body = (string) $e->getResponse()->getBody();
            Log::error('PowerBI ping 4xx', ['status' => $e->getResponse()->getStatusCode(), 'body' => $body]);
            throw $e;
        }
    }

    /* ================== Lectura (Workspaces/Reports/Dashboards) ================== */

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

    /* ================== GenerateToken universal (report|dashboard) ================== */

    /**
     * Genera embed token para report o dashboard. Soporta:
     * - cross-workspace (targetWorkspaces)
     * - RLS (identities) si pasás datasetId + rlsUser/rlsRoles.
     */
    public function generateEmbedToken(
        string $type,                  // 'report' | 'dashboard'
        string $id,                    // reportId o dashboardId
        ?string $workspaceId = null,   // workspace donde está el item (opcional)
        ?string $datasetId   = null,   // dataset explícito para reportes (opcional)
        ?string $rlsUser     = null,   // opcional
        array $rlsRoles      = []      // opcional
    ): array {
        $token = $this->getAccessToken();
        $H     = $this->authHeaders($token);

        // 1) Info del item para embedUrl (y dataset si es report)
        $infoUrl = $type === 'dashboard'
            ? ($workspaceId ? "{$this->base}/groups/{$workspaceId}/dashboards/{$id}" : "{$this->base}/dashboards/{$id}")
            : ($workspaceId ? "{$this->base}/groups/{$workspaceId}/reports/{$id}"    : "{$this->base}/reports/{$id}");

        try {
            $infoResp = $this->http->get($infoUrl, ['headers' => $H]);
        } catch (ClientException $e) {
            $this->logHttpError("GET item info: {$infoUrl}", $e);
            throw $e;
        }

        $info      = json_decode((string) $infoResp->getBody(), true);
        $embedUrl  = $info['embedUrl'] ?? '';
        $reportDs  = $type === 'report' ? ($info['datasetId'] ?? '') : '';
        $dsWsId    = $type === 'report' ? ($info['datasetWorkspaceId'] ?? $workspaceId) : $workspaceId;
        $finalDsId = $datasetId ?: $reportDs;

        // 2) Payload GenerateToken
        $payload = [
            'accessLevel' => 'View',
            'allowSaveAs' => false,
        ];

        if ($type === 'report') {
            $payload['reports'] = [['id' => $id]];
            if ($finalDsId) {
                $payload['datasets'] = [['id' => $finalDsId]];
            }
            $tw = [];
            if ($workspaceId) $tw[] = ['id' => $workspaceId];
            if ($dsWsId && $dsWsId !== $workspaceId) $tw[] = ['id' => $dsWsId];
            if ($tw) $payload['targetWorkspaces'] = $tw;

            // RLS
            if ($finalDsId && ($rlsUser || !empty($rlsRoles))) {
                $ident = [
                    'username' => $rlsUser ?: 'anonymous@local',
                    'datasets' => [['id' => $finalDsId]],
                ];
                if (!empty($rlsRoles)) $ident['roles'] = array_values(array_unique($rlsRoles));
                $payload['identities'] = [$ident];
            }
        } else {
            // dashboard
            $payload['dashboards'] = [['id' => $id]];
            if ($workspaceId) $payload['targetWorkspaces'] = [['id' => $workspaceId]];
        }

        // 3) POST GenerateToken
        try {
            $tokResp = $this->http->post("{$this->base}/GenerateToken", [
                'headers' => $H,
                'json'    => $payload,
            ]);
        } catch (ClientException $e) {
            $this->logHttpError('POST /GenerateToken', $e, ['payload' => $payload]);
            throw $e;
        }

        $tok = json_decode((string) $tokResp->getBody(), true);
        return [
            'type'       => $type,
            'id'         => $id,
            'embedUrl'   => $embedUrl,
            'embedToken' => $tok['token'] ?? '',
            'expiration' => $tok['expiration'] ?? '',
        ];
    }

    /* ================== Helpers ================== */

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
