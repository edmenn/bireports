<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $roles = Role::query()
            ->when($q !== '', function ($qr) use ($q) {
                $needle = mb_strtolower($q);
                $qr->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Pre-load permissions to avoid N+1 counting
        $roles->getCollection()->load('permissions');

        // Count users per role (one query per role; manageable dataset)
        $userCounts = [];
        foreach ($roles as $r) {
            try {
                $userCounts[$r->id] = (int) (\App\Models\User::role($r->name)->count());
            } catch (\Throwable $e) {
                $userCounts[$r->id] = 0;
            }
        }

        return view('roles.index', compact('roles', 'q', 'userCounts'));
    }

    public function create(): View
    {
        [$sections, $allPermissions] = $this->buildPermissionSections();
        return view('roles.create', compact('sections', 'allPermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:150','unique:roles,name'],
            'permissions' => ['nullable','array'],
            'permissions.*' => ['string'],
            'badge_bg' => ['nullable','regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'badge_text' => ['nullable','regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        // Optional badge colours
        if (!empty($data['badge_bg']) || !empty($data['badge_text'])) {
            $role->forceFill([
                'badge_bg' => $data['badge_bg'] ?? null,
                'badge_text' => $data['badge_text'] ?? null,
            ])->save();
        }

        $perms = array_values(array_unique($data['permissions'] ?? []));
        if (!empty($perms)) {
            $role->syncPermissions($perms);
        }

        return redirect()->route('roles.index')->with('success', __('profiles.flash.created'));
    }

    public function edit(Role $rol): View
    {
        $role = $rol;
        [$sections, $allPermissions] = $this->buildPermissionSections();
        $isAdmin = (mb_strtolower($role->name) === 'admin');
        $current = $isAdmin ? $allPermissions : $role->permissions()->pluck('name')->all();
        return view('roles.edit', compact('role','sections','allPermissions','current','isAdmin'));
    }

    public function update(Request $request, Role $rol): RedirectResponse
    {
        $role = $rol;
        $data = $request->validate([
            'name' => ['required','string','max:150','unique:roles,name,'.$role->id],
            'permissions' => ['nullable','array'],
            'permissions.*' => ['string'],
            'badge_bg' => ['nullable','regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
            'badge_text' => ['nullable','regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'],
        ]);

        // Prevent renaming the admin role to preserve business rules
        if (mb_strtolower($role->name) === 'admin' && mb_strtolower($data['name']) !== 'admin') {
            return back()->with('error', __('profiles.flash.cannot_rename_admin'));
        }

        $role->name = $data['name'];
        $role->badge_bg = $data['badge_bg'] ?? null;
        $role->badge_text = $data['badge_text'] ?? null;
        $role->save();

        // Admin role always receives every permission (not editable)
        if (mb_strtolower($role->name) === 'admin') {
            $role->syncPermissions(\Spatie\Permission\Models\Permission::all());
        } else {
            $perms = array_values(array_unique($data['permissions'] ?? []));

            // Audit: diff permissions before/after
            try {
                $before = $role->permissions()->pluck('name')->all();
                $after  = $perms;
                sort($before); sort($after);
                $added   = array_values(array_diff($after, $before));
                $removed = array_values(array_diff($before, $after));
                $role->syncPermissions($perms);
                if (!empty($added) || !empty($removed)) {
                    \App\Models\EntityAudit::create([
                        'user_id' => optional($request->user())->id,
                        'action'  => 'synced',
                        'auditable_type' => get_class($role),
                        'auditable_id'   => (string) $role->getKey(),
                        'changes' => null,
                        'meta'    => [
                            'context' => 'role.permissions',
                            'added'   => $added,
                            'removed' => $removed,
                            'route'   => optional($request->route())->getName(),
                            'ip'      => (string) $request->ip(),
                        ],
                    ]);
                }
            } catch (\Throwable) {
                $role->syncPermissions($perms);
            }
        }

        return redirect()->route('roles.index')->with('success', __('profiles.flash.updated'));
    }

    public function destroy(Role $rol): RedirectResponse
    {
        $role = $rol;
        if (in_array(mb_strtolower($role->name), ['admin'], true)) {
            return back()->with('error', __('profiles.flash.cannot_delete_admin'));
        }
        // Block deletion when users are assigned to the role
        try {
            $assigned = (int) (\App\Models\User::role($role->name)->count());
            if ($assigned > 0) {
                return back()->with('warning', __('profiles.flash.cannot_delete_assigned', ['count' => $assigned]));
            }
        } catch (\Throwable $e) {
            // If verification fails, skip deletion for safety
            return back()->with('error', __('profiles.flash.assignments_unknown'));
        }
        try {
            $role->delete();
            return redirect()->route('roles.index')->with('success', __('profiles.flash.deleted'));
        } catch (\Throwable $e) {
            return back()->with('error', __('profiles.flash.delete_failed'));
        }
    }

    /**
     * Build permission sections and labels for the UI.
     * Returns [sections, allPermissions]
     * - sections: [ { 'key': string, 'label': string, 'items': [ ['name'=>..., 'label'=>...], ... ] }, ... ]
     * - allPermissions: array<string> containing every permission name
     */
    private function buildPermissionSections(): array
    {
        $perms = Permission::query()->orderBy('name')->pluck('name')->all();

        // Load the optional map from configuration
        $map = config('permissions_map.sections', []);
        $order = config('permissions_map.order', []);
        $unknownSectionKey = config('permissions_map.unknown_section_label', 'profiles.permissions.unknown_section');
        $fallbackLabel = __($unknownSectionKey);

        // Build the base catalogue from existing permissions
        $bySection = [];
        foreach ($perms as $p) {
            $secKey = strpos($p, '.') !== false ? explode('.', $p, 2)[0] : 'otros';
            $bySection[$secKey] = $bySection[$secKey] ?? [];
            $bySection[$secKey][] = $p;
        }

        // Build the structure with friendly labels
        $sections = [];

        // First, sections defined in config (respect ordering)
        foreach ($order as $key) {
            if (!array_key_exists($key, $bySection) && !array_key_exists($key, $map)) {
                continue;
            }
            $sections[] = $this->sectionFromKey($key, $bySection[$key] ?? [], $map, $fallbackLabel);
            unset($bySection[$key]);
        }

        // Then append any other dynamically detected sections
        foreach ($bySection as $key => $list) {
            $sections[] = $this->sectionFromKey($key, $list, $map, $fallbackLabel);
        }

        return [$sections, $perms];
    }

    private function sectionFromKey(string $key, array $permNames, array $map, string $fallbackLabel): array
    {
        $conf = $map[$key] ?? [
            'label' => $this->humanize($key),
            'permissions' => [],
        ];

        // Merge configured permissions first (to control order/labels), then append the rest
        $items = [];
        $configured = $conf['permissions'] ?? [];
        foreach ($configured as $pName => $label) {
            $items[] = [
                'name' => $pName,
                'label' => is_string($label) ? __($label) : $label,
            ];
        }

        // Add unmapped permissions sorted alphabetically
        $existing = array_map(fn ($i) => $i['name'], $items);
        sort($permNames);
        foreach ($permNames as $p) {
            if (in_array($p, $existing, true)) continue;
            $items[] = [ 'name' => $p, 'label' => $this->humanizePermission($p) ];
        }

        $sectionLabel = $conf['label'] ?? null;
        if (is_string($sectionLabel)) {
            $sectionLabel = __($sectionLabel);
        }
        $sectionLabel ??= ($key === 'otros' ? $fallbackLabel : $this->humanize($key));

        return [
            'key' => $key,
            'label' => $sectionLabel,
            'items' => $items,
        ];
    }

    private function humanize(string $key): string
    {
        $key = str_replace(['_', '-'], ' ', $key);
        return ucfirst(mb_strtolower($key));
    }

    private function humanizePermission(string $perm): string
    {
        // Ej: "menu.reports_index" => "Menu: reports index"
        if (strpos($perm, '.') !== false) {
            [$sec, $rest] = explode('.', $perm, 2);
            $prefixKey = 'profiles.permissions.section_prefix.' . $sec;
            $sectionName = __($prefixKey);
            if ($sectionName === $prefixKey) {
                $sectionName = $this->humanize($sec);
            }
            return $sectionName . ': ' . str_replace(['_', '-'], ' ', $rest);
        }

        return $this->humanize($perm);
    }
}

