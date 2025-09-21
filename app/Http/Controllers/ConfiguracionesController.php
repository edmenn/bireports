<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\ConfiguracionThemePreset;
use App\Http\Requests\UpdateConfiguracionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ConfiguracionesController extends Controller
{
    private const BUILTIN_PRESETS = [];

    public function index(): View
    {
        $config = $this->loadSingletonConfig();
        return view('configuraciones.index', compact('config'));
    }

    public function theme(): View
    {
        $config = $this->loadSingletonConfig();
        $config->theme_name = $this->normalizePresetName($config->theme_name);
        $presets = $config->themePresets()->orderBy('name')->get();

        return view('configuraciones.tema', [
            'config' => $config,
            'savedPresets' => $presets,
            'builtinPresets' => self::BUILTIN_PRESETS,
        ]);
    }

    public function login(): View
    {
        $config = $this->loadSingletonConfig();
        return view('configuraciones.login', compact('config'));
    }

    protected function loadSingletonConfig(): Configuracion
    {
        return Configuracion::firstOrCreate(['id' => 1], $this->defaultConfigAttributes());
    }

    protected function defaultConfigAttributes(): array
    {
        return [
            'nombre'                        => 'Default',
            'tipo_visualizacion'            => 'pastas',
            'email_locale'                  => 'es',
            'powerbi_scope'                 => 'https://analysis.windows.net/powerbi/api/.default',
            'powerbi_token_refresh_minutes' => 0,
        ];
    }

    protected function normalizePresetName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $normalized = Str::lower(Str::ascii($name));
        return $normalized === 'marron' ? 'Marron' : $name;
    }

    protected function defaultThemePalette(): array
    {
        return [
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
        $themeConfig = $this->extractThemePalette($request);
        if (!empty($themeConfig)) {
            $data['theme_config'] = $themeConfig;
        }

        $themePresetId = (int) $request->input('theme_preset_id', 0);
        $themeName = (string) ($request->input('theme_name') ?? '');
        $themeName = $this->normalizePresetName($themeName) ?? '';

        if (Str::lower($themeName) === Str::lower('Personalizado')) {
            $themeName = '';
        }

        if ($themeName === '') {
            $data['theme_name'] = null;
        } else {
            $data['theme_name'] = $themeName;
        }

        if ($themePresetId > 0 && $themeName !== '') {
            $preset = $configuracion->themePresets()->find($themePresetId);
            if ($preset) {
                $preset->update(['config' => $themeConfig ?: $this->defaultThemePalette()]);
                $data['theme_name'] = $preset->name;
            }
        } else {
            $themePresetId = 0;
        }

        // Si no se eligió preset y tampoco se enviaron valores de colores, aplicamos el default
        if ($themeName === '' && empty($themeConfig)) {
            $data['theme_config'] = $this->defaultThemePalette();
        }

        // Toggles de login (checkbox): solo actualizar si vienen en la request
        if ($request->has('microsoft_login_enabled')) {
            $data['microsoft_login_enabled'] = $request->boolean('microsoft_login_enabled');
        }
        if ($request->has('local_login_enabled')) {
            $data['local_login_enabled'] = $request->boolean('local_login_enabled');
        }
        // Toggle de pre-warm
        if ($request->has('prewarm_enabled')) {
            $data['prewarm_enabled'] = $request->boolean('prewarm_enabled');
        }
        // [Minimalista] Toggle de encendido on-demand
        if ($request->has('capacity_on_demand_enable')) {
            $data['capacity_on_demand_enable'] = $request->boolean('capacity_on_demand_enable');
        }

        // Manejo de archivos (branding)
        if ($request->hasFile('site_logo')) {
            $path = $request->file('site_logo')->store('branding', 'public');
            $data['site_logo_path'] = $path;
        }
        if ($request->has('site_logo_width')) {
            $data['site_logo_width'] = $request->integer('site_logo_width');
        }
        if ($request->has('site_logo_height')) {
            $data['site_logo_height'] = $request->integer('site_logo_height');
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
        if ($request->has('site_logo_compact_width')) {
            $data['site_logo_compact_width'] = $request->integer('site_logo_compact_width');
        }
        if ($request->has('site_logo_compact_height')) {
            $data['site_logo_compact_height'] = $request->integer('site_logo_compact_height');
        }

        // Icono del preloader (centro al refrescar)
        if ($request->hasFile('preloader_logo')) {
            $path = $request->file('preloader_logo')->store('branding', 'public');
            $data['preloader_logo_path'] = $path;
        }
        if ($request->has('preloader_logo_width')) {
            $data['preloader_logo_width'] = $request->integer('preloader_logo_width');
        }
        if ($request->has('preloader_logo_height')) {
            $data['preloader_logo_height'] = $request->integer('preloader_logo_height');
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

        // Snapshot ANTES
        $before = $configuracion->replicate();

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

    public function storeThemePreset(Request $request): RedirectResponse
    {
        $config = $this->loadSingletonConfig();

        $validated = $request->validate([
            'preset_name' => [
                'required',
                'string',
                'max:50',
                Rule::notIn(array_merge(array_keys(self::BUILTIN_PRESETS), ['Personalizado'])),
            ],
        ]);

        $themeConfig = $this->extractThemePalette($request);
        if (empty($themeConfig)) {
            return redirect()
                ->route('configuraciones.tema', ['tab' => $request->input('tab', 'pagina')])
                ->withInput()
                ->with('error', 'No hay colores configurados para guardar.');
        }

        $name = trim($validated['preset_name']);
        $normalized = Str::lower(Str::ascii($name));
        $builtinNormalized = array_map(fn($value) => Str::lower(Str::ascii($value)), array_keys(self::BUILTIN_PRESETS));
        if (in_array($normalized, $builtinNormalized, true)) {
            return redirect()
                ->route('configuraciones.tema', ['tab' => $request->input('tab', 'pagina')])
                ->withInput()
                ->with('error', 'No puedes usar el nombre de un tema predefinido.');
        }

        $exists = $config->themePresets()
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->exists();

        if (! $exists) {
            $exists = $config->themePresets()
                ->get()
                ->contains(fn($preset) => Str::lower(Str::ascii($preset->name)) === $normalized);
        }

        if ($exists) {
            return redirect()
                ->route('configuraciones.tema', ['tab' => $request->input('tab', 'pagina')])
                ->withInput()
                ->with('error', 'Ya existe un tema con ese nombre.');
        }

        $config->themePresets()->create([
            'name' => $name,
            'config' => $themeConfig,
        ]);

        return redirect()
            ->route('configuraciones.tema', ['tab' => $request->input('tab', 'pagina')])
            ->with('success', sprintf('Tema guardado como "%s".', $name));
    }

    public function destroyThemePreset(Request $request, ConfiguracionThemePreset $preset): RedirectResponse
    {
        $config = $this->loadSingletonConfig();
        if ($preset->configuracion_id !== $config->id) {
            abort(404);
        }

        $presetName = $preset->name;
        $preset->delete();

        if ($config->theme_name === $presetName) {
            $config->forceFill([
                'theme_name' => null,
                'theme_config' => $this->defaultThemePalette(),
            ])->save();
        }

        return redirect()
            ->route('configuraciones.tema', ['tab' => $request->input('tab', 'pagina')])
            ->with('success', 'Tema eliminado.');
    }

    protected function extractThemePalette(Request $request): array
    {
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

        if ($request->filled('login_logo_width')) {
            $themeConfig['login_logo_width'] = (int) $request->input('login_logo_width');
        }
        if ($request->filled('login_logo_height')) {
            $themeConfig['login_logo_height'] = (int) $request->input('login_logo_height');
        }

        return array_filter($themeConfig, fn($v) => $v !== null && $v !== '');
    }
}
