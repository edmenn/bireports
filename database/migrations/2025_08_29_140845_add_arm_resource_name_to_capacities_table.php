<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('capacities', function (Blueprint $table) {
            if (!Schema::hasColumn('capacities', 'arm_resource_name')) {
                // Nombre EXACTO del recurso en Azure (Fabric/Dedicated)
                $table->string('arm_resource_name', 128)->nullable()->after('display_name');
                $table->index('arm_resource_name', 'cap_arm_name_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('capacities', function (Blueprint $table) {
            // Drop index si existe (algunos drivers requieren try/catch)
            try { $table->dropIndex('cap_arm_name_idx'); } catch (\Throwable $e) {}

            if (Schema::hasColumn('capacities', 'arm_resource_name')) {
                $table->dropColumn('arm_resource_name');
            }
        });
    }
};
