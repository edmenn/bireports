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

        // Pre-cargar permisos para contar sin N+1
        $roles->getCollection()->load('permissions');

        // Conteo de usuarios por rol (consulta por cada rol; dataset acotado)
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

        // Colores opcionales
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

        return redirect()->route('roles.index')->with('success', 'Perfil creado.');
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

        // Evitar renombrar el rol admin para no perder reglas de negocio implícitas
        if (mb_strtolower($role->name) === 'admin' && mb_strtolower($data['name']) !== 'admin') {
            return back()->with('error', 'El rol "admin" no puede ser renombrado.');
        }

        $role->name = $data['name'];
        $role->badge_bg = $data['badge_bg'] ?? null;
        $role->badge_text = $data['badge_text'] ?? null;
        $role->save();

        // Admin: siempre todos los permisos (no editable)
        if (mb_strtolower($role->name) === 'admin') {
            $role->syncPermissions(\Spatie\Permission\Models\Permission::all());
        } else {
            $perms = array_values(array_unique($data['permissions'] ?? []));

            // Auditoría: dif de permisos antes/después
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

        return redirect()->route('roles.index')->with('success', 'Perfil actualizado.');
    }

    public function destroy(Role $rol): RedirectResponse
    {
        $role = $rol;
        if (in_array(mb_strtolower($role->name), ['admin'], true)) {
            return back()->with('error', 'El rol "admin" no puede eliminarse.');
        }
        try {
            $role->delete();
            return redirect()->route('roles.index')->with('success', 'Perfil eliminado.');
        } catch (\Throwable $e) {
            return back()->with('error', 'No se pudo eliminar el perfil.');
        }
    }

    /**
     * Construye secciones y labels de permisos para la UI.
     * Retorna [sections, allPermissions]
     * - sections: [ [ 'key'=>string, 'label'=>string, 'items'=> [ ['name'=>..., 'label'=>...], ... ] ], ... ]
     * - allPermissions: array<string> con todos los nombres
     */
    private function buildPermissionSections(): array
    {
        $perms = Permission::query()->orderBy('name')->pluck('name')->all();

        // Carga mapa opcional desde config
        $map = config('permissions_map.sections', []);
        $order = config('permissions_map.order', []);
        $fallbackLabel = config('permissions_map.unknown_section_label', 'Otros permisos');

        // Construir catálogo base desde permisos existentes
        $bySection = [];
        foreach ($perms as $p) {
            $secKey = strpos($p, '.') !== false ? explode('.', $p, 2)[0] : 'otros';
            $bySection[$secKey] = $bySection[$secKey] ?? [];
            $bySection[$secKey][] = $p;
        }

        // Armar estructura con labels amigables
        $sections = [];

        // Primero, secciones definidas en config (respetar orden)
        foreach ($order as $key) {
            if (!array_key_exists($key, $bySection) && !array_key_exists($key, $map)) {
                continue;
            }
            $sections[] = $this->sectionFromKey($key, $bySection[$key] ?? [], $map, $fallbackLabel);
            unset($bySection[$key]);
        }

        // Luego, el resto de secciones detectadas dinámicamente
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

        // Fusionar: primero los del config (para controlar orden y labels), luego los restantes
        $items = [];
        $configured = $conf['permissions'] ?? [];
        foreach ($configured as $pName => $label) {
            $items[] = [ 'name' => $pName, 'label' => $label ];
        }

        // Agregar no mapeados en orden alfabético
        $existing = array_map(fn ($i) => $i['name'], $items);
        sort($permNames);
        foreach ($permNames as $p) {
            if (in_array($p, $existing, true)) continue;
            $items[] = [ 'name' => $p, 'label' => $this->humanizePermission($p) ];
        }

        return [
            'key' => $key,
            'label' => $conf['label'] ?? ($key === 'otros' ? $fallbackLabel : $this->humanize($key)),
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
        // Ej: "menu.reports_index" => "Menú: reports index"
        if (strpos($perm, '.') !== false) {
            [$sec, $rest] = explode('.', $perm, 2);
            $sec = $sec === 'menu' ? 'Menú' : $this->humanize($sec);
            return $sec . ': ' . str_replace(['_', '-'], ' ', $rest);
        }
        return $this->humanize($perm);
    }
}
