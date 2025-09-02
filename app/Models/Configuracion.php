<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'powerbi_client_id',
        'powerbi_client_secret',
        'powerbi_tenant_id',
        'powerbi_redirect_uri',
        'powerbi_scope',
        'powerbi_token_refresh_minutes',
        'microsoft_login_enabled',

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
}
