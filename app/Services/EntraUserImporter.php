<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Throwable;

// Funciones nativas desde el namespace global (evita P1010 de Intelephense)
use function trim;
use function ltrim;
use function strtolower;
use function str_ends_with;
use function bin2hex;
use function random_bytes;
use function explode;
use function array_unique;
use function array_values;

final class EntraUserImporter
{
    public function __construct(
        private readonly MicrosoftGraphService $graph
    ) {}

    /**
     * Importa SOLO los usuarios seleccionados en la previsualización.
     * Ahora considera aliases (UPN, mail y otros) para evitar duplicados.
     *
     * @param array<int,array{email:string,name?:string|null,aliases?:array<int,string>}> $entries
     * @param string|null $assignRole Rol a asignar si el usuario es creado.
     * @return array{created:int, updated:int, skipped:int, errors:int}
     */
    public function importSelected(array $entries, ?string $assignRole = null): array
    {
        $created = 0; $updated = 0; $skipped = 0; $errors = 0;

        foreach ($entries as $e) {
            try {
                $email = trim((string)($e['email'] ?? ''));
                $name  = trim((string)($e['name'] ?? ''));
                $aliases = [];
                foreach ((array)($e['aliases'] ?? []) as $a) {
                    $a = trim((string)$a);
                    if ($a !== '') { $aliases[] = strtolower($a); }
                }

                if ($email === '') {
                    $skipped++;
                    continue;
                }
                if ($name === '') {
                    $name = $email;
                }

                // Resolver candidatos (incluye el email preferido también)
                $candidates = array_values(array_unique(array_merge([$email], $aliases)));

                $externalId = null;
                // Si el cliente no envió aliases, consultamos Graph para evitar duplicados por alias
                if (empty($aliases)) {
                    try {
                        $found = $this->graph->findUserByAnyAddress($email);
                        if (is_array($found)) {
                            $id   = trim((string)($found['id'] ?? ''));
                            // Construir nombre preferido con givenName + surname; fallback a displayName
                            $gn   = trim((string)($found['givenName'] ?? ''));
                            $sn   = trim((string)($found['surname'] ?? ''));
                            $disp = trim($gn . ' ' . $sn);
                            if ($disp === '') {
                                $disp = trim((string)($found['displayName'] ?? ''));
                            }
                            if ($id !== '') { $externalId = $id; }
                            if ($name === '' && $disp !== '') { $name = $disp; }

                            // Construimos candidatos al estilo sync()
                            $upn  = trim((string)($found['userPrincipalName'] ?? ''));
                            $mail = trim((string)($found['mail'] ?? ''));
                            if ($upn !== '')  { $candidates[] = strtolower($upn); }
                            if ($mail !== '') { $candidates[] = strtolower($mail); }
                            foreach ((array)($found['otherMails'] ?? []) as $alt) {
                                $alt = trim((string)$alt); if ($alt !== '') { $candidates[] = strtolower($alt); }
                            }
                            foreach ((array)($found['proxyAddresses'] ?? []) as $p) {
                                $p = trim((string)$p); if ($p === '') { continue; }
                                $parts = explode(':', $p, 2);
                                $addr  = isset($parts[1]) ? $parts[1] : $parts[0];
                                if ($addr !== '') { $candidates[] = strtolower($addr); }
                            }
                            $candidates = array_values(array_unique($candidates));
                        }
                    } catch (Throwable $gx) {
                        Log::warning('importSelected: no se pudo consultar Graph para aliases', [
                            'email' => $email,
                            'exception' => $gx->getMessage(),
                        ]);
                    }
                }

                // Upsert por alias si tenemos candidatos; si no, exacto.
                if (!empty($candidates)) {
                    $result = $this->upsertUserByCandidates($candidates, $email, $name, $assignRole, false, 'entra', $externalId);
                } else {
                    // Sin candidatos, hacemos upsert exacto
                    $result = $this->upsertUserExact($email, $name, $assignRole, false, 'entra', $externalId);
                }

                match ($result) {
                    'created' => $created++,
                    'updated' => $updated++,
                    default   => $skipped++,
                };
            } catch (Throwable $ex) {
                $errors++;
                Log::error('Error importSelected usuario', [
                    'entry' => $e,
                    'exception' => $ex,
                ]);
            }
        }

        return compact('created','updated','skipped','errors');
    }

