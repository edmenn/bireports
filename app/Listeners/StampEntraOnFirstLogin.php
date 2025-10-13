<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Configuration;
use App\Models\User;
use App\Services\MicrosoftGraphService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class StampEntraOnFirstLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;
        if (! $user instanceof User) {
            return;
        }

        // Skip users that already declare an explicit provider.
        if (!empty($user->auth_provider)) {
            return;
        }

        // Only proceed when Microsoft login is enabled in configuration.
        $cfg = Configuration::query()->first();
        if (!$cfg || !($cfg->microsoft_login_enabled ?? false)) {
            return;
        }

        try {
            /** @var MicrosoftGraphService $graph */
            $graph = app(MicrosoftGraphService::class);

            // Look up the user in Entra using any known address (UPN/mail/proxyAddresses).
            $entry = $graph->findUserByAnyAddress($user->email);

            if (!$entry) {
                Log::info('StampEntraOnFirstLogin: email not found in Entra', [
                    'email' => $user->email,
                ]);
                return;
            }

            $objectId   = (string)($entry['id'] ?? '');
            // Prefer givenName + surname; fallback to displayName.
            $given      = trim((string)($entry['givenName'] ?? ''));
            $surname    = trim((string)($entry['surname'] ?? ''));
            $display    = trim($given . ' ' . $surname);
            if ($display === '') {
                $display = (string)($entry['displayName'] ?? '');
            }
            // Important: do not change the email to avoid breaking login flows.
            // Only stamp provider and external_id if they were missing.

            DB::transaction(function () use ($user, $objectId, $display) {
                $changed = false;

                if (empty($user->auth_provider)) {
                    $user->auth_provider = 'entra';
                    $changed = true;
                }

                if ($objectId !== '' && empty($user->external_id)) {
                    $user->external_id = $objectId;
                    $changed = true;
                }

                if ($display !== '' && empty($user->name)) {
                    $user->name = $display;
                    $changed = true;
                }

                if ($changed) {
                    $user->save();
                }
            });
        } catch (Throwable $e) {
            Log::warning('StampEntraOnFirstLogin: failed querying Graph', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
