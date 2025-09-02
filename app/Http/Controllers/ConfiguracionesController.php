<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Http\Requests\UpdateConfiguracionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ConfiguracionesController extends Controller
{
    public function index(): View
    {
        // Garantizamos que exista UNA fila (id=1)
        $config = Configuracion::firstOrCreate(
            ['id' => 1],
            [
                'nombre'                        => 'Default',
                'tipo_visualizacion'            => 'pastas',
                'email_locale'                  => 'es',
                'powerbi_scope'                 => 'https://analysis.windows.net/powerbi/api/.default',
                'powerbi_token_refresh_minutes' => 0,
            ]
        );

        return view('configuraciones.index', compact('config'));
    }

    public function theme(): View
    {
        // Garantizamos que exista UNA fila (id=1)
        $config = Configuracion::firstOrCreate(
            ['id' => 1],
            [
                'nombre'                        => 'Default',
                'tipo_visualizacion'            => 'pastas',
                'email_locale'                  => 'es',
                'powerbi_scope'                 => 'https://analysis.windows.net/powerbi/api/.default',
                'powerbi_token_refresh_minutes' => 0,
            ]
        );

        return view('configuraciones.tema', compact('config'));
    }

    public function login(): View
    {
        $config = Configuracion::firstOrCreate(
            ['id' => 1],
            [
                'nombre'                        => 'Default',
                'tipo_visualizacion'            => 'pastas',
                'email_locale'                  => 'es',
                'powerbi_scope'                 => 'https://analysis.windows.net/powerbi/api/.default',
                'powerbi_token_refresh_minutes' => 0,
            ]
        );

        return view('configuraciones.login', compact('config'));
    }


    public function update(UpdateConfiguracionRequest $request, Configuracion $configuracion): RedirectResponse
    {
        // Solo actualizamos columnas que EXISTEN realmente en la tabla
        $input   = $request->validated();
        $columns = Schema::getColumnListing($configuracion->getTable());
        $data    = array_intersect_key($input, array_flip($columns));

        // No sobrescribir secret si viene enmascarado o vacío
        if (array_key_exists('powerbi_client_secret', $data)) {
            $raw = (string) ($request->input('powerbi_client_secret') ?? '');
            if ($raw === '' || str_contains($raw, '•')) {
                unset($data['powerbi_client_secret']);
            }
        }

        // Reset a colores de fábrica
        if ($request->boolean('reset_theme')) {
            $data['theme_config'] = null;
            $data['theme_name'] = null;
            $data['theme_navbar_class'] = null;
            $data['theme_sidebar_class'] = null;
        }

        // Empaquetar configuración de colores en JSON (theme_config)
        $themeConfig = [
            'main_bg' => $request->string('color_main_bg')->toString(),
            'main_text' => $request->string('color_main_text')->toString(),
            'navbar_bg' => $request->string('color_navbar_bg')->toString(),
            'navbar_text' => $request->string('color_navbar_text')->toString(),
            'menu_bg' => $request->string('color_menu_bg')->toString(),
            'menu_text' => $request->string('color_menu_text')->toString(),
            'highlight_bg' => $request->string('color_highlight_bg')->toString(),
            'highlight_text' => $request->string('color_highlight_text')->toString(),
            'container_bg' => $request->string('color_container_bg')->toString(),
            'container_alt_bg' => $request->string('color_container_alt_bg')->toString(),
            'container_text' => $request->string('color_container_text')->toString(),
            'icon_bg' => $request->string('color_icon_bg')->toString(),
            'icon_text' => $request->string('color_icon_text')->toString(),
            'btn_bg' => $request->string('color_btn_bg')->toString(),
            'btn_text' => $request->string('color_btn_text')->toString(),
            'btn_hover' => $request->string('color_btn_hover')->toString(),
            'btn_active' => $request->string('color_btn_active')->toString(),
        ];
        // Filtrar vacíos
        $themeConfig = array_filter($themeConfig, fn($v) => $v !== null && $v !== '');
        if (!empty($themeConfig)) {
            $data['theme_config'] = $themeConfig;
        }

        // Toggle de login con Microsoft (checkbox)
        $data['microsoft_login_enabled'] = $request->boolean('microsoft_login_enabled');

        // Manejo de archivos (branding)
        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('branding', 'public');
            $data['site_logo_path'] = $path;
        }

        if ($request->hasFile('site_favicon')) {
            $path = $request->file('site_favicon')->store('branding', 'public');
            $data['site_favicon_path'] = $path;
            try {
                $abs = Storage::disk('public')->path($path);
                @copy($abs, public_path('favicon.ico'));
            } catch (\Throwable $e) {
                // Ignorar fallos de copia; quedará accesible vía /storage
            }
        }

        // Logo compacto (para sidebar colapsada)
        if ($request->hasFile('site_logo_compact')) {
            $path = $request->file('site_logo_compact')->store('branding', 'public');
            $data['site_logo_compact_path'] = $path;
        }

        if ($request->hasFile('login_bg')) {
            $path = $request->file('login_bg')->store('branding', 'public');
            $data['login_bg_path'] = $path;
        }

        // Logo del login (puede ser distinto al principal)
        if ($request->hasFile('login_logo')) {
            $path = $request->file('login_logo')->store('branding', 'public');
            $data['login_logo_path'] = $path;
        }

        $configuracion->update($data);

        // Redirección: respetar retorno si viene desde "Tema"
        $returnTo = (string) $request->input('return_to', '');
        if ($returnTo && Str::startsWith($returnTo, url('/configuraciones'))) {
            return redirect($returnTo)->with('success', 'Configuración actualizada.');
        }

        return redirect()->route('configuraciones.index')->with('success', 'Configuración actualizada.');
    }
}
