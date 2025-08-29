<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Capacity;
use App\Models\Configuracion;
use App\Services\PowerBiCapacityService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class PowerBiDoctor extends Command
{
    protected $signature = 'powerbi:doctor {--show-secrets=0 : Muestra client_id/tenant_id (nunca el secret) para depurar}';
    protected $description = 'Diagnóstico de credenciales, API de Power BI (myorg) y permisos ARM para pausar/reanudar capacidades.';

    /** @var Client */
    private Client $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new Client([
            'timeout'     => 30,
            'http_errors' => false,
        ]);
    }

    public function handle(PowerBiCapacityService $svc): int
    {
        $this->info('== PowerBI Doctor ==');
        $this->line('');

        // 1) Cargar configuraciones
        /** @var Configuracion|null $cfg */
        $cfg = Configuracion::query()->first();
        if (!$cfg) {
            $this->error('No hay fila en "configuraciones". Crea una y completa credenciales y datos de ARM.');
            return self::FAILURE;
        }

        $tenantId     = (string) ($cfg->powerbi_tenant_id ?? '');
        $clientId     = (string) ($cfg->powerbi_client_id ?? '');
        $clientSecret = (string) ($cfg->powerbi_client_secret ?? '');
        $subId        = (string) ($cfg->azure_subscription_id ?? '');
        $rgName       = (string) ($cfg->azure_resource_group ?? '');

        $this->section('A) Configuraciones base');
        $showSecrets = (bool) (int) $this->option('show-secrets');

        $this->table(
            ['Clave', 'Valor'],
            [
                ['powerbi_tenant_id', $showSecrets ? $tenantId : $this->mask($tenantId)],
                ['powerbi_client_id', $showSecrets ? $clientId : $this->mask($clientId)],
                ['powerbi_client_secret', $clientSecret ? '*** (definido)' : '(vacío)'],
                ['azure_subscription_id', $subId ?: '(vacío)'],
                ['azure_resource_group',  $rgName ?: '(vacío)'],
            ]
        );

        if ($tenantId === '' || $clientId === '' || $clientSecret === '') {
            $this->error('Faltan credenciales Power BI (tenant/client/secret).');
            return self::FAILURE;
        }

        // 2) Obtener tokens
        $this->section('B) OAuth Tokens');
        $pbiToken = $this->getToken($tenantId, $clientId, $clientSecret, 'https://analysis.windows.net/powerbi/api/.default', 'PBI');
        if (!$pbiToken) {
            $this->error('No se pudo obtener token PBI. Revisa tenant/client/secret y consentimientos.');
            return self::FAILURE;
        }
        $this->info('✔ Token PBI OK');

        $armToken = null;
        if ($subId !== '' && $rgName !== '') {
            $armToken = $this->getToken($tenantId, $clientId, $clientSecret, 'https://management.azure.com/.default', 'ARM');
            if ($armToken) {
                $this->info('✔ Token ARM OK');
            } else {
                $this->warn('✖ No se pudo obtener token ARM (management.azure.com). Revisa permisos de la app en Entra ID.');
            }
        } else {
            $this->warn('ℹ No hay subscription/resourceGroup en configuraciones. Saltando pruebas ARM.');
        }

        // 3) Listar capacidades myorg
        $this->section('C) Power BI API (myorg) — listado de capacidades');
        try {
            $caps = $svc->listCapacities();
            if (empty($caps)) {
                $this->warn('No se recibieron capacidades desde myorg. Verifica Tenant settings (Allow service principals to use Power BI APIs).');
            } else {
                $rows = [];
                foreach ($caps as $c) {
                    $rows[] = [
                        $c['id'] ?? '',
                        $c['displayName'] ?? $c['name'] ?? '',
                        $c['sku'] ?? '',
                        $c['state'] ?? '',
                        $c['region'] ?? '',
                    ];
                }
                $this->table(['GUID', 'Nombre', 'SKU', 'Estado', 'Región'], $rows);
                $this->info('✔ Acceso a /myorg/capacities OK');
            }
        } catch (\Throwable $e) {
            $this->error('Error llamando /myorg/capacities: ' . $e->getMessage());
            return self::FAILURE;
        }

        // 4) Comprobación ARM por capacidad en BD (si hay token ARM)
        if ($armToken && $subId && $rgName) {
            $this->section('D) ARM — verificación por capacidad en BD');

            $dbCaps = Capacity::query()->orderBy('display_name')->get();
            if ($dbCaps->isEmpty()) {
                $this->warn('No hay capacidades en BD. Ejecuta: php artisan powerbi:capacities:import');
            } else {
                $rows = [];
                foreach ($dbCaps as $cap) {
                    $provider = $this->detectProviderBySku((string) $cap->sku);
                    $name     = trim((string) ($cap->arm_resource_name ?: $cap->display_name));
                    $verdict  = 'OK';
                    $detail   = '';

                    if ($name === '') {
                        $verdict = 'ERROR';
                        $detail  = 'arm_resource_name/display_name vacío';
                    } else {
                        $api = $provider === 'Microsoft.Fabric' ? '2023-11-01' : '2021-01-01';
                        $url = sprintf(
                            'https://management.azure.com/subscriptions/%s/resourceGroups/%s/providers/%s/capacities/%s?api-version=%s',
                            rawurlencode($subId),
                            rawurlencode($rgName),
                            $provider,
                            rawurlencode($name),
                            $api
                        );

                        $resp = $this->http->get($url, [
                            'headers' => [
                                'Authorization' => "Bearer {$armToken}",
                                'Content-Type'  => 'application/json',
                            ],
                        ]);

                        $code = $resp->getStatusCode();
                        if (in_array($code, [200, 202, 204], true)) {
                            $verdict = 'OK';
                            $detail  = 'Acceso ARM correcto';
                        } elseif ($code === 404) {
                            $verdict = '404';
                            $detail  = 'No se encuentra el recurso: verifica arm_resource_name, provider, RG y suscripción.';
                        } elseif ($code === 403) {
                            $verdict = '403';
                            $detail  = 'Permisos insuficientes: asigna RBAC (Contributor) al SP en el RG/capacidad.';
                        } elseif ($code === 401) {
                            $verdict = '401';
                            $detail  = 'Token inválido/expirado para ARM.';
                        } else {
                            $verdict = (string) $code;
                            $detail  = 'Respuesta inesperada de ARM';
                        }
                    }

                    $rows[] = [
                        $cap->capacity_uuid,
                        $cap->display_name,
                        $cap->sku,
                        $provider,
                        $name,
                        $verdict,
                        $detail,
                    ];
                }

                $this->table(
                    ['GUID', 'Display', 'SKU', 'Provider', 'ARM name', 'Veredicto', 'Detalle'],
                    $rows
                );

                $this->line('Notas:');
                $this->line('- 404 → nombre ARM/Provider/RG/Suscripción incorrectos.');
                $this->line('- 403 → falta RBAC para el Service Principal (Contributor u Owner).');
                $this->line('- 401 → token ARM inválido o expirado.');
            }
        } else {
            $this->section('D) ARM — omitido');
            $this->line('Para habilitar diagnóstico ARM completa azure_subscription_id y azure_resource_group, y asegúrate de obtener token ARM.');
        }

        $this->section('E) Recomendaciones');
        $this->bullet('Power BI Admin Portal → Tenant settings → Allow service principals to use Power BI APIs (incluye tu SP o su grupo).');
        $this->bullet('Azure: registra Resource Providers Microsoft.Fabric/Microsoft.PowerBIDedicated en la suscripción.');
        $this->bullet('Azure RBAC: asigna Contributor al SP sobre el Resource Group de las capacidades.');
        $this->bullet('Completa arm_resource_name en BD si el display_name no coincide con el nombre del recurso de Azure.');
        $this->line('');
        $this->info('Diagnóstico finalizado.');
        return self::SUCCESS;
    }

    /* ================== helpers ================== */

    private function mask(string $v): string
    {
        if ($v === '') return '(vacío)';
        $len = strlen($v);
        if ($len <= 6) return str_repeat('*', $len);
        return substr($v, 0, 3) . str_repeat('*', $len - 6) . substr($v, -3);
    }

    private function getToken(string $tenant, string $clientId, string $secret, string $scope, string $label): ?string
    {
        $url = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";
        $resp = $this->http->post($url, [
            'form_params' => [
                'client_id'     => $clientId,
                'client_secret' => $secret,
                'grant_type'    => 'client_credentials',
                'scope'         => $scope,
            ],
        ]);

        $code = $resp->getStatusCode();
        if ($code !== 200) {
            $this->error("Token {$label}: {$code} → " . (string) $resp->getBody());
            return null;
        }

        $json = json_decode((string) $resp->getBody(), true);
        $token = (string) ($json['access_token'] ?? '');
        if ($token === '') {
            $this->error("Token {$label}: respuesta sin access_token.");
            return null;
        }
        return $token;
    }

    private function detectProviderBySku(string $sku): string
    {
        $u = strtoupper($sku);
        if (str_starts_with($u, 'F')) {
            return 'Microsoft.Fabric';
        }
        if (str_starts_with($u, 'A') || str_starts_with($u, 'EM')) {
            return 'Microsoft.PowerBIDedicated';
        }
        // por defecto intenta Fabric
        return 'Microsoft.Fabric';
    }

    private function section(string $title): void
    {
        $this->line('');
        $this->line(" <info>{$title}</info>");
        $this->line(str_repeat('-', max(8, strlen($title) + 2)));
    }

    private function bullet(string $text): void
    {
        $this->line(" • {$text}");
    }
}
