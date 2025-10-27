<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Configuration extends Model
{
    use HasFactory;

    protected $table = 'configurations';

    public function themePresets()
    {
        return $this->hasMany(ConfigurationThemePreset::class);
    }

    protected $fillable = [
        'nombre',
        'dominio_personalizado',
        'google_analytics_code',
        'tipo_visualizacion',
        'email_locale',
        'app_timezone_db',
        'mail_driver',
        'mail_host',
        'mail_port',
        'mail_encryption',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
        'powerbi_client_id',
        'powerbi_client_secret',
        'powerbi_tenant_id',
        'powerbi_redirect_uri',
        'powerbi_scope',
        'powerbi_token_refresh_minutes',
        'prewarm_enabled',
        // [Minimalist] On-demand viewer parameters
        'capacity_on_demand_enable',
        'capacity_on_demand_message',
        'capacity_start_timeout_sec',
        'capacity_start_poll_interval_sec',
        'report_load_timeout_sec',
        'microsoft_login_enabled',
        'local_login_enabled',

        // Newly added ARM fields
        'azure_subscription_id',
        'azure_resource_group',

        // Theme and branding
        'theme_navbar_class',
        'theme_sidebar_class',
        'site_title',
        'site_logo_path',
        'site_logo_width',
        'site_logo_height',
        'site_logo_compact_path',
        'site_logo_compact_width',
        'site_logo_compact_height',
        'site_favicon_path',
        'preloader_logo_path',
        'preloader_logo_width',
        'preloader_logo_height',
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
        // [Minimalist]
        'capacity_on_demand_enable' => 'boolean',
        'capacity_start_timeout_sec' => 'integer',
        'capacity_start_poll_interval_sec' => 'integer',
        'report_load_timeout_sec' => 'integer',
        'preloader_logo_width' => 'integer',
        'preloader_logo_height' => 'integer',
        'site_logo_width' => 'integer',
        'site_logo_height' => 'integer',
        'site_logo_compact_width' => 'integer',
        'site_logo_compact_height' => 'integer',
        'mail_port' => 'integer',
    ];

    // Store the client secret encrypted; tolerate legacy plaintext values
    public function getPowerbiClientSecretAttribute($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value; // compatibility with legacy values stored without encryption
        }
    }

    public function setPowerbiClientSecretAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['powerbi_client_secret'] = $value;
            return;
        }
        // Always store encrypted; the controller prevents overwriting masked values
        $this->attributes['powerbi_client_secret'] = Crypt::encryptString((string) $value);
    }
}
