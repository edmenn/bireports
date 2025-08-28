<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Str;

class ClientsSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Cliente Demo',
                'custom_domain' => null,
                'ga_tracking_code' => null,
                'default_view_type' => 'folders',
                'default_email_lang' => 'es',
            ]
        );

        // Asignar el primer usuario al cliente demo (ajustá si querés otro)
        $user = User::orderBy('id')->first();
        if ($user && !$user->client_id) {
            $user->client_id = $client->id;
            $user->save();
        }
    }
}
