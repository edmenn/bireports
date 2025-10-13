<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Capacity;
use App\Models\Configuration;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class DiscoverArmCapacities extends Command
{
    protected $signature = 'powerbi:discover
        {--dry-run=0 : Simula sin escribir cambios en BD}
        {--debug-arm=0 : Muestra respuestas ARM útiles para depurar}';

    protected $description = 'Autodescubre Subscription/ResourceGroup y nombres ARM de capacidades (Fabric/Dedicated) sin depender de listar RGs.';

    private Client $http;

    public function __construct()
    {
        parent::__construct();
        $this->http = new Client([
            'timeout'     => 40,
            'http_errors' => false,
        ]);
    }

    public function handle(): int
    {
        $dryRun   = (bool) (int) $this->option('dry-run');
        $debugArm = (bool) (int) $this->option('debug-arm');

        /** @var Configuration|null $cfg */
        $cfg = Configuration::query()->first();
        if (!$cfg) {
            $cfg = Configuration::query()->create([
                'nombre' => config('app.name', 'App'),
            ]);
        }

        $tenant = (string) ($cfg->powerbi_tenant_id ?? '');
        $client = (string) ($cfg->powerbi_client_id ?? '');
        $secret = (string) ($cfg->powerbi_client_secret ?? '');

        if ($tenant === '' || $client === '' || $secret === '') {
            $this->error('Faltan credenciales Power BI/Fabric (tenant/client/secret) en configurations.');
            return self::FAILURE;
        }

        $armToken = $this->getToken($tenant, $client, $secret, 'https://management.azure.com/.default');
        if (!$armToken) {
            $this->error('No se pudo obtener token ARM. Revisa permisos de la App Registration en Entra ID.');
            return self::FAILURE;
        }
        $this->info('✔ Token ARM OK');

        // 1) Suscripciones visibles para el SP
        $subs = $this->getSubscriptions($armToken, $debugArm);
        if (empty($subs)) {
            $this->warn('No hay suscripciones visibles para este Service Principal.');
            return self::SUCCESS;
        }

        $summaryRows = [];
        $updates     = 0;

        $firstGoodSubscription = null;
        $firstGoodRG           = null;

        foreach ($subs as $sub) {
            $subId   = (string) ($sub['subscriptionId'] ?? '');
            $subName = (string) ($sub['displayName'] ?? '');
            if ($subId === '') continue;

            $this->line('');
            $this->line("<info>Suscripción:</info> {$subName} ({$subId})");

            // 2) Capacities por SUSCRIPCIÓN (sin listar RGs)
            $fabricCaps = $this->listBySubscription(
                $armToken,
                $subId,
                'Microsoft.Fabric',
                '2023-11-01',
                $debugArm
            );

            $dedicatedCaps = $this->listBySubscription(
                $armToken,
                $subId,
                'Microsoft.PowerBIDedicated',
                '2021-01-01',
                $debugArm
            );

            $armCaps = array_merge(
                $this->mapArmCaps($fabricCaps, 'Microsoft.Fabric'),
                $this->mapArmCaps($dedicatedCaps, 'Microsoft.PowerBIDedicated'),
            );

            if (empty($armCaps)) {
                $this->line('  - (sin capacities ARM a nivel suscripción)');
                continue;
            }

            foreach ($armCaps as $armCap) {
                $armName   = (string) ($armCap['name'] ?? '');
                $provider  = (string) ($armCap['provider'] ?? '');
                $sku       = (string) ($armCap['sku'] ?? '');
                $location  = (string) ($armCap['location'] ?? '');
                $rg        = (string) ($armCap['resourceGroup'] ?? '');

                // Intento de match en BD:
                /** @var Capacity|null $dbCap */
                $dbCap = Capacity::query()
                    ->whereRaw('LOWER(display_name) = ?', [mb_strtolower($armName)])
                    ->orWhere(function ($q) use ($sku, $location) {
                        $q->where('sku', $sku)->where('region', $location);
                    })
                    ->first();

                if (!$dbCap) {
                    $summaryRows[] = [$subId, $rg, $armName, $provider, $sku, $location, 'NO MATCH', '-'];
                    continue;
                }

                $changed = false;

                if (empty($dbCap->arm_resource_name) || $dbCap->arm_resource_name !== $armName) {
                    if (!$dryRun) {
                        $dbCap->arm_resource_name = $armName;
                        $dbCap->save();
                    }
                    $changed = true;
                    $updates++;
                }

                if (!$cfg->azure_subscription_id && !$cfg->azure_resource_group && $rg !== '') {
                    $firstGoodSubscription = $firstGoodSubscription ?? $subId;
                    $firstGoodRG           = $firstGoodRG ?? $rg;
                }

                $summaryRows[] = [
                    $subId,
                    $rg,
                    $armName,
                    $provider,
                    $sku,
                    $location,
                    'MATCH',
                    $changed ? 'arm_resource_name actualizado' : 'sin cambios',
                ];
            }
        }

        // Resumen
        $this->line('');
        $this->table(
            ['Subscription', 'ResourceGroup', 'ARM Name', 'Provider', 'SKU', 'Location', 'Match', 'Detalle'],
            $summaryRows
        );

        // Guardar subscription+RG si faltaban
        if ($firstGoodSubscription && $firstGoodRG) {
            if ($dryRun) {
                $this->warn("DRY-RUN: completaría configurations con subscription={$firstGoodSubscription}, rg={$firstGoodRG}");
            } else {
                $cfg->azure_subscription_id = $firstGoodSubscription;
                $cfg->azure_resource_group  = $firstGoodRG;
                $cfg->save();
                $this->info("✔ Configurationes actualizadas: subscription={$firstGoodSubscription}, rg={$firstGoodRG}");
            }
        }

        $this->info("Descubrimiento finalizado. Cambios en capacities: {$updates}" . ($dryRun ? ' (dry-run)' : ''));
        $this->line('Si queda algún NO MATCH, ajusta arm_resource_name manualmente o permisos de visibilidad.');
        return self::SUCCESS;
    }

    /* ===================== Helpers HTTP / ARM ===================== */

    private function getToken(string $tenant, string $clientId, string $secret, string $scope): ?string
    {
        $url  = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";
        $resp = $this->http->post($url, [
            'form_params' => [
                'client_id'     => $clientId,
                'client_secret' => $secret,
                'grant_type'    => 'client_credentials',
                'scope'         => $scope,
            ],
        ]);

        if ($resp->getStatusCode() !== 200) {
            $this->error("Token ARM error {$resp->getStatusCode()}: " . (string) $resp->getBody());
            return null;
        }

        $json  = json_decode((string) $resp->getBody(), true);
        $token = (string) ($json['access_token'] ?? '');
        return $token !== '' ? $token : null;
    }

    /**
     * @return array<int, array<string,mixed>>
     */
    private function getSubscriptions(string $armToken, bool $debugArm): array
    {
        $url  = 'https://management.azure.com/subscriptions?api-version=2020-01-01';
        $resp = $this->http->get($url, [
            'headers' => ['Authorization' => "Bearer {$armToken}"],
        ]);

        if ($debugArm) $this->dumpHttp('GET subscriptions', $resp);

        if ($resp->getStatusCode() !== 200) return [];
        $json = json_decode((string) $resp->getBody(), true);
        return (array) ($json['value'] ?? []);
    }

    /**
     * Lista capacities por suscripción (sin RGs).
     * @return array<int, array<string,mixed>>
     */
    private function listBySubscription(
        string $armToken,
        string $subId,
        string $provider,
        string $apiVersion,
        bool $debugArm
    ): array {
        $url = "https://management.azure.com/subscriptions/{$subId}/providers/{$provider}/capacities?api-version={$apiVersion}";
        $resp = $this->http->get($url, [
            'headers' => ['Authorization' => "Bearer {$armToken}"],
        ]);

        if ($debugArm) $this->dumpHttp("GET capacities by subscription ({$provider})", $resp);

        if ($resp->getStatusCode() !== 200) return [];
        $json = json_decode((string) $resp->getBody(), true);
        return (array) ($json['value'] ?? []);
    }

    /**
     * @param array<int, array<string,mixed>> $list
     * @return array<int, array<string,mixed>>
     */
    private function mapArmCaps(array $list, string $provider): array
    {
        $out = [];
        foreach ($list as $item) {
            // id esperado:
            // /subscriptions/{sub}/resourceGroups/{rg}/providers/{provider}/capacities/{name}
            $id   = (string) ($item['id'] ?? '');
            $name = (string) ($item['name'] ?? '');
            $sku  = $item['sku']['name'] ?? ($item['properties']['sku'] ?? '');
            $loc  = (string) ($item['location'] ?? '');

            $rg = $this->parseRgFromId($id);

            $out[] = [
                'name'          => $name,
                'provider'      => $provider,
                'sku'           => (string) $sku,
                'location'      => $loc,
                'resourceGroup' => $rg,
            ];
        }
        return $out;
    }

    private function parseRgFromId(string $id): string
    {
        // Partido por '/', buscamos el segmento "resourceGroups"
        // /subscriptions/{sub}/resourceGroups/{rg}/providers/.../capacities/{name}
        if ($id === '') return '';
        $parts = explode('/', trim($id, '/'));
        foreach ($parts as $i => $p) {
            if (strcasecmp($p, 'resourceGroups') === 0 && isset($parts[$i+1])) {
                return $parts[$i+1];
            }
        }
        return '';
    }

    private function dumpHttp(string $label, \Psr\Http\Message\ResponseInterface $resp): void
    {
        $this->line("  [{$label}] HTTP {$resp->getStatusCode()}");
        $body = (string) $resp->getBody();
        $this->line('  Body: ' . mb_substr($body, 0, 800));
    }
}
