<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ya controlamos acceso por middleware/roles
    }

    public function rules(): array
    {
        return [
            'powerbi_client_id'              => ['nullable','string','max:255'],
            'powerbi_client_secret'          => ['nullable','string'],
            'powerbi_tenant_id'              => ['nullable','string','max:255'],
            'powerbi_redirect_uri'           => ['nullable','url','max:255'],
            'powerbi_scope'                  => ['nullable','string','max:255'],
            'powerbi_token_refresh_minutes'  => ['nullable','integer','min:0','max:1440'],
        ];
    }
}
