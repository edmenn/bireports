<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['nullable', 'string', 'max:255'],
            'dominio_personalizado' => ['nullable', 'string', 'max:255'],
            'google_analytics_code' => ['nullable', 'string', 'max:255'],
            'tipo_visualizacion' => ['nullable', 'in:pastas,iconos,lista'],
            'email_locale' => ['nullable', 'in:es,pt,en,ru'],
            'app_timezone_db' => ['nullable', Rule::in(timezone_identifiers_list())],
            'mail_driver' => ['nullable', 'in:smtp,sendmail,log,mailjet'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_encryption' => ['nullable', 'in:tls,ssl,none'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'powerbi_client_id' => ['nullable', 'string', 'max:255'],
            'powerbi_client_secret' => ['nullable', 'string'],
            'powerbi_tenant_id' => ['nullable', 'string', 'max:255'],
            // redirect_uri y scope se gestionan fuera de la UI (scope via .env)
            'powerbi_token_refresh_minutes' => ['nullable', 'integer', 'min:0'],
            'microsoft_login_enabled' => ['nullable', 'boolean'],
            'local_login_enabled' => ['nullable', 'boolean'],
            'prewarm_enabled' => ['nullable', 'boolean'],
            // [Minimalista] Parámetros on-demand del viewer
            'capacity_on_demand_enable' => ['nullable', 'boolean'],
            'capacity_on_demand_message' => ['nullable', 'string', 'max:255'],
            'capacity_start_timeout_sec' => ['nullable', 'integer', 'min:10', 'max:600'],
            'capacity_start_poll_interval_sec' => ['nullable', 'integer', 'min:1', 'max:60'],
            // Timeout de carga del viewer (post-embed)
            'report_load_timeout_sec' => ['nullable', 'integer', 'min:5', 'max:300'],

            // Tema y branding
            'theme_navbar_class' => ['nullable', 'string', 'max:100'],
            'theme_sidebar_class' => ['nullable', 'string', 'max:100'],
            'site_title' => ['nullable', 'string', 'max:255'],
            'site_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'site_logo_width' => ['nullable', 'integer', 'min:16', 'max:256'],
            'site_logo_height' => ['nullable', 'integer', 'min:16', 'max:128'],
            'site_logo_compact' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
            'site_logo_compact_width' => ['nullable', 'integer', 'min:16', 'max:256'],
            'site_logo_compact_height' => ['nullable', 'integer', 'min:16', 'max:128'],
            'site_favicon' => ['nullable', 'mimes:ico,png', 'max:1024'],
            'preloader_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
            'preloader_logo_width' => ['nullable', 'integer', 'min:16', 'max:512'],
            'preloader_logo_height' => ['nullable', 'integer', 'min:16', 'max:512'],
            'login_bg' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'login_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'login_logo_width' => ['nullable', 'integer', 'min:16', 'max:512'],
            'login_logo_height' => ['nullable', 'integer', 'min:16', 'max:512'],

            'theme_name' => ['nullable', 'string', 'max:50'],
            'theme_preset_id' => ['nullable', 'integer', 'min:0'],
            'reset_theme' => ['nullable', 'boolean'],
            'color_main_bg' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_main_text' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_navbar_bg' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_navbar_text' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_menu_bg' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_menu_text' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_highlight_bg' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_highlight_text' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_container_bg' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_container_alt_bg' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_container_text' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_icon_bg' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_icon_text' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_btn_bg' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_btn_text' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_btn_hover' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'color_btn_active' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],

            // Footer fijo por código: no se valida ni acepta desde UI
        ];
    }

    public function attributes(): array
    {
        return [
            'mail_driver' => __('settings.mail.fields.driver.label'),
            'mail_host' => __('settings.mail.fields.host.label'),
            'mail_port' => __('settings.mail.fields.port.label'),
            'mail_encryption' => __('settings.mail.fields.encryption.label'),
            'mail_username' => __('settings.mail.fields.username.label'),
            'mail_password' => __('settings.mail.fields.password.label'),
            'mail_from_address' => __('settings.mail.fields.from_address.label'),
            'mail_from_name' => __('settings.mail.fields.from_name.label'),
            'site_logo' => __('config_theme.cards.branding.fields.site_logo.label'),
            'site_logo_compact' => __('config_theme.cards.branding.fields.site_logo_compact.label'),
            'site_favicon' => __('config_theme.cards.branding.fields.site_favicon.label'),
            'preloader_logo' => __('config_theme.cards.branding.fields.preloader_logo.label'),
            'login_logo' => __('settings.login.cards.uploads.fields.logo.label'),
            'login_bg' => __('settings.login.cards.uploads.fields.background.label'),
        ];
    }

    public function messages(): array
    {
        $limits = [
            'site_logo' => 2,
            'site_logo_compact' => 1,
            'site_favicon' => 1,
            'preloader_logo' => 1,
            'login_logo' => 2,
            'login_bg' => 2,
        ];
        $messages = [];

        foreach ($limits as $field => $maxMb) {
            $messages[$field.'.image'] = __('config_theme.errors.invalid_image');
            $messages[$field.'.mimes'] = __('config_theme.errors.invalid_file_type');
            $messages[$field.'.max'] = __('config_theme.errors.file_too_large', ['max' => $maxMb]);
            $messages[$field.'.uploaded'] = __('config_theme.errors.file_too_large', ['max' => $maxMb]);
        }

        return $messages;
    }
}
