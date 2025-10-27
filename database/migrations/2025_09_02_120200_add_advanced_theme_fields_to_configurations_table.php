<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('configurations', 'theme_name')) {
                $table->string('theme_name', 50)->nullable()->after('site_title');
            }
            if (!Schema::hasColumn('configurations', 'theme_config')) {
                $table->json('theme_config')->nullable()->after('theme_name');
            }
            if (!Schema::hasColumn('configurations', 'site_logo_compact_path')) {
                $table->string('site_logo_compact_path', 255)->nullable()->after('site_logo_path');
            }
            if (!Schema::hasColumn('configurations', 'footer_text')) {
                $table->string('footer_text', 255)->nullable()->after('site_favicon_path');
            }
            if (!Schema::hasColumn('configurations', 'footer_enabled')) {
                $table->boolean('footer_enabled')->default(true)->after('footer_text');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (Schema::hasColumn('configurations', 'footer_enabled')) {
                $table->dropColumn('footer_enabled');
            }
            if (Schema::hasColumn('configurations', 'footer_text')) {
                $table->dropColumn('footer_text');
            }
            if (Schema::hasColumn('configurations', 'site_logo_compact_path')) {
                $table->dropColumn('site_logo_compact_path');
            }
            if (Schema::hasColumn('configurations', 'theme_config')) {
                $table->dropColumn('theme_config');
            }
            if (Schema::hasColumn('configurations', 'theme_name')) {
                $table->dropColumn('theme_name');
            }
        });
    }
};

