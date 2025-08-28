<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('empresas', 'client_id')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->foreignId('client_id')
                      ->nullable() // dejamos nullable para poder completar datos sin romper nada
                      ->constrained('clients')
                      ->cascadeOnDelete()
                      ->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('empresas', 'client_id')) {
            Schema::table('empresas', function (Blueprint $table) {
                $table->dropConstrainedForeignId('client_id');
            });
        }
    }
};
