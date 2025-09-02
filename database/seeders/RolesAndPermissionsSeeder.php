<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Permisos base de menú/acciones
        $perms = [
            'reports.view',            // ver informes/embed
            'menu.portal',             // ver menú del portal de usuario
            'menu.reports_index',      // ver menú Informes (admin/editor)
            'menu.workspaces',         // ver menú Espacios de trabajo
            'menu.capacities',         // ver menú Capacidades
            'menu.apps',               // ver menú Aplicaciones
            'menu.users',              // ver ABM Usuarios
            'menu.groups',             // ver ABM Grupos
            'menu.folders',            // ver ABM Carpetas
            'menu.configuraciones',    // ver Configuraciones
            'menu.auditoria',          // ver Auditoría
        ];

        foreach ($perms as $p) {
            Permission::findOrCreate($p, 'web');
        }

        // Roles
        $admin  = Role::findOrCreate('admin', 'web');
        $editor = Role::findOrCreate('editor', 'web');
        $usuario = Role::findOrCreate('usuario', 'web');

        // Asignaciones de permisos de menú
        $admin->givePermissionTo([
            'reports.view', 'menu.portal', 'menu.reports_index', 'menu.workspaces', 'menu.capacities',
            'menu.apps', 'menu.users', 'menu.groups', 'menu.folders', 'menu.configuraciones', 'menu.auditoria'
        ]);
        $editor->givePermissionTo([
            'reports.view', 'menu.reports_index', 'menu.workspaces', 'menu.capacities'
        ]);
        $usuario->givePermissionTo([
            'reports.view', 'menu.portal'
        ]);

        // Asignar el primer usuario como admin (ajusta si querés otro criterio)
        $user = \App\Models\User::orderBy('id')->first();
        if ($user && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
