<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Permisos base (ajustables)
        $perms = [
            'reports.view',          // ver informes
        ];

        foreach ($perms as $p) {
            Permission::findOrCreate($p, 'web');
        }

        // Roles
        $admin  = Role::findOrCreate('admin', 'web');
        $editor = Role::findOrCreate('editor', 'web');
        $usuario = Role::findOrCreate('usuario', 'web');

        // Asignaciones básicas
        $admin->givePermissionTo(['reports.view']);
        $editor->givePermissionTo(['reports.view']);
        $usuario->givePermissionTo(['reports.view']);

        // Asignar el primer usuario como admin (ajusta si querés otro criterio)
        $user = \App\Models\User::orderBy('id')->first();
        if ($user && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        }
    }
}
