<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario de prueba solo si no existe
        User::firstOrCreate(
            ['email' => 'test@example.com'], // condición de búsqueda
            [
                'name'     => 'Test User',
                'password' => bcrypt('password'), // contraseña por defecto
            ]
        );

        // Ejecutamos los seeders necesarios
        $this->call([
            RolesAndPermissionsSeeder::class,
            ConfiguracionSeeder::class,
        ]);
    }
}