    /**
     * Refresca datos (nombre, external_id si falta) de usuarios ya importados desde Entra.
     * - No crea usuarios nuevos.
     * - No cambia email.
     *
     * @return array{updated:int, skipped:int, errors:int, total:int}
     */
    public function refreshExisting(): array
    {
        $updated = 0; $skipped = 0; $errors = 0; $total = 0;

        // Procesar en chunks para no cargar toda la tabla en memoria
        User::query()
            ->where('auth_provider', 'entra')
            ->orderBy('id')
            ->chunk(200, function ($users) use (&$updated, &$skipped, &$errors, &$total) {
                foreach ($users as $user) {
                    /** @var User $user */
                    $total++;
                    try {
                        $entry = null;
                        $eid = trim((string)($user->external_id ?? ''));
                        if ($eid !== '') {
                            $entry = $this->graph->getUserById($eid);
                        }
                        if (!$entry) {
                            $entry = $this->graph->findUserByAnyAddress($user->email);
                        }
                        if (!is_array($entry)) {
                            $skipped++;
                            continue;
                        }

                        $given   = trim((string)($entry['givenName'] ?? ''));
                        $surname = trim((string)($entry['surname'] ?? ''));
                        $nameNew = trim($given . ' ' . $surname);
                        if ($nameNew === '') {
                            $nameNew = trim((string)($entry['displayName'] ?? ''));
                        }
                        $idNew   = trim((string)($entry['id'] ?? ''));
                        $accEnabled = (bool)($entry['accountEnabled'] ?? false);

                        $changed = false;
                        if ($nameNew !== '' && $nameNew !== (string)$user->name) {
                            $user->name = $nameNew;
                            $changed = true;
                        }
                        if ($idNew !== '' && empty($user->external_id)) {
                            $user->external_id = $idNew;
                            $changed = true;
                        }
                        // Bloquear solo si Entra está deshabilitado. No desbloquear si está habilitado.
                        $localBlocked = (bool)($user->is_blocked ?? false);
                        if (!$accEnabled && !$localBlocked) {
                            $user->is_blocked = true;
                            $changed = true;
                        }

                        if ($changed) {
                            $user->save();
                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } catch (Throwable $e) {
                        $errors++;
                        Log::warning('refreshExisting: error actualizando usuario', [
                            'user_id' => $user->id,
                            'email'   => $user->email,
                            'error'   => $e->getMessage(),
                        ]);
                    }
                }
            });

        return compact('updated','skipped','errors','total');
    }

    /**
     * Sincroniza usuarios desde Entra ID (Microsoft Graph) hacia la tabla users.
     *
     * @param string|null $query Prefiltro por startsWith(displayName|userPrincipalName).
     * @param string|null $onlyDomain Importar solo correos/UPN que terminen en este dominio (ej: "empresa.com").
     * @param string|null $assignRole Rol a asignar si el usuario es creado (Spatie Permission). Si null, no asigna.
     * @param bool $dryRun Si true, no escribe en BD; solo muestra qué haría.
     * @return array{created:int, updated:int, skipped:int, errors:int}
     */
    public function sync(?string $query = null, ?string $onlyDomain = null, ?string $assignRole = null, bool $dryRun = false): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors  = 0;

        $next = null;

        do {
            try {
                // Si querés traer solo activos en el modo masivo, pasá true como 3er arg:
                // $page = $this->graph->listUsers($query, $next, true);
                $page = $this->graph->listUsers($query, $next);
            } catch (Throwable $e) {
                Log::error('Graph listUsers error', ['exception' => $e]);
                throw new RuntimeException('No se pudo consultar Microsoft Graph: ' . $e->getMessage(), 0, $e);
            }

            $items = $page['items'] ?? [];
            $next  = $page['next']  ?? null;

            foreach ($items as $it) {
                try {
                    $id    = (string)($it['id'] ?? ''); // Object ID de Entra
                    // Preferir nombre compuesto por givenName + surname; fallback a displayName
                    $given   = trim((string)($it['givenName'] ?? ''));
                    $surname = trim((string)($it['surname'] ?? ''));
                    $name    = trim($given . ' ' . $surname);
                    if ($name === '') {
                        $name = trim((string)($it['displayName'] ?? ''));
                    }
                    $mail  = trim((string)($it['mail'] ?? ''));
                    $upn   = trim((string)($it['userPrincipalName'] ?? ''));

                    // === Candidatos de email (LOWERCASE) para evitar duplicados por alias ===
                    $candidates = [];
                    if ($upn  !== '') { $candidates[] = strtolower($upn); }
                    if ($mail !== '') { $candidates[] = strtolower($mail); }

                    foreach ((array)($it['otherMails'] ?? []) as $alt) {
                        $alt = trim((string)$alt);
                        if ($alt !== '') { $candidates[] = strtolower($alt); }
                    }
                    foreach ((array)($it['proxyAddresses'] ?? []) as $p) {
                        $p = trim((string)$p);
                        if ($p === '') { continue; }
                        // proxyAddresses -> "smtp:alias@dominio" o "SMTP:principal@dominio"
                        $parts = explode(':', $p, 2);
                        $addr  = isset($parts[1]) ? $parts[1] : $parts[0];
                        if ($addr !== '') { $candidates[] = strtolower($addr); }
                    }
                    $candidates = array_values(array_unique($candidates));

                    // Email preferido a guardar (UPN → mail → primer alias)
                    $emailPreferred = $upn !== '' ? $upn : ($mail !== '' ? $mail : ($candidates[0] ?? ''));

                    if ($emailPreferred === '' && empty($candidates)) {
                        $skipped++;
                        Log::warning('Usuario saltado: sin email/UPN/alias', ['graph_id' => $id, 'displayName' => $name]);
                        continue;
                    }

                    // Filtro por dominio: aceptamos si CUALQUIER alias cae en el dominio
                    if ($onlyDomain) {
                        $onlyDomain = ltrim($onlyDomain, '@');
                        $matchDomain = false;
                        foreach ($candidates ?: [$emailPreferred] as $cand) {
                            if ($cand !== '' && str_ends_with(strtolower($cand), '@' . strtolower($onlyDomain))) {
                                $matchDomain = true;
                                break;
                            }
                        }
                        if (! $matchDomain) {
                            $skipped++;
                            continue;
                        }
                    }

                    // Nombre de respaldo si no viene displayName
                    if ($name === '') {
                        $name = $emailPreferred !== '' ? $emailPreferred : ($candidates[0] ?? 'Usuario sin nombre');
                    }

                    // === Upsert por CUALQUIERA de los alias (whereIn) ===
                    $result = DB::transaction(function () use ($candidates, $emailPreferred, $name, $assignRole, $dryRun, $id) {
                        /** @var User|null $existing */
                        $existing = null;

                        if (!empty($candidates)) {
                            $existing = User::query()
                                ->whereIn('email', $candidates)
                                ->first();
                        } elseif ($emailPreferred !== '') {
                            $existing = User::query()
                                ->where('email', strtolower($emailPreferred))
                                ->orWhere('email', $emailPreferred)
                                ->first();
                        }

                        if ($existing) {
                            $changed = false;

                            if ($existing->name !== $name && $name !== '') {
                                $existing->name = $name;
                                $changed = true;
                            }
                            // === NUEVO: marcar proveedor y guardar external_id si no estaban
                            if (empty($existing->auth_provider)) {
                                $existing->auth_provider = 'entra';
                                $changed = true;
                            }
                            if (!empty($id) && empty($existing->external_id)) {
                                $existing->external_id = $id;
                                $changed = true;
                            }

                            if ($changed && !$dryRun) {
                                $existing->save();
                            }
                            return $changed ? 'updated' : 'skipped';
                        }

                        if ($dryRun) {
                            return 'created';
                        }

                        $emailToSave = $emailPreferred !== '' ? $emailPreferred : ($candidates[0] ?? '');
                        $user = new User();
                        $user->name          = $name;
                        $user->email         = $emailToSave;
                        $user->auth_provider = 'entra';     // ⬅️ marca proveedor
                        $user->external_id   = $id ?: null; // ⬅️ guarda Object ID si vino
                        // Password dummy hash (no se usa si hay login por Microsoft)
                        $user->password      = Hash::make(bin2hex(random_bytes(16)));
                        $user->save();

                        if ($assignRole) {
                            try {
                                $user->assignRole($assignRole);
                            } catch (Throwable $e) {
                                Log::warning('No se pudo asignar rol al usuario recién creado', [
                                    'email' => $emailToSave,
                                    'role'  => $assignRole,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }

                        return 'created';
                    });

                    match ($result) {
                        'created' => $created++,
                        'updated' => $updated++,
                        default   => $skipped++,
                    };
                } catch (Throwable $e) {
                    $errors++;
                    Log::error('Error al importar usuario desde Graph', [
                        'graph_user' => $it,
                        'exception'  => $e,
                    ]);
                }
            }
        } while ($next !== null);

        return compact('created', 'updated', 'skipped', 'errors');
    }

    /**
     * Upsert de usuario por email exacto (transaccional).
     * Usado por importSelected. En sync() hacemos upsert por alias.
     *
     * @return 'created'|'updated'|'skipped'
     */
    private function upsertUserExact(string $email, string $name, ?string $assignRole, bool $dryRun, ?string $provider, ?string $externalId): string
    {
        return DB::transaction(function () use ($email, $name, $assignRole, $dryRun, $provider, $externalId) {
            /** @var User|null $existing */
            $existing = User::query()->where('email', $email)->first();

            if ($existing) {
                $changed = false;
                if ($existing->name !== $name && $name !== '') {
                    $existing->name = $name;
                    $changed = true;
                }
                // Si no tenía proveedor/id externo, completar
                if ($provider && empty($existing->auth_provider)) {
                    $existing->auth_provider = $provider;
                    $changed = true;
                }
                if ($externalId && empty($existing->external_id)) {
                    $existing->external_id = $externalId;
                    $changed = true;
                }

                if ($changed && !$dryRun) {
                    $existing->save();
                }

                return $changed ? 'updated' : 'skipped';
            }

            if ($dryRun) {
                return 'created';
            }

            $user = new User();
            $user->name          = $name;
            $user->email         = $email;
            $user->auth_provider = $provider ?: null;
            $user->external_id   = $externalId ?: null;
            // Password dummy hash (no se usa si hay login por Microsoft)
            $user->password      = Hash::make(bin2hex(random_bytes(16)));
            $user->save();

            if ($assignRole) {
                try {
                    $user->assignRole($assignRole);
                } catch (Throwable $e) {
                    Log::warning('No se pudo asignar rol al usuario recién creado', [
                        'email' => $email,
                        'role'  => $assignRole,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return 'created';
        });
    }

    /**
     * Upsert por lista de candidatos (emails/aliases) intentando evitar duplicados.
     * @param array<int,string> $candidates Emails/aliases en minúsculas preferentemente
     * @return 'created'|'updated'|'skipped'
     */
    private function upsertUserByCandidates(array $candidates, string $fallbackEmail, string $name, ?string $assignRole, bool $dryRun, ?string $provider, ?string $externalId): string
    {
        $candidates = array_values(array_unique(array_map(static function ($v) {
            $v = trim((string)$v);
            return $v === '' ? '' : strtolower($v);
        }, $candidates)));

        return DB::transaction(function () use ($candidates, $fallbackEmail, $name, $assignRole, $dryRun, $provider, $externalId) {
            /** @var User|null $existing */
            $existing = null;

            if (!empty($candidates)) {
                $existing = User::query()->whereIn('email', $candidates)->first();
            }
            if (!$existing && $fallbackEmail !== '') {
                $existing = User::query()
                    ->where('email', strtolower($fallbackEmail))
                    ->orWhere('email', $fallbackEmail)
                    ->first();
            }

            if ($existing) {
                $changed = false;

                if ($existing->name !== $name && $name !== '') {
                    $existing->name = $name;
                    $changed = true;
                }
                if ($provider && empty($existing->auth_provider)) {
                    $existing->auth_provider = $provider;
                    $changed = true;
                }
                if ($externalId && empty($existing->external_id)) {
                    $existing->external_id = $externalId;
                    $changed = true;
                }

                if ($changed && !$dryRun) {
                    $existing->save();
                }
                return $changed ? 'updated' : 'skipped';
            }

            if ($dryRun) {
                return 'created';
            }

            $emailToSave = $fallbackEmail !== '' ? $fallbackEmail : ($candidates[0] ?? '');
            $user = new User();
            $user->name          = $name;
            $user->email         = $emailToSave;
            $user->auth_provider = $provider ?: null;
            $user->external_id   = $externalId ?: null;
            $user->password      = Hash::make(bin2hex(random_bytes(16)));
            $user->save();

            if ($assignRole) {
                try {
                    $user->assignRole($assignRole);
                } catch (Throwable $e) {
                    Log::warning('No se pudo asignar rol al usuario recién creado', [
                        'email' => $emailToSave,
                        'role'  => $assignRole,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return 'created';
        });
    }
}
