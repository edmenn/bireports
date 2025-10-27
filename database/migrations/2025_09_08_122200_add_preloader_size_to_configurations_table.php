<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('configurations', 'preloader_logo_width')) {
                $table->unsignedSmallInteger('preloader_logo_width')->nullable()->after('preloader_logo_path');
            }
            if (!Schema::hasColumn('configurations', 'preloader_logo_height')) {
                $table->unsignedSmallInteger('preloader_logo_height')->nullable()->after('preloader_logo_width');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (Schema::hasColumn('configurations', 'preloader_logo_width')) {
                $table->dropColumn('preloader_logo_width');
            }
            if (Schema::hasColumn('configurations', 'preloader_logo_height')) {
                $table->dropColumn('preloader_logo_height');
            }
        });
    }
};

