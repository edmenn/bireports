<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\EntraUserImporter;
use App\Services\MicrosoftGraphService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

// Import native functions from the global namespace (avoid Intelephense P1010)
use function trim;
use function strtolower;
use function explode;
use function is_array;
//use function is_string;
use function array_values;
use function mb_strtolower;
use function strcasecmp;

final class UsersSyncController extends Controller
{
    public function __construct(
        private readonly MicrosoftGraphService $graph,
        private readonly EntraUserImporter $importer
    ) {
        // Admin role middleware is defined in routes/web.php
    }

    /**
     * Preview Entra users with pagination (AJAX).
     * GET /usuarios/sync-entra/preview
     */
    public function preview(Request $request): JsonResponse
    {
        $query      = trim($request->input('query', ''));
        $onlyActive = (bool) $request->input('only_active', false);
        $next       = $request->string('next')->toString() ?: null;
        $sort       = $request->string('sort')->toString();
        $pageSize   = (int) $request->input('page_size', 50);
        if ($pageSize < 10) { $pageSize = 10; }
        if ($pageSize > 999) { $pageSize = 999; }
        if ($onlyActive && $query === '') { $pageSize = 999; }

        try {
            $items = [];
            $nextLink = $next;
            $fetchAll = ($onlyActive && $query === '');
            $maxTotal = 20000;
            $maxLoops = 200;
            $truncated = false;

            // Acumular suficientes elementos respetando filtros locales (dominio)
            $loops = 0;
            $orderBy = ($query === '' && !$fetchAll) ? 'displayName' : null;
            $graphOnlyActive = ($onlyActive && $query !== '' && !$fetchAll);
            do {
                $page   = $this->graph->listUsers(
                    $query !== '' ? $query : null,
                    $nextLink,
                    $graphOnlyActive,
                    $orderBy,
                    $pageSize
                );
                $pageItems = (array) ($page['items'] ?? []);
                $prevNext  = $nextLink;
                $nextLink  = $page['next'] ?? null;

                if ($onlyActive) {
                    $pageItems = array_values(array_filter($pageItems, function (array $u): bool {
                        // Treat null as active to avoid dropping guests or incomplete records.
                        return ($u['accountEnabled'] ?? true) !== false;
                    }));
                }

                $items = array_merge($items, $pageItems);
                $loops++;
                // Cortar si Graph devuelve el mismo next (posible bucle)
                if ($nextLink !== null && $nextLink === $prevNext) {
                    $nextLink = null;
                }
                if ($fetchAll && count($items) >= $maxTotal) {
                    $truncated = true;
                    $nextLink = null;
                }
            } while (
                $fetchAll
                    ? ($nextLink !== null && $loops < $maxLoops)
                    : (count($items) < $pageSize && $nextLink !== null && $loops < 10)
            );

            if ($fetchAll && $loops >= $maxLoops && $nextLink !== null) {
                $truncated = true;
                $nextLink = null;
            }

            // Limitar al tama¤o solicitado y exponer el cursor para continuar
            if (!$fetchAll) {
                $items = array_slice($items, 0, $pageSize);
            }

            $displayName = static function (array $u): string {
                $given   = trim((string)($u['givenName'] ?? ''));
                $surname = trim((string)($u['surname'] ?? ''));
                $display = trim($given . ' ' . $surname);
                if ($display === '') {
                    $display = trim((string)($u['displayName'] ?? ''));
                }
                if ($display === '') {
                    $mail = trim((string)($u['mail'] ?? ''));
                    $upn  = trim((string)($u['userPrincipalName'] ?? ''));
                    $display = $mail !== '' ? $mail : $upn;
                }
                return $display;
            };

            if (in_array($sort, ['name_asc', 'name_desc'], true)) {
                usort($items, function (array $a, array $b) use ($displayName, $sort): int {
                    $cmp = strcmp(mb_strtolower($displayName($a)), mb_strtolower($displayName($b)));
                    return $sort === 'name_desc' ? -$cmp : $cmp;
                });
            } elseif ($fetchAll && $query === '') {
                usort($items, function (array $a, array $b) use ($displayName): int {
                    return strcmp(mb_strtolower($displayName($a)), mb_strtolower($displayName($b)));
                });
            }

            // Render Blade partial rows
            $rows = view('users.partials.entra_rows', ['items' => $items])->render();

            $hasMore = (!$truncated && $nextLink !== null);

            return response()->json([
                'rows'      => $rows,
                'next'      => $nextLink,
                'count'     => count($items),
                'has_more'  => $hasMore,
                'truncated' => $truncated,
            ]);
        } catch (Throwable $e) {
            Log::error('Preview Entra error', ['exception' => $e]);
            return response()->json([
                'message' => __('users.sync.errors.graph'),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk import (without selection, honouring filters).
     * POST /usuarios/sync-entra
     */
    public function sync(Request $request): RedirectResponse
    {
        $query      = trim($request->input('query', ''));
        $domain     = trim($request->input('domain', ''));
        $assignRole = trim($request->input('assign_role', ''));
        $dryRun     = (bool) $request->input('dry_run', false);

        try {
            $stats = $this->importer->sync(
                $query !== '' ? $query : null,
                $domain !== '' ? $domain : null,
                $assignRole !== '' ? $assignRole : null,
                $dryRun
            );

            $summary = $dryRun
                ? __('users.sync.summary.dry_run', $stats)
                : __('users.sync.summary.import', $stats);

            return redirect()->route('users.index')->with('success', $summary);
        } catch (Throwable $e) {
            Log::error('Sync Entra error', ['exception' => $e]);
            return redirect()->route('users.index')->with(
                'error',
                __('users.sync.errors.sync', ['message' => $e->getMessage()])
            );
        }
    }

    /**
     * Importa SOLO los usuarios seleccionados en el modal.
     * POST /usuarios/sync-entra/selected
     */
    public function importSelected(Request $request): RedirectResponse
    {
        // selected[n][email], selected[n][name], selected[n][aliases][]
        $selected = $request->input('selected', []);

        if (!is_array($selected) || empty($selected)) {
            return redirect()->route('users.index')->with('warning', __('users.sync.no_selection'));
        }

        $entries = [];
        foreach ($selected as $row) {
            if (!is_array($row)) {
                continue;
            }
            $email = trim((string)($row['email'] ?? ''));
            $name  = trim((string)($row['name'] ?? ''));
            $aliases = [];
            if (isset($row['aliases']) && is_array($row['aliases'])) {
                foreach ($row['aliases'] as $a) {
                    $a = strtolower(trim((string)$a));
                    if ($a !== '') $aliases[] = $a;
                }
                // Ensure aliases remain unique
                $aliases = array_values(array_unique($aliases));
            }

            if ($email === '') {
                continue;
            }
            if ($name === '') {
                $name = $email;
            }
            $entries[] = ['email' => $email, 'name' => $name, 'aliases' => $aliases];
        }

        if (empty($entries)) {
            return redirect()->route('users.index')->with('warning', __('users.sync.no_entries'));
        }

        $assignRole = trim($request->input('assign_role', ''));

        try {
            $stats = $this->importer->importSelected(
                $entries,
                $assignRole !== '' ? $assignRole : null
            );

            $summary = __('users.sync.summary.selected', $stats);

            return redirect()->route('users.index')->with('success', $summary);
        } catch (Throwable $e) {
            Log::error('Import Selected Entra error', ['exception' => $e]);
            return redirect()->route('users.index')->with(
                'error',
                __('users.sync.errors.import_selected', ['message' => $e->getMessage()])
            );
        }
    }

    /**
     * Actualiza datos de usuarios ya importados desde Entra (solo existentes).
     * POST /usuarios/sync-entra/refresh-existing
     */
    public function refreshExisting(Request $request): RedirectResponse
    {
        try {
            $stats = $this->importer->refreshExisting();
            $summary = __('users.sync.summary.refresh', $stats);
            return redirect()->route('users.index')->with('success', $summary);
        } catch (Throwable $e) {
            Log::error('Refresh Existing Entra error', ['exception' => $e]);
            return redirect()->route('users.index')->with(
                'error',
                __('users.sync.errors.refresh', ['message' => $e->getMessage()])
            );
        }
    }
}
