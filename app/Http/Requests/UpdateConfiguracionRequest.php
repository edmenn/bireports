<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfiguracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'                         => ['nullable','string','max:255'],
            'dominio_personalizado'          => ['nullable','string','max:255'],
            'google_analytics_code'          => ['nullable','string','max:255'],
            'tipo_visualizacion'             => ['nullable','in:pastas,iconos,lista'],
            'email_locale'                   => ['nullable','in:es,pt,en'],
            'powerbi_client_id'              => ['nullable','string','max:255'],
            'powerbi_client_secret'          => ['nullable','string'],
            'powerbi_tenant_id'              => ['nullable','string','max:255'],
            'powerbi_redirect_uri'           => ['nullable','string','max:255'],
            'powerbi_scope'                  => ['nullable','string','max:255'],
            'powerbi_token_refresh_minutes'  => ['nullable','integer','min:0'],
        ];
    }
}
