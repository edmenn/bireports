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
        // Seed default administrator if it does not already exist
        User::firstOrCreate(
            ['email' => 'admin@birentable.com'], // lookup condition
            [
                'name'     => 'Administrator',
                'password' => bcrypt('XH6cdURvPXk2uOhTm3K8'), // default password
            ]
        );

        $this->call([
            PermissionsSeeder::class,
            RolesAndPermissionsSeeder::class,
            ConfigurationSeeder::class,
        ]);
    }
}
