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
            // Opcional: permiso específico para ver ABM de Perfiles
            'menu.roles',
        ];

        foreach ($perms as $p) {
            Permission::findOrCreate($p, 'web');
        }

        // Roles
        $admin  = Role::findOrCreate('admin', 'web');
        $editor = Role::findOrCreate('editor', 'web');
        $usuario = Role::findOrCreate('usuario', 'web');

        // Colores por defecto para badges (pueden editarse desde Perfiles)
        try {
            $admin->forceFill(['badge_bg' => '#fee2e2', 'badge_text' => '#b91c1c'])->save();
            $editor->forceFill(['badge_bg' => '#fef9c3', 'badge_text' => '#a16207'])->save();
            $usuario->forceFill(['badge_bg' => '#dcfce7', 'badge_text' => '#166534'])->save();
        } catch (\Throwable $e) {
            // columnas podrían no existir en instalaciones antiguas; omitir silenciosamente
        }

        // ADMIN: acceso total (todos los permisos existentes)
        try {
            $admin->syncPermissions(Permission::all());
        } catch (\Throwable $e) {
            // si falla por cache/config, al menos aseguramos menú principal
            $admin->givePermissionTo([
                'reports.view', 'menu.portal', 'menu.reports_index', 'menu.workspaces', 'menu.capacities',
                'menu.apps', 'menu.users', 'menu.groups', 'menu.folders', 'menu.configuraciones', 'menu.auditoria',
                'menu.roles'
            ]);
        }
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
