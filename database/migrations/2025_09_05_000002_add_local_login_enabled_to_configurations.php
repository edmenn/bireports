<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('configurations', 'local_login_enabled')) {
                $table->boolean('local_login_enabled')->default(true)->after('microsoft_login_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (Schema::hasColumn('configurations', 'local_login_enabled')) {
                $table->dropColumn('local_login_enabled');
            }
        });
    }
};

