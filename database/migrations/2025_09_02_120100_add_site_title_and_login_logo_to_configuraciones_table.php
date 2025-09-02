<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            if (!Schema::hasColumn('configuraciones', 'site_title')) {
                $table->string('site_title', 255)->nullable()->after('theme_sidebar_class');
            }
            if (!Schema::hasColumn('configuraciones', 'login_logo_path')) {
                $table->string('login_logo_path', 255)->nullable()->after('login_bg_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            if (Schema::hasColumn('configuraciones', 'login_logo_path')) {
                $table->dropColumn('login_logo_path');
            }
            if (Schema::hasColumn('configuraciones', 'site_title')) {
                $table->dropColumn('site_title');
            }
        });
    }
};

