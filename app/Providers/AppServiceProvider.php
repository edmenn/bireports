<?php

namespace App\Providers;

use App\Mail\MailjetTransport;
use App\Models\Configuration;
use App\Models\ReportSubscription;
use App\Policies\ReportSubscriptionPolicy;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mime\Address;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(ReportSubscription::class, ReportSubscriptionPolicy::class);

        Mail::extend('mailjet', function (array $config = []) {
            $fromAddress = (string) config('mail.from.address', '');

            return new MailjetTransport(
                new Client(['timeout' => (float) ($config['timeout'] ?? 10)]),
                (string) ($config['api_key'] ?? ''),
                (string) ($config['api_secret'] ?? ''),
                (string) ($config['endpoint'] ?? 'https://api.mailjet.com/v3.1/send'),
                filter_var($config['sandbox'] ?? false, FILTER_VALIDATE_BOOL),
                $fromAddress !== '' ? new Address($fromAddress, (string) config('mail.from.name', '')) : null,
            );
        });

        if (app()->environment('production')) {
            if (config('app.url')) {
                URL::forceRootUrl(config('app.url'));
            }
            URL::forceScheme('https');
        }

        try {
            $cfg = Configuration::query()->find(1);
        } catch (\Throwable) {
            $cfg = null;
        }

        View::share('appCfg', $cfg);

        if (! $cfg) {
            return;
        }

        $mail = [];
        $driver = trim((string) ($cfg->mail_driver ?? ''));
        if ($driver !== '') {
            $mail['mail.default'] = $driver;
        }
        if ($cfg->mail_from_address) {
            $mail['mail.from.address'] = $cfg->mail_from_address;
        }
        if ($cfg->mail_from_name) {
            $mail['mail.from.name'] = $cfg->mail_from_name;
        }

        $smtp = array_filter([
            'mail.mailers.smtp.host' => $cfg->mail_host,
            'mail.mailers.smtp.port' => $cfg->mail_port ? (int) $cfg->mail_port : null,
            'mail.mailers.smtp.username' => $cfg->mail_username,
            'mail.mailers.smtp.password' => $cfg->mail_password,
        ], fn ($value) => $value !== null && $value !== '');
        if ($smtp !== []) {
            $smtp['mail.mailers.smtp.transport'] = 'smtp';
            $smtp['mail.mailers.smtp.encryption'] = $cfg->mail_encryption === 'none' ? null : $cfg->mail_encryption;
        }

        $mailjet = array_filter([
            'mail.mailers.mailjet.api_key' => $cfg->mail_username,
            'mail.mailers.mailjet.api_secret' => $cfg->mail_password,
            'mail.mailers.mailjet.endpoint' => str_contains((string) $cfg->mail_host, '://') ? rtrim((string) $cfg->mail_host, '/') : null,
        ], fn ($value) => $value !== null && $value !== '');

        config($mail + $smtp + $mailjet);
    }
}
