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

        // ⬇️ nuevos campos ARM
        'azure_subscription_id',
        'azure_resource_group',
    ];
}
