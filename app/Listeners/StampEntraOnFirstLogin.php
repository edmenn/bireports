<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Configuracion;
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

        // Si ya fue marcado como externo o explícitamente local, no tocamos.
        if (!empty($user->auth_provider)) {
            return;
        }

        // Solo si el login Microsoft está habilitado en Configuraciones.
        $cfg = Configuracion::query()->first();
        if (!$cfg || !($cfg->microsoft_login_enabled ?? false)) {
            return;
        }

        try {
            /** @var MicrosoftGraphService $graph */
            $graph = app(MicrosoftGraphService::class);

            // Buscar en Entra por cualquiera de sus direcciones (UPN/mail/proxyAddresses)
            $entry = $graph->findUserByAnyAddress($user->email);

            if (!$entry) {
                // No existe en el tenant → no marcamos (opcional: log de aviso)
                Log::info('StampEntraOnFirstLogin: email no encontrado en Entra', [
                    'email' => $user->email,
                ]);
                return;
            }

            $objectId   = (string)($entry['id'] ?? '');
            // Preferir givenName + surname; fallback a displayName
            $given      = trim((string)($entry['givenName'] ?? ''));
            $surname    = trim((string)($entry['surname'] ?? ''));
            $display    = trim($given . ' ' . $surname);
            if ($display === '') {
                $display = (string)($entry['displayName'] ?? '');
            }
            // Importante: NO cambiamos el email aquí para no romper el login.
            // Solo marcamos proveedor y guardamos external_id si no lo tenía.

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
            Log::warning('StampEntraOnFirstLogin: error consultando Graph', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
