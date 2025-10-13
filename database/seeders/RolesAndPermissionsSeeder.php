<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Normalize legacy permission slugs before seeding.
        $legacyPermissionMap = [
            'menu.auditoria'        => 'menu.audit',
            'menu.configuraciones'  => 'menu.configurations',
        ];
        foreach ($legacyPermissionMap as $legacy => $canonical) {
            $legacyPermission = Permission::query()
                ->where('name', $legacy)
                ->where('guard_name', 'web')
                ->first();

            if ($legacyPermission) {
                $conflict = Permission::query()
                    ->where('name', $canonical)
                    ->where('guard_name', 'web')
                    ->first();

                if (! $conflict) {
                    $legacyPermission->forceFill(['name' => $canonical])->save();
                } else {
                    $legacyPermission->roles()->each(
                        fn ($role) => $role->givePermissionTo($canonical)
                    );
                    $legacyPermission->delete();
                }
            }
        }

        Permission::query()->where('name', 'menu.apps')->where('guard_name', 'web')->delete();
        $perms = [
            'reports.view',
            'menu.portal',
            'menu.reports_index',
            'menu.workspaces',
            'menu.capacities',
            'menu.applications',
            'menu.users',
            'menu.groups',
            'menu.folders',
            'menu.configurations',
            'menu.audit',
            'menu.roles',
        ];

        foreach ($perms as $p) {
            Permission::findOrCreate($p, 'web');
        }

        // Rename legacy role slugs to their English counterparts for compatibility.
        $legacyRole = Role::query()->where('name', 'usuario')->where('guard_name', 'web')->first();
        if ($legacyRole) {
            $existingViewer = Role::query()->where('name', 'viewer')->where('guard_name', 'web')->first();
            if ($existingViewer) {
                $legacyRole->users()->each(fn ($user) => $user->assignRole($existingViewer));
                $legacyRole->delete();
            } else {
                $legacyRole->forceFill(['name' => 'viewer'])->save();
            }
        }

        $admin  = Role::findOrCreate('admin', 'web');
        $editor = Role::findOrCreate('editor', 'web');
        $viewer = Role::findOrCreate('viewer', 'web');

        // Default badge colors (configurable from the UI)
        try {
            $admin->forceFill(['badge_bg' => '#fee2e2', 'badge_text' => '#b91c1c'])->save();
            $editor->forceFill(['badge_bg' => '#fef9c3', 'badge_text' => '#a16207'])->save();
            $viewer->forceFill(['badge_bg' => '#dcfce7', 'badge_text' => '#166534'])->save();
        } catch (\Throwable $e) {
            // Older installations might not have the badge columns; ignore errors.
        }

        // Admin: full access (all current permissions)
        try {
            $admin->syncPermissions(Permission::all());
        } catch (\Throwable $e) {
            // If cache/config prevents sync, ensure main menu permissions
            $admin->givePermissionTo([
                'reports.view', 'menu.portal', 'menu.reports_index', 'menu.workspaces', 'menu.capacities',
                'menu.applications', 'menu.users', 'menu.groups', 'menu.folders', 'menu.configurations', 'menu.audit',
                'menu.roles'
            ]);
        }
        $editor->givePermissionTo([
            'reports.view', 'menu.reports_index', 'menu.workspaces', 'menu.capacities'
        ]);
        $viewer->givePermissionTo([
            'reports.view', 'menu.portal'
        ]);

        // Assign the first user as admin (adjust if you prefer another rule)
        $user = \App\Models\User::orderBy('id')->first();
        if ($user && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}

