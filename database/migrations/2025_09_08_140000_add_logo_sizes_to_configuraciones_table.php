<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            if (!Schema::hasColumn('configuraciones', 'site_logo_width')) {
                $table->unsignedSmallInteger('site_logo_width')->nullable()->after('site_logo_path');
            }
            if (!Schema::hasColumn('configuraciones', 'site_logo_height')) {
                $table->unsignedSmallInteger('site_logo_height')->nullable()->after('site_logo_width');
            }
            if (!Schema::hasColumn('configuraciones', 'site_logo_compact_width')) {
                $table->unsignedSmallInteger('site_logo_compact_width')->nullable()->after('site_logo_compact_path');
            }
            if (!Schema::hasColumn('configuraciones', 'site_logo_compact_height')) {
                $table->unsignedSmallInteger('site_logo_compact_height')->nullable()->after('site_logo_compact_width');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            foreach (['site_logo_width','site_logo_height','site_logo_compact_width','site_logo_compact_height'] as $col) {
                if (Schema::hasColumn('configuraciones', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

