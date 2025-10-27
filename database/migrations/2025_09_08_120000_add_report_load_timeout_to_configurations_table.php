<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('configurations', 'report_load_timeout_sec')) {
                $table->unsignedInteger('report_load_timeout_sec')->default(10)->after('capacity_start_poll_interval_sec');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (Schema::hasColumn('configurations', 'report_load_timeout_sec')) {
                $table->dropColumn('report_load_timeout_sec');
            }
        });
    }
};

