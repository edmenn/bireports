<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            // [Minimalista] Parámetros globales para encendido on-demand del viewer
            if (!Schema::hasColumn('configuraciones', 'capacity_on_demand_enable')) {
                $table->boolean('capacity_on_demand_enable')->default(true)->after('prewarm_enabled');
            }
            if (!Schema::hasColumn('configuraciones', 'capacity_on_demand_message')) {
                $table->string('capacity_on_demand_message', 255)->nullable()->after('capacity_on_demand_enable');
            }
            if (!Schema::hasColumn('configuraciones', 'capacity_start_timeout_sec')) {
                $table->unsignedInteger('capacity_start_timeout_sec')->default(120)->after('capacity_on_demand_message');
            }
            if (!Schema::hasColumn('configuraciones', 'capacity_start_poll_interval_sec')) {
                $table->unsignedInteger('capacity_start_poll_interval_sec')->default(5)->after('capacity_start_timeout_sec');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            if (Schema::hasColumn('configuraciones', 'capacity_start_poll_interval_sec')) {
                $table->dropColumn('capacity_start_poll_interval_sec');
            }
            if (Schema::hasColumn('configuraciones', 'capacity_start_timeout_sec')) {
                $table->dropColumn('capacity_start_timeout_sec');
            }
            if (Schema::hasColumn('configuraciones', 'capacity_on_demand_message')) {
                $table->dropColumn('capacity_on_demand_message');
            }
            if (Schema::hasColumn('configuraciones', 'capacity_on_demand_enable')) {
                $table->dropColumn('capacity_on_demand_enable');
            }
        });
    }
};

