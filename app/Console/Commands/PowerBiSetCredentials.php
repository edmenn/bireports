<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Configuracion;
use Illuminate\Console\Command;

class PowerBiSetCredentials extends Command
{
    protected $signature = 'powerbi:credentials:set
        {--tenant= : Tenant (Directory) ID de Entra}
        {--client= : Application (Client) ID}
        {--secret= : Client Secret (valor)}
        {--name= : Nombre del cliente/instancia (opcional)}
        {--activate : Marcar esta fila como activa (desactiva las demás)}';

    protected $description = 'Guarda las credenciales de Power BI/Fabric en la BD (tabla configuracions)';

    public function handle(): int
    {
        $tenant = (string) ($this->option('tenant') ?? '');
        $client = (string) ($this->option('client') ?? '');
        $secret = (string) ($this->option('secret') ?? '');
        $name   = (string) ($this->option('name') ?? '');
        $activate = (bool) $this->option('activate');

        // Solicitar interactivo si faltan
        if ($tenant === '') {
            $tenant = (string) $this->ask('Tenant (Directory) ID');
        }
        if ($client === '') {
            $client = (string) $this->ask('Application (Client) ID');
        }
        if ($secret === '') {
            $secret = (string) $this->secret('Client Secret');
        }

        if ($tenant === '' || $client === '' || $secret === '') {
            $this->error('Faltan datos: tenant, client y secret son obligatorios.');
            return self::FAILURE;
        }

        // Si se indica --activate => desactivar las demás
        if ($activate) {
            Configuracion::query()->update(['is_active' => false]);
        }

        $row = Configuracion::query()->updateOrCreate(
            [
                'tenant_id' => $tenant,
                'client_id' => $client,
            ],
            [
                'client_secret' => $secret,
                'customer_name' => $name !== '' ? $name : null,
                'is_active'     => $activate ? true : false,
            ]
        );

        // Si no había ninguna activa y no se pasó --activate, activamos esta por conveniencia
        if (! $activate && ! Configuracion::query()->where('is_active', true)->exists()) {
            $row->is_active = true;
            $row->save();
        }

        $this->info('Credenciales guardadas correctamente.');
        $this->line('Resumen:');
        $this->line('- Tenant ID : ' . $row->tenant_id);
        $this->line('- Client ID : ' . $row->client_id);
        $this->line('- Activa    : ' . ($row->is_active ? 'Sí' : 'No'));
        if ($row->customer_name) {
            $this->line('- Nombre    : ' . $row->customer_name);
        }

        return self::SUCCESS;
    }
}
