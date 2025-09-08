<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            if (!Schema::hasColumn('configuraciones', 'preloader_logo_path')) {
                $table->string('preloader_logo_path')->nullable()->after('site_favicon_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            if (Schema::hasColumn('configuraciones', 'preloader_logo_path')) {
                $table->dropColumn('preloader_logo_path');
            }
        });
    }
};

