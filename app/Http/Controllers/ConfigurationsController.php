<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\ConfigurationThemePreset;
use App\Http\Requests\UpdateConfigurationRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ConfigurationsController extends Controller
{
    private const BUILTIN_PRESETS = [];

    public function index(): View
    {
        $config = $this->loadSingletonConfig();
        return view('configurations.index', compact('config'));
    }

    public function theme(): View
    {
        $config = $this->loadSingletonConfig();
        $config->theme_name = $this->normalizePresetName($config->theme_name);
        $presets = $config->themePresets()->orderBy('name')->get();

        return view('configurations.theme', [
            'config' => $config,
            'savedPresets' => $presets,
            'builtinPresets' => self::BUILTIN_PRESETS,
        ]);
    }

    public function login(): View
    {
        $config = $this->loadSingletonConfig();
        return view('configurations.login', compact('config'));
    }

    protected function loadSingletonConfig(): Configuration
    {
        return Configuration::firstOrCreate(['id' => 1], $this->defaultConfigAttributes());
    }

    protected function defaultConfigAttributes(): array
    {
        return [
            'nombre'                        => 'Default',
            'tipo_visualizacion'            => 'pastas',
            'email_locale'                  => 'es',
            'powerbi_scope'                 => 'https://analysis.windows.net/powerbi/api/.default',
            'powerbi_token_refresh_minutes' => 0,
            'site_logo_width'               => 120,
            'site_logo_height'              => 40,
            'site_logo_compact_width'       => 40,
            'site_logo_compact_height'      => 40,
            'preloader_logo_width'          => 60,
            'preloader_logo_height'         => 60,
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
            'icon_bg' => '#e5e7eb',
            'icon_text' => '#111111',
            'highlight_bg' => '#6b7280',
            'highlight_text' => '#ffffff',
            'btn_bg' => '#6b7280',
            'btn_text' => '#ffffff',
            'btn_hover' => '#4b5563',
            'btn_active' => '#374151',
        ];
    }


    public function update(UpdateConfigurationRequest $request, Configuration $configuration): RedirectResponse
    {
        // Only update columns that actually exist on the table
        $input   = $request->validated();
        $columns = Schema::getColumnListing($configuration->getTable());
        $data    = array_intersect_key($input, array_flip($columns));

        // Do not overwrite the secret when it is masked or empty
        if (array_key_exists('powerbi_client_secret', $data)) {
            $raw = (string) ($request->input('powerbi_client_secret') ?? '');
            if ($raw === '' || str_contains($raw, '•')) {
                unset($data['powerbi_client_secret']);
            }
        }

        // Package the color configuration into JSON (theme_config)
        $themeConfig = $this->extractThemePalette($request);
        $themePresetId = (int) $request->input('theme_preset_id', 0);
        $themeNameInput = $request->input('theme_name');

        $shouldResetTheme = $request->boolean('reset_theme');
        $shouldUpdateTheme = $shouldResetTheme
            || $themePresetId > 0
            || $request->has('theme_preset_id')
            || $request->has('theme_name')
            || !empty($themeConfig);

        if ($shouldResetTheme) {
            // Reset to factory colors
            $data['theme_config'] = null;
            $data['theme_name'] = null;
            $data['theme_navbar_class'] = null;
            $data['theme_sidebar_class'] = null;
        } elseif ($shouldUpdateTheme) {
            if (!empty($themeConfig)) {
                $data['theme_config'] = $themeConfig;
            }

            $themeName = (string) ($themeNameInput ?? '');
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
                $preset = $configuration->themePresets()->find($themePresetId);
                if ($preset) {
                    $preset->update(['config' => $themeConfig ?: $this->defaultThemePalette()]);
                    $data['theme_name'] = $preset->name;
                }
            }

            // Preserve the default palette when no preset or custom colors were supplied
            if ($themeName === '' && empty($themeConfig)) {
                $data['theme_config'] = $this->defaultThemePalette();
            }
        }
        // Login toggles (checkboxes): only update when the fields are present
        if ($request->has('microsoft_login_enabled')) {
            $data['microsoft_login_enabled'] = $request->boolean('microsoft_login_enabled');
        }
        if ($request->has('local_login_enabled')) {
            $data['local_login_enabled'] = $request->boolean('local_login_enabled');
        }
        // Toggle dataset warm-up
        if ($request->has('prewarm_enabled')) {
            $data['prewarm_enabled'] = $request->boolean('prewarm_enabled');
        }
        // Minimal on-demand warm-up toggle
        if ($request->has('capacity_on_demand_enable')) {
            $data['capacity_on_demand_enable'] = $request->boolean('capacity_on_demand_enable');
        }

        // Branding file management
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
                // Ignore copy failures; the file remains accessible via /storage
            }
        }

        // Compact logo (for a collapsed sidebar)
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

        // Preloader icon (centered during refresh)
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

        // Login logo (can differ from the main logo)
        if ($request->hasFile('login_logo')) {
            $path = $request->file('login_logo')->store('branding', 'public');
            $data['login_logo_path'] = $path;
        }

        // Additional guard: avoid overwriting the secret if the value is masked
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

        // Remove legacy appearance classes that are no longer used
        $data['theme_navbar_class'] = null;
        $data['theme_sidebar_class'] = null;

        // Snapshot BEFORE
        $before = $configuration->replicate();

        $configuration->update($data);

        // Change audit event
        try {
            $after = $configuration->fresh();
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

        // Redirect: honour the return URL when coming from the theme screen
        $returnTo = (string) $request->input('return_to', '');
        if ($returnTo && Str::startsWith($returnTo, url('/configurations'))) {
            return redirect($returnTo)->with('success', __('configurations.flash.updated'));
        }

        return redirect()->route('configurations.index')->with('success', __('configurations.flash.updated'));
    }

    public function storeThemePreset(Request $request): RedirectResponse
    {
        $config = $this->loadSingletonConfig();

        $validated = $request->validate([
            'preset_name' => [
                'required',
                'string',
                'max:50',
                Rule::notIn(array_merge(array_keys(self::BUILTIN_PRESETS), ['Per' . 'sonalizado'])),
            ],
        ]);

        $themeConfig = $this->extractThemePalette($request);
        if (empty($themeConfig)) {
            return redirect()
                ->route('configurations.theme', ['tab' => $request->input('tab', 'pa' . 'gina')])
                ->withInput()
                ->with('error', __('configurations.errors.no_colors'));
        }

        $name = trim($validated['preset_name']);
        $normalized = Str::lower(Str::ascii($name));
        $builtinNormalized = array_map(fn($value) => Str::lower(Str::ascii($value)), array_keys(self::BUILTIN_PRESETS));
        if (in_array($normalized, $builtinNormalized, true)) {
            return redirect()
                ->route('configurations.theme', ['tab' => $request->input('tab', 'pa' . 'gina')])
                ->withInput()
                ->with('error', __('configurations.errors.preset_reserved'));
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
                ->route('configurations.theme', ['tab' => $request->input('tab', 'pa' . 'gina')])
                ->withInput()
                ->with('error', __('configurations.errors.preset_exists'));
        }

        $preset = $config->themePresets()->create([
            'name' => $name,
            'config' => $themeConfig,
        ]);

        $config->forceFill([
            'theme_name'   => $name,
            'theme_config' => $themeConfig ?: $this->defaultThemePalette(),
        ])->save();

        return redirect()
            ->route('configurations.theme', ['tab' => $request->input('tab', 'pa' . 'gina')])
            ->withInput([
                'theme_name'      => $name,
                'theme_preset_id' => $preset->id,
            ])
            ->with('success', __('configurations.flash.preset_saved', ['name' => $name]));
    }

    public function destroyThemePreset(Request $request, ConfigurationThemePreset $preset): RedirectResponse
    {
        $config = $this->loadSingletonConfig();
        if ($preset->configuration_id !== $config->id) {
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
            ->route('configurations.theme', ['tab' => $request->input('tab', 'pa' . 'gina')])
            ->with('success', __('configurations.flash.preset_deleted'));
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

        $themeConfig = array_filter(
            $themeConfig,
            fn($value, $key) => str_starts_with($key, 'login_logo_')
                || (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)),
            ARRAY_FILTER_USE_BOTH
        );

        foreach (['login_logo_width', 'login_logo_height'] as $key) {
            if (isset($themeConfig[$key])) {
                $themeConfig[$key] = max(16, min(512, (int) $themeConfig[$key]));
            }
        }

        return $themeConfig;
    }

    public function testMail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:2000'],
        ], [], [
            'recipient' => __('settings.mail.test.recipient_label'),
            'subject' => __('settings.mail.test.subject_label'),
            'message' => __('settings.mail.test.message_label'),
        ]);

        $configuration = $this->loadSingletonConfig();

        $driver = trim((string) ($configuration->mail_driver ?? ''));
        if ($driver === '') {
            $driver = (string) config('mail.default', 'smtp');
        }

        $mailers = (array) config('mail.mailers', []);
        if (! array_key_exists($driver, $mailers)) {
            return response()->json([
                'status' => 'error',
                'message' => __('settings.mail.test.driver_missing'),
            ], 422);
        }

        if ($driver === 'smtp') {
            if (empty($configuration->mail_host) || empty($configuration->mail_port)) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('settings.mail.test.smtp_missing'),
                ], 422);
            }

            config([
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $configuration->mail_host,
                'mail.mailers.smtp.port' => (int) $configuration->mail_port,
                'mail.mailers.smtp.username' => $configuration->mail_username ?: null,
                'mail.mailers.smtp.password' => $configuration->mail_password ?: null,
                'mail.mailers.smtp.encryption' => $configuration->mail_encryption === 'none' ? null : $configuration->mail_encryption,
            ]);
        }
        if ($driver === 'mailjet') {
            if (empty($configuration->mail_username) || empty($configuration->mail_password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('settings.mail.test.mailjet_missing'),
                ], 422);
            }

            $mailjetConfig = [
                'mail.mailers.mailjet.transport' => 'mailjet',
                'mail.mailers.mailjet.api_key' => $configuration->mail_username,
                'mail.mailers.mailjet.api_secret' => $configuration->mail_password,
            ];

            if (! empty($configuration->mail_host) && str_contains((string) $configuration->mail_host, '://')) {
                $mailjetConfig['mail.mailers.mailjet.endpoint'] = rtrim((string) $configuration->mail_host, '/');
            }

            config($mailjetConfig);
        }

        config(['mail.default' => $driver]);

        if (! empty($configuration->mail_from_address)) {
            config(['mail.from.address' => $configuration->mail_from_address]);
        }
        if (! empty($configuration->mail_from_name)) {
            config(['mail.from.name' => $configuration->mail_from_name]);
        }

        $recipient = $validated['recipient'];
        $subject = trim((string) ($validated['subject'] ?? ''));
        if ($subject === '') {
            $subject = __('settings.mail.test.default_subject', ['app' => config('app.name')]);
        }
        $messageBody = $validated['message'];
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        try {
            Mail::mailer($driver)->raw($messageBody, static function ($message) use ($recipient, $subject, $fromAddress, $fromName) {
                $message->to($recipient);
                if ($fromAddress) {
                    $message->from($fromAddress, $fromName ?: null);
                }
                $message->subject($subject);
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'status' => 'error',
                'message' => __('settings.mail.test.failed', ['error' => $e->getMessage()]),
            ], 500);
        }

        return response()->json([
            'status' => 'ok',
            'message' => __('settings.mail.test.success'),
        ]);
    }
}
