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
        // Taxonomia de permisos por modulo/accion
        Permission::query()->where('name', 'apps.view')->where('guard_name', 'web')->delete();
        $perms = [
            // Portal
            'portal.view',

            // Reports
            'reports.view', 'reports.create', 'reports.edit', 'reports.delete', 'reports.export',

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

        // Crear permisos si no existen
        foreach ($perms as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Asignacion de permisos por rol
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $usuario = Role::firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);

        // Admin: todos
        $admin->syncPermissions(Permission::all());

        // Editor: acceso de trabajo (sin administrar usuarios/config)
        $editorPerms = [
            'portal.view',
            'reports.view', 'reports.create', 'reports.edit', 'reports.export',
            'datasets.view', 'datasets.refresh', 'datasets.schedule.view', 'datasets.schedule.edit', 'datasets.export',
            'workspaces.view',
            'applications.view',
            'capacities.view', 'capacities.pause', 'capacities.resume', 'capacities.schedule.view', 'capacities.schedule.edit', 'capacities.audit.view',
            'audit.view', 'audit.export',
        ];
        $editor->syncPermissions($editorPerms);

        // Usuario: solo lectura de portal y consumo
        $usuarioPerms = [
            'portal.view',
            'reports.view',
            'datasets.view',
            'workspaces.view',
            'applications.view',
        ];
        $usuario->syncPermissions($usuarioPerms);
    }
}
