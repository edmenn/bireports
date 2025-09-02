<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Proveedor de autenticación: null/local/entra/…
            $table->string('auth_provider', 20)->nullable()->index()->after('remember_token');
            // Id externo (Object ID de Entra); único si se informa
            $table->string('external_id', 100)->nullable()->unique()->after('auth_provider');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['auth_provider', 'external_id']);
        });
    }
};
