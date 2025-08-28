<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Permisos base (agregá más luego)
        $perms = [
            'super-admin.panel',     // ver /admin
            'companies.manage',      // CRUD empresas
            'reports.view',          // ver informes
        ];

        foreach ($perms as $p) {
            Permission::findOrCreate($p, 'web');
        }

        // Roles
        $super = Role::findOrCreate('super_admin', 'web');
        $clientAdmin = Role::findOrCreate('client_admin', 'web');
        $viewer = Role::findOrCreate('viewer', 'web');

        // Asignaciones básicas
        $super->givePermissionTo(['super-admin.panel', 'companies.manage', 'reports.view']);
        $clientAdmin->givePermissionTo(['companies.manage', 'reports.view']);
        $viewer->givePermissionTo(['reports.view']);

        // Asignar el primer usuario como super_admin (ajusta si querés otro criterio)
        $user = \App\Models\User::orderBy('id')->first();
        if ($user && !$user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
        }
    }
}
