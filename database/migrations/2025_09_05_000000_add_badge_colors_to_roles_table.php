<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('badge_bg', 20)->nullable()->after('guard_name');
            $table->string('badge_text', 20)->nullable()->after('badge_bg');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['badge_bg', 'badge_text']);
        });
    }
};

