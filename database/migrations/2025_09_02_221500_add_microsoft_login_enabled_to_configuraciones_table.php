<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            if (! Schema::hasColumn('configuraciones', 'microsoft_login_enabled')) {
                $table->boolean('microsoft_login_enabled')->default(false)->after('powerbi_token_refresh_minutes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            if (Schema::hasColumn('configuraciones', 'microsoft_login_enabled')) {
                $table->dropColumn('microsoft_login_enabled');
            }
        });
    }
};

