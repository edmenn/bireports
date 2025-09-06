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
            // redirect_uri y scope se gestionan fuera de la UI (scope via .env)
            'powerbi_token_refresh_minutes'  => ['nullable','integer','min:0'],
            'microsoft_login_enabled'        => ['nullable','boolean'],
            'local_login_enabled'            => ['nullable','boolean'],
            // [Minimalista] Parámetros on-demand del viewer
            'capacity_on_demand_enable'      => ['nullable','boolean'],
            'capacity_on_demand_message'     => ['nullable','string','max:255'],
            'capacity_start_timeout_sec'     => ['nullable','integer','min:10','max:600'],
            'capacity_start_poll_interval_sec' => ['nullable','integer','min:1','max:60'],

            // Tema y branding
            'theme_navbar_class'             => ['nullable','string','max:100'],
            'theme_sidebar_class'            => ['nullable','string','max:100'],
            'site_title'                     => ['nullable','string','max:255'],
            'site_logo'                      => ['nullable','image','mimes:png,jpg,jpeg,svg','max:2048'],
            'site_logo_compact'              => ['nullable','image','mimes:png,jpg,jpeg,svg','max:1024'],
            'site_favicon'                   => ['nullable','mimes:ico,png','max:1024'],
            'login_bg'                       => ['nullable','image','mimes:png,jpg,jpeg','max:4096'],
            'login_logo'                     => ['nullable','image','mimes:png,jpg,jpeg,svg','max:2048'],

            'theme_name'                     => ['nullable','string','max:50'],
            // Colores HEX permitimos string, validación básica opcional
            'color_main_bg'                  => ['nullable','string','max:9'],
            'color_main_text'                => ['nullable','string','max:9'],
            'color_navbar_bg'                => ['nullable','string','max:9'],
            'color_navbar_text'              => ['nullable','string','max:9'],
            'color_menu_bg'                  => ['nullable','string','max:9'],
            'color_menu_text'                => ['nullable','string','max:9'],
            'color_highlight_bg'             => ['nullable','string','max:9'],
            'color_highlight_text'           => ['nullable','string','max:9'],
            'color_container_bg'             => ['nullable','string','max:9'],
            'color_container_alt_bg'         => ['nullable','string','max:9'],
            'color_container_text'           => ['nullable','string','max:9'],
            'color_icon_bg'                  => ['nullable','string','max:9'],
            'color_icon_text'                => ['nullable','string','max:9'],
            'color_btn_bg'                   => ['nullable','string','max:9'],
            'color_btn_text'                 => ['nullable','string','max:9'],
            'color_btn_hover'                => ['nullable','string','max:9'],
            'color_btn_active'               => ['nullable','string','max:9'],

            // Footer fijo por código: no se valida ni acepta desde UI
        ];
    }
}
