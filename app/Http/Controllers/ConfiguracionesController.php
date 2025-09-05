<?php
declare(strict_types=1);

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

        // Sincronizar "Predeterminado" con paleta gris
        $themeName = (string) $request->input('theme_name', '');
        $defaultPreset = [
            'main_bg' => '#f4f6f9',
            'main_text' => '#111111',
            'navbar_bg' => '#374151',
            'navbar_text' => '#f9fafb',
            'menu_bg' => '#1f2d37',
            'menu_text' => '#d1d5db',
            'container_bg' => '#ffffff',
            'container_alt_bg' => '#f8fafc',
            'container_text' => '#111111',
            'highlight_bg' => '#6b7280',
            'highlight_text' => '#ffffff',
            'btn_bg' => '#6b7280',
            'btn_text' => '#ffffff',
            'btn_hover' => '#4b5563',
            'btn_active' => '#374151',
        ];
        if ($themeName === '') {
            // Fuerza a guardar la paleta gris por defecto
            $data['theme_config'] = $defaultPreset;
        }

        // Toggles de login (checkbox)
        $data['microsoft_login_enabled'] = $request->boolean('microsoft_login_enabled');
        $data['local_login_enabled'] = $request->boolean('local_login_enabled');
        // Toggle de pre-warm
        $data['prewarm_enabled'] = $request->boolean('prewarm_enabled');

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

        // Protección adicional: evitar sobrescritura del secreto si viene enmascarado
        if (array_key_exists('powerbi_client_secret', $data)) {
            $raw2 = (string) ($request->input('powerbi_client_secret') ?? '');
            $looksMasked2 = ($raw2 === '')
                || str_contains($raw2, "\x07")
                || (bool) preg_match('/^\*+$/u', $raw2)
                || (bool) preg_match('/^[\x{2022}\x{00B7}\x{25CF}]+$/u', $raw2);
            if ($looksMasked2) {
                unset($data['powerbi_client_secret']);
            }
        }

        // Remover cualquier clase de apariencia previa (ya no se usan)
        $data['theme_navbar_class'] = null;
        $data['theme_sidebar_class'] = null;

        $configuracion->update($data);

        // Auditoría de cambios (evento)
        try {
            $after = $configuracion->fresh();
            $maskFields = ['powerbi_client_secret'];
            $changes = [];
            foreach (array_keys($data) as $k) {
                $old = $before->{$k} ?? null;
                $new = $after->{$k} ?? null;
                if (is_array($old)) $old = json_encode($old);
                if (is_array($new)) $new = json_encode($new);
                if ($old === $new) continue;
                if (in_array($k, $maskFields, true)) {
                    $old = $old !== null ? '***' : null;
                    $new = $new !== null ? '***' : null;
                }
                $changes[] = ['field' => $k, 'old' => $old, 'new' => $new];
            }
            if (!empty($changes)) {
                \App\Models\ConfigAudit::create([
                    'user_id' => optional($request->user())->id,
                    'ip'      => (string) $request->ip(),
                    'changes' => $changes,
                ]);
            }
        } catch (\Throwable $e) {}

        // Redirección: respetar retorno si viene desde "Tema"
        $returnTo = (string) $request->input('return_to', '');
        if ($returnTo && Str::startsWith($returnTo, url('/configuraciones'))) {
            return redirect($returnTo)->with('success', 'Configuración actualizada.');
        }

        return redirect()->route('configuraciones.index')->with('success', 'Configuración actualizada.');
    }
}

