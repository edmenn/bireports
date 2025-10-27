<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('configurations', 'theme_navbar_class')) {
                $table->string('theme_navbar_class', 100)->nullable()->after('email_locale');
            }
            if (!Schema::hasColumn('configurations', 'theme_sidebar_class')) {
                $table->string('theme_sidebar_class', 100)->nullable()->after('theme_navbar_class');
            }
            if (!Schema::hasColumn('configurations', 'site_logo_path')) {
                $table->string('site_logo_path', 255)->nullable()->after('theme_sidebar_class');
            }
            if (!Schema::hasColumn('configurations', 'site_favicon_path')) {
                $table->string('site_favicon_path', 255)->nullable()->after('site_logo_path');
            }
            if (!Schema::hasColumn('configurations', 'login_bg_path')) {
                $table->string('login_bg_path', 255)->nullable()->after('site_favicon_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (Schema::hasColumn('configurations', 'login_bg_path')) {
                $table->dropColumn('login_bg_path');
            }
            if (Schema::hasColumn('configurations', 'site_favicon_path')) {
                $table->dropColumn('site_favicon_path');
            }
            if (Schema::hasColumn('configurations', 'site_logo_path')) {
                $table->dropColumn('site_logo_path');
            }
            if (Schema::hasColumn('configurations', 'theme_sidebar_class')) {
                $table->dropColumn('theme_sidebar_class');
            }
            if (Schema::hasColumn('configurations', 'theme_navbar_class')) {
                $table->dropColumn('theme_navbar_class');
            }
        });
    }
};

