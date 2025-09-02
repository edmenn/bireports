<?php

namespace App\Services;

use App\Models\Configuracion;
use GuzzleHttp\Client;
use RuntimeException;

class MicrosoftGraphService
{
    private Client $http;
    private Configuracion $config;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30]);
        $this->config = Configuracion::query()->first() ?? new Configuracion();
    }

    public function getAccessToken(): string
    {
        $tenant = trim((string) $this->config->powerbi_tenant_id);
        $client = trim((string) $this->config->powerbi_client_id);
        $secret = trim((string) $this->config->powerbi_client_secret);

        if ($tenant === '' || $client === '' || $secret === '') {
            throw new RuntimeException('Faltan credenciales de Microsoft (tenant/client/secret) en Configuraciones.');
        }

        $url = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";
        $resp = $this->http->post($url, [
            'form_params' => [
                'client_id'     => $client,
                'client_secret' => $secret,
                'grant_type'    => 'client_credentials',
                'scope'         => 'https://graph.microsoft.com/.default',
            ],
            'headers' => ['Accept' => 'application/json'],
        ]);

        $data = json_decode((string) $resp->getBody(), true) ?: [];
        $token = (string) ($data['access_token'] ?? '');
        if ($token === '') {
            throw new RuntimeException('No se recibió access_token de Microsoft Graph.');
        }

        return $token;
    }

    /**
     * Lista usuarios desde Microsoft Graph.
     * @return array{items: list<array<string,mixed>>, next: string|null}
     */
    public function listUsers(?string $query = null, ?string $next = null): array
    {
        $token = $this->getAccessToken();
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];

        if ($next) {
            $url = $next; // next incluye query completa
        } else {
            $base = 'https://graph.microsoft.com/v1.0/users';
            $params = [
                '$select' => 'id,displayName,mail,userPrincipalName,givenName,surname',
                '$top'    => '25',
            ];
            if ($query) {
                // Filtrar por startswith en displayName o userPrincipalName
                $filter = "startsWith(displayName,'" . addslashes($query) . "') or startsWith(userPrincipalName,'" . addslashes($query) . "')";
                $params['$filter'] = $filter;
            }
            $url = $base . '?' . http_build_query($params);
        }

        $resp = $this->http->get($url, ['headers' => $headers]);
        $data = json_decode((string) $resp->getBody(), true) ?: [];
        $items = (array) ($data['value'] ?? []);
        $nextLink = isset($data['@odata.nextLink']) ? (string) $data['@odata.nextLink'] : null;
        return ['items' => $items, 'next' => $nextLink];
    }
}

