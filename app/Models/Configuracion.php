<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'nombre',
        'dominio_personalizado',
        'google_analytics_code',
        'tipo_visualizacion',
        'email_locale',
        'app_timezone_db',
        'powerbi_client_id',
        'powerbi_client_secret',
        'powerbi_tenant_id',
        'powerbi_redirect_uri',
        'powerbi_scope',
        'powerbi_token_refresh_minutes',
        'prewarm_enabled',
        // [Minimalista] Parámetros on-demand viewer
        'capacity_on_demand_enable',
        'capacity_on_demand_message',
        'capacity_start_timeout_sec',
        'capacity_start_poll_interval_sec',
        'microsoft_login_enabled',
        'local_login_enabled',

        // ⬇️ nuevos campos ARM
        'azure_subscription_id',
        'azure_resource_group',

        // Tema y branding
        'theme_navbar_class',
        'theme_sidebar_class',
        'site_title',
        'site_logo_path',
        'site_logo_compact_path',
        'site_favicon_path',
        'login_bg_path',
        'login_logo_path',
        'theme_name',
        'theme_config',
        'footer_text',
        'footer_enabled',
    ];

    protected $casts = [
        'theme_config' => 'array',
        'microsoft_login_enabled' => 'boolean',
        'local_login_enabled' => 'boolean',
        'footer_enabled' => 'boolean',
        'powerbi_token_refresh_minutes' => 'integer',
        'prewarm_enabled' => 'boolean',
        // [Minimalista]
        'capacity_on_demand_enable' => 'boolean',
        'capacity_start_timeout_sec' => 'integer',
        'capacity_start_poll_interval_sec' => 'integer',
    ];

    // Almacenar el client secret encriptado; tolera valores antiguos en texto claro
    public function getPowerbiClientSecretAttribute($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value; // compatibilidad con valores preexistentes sin cifrar
        }
    }

    public function setPowerbiClientSecretAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['powerbi_client_secret'] = $value;
            return;
        }
        // Siempre guardar cifrado; si viene enmascarado, el controlador ya evita sobrescribir
        $this->attributes['powerbi_client_secret'] = Crypt::encryptString((string) $value);
    }
}
