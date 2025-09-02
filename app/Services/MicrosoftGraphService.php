<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Configuracion;
use GuzzleHttp\Client;
use RuntimeException;

// Funciones nativas (evita P1010 de Intelephense)
use function trim;
use function json_decode;
use function http_build_query;
use function addslashes;
use function implode;
use function strtolower;

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

        $data  = json_decode((string) $resp->getBody(), true) ?: [];
        $token = (string) ($data['access_token'] ?? '');
        if ($token === '') {
            throw new RuntimeException('No se recibió access_token de Microsoft Graph.');
        }

        return $token;
    }

    /**
     * Lista usuarios desde Microsoft Graph (paginado).
     *
     * @param string|null $query      Prefijo para startsWith(displayName|userPrincipalName)
     * @param string|null $next       @odata.nextLink (URL completa) para paginar
     * @param bool        $onlyActive Filtra accountEnabled eq true
     * @return array{items: list<array<string,mixed>>, next: string|null}
     */
    public function listUsers(?string $query = null, ?string $next = null, bool $onlyActive = false): array
    {
        $token = $this->getAccessToken();
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        if ($next) {
            // $next es una URL completa devuelta por Graph (incluye query)
            $url = $next;
        } else {
            $base = 'https://graph.microsoft.com/v1.0/users';
            $params = [
                // Traemos alias para detectar duplicados/alias
                '$select' => 'id,displayName,mail,userPrincipalName,givenName,surname,accountEnabled,otherMails,proxyAddresses',
                '$top'    => '25',
            ];

            $filters = [];
            if ($query) {
                $safe = addslashes($query);
                $filters[] = "(startsWith(displayName,'{$safe}') or startsWith(userPrincipalName,'{$safe}'))";
            }
            if ($onlyActive) {
                $filters[] = '(accountEnabled eq true)';
            }
            if ($filters) {
                $params['$filter'] = implode(' and ', $filters);
            }

            $url = $base . '?' . http_build_query($params);
        }

        $resp  = $this->http->get($url, ['headers' => $headers]);
        $data  = json_decode((string) $resp->getBody(), true) ?: [];
        $items = (array) ($data['value'] ?? []);
        $nextLink = isset($data['@odata.nextLink']) ? (string) $data['@odata.nextLink'] : null;

        return ['items' => $items, 'next' => $nextLink];
    }

    /**
     * Busca un usuario por UPN, mail o proxyAddresses (cualquier alias).
     * Devuelve el primer match o null. No lanza excepción (seguro para usar en listeners de login).
     *
     * @return array<string,mixed>|null
     */
    public function findUserByAnyAddress(string $email): ?array
    {
        $token = $this->getAccessToken();

        $email = strtolower(trim($email));

        // Coincidencia exacta por:
        //  - userPrincipalName
        //  - mail
        //  - proxyAddresses (alias) como 'smtp:alias@dominio'
        $filter = sprintf(
            "userPrincipalName eq '%s' or mail eq '%s' or proxyAddresses/any(p:p eq 'smtp:%s')",
            addslashes($email),
            addslashes($email),
            addslashes($email)
        );

        $params = [
            '$top'    => '1',
            '$select' => 'id,displayName,mail,userPrincipalName,otherMails,proxyAddresses,accountEnabled',
            '$filter' => $filter,
        ];

        $url = 'https://graph.microsoft.com/v1.0/users?' . http_build_query($params);

        $resp = $this->http->get($url, [
            'headers'     => [
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ],
            'http_errors' => false, // no queremos romper el login por un 4xx/5xx
            'timeout'     => 30,
        ]);

        $status = $resp->getStatusCode();
        if ($status < 200 || $status >= 300) {
            return null;
        }

        $data  = json_decode((string) $resp->getBody(), true) ?: [];
        $items = (array) ($data['value'] ?? []);
        return $items[0] ?? null;
    }
}
