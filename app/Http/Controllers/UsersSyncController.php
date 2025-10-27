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
use function ltrim;
use function strtolower;
use function explode;
use function is_array;
//use function is_string;
use function array_values;

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
        $domain     = trim($request->input('domain', ''));
        $onlyActive = (bool) $request->input('only_active', false);
        $next       = $request->string('next')->toString() ?: null;

        try {
            $page  = $this->graph->listUsers($query !== '' ? $query : null, $next, $onlyActive);
            $items = (array) ($page['items'] ?? []);
            $nextLink = $page['next'] ?? null;

            // Apply domain filtering here: accept when ANY alias matches
            if ($domain !== '') {
                $domain = ltrim($domain, '@');
                $domainLower = strtolower($domain);

                $items = array_values(array_filter($items, function (array $u) use ($domainLower): bool {
                    $candidates = [];

                    $upn  = strtolower(trim((string)($u['userPrincipalName'] ?? '')));
                    $mail = strtolower(trim((string)($u['mail'] ?? '')));

                    if ($upn !== '')  { $candidates[] = $upn; }
                    if ($mail !== '') { $candidates[] = $mail; }

                    foreach ((array)($u['otherMails'] ?? []) as $alt) {
                        $alt = strtolower(trim((string)$alt));
                        if ($alt !== '') { $candidates[] = $alt; }
                    }
                    foreach ((array)($u['proxyAddresses'] ?? []) as $p) {
                        $p = trim((string)$p);
                        if ($p === '') { continue; }
                        // proxyAddresses: "smtp:alias@domain" or "SMTP:primary@domain"
                        $parts = explode(':', $p, 2);
                        $addr  = isset($parts[1]) ? strtolower($parts[1]) : strtolower($parts[0]);
                        if ($addr !== '') { $candidates[] = $addr; }
                    }

                    foreach ($candidates as $cand) {
                        // Match addresses whose domain suffix equals the requested domain
                        if (str_ends_with($cand, '@' . $domainLower)) {
                            return true;
                        }
                    }
                    return false;
                }));
            }

            // Render Blade partial rows
            $rows = view('users.partials.entra_rows', ['items' => $items])->render();

            return response()->json([
                'rows'  => $rows,
                'next'  => $nextLink,
                'count' => count($items),
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

