<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Normalize legacy role slug before assigning permissions.
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

        // Permission taxonomy per module/action
        Permission::query()->where('name', 'apps.view')->where('guard_name', 'web')->delete();
        $perms = [
            // Portal
            'portal.view',

            // Reports
            'reports.view', 'reports.create', 'reports.edit', 'reports.delete', 'reports.export', 'report_subscriptions.manage',

            // Datasets
            'datasets.view', 'datasets.refresh', 'datasets.schedule.view', 'datasets.schedule.edit', 'datasets.export',

            // Workspaces
            'workspaces.view',

            // Applications
            'applications.view', 'applications.create', 'applications.edit', 'applications.delete', 'application_sections.manage',

            // Capacities
            'capacities.view', 'capacities.pause', 'capacities.resume', 'capacities.schedule.view', 'capacities.schedule.edit', 'capacities.audit.view',

            // Users / Roles / Groups / Folders
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.sync',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'groups.view', 'groups.create', 'groups.edit', 'groups.delete',
            'folders.view', 'folders.create', 'folders.edit', 'folders.delete',

            // Config & Audit
            'config.view', 'config.edit',
            'audit.view', 'audit.export', 'audit.reset',
        ];

        // Create permissions where missing
        foreach ($perms as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Role permission assignments
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);

        // Admin: full access
        $admin->syncPermissions(Permission::all());

        // Editor: operational access (no user/config management)
        $editorPerms = [
            'portal.view',
            'reports.view', 'reports.create', 'reports.edit', 'reports.export', 'report_subscriptions.manage',
            'datasets.view', 'datasets.refresh', 'datasets.schedule.view', 'datasets.schedule.edit', 'datasets.export',
            'workspaces.view',
            'applications.view',
            'capacities.view', 'capacities.pause', 'capacities.resume', 'capacities.schedule.view', 'capacities.schedule.edit', 'capacities.audit.view',
            'audit.view', 'audit.export',
        ];
        $editor->syncPermissions($editorPerms);

        // Viewer: portal consumption only
        $viewerPerms = [
            'portal.view',
            'reports.view',
            'datasets.view',
            'workspaces.view',
            'applications.view',
        ];
        $viewer->syncPermissions($viewerPerms);
    }
}
