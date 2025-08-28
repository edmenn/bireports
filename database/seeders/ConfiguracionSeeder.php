<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('configuraciones')->count() === 0) {
            DB::table('configuraciones')->insert([
                'nombre'                     => 'Configuración inicial',
                'dominio_personalizado'      => null,
                'google_analytics_code'      => null,
                'email_locale'               => 'es',
                'powerbi_client_id'          => null,
                'powerbi_client_secret'      => null,
                'powerbi_tenant_id'          => null,
                'powerbi_redirect_uri'       => null,
                'powerbi_scope'              => 'https://analysis.windows.net/powerbi/api/.default',
                'powerbi_token_refresh_minutes' => 0,
                'created_at'                 => now(),
                'updated_at'                 => now(),
            ]);
        }
    }
}
