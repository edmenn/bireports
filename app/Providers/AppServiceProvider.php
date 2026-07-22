<?php

namespace App\Providers;

use App\Mail\MailjetTransport;
use App\Models\Configuration;
use App\Models\ReportSubscription;
use App\Policies\ReportSubscriptionPolicy;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ReportSubscription::class, ReportSubscriptionPolicy::class);

        Mail::extend('mailjet', function (array $config = []) {
            $apiKey = (string) ($config['api_key'] ?? '');
            $apiSecret = (string) ($config['api_secret'] ?? '');
            $endpoint = (string) ($config['endpoint'] ?? 'https://api.mailjet.com/v3.1/send');
            $timeout = (float) ($config['timeout'] ?? 10);
            $sandboxRaw = $config['sandbox'] ?? false;
            $sandbox = filter_var($sandboxRaw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $sandbox = $sandbox === null ? false : $sandbox;

            $defaultFromAddress = (string) config('mail.from.address', '');
            $defaultFromName = (string) config('mail.from.name', '');
            $defaultFrom = $defaultFromAddress !== '' ? new Address($defaultFromAddress, $defaultFromName) : null;

            return new MailjetTransport(
                new Client(['timeout' => $timeout]),
                $apiKey,
                $apiSecret,
                $endpoint,
                $sandbox,
                $defaultFrom
            );
        });

        // In production, enforce absolute URLs and HTTPS.
        if (app()->environment('production')) {
            try {
                if (config('app.url')) {
                    URL::forceRootUrl(config('app.url'));
                }
            } catch (\Throwable $e) {
                // Do not block the boot sequence because of configuration issues.
            }
            URL::forceScheme('https');
        }
        // Always keep the footer fixed (native AdminLTE layout behaviour).
        config(['adminlte.layout_fixed_footer' => true]);
        // Load branding/theme settings from the single configuration row.
        try {
            $cfg = Configuration::query()->find(1);
        } catch (\Throwable $e) {
            $cfg = null;
        }

        if ($cfg) {
            $mailOverrides = [];
            $mailDriver = trim((string) ($cfg->mail_driver ?? ''));
            if ($mailDriver !== '') {
                $mailOverrides['mail.default'] = $mailDriver;
            }

            $smtpOverrides = [];
            if (! empty($cfg->mail_host)) {
                $smtpOverrides['mail.mailers.smtp.host'] = $cfg->mail_host;
            }
            if (! empty($cfg->mail_port)) {
                $smtpOverrides['mail.mailers.smtp.port'] = (int) $cfg->mail_port;
            }
            $encryption = $cfg->mail_encryption;
            if ($encryption !== null && $encryption !== '') {
                $smtpOverrides['mail.mailers.smtp.encryption'] = $encryption === 'none' ? null : $encryption;
            }
            if (! empty($cfg->mail_username)) {
                $smtpOverrides['mail.mailers.smtp.username'] = $cfg->mail_username;
            }
            if (! empty($cfg->mail_password)) {
                $smtpOverrides['mail.mailers.smtp.password'] = $cfg->mail_password;
            }
            if (! empty($smtpOverrides)) {
                $smtpOverrides['mail.mailers.smtp.transport'] = 'smtp';
            }
            $mailjetOverrides = [];
            if (! empty($cfg->mail_username)) {
                $mailjetOverrides['mail.mailers.mailjet.api_key'] = $cfg->mail_username;
            }
            if (! empty($cfg->mail_password)) {
                $mailjetOverrides['mail.mailers.mailjet.api_secret'] = $cfg->mail_password;
            }
            if (! empty($cfg->mail_host) && str_contains((string) $cfg->mail_host, '://')) {
                $mailjetOverrides['mail.mailers.mailjet.endpoint'] = rtrim((string) $cfg->mail_host, '/');
            }
            if (! empty($cfg->mail_from_address)) {
                $mailOverrides['mail.from.address'] = $cfg->mail_from_address;
            }
            if (! empty($cfg->mail_from_name)) {
                $mailOverrides['mail.from.name'] = $cfg->mail_from_name;
            }
            if (! empty($mailOverrides)) {
                config($mailOverrides);
            }
            if (! empty($smtpOverrides)) {
                config($smtpOverrides);
            }
            if (! empty($mailjetOverrides)) {
                config($mailjetOverrides);
            }

            $isDark = optional(auth()->user())->theme === 'dark'
                || session('theme') === 'dark'
                || (bool) config('adminlte.layout_dark_mode', false);
            // Force the global footer to stay fixed.
            config(['adminlte.layout_fixed_footer' => true]);

            $navbarClass = $cfg->theme_navbar_class ?: config('adminlte.classes_topnav', 'navbar-white navbar-light');
            $sidebarClass = $cfg->theme_sidebar_class ?: config('adminlte.classes_sidebar', 'sidebar-dark-primary elevation-4');
            $navbarTokens = array_filter(preg_split('/\\s+/', (string) $navbarClass));
            $bgToken = null;
            foreach ($navbarTokens as $token) {
                if (Str::startsWith($token, 'navbar-') && ! in_array($token, ['navbar-dark', 'navbar-light'], true)) {
                    $bgToken = $token;
                    break;
                }
            }
            if ($bgToken) {
                $tone = Str::after($bgToken, 'navbar-');
                $footerTextClass = in_array($tone, ['white', 'light', 'default'], true) ? 'text-muted' : 'text-white';
                $footerClass = 'bg-'.$tone.' '.$footerTextClass.' border-0 text-sm';
            } elseif (Str::contains($navbarClass, 'navbar-dark')) {
                $footerClass = 'bg-dark text-light border-0 text-sm';
            } else {
                $footerClass = 'bg-white text-muted border-0 text-sm';
            }

            config([
                'adminlte.theme.navbar_class' => $navbarClass,
                'adminlte.theme.sidebar_class' => $sidebarClass,
                'adminlte.theme.footer_class' => $footerClass,
            ]);

            if (! $isDark) {
                config([
                    'adminlte.classes_topnav' => $navbarClass,
                    'adminlte.classes_sidebar' => $sidebarClass,
                    'adminlte.classes_footer' => $footerClass,
                ]);
            }

            if (! empty($cfg->site_title)) {
                config(['adminlte.title' => $cfg->site_title]);
            }
            if ($isDark) {
                // When dark mode is enabled, enforce dark classes for navbar and sidebar.
                config([
                    'adminlte.classes_topnav' => 'navbar-dark navbar-dark',
                    'adminlte.classes_sidebar' => 'sidebar-dark-primary elevation-4',
                    'adminlte.classes_footer' => 'bg-dark text-light border-0 text-sm',
                ]);
            }
            // AdminLTE expects:
            //  - logo_img     => compact variant (xs)
            //  - logo_img_xl  => main logo (xl)
            // Map configuration fields accordingly.
            if (! empty($cfg->site_logo_path)) {
                // Full-size logo.
                $logoUrl = Storage::disk('public')->url($cfg->site_logo_path);
                config([
                    'adminlte.logo_img_xl' => ltrim(parse_url($logoUrl, PHP_URL_PATH), '/'),
                    'adminlte.logo_img_xl_class' => 'brand-image',
                ]);
            }
            if (! empty($cfg->site_logo_compact_path)) {
                // Compact logo.
                $logoSmallUrl = Storage::disk('public')->url($cfg->site_logo_compact_path);
                config([
                    'adminlte.logo_img' => ltrim(parse_url($logoSmallUrl, PHP_URL_PATH), '/'),
                    'adminlte.logo_img_class' => 'brand-image',
                ]);
            }
            if (! empty($cfg->site_favicon_path)) {
                // When a favicon is present, enable ico usage and expose the relative path for the layout.
                $favUrl = Storage::disk('public')->url($cfg->site_favicon_path);
                config([
                    'adminlte.use_ico_only' => true,
                    // Ruta relativa para usar con asset(...)
                    'adminlte.favicon_path' => ltrim(parse_url($favUrl, PHP_URL_PATH), '/'),
                ]);
            }
            // Preloader (central image shown during refresh).
            if (! empty($cfg->preloader_logo_path)) {
                $preUrl = Storage::disk('public')->url($cfg->preloader_logo_path);
                config([
                    'adminlte.preloader.img.path' => ltrim(parse_url($preUrl, PHP_URL_PATH), '/'),
                    'adminlte.preloader.img.alt' => $cfg->site_title ?: 'Loading...',
                    // Avoid effects that distort the perception of the icon.
                    'adminlte.preloader.img.effect' => null,
                ]);
            }
            if (! empty($cfg->preloader_logo_width)) {
                config(['adminlte.preloader.img.width' => (int) $cfg->preloader_logo_width]);
            }
            if (! empty($cfg->preloader_logo_height)) {
                config(['adminlte.preloader.img.height' => (int) $cfg->preloader_logo_height]);
            }
            // Optional: honour preloader size stored in theme_config.
            try {
                $varsTheme = is_array($cfg->theme_config)
                    ? $cfg->theme_config
                    : (is_string($cfg->theme_config) ? (json_decode($cfg->theme_config, true) ?: []) : []);
                if (! empty($varsTheme['preloader_logo_width'])) {
                    config(['adminlte.preloader.img.width' => (int) $varsTheme['preloader_logo_width']]);
                }
                if (! empty($varsTheme['preloader_logo_height'])) {
                    config(['adminlte.preloader.img.height' => (int) $varsTheme['preloader_logo_height']]);
                }
            } catch (\Throwable $e) {
            }
            if (! empty($cfg->login_logo_path)) {
                $authLogoUrl = Storage::disk('public')->url($cfg->login_logo_path);
                config([
                    'adminlte.auth_logo.enabled' => true,
                    'adminlte.auth_logo.img.path' => ltrim(parse_url($authLogoUrl, PHP_URL_PATH), '/'),
                ]);
                // Tamaño opcional del logo de login desde theme_config
                $varsAuth = is_array($cfg->theme_config) ? $cfg->theme_config : (is_string($cfg->theme_config) ? (json_decode($cfg->theme_config, true) ?: []) : []);
                if (! empty($varsAuth['login_logo_width'])) {
                    config(['adminlte.auth_logo.img.width' => (int) $varsAuth['login_logo_width']]);
                }
                if (! empty($varsAuth['login_logo_height'])) {
                    config(['adminlte.auth_logo.img.height' => (int) $varsAuth['login_logo_height']]);
                }
            }

            // Inyectar CSS de tema y footer mediante stacks (compatibles con AdminLTE)
            View::composer('adminlte::page', function ($view) use ($cfg) {
                $cfg = Configuration::query()->find(1) ?? $cfg;
                $publicAsset = fn (?string $path) => $path
                    ? ltrim((string) parse_url(Storage::disk('public')->url($path), PHP_URL_PATH), '/')
                    : null;
                if ($path = $publicAsset($cfg->site_logo_path)) {
                    config(['adminlte.logo_img_xl' => $path, 'adminlte.logo_img_xl_class' => 'brand-image']);
                }
                if ($path = $publicAsset($cfg->site_logo_compact_path)) {
                    config(['adminlte.logo_img' => $path, 'adminlte.logo_img_class' => 'brand-image']);
                }
                if ($path = $publicAsset($cfg->site_favicon_path)) {
                    config(['adminlte.use_ico_only' => true, 'adminlte.favicon_path' => $path]);
                }
                if ($path = $publicAsset($cfg->preloader_logo_path)) {
                    config([
                        'adminlte.preloader.img.path' => $path,
                        'adminlte.preloader.img.width' => (int) ($cfg->preloader_logo_width ?: 60),
                        'adminlte.preloader.img.height' => (int) ($cfg->preloader_logo_height ?: 60),
                        'adminlte.preloader.img.effect' => null,
                    ]);
                }
                $vars = is_array($cfg->theme_config) ? $cfg->theme_config : (is_string($cfg->theme_config) ? json_decode($cfg->theme_config, true) : []);
                $isDark = optional(auth()->user())->theme === 'dark'
                    || session('theme') === 'dark'
                    || (bool) config('adminlte.layout_dark_mode', false);

                if (! $isDark) {
                    $view->getFactory()->startPush('css', view('partials.theme-styles', ['theme' => $vars])->render());
                }

                // Ajustes de tamaño de logos
                $css = '';
                $lw = (int) ($cfg->site_logo_width ?? 0);
                $lh = (int) ($cfg->site_logo_height ?? 0);
                $cw = (int) ($cfg->site_logo_compact_width ?? 0);
                $ch = (int) ($cfg->site_logo_compact_height ?? 0);
                if ($lw > 0 || $lh > 0) {
                    $w = max(16, min($lw ?: 256, 256));
                    $h = max(16, min($lh ?: 128, 128));
                    $css .= ".brand-link .brand-image{width:auto!important;height:auto!important;max-width:min({$w}px,100%)!important;max-height:min({$h}px,calc(var(--app-navbar-h,56px) - 12px))!important;object-fit:contain;}\n";
                }
                if ($cw > 0 || $ch > 0) {
                    $w = max(16, min($cw ?: 256, 256));
                    $h = max(16, min($ch ?: 128, 128));
                    $css .= ".brand-link .brand-image-xs,.brand-link .brand-image-sm{width:auto!important;height:auto!important;max-width:min({$w}px,100%)!important;max-height:min({$h}px,calc(var(--app-navbar-h,56px) - 12px))!important;object-fit:contain;}\n";
                }
                if ($css !== '') {
                    $view->getFactory()->startPush('css', '<style>'.$css.'</style>');
                }

                // Footer aligned with the active theme plus color synchronization
                $footerEnabled = $cfg->footer_enabled ?? true;
                if ($footerEnabled) {
                    $footerRaw = $cfg->footer_text;
                    $footerText = is_string($footerRaw) ? trim($footerRaw) : '';
                    $replacements = [
                        'app' => $cfg->site_title ?: config('app.name', 'PowerBIDash'),
                        'year' => date('Y'),
                    ];

                    $view->getFactory()->startSection('footer', view('partials.footer-fixed', [
                        'text' => $footerText !== '' ? $footerText : null,
                        'text_key' => 'footer.signature',
                        'replacements' => $replacements,
                    ])->render());
                    $view->getFactory()->startPush('js', view('partials.footer-sync-js')->render());
                }
            });

            // CSS específico para login: imagen de fondo responsive (contain) + tamaño de logo
            View::composer([
                'adminlte::auth.auth-page',
                'adminlte::auth.login',
                'adminlte::auth.register',
                'adminlte::auth.passwords.email',
                'adminlte::auth.passwords.reset',
            ], function ($view) use ($cfg) {
                $cfg = Configuration::query()->find(1) ?? $cfg;
                $css = '';
                try {
                    if (! empty($cfg->login_bg_path)) {
                        $bgUrl = Storage::disk('public')->url($cfg->login_bg_path);
                        $bgRel = ltrim(parse_url($bgUrl, PHP_URL_PATH), '/');
                        $css .= ".login-page,.register-page,.auth-page{background-image:url('/{$bgRel}');background-position:center center;background-repeat:no-repeat;background-size:contain;background-attachment:fixed;}\n";
                    }
                } catch (\Throwable $e) {
                }
                $varsAuth = is_array($cfg->theme_config) ? $cfg->theme_config : (is_string($cfg->theme_config) ? (json_decode($cfg->theme_config, true) ?: []) : []);
                $view->getFactory()->startPush('css', view('partials.theme-styles', ['theme' => $varsAuth])->render());
                $w = isset($varsAuth['login_logo_width']) ? (int) $varsAuth['login_logo_width'] : 0;
                $h = isset($varsAuth['login_logo_height']) ? (int) $varsAuth['login_logo_height'] : 0;
                if ($w > 0 || $h > 0) {
                    $s = '';
                    $maxW = $w > 0 ? $w : 512;
                    if ($h > 0) {
                        $s .= "max-height: {$h}px;";
                    }
                    $css .= ".auth-logo img,.login-logo img,.brand-logo{ {$s} width:auto; height:auto; object-fit:contain; max-width:min(100%, {$maxW}px); }\n";
                }
                if ($css !== '') {
                    try {
                        $view->getFactory()->startPush('css', "<style>{$css}</style>");
                    } catch (\Throwable $e) {
                    }
                }
            });
            // Cache busting para assets estáticos servidos por Nginx con cache largo
            try {
                $css = public_path('css/custom.css');
                if (is_file($css)) {
                    $v = (string) @filemtime($css) ?: (string) time();
                    config([
                        'adminlte.plugins.AppTheme.files' => [
                            ['type' => 'css', 'asset' => true, 'location' => 'css/custom.css?v='.$v],
                        ],
                    ]);
                }
            } catch (\Throwable $e) {
                // Ignorar fallos de lectura
            }
            try {
                $js = public_path('js/menu-state.js');
                if (is_file($js)) {
                    $v = (string) @filemtime($js) ?: (string) time();
                    config([
                        'adminlte.plugins.AppMenuState.files' => [
                            ['type' => 'js', 'asset' => true, 'location' => 'js/menu-state.js?v='.$v],
                        ],
                    ]);
                }
            } catch (\Throwable $e) {
                // Ignorar fallos de lectura
            }
        }
    }
}
