<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            // GUID de la suscripción (36 con guiones)
            if (!Schema::hasColumn('configurations', 'azure_subscription_id')) {
                $table->string('azure_subscription_id', 36)->nullable()->after('powerbi_token_refresh_minutes');
            }

            // Nombre del Resource Group
            if (!Schema::hasColumn('configurations', 'azure_resource_group')) {
                $table->string('azure_resource_group', 128)->nullable()->after('azure_subscription_id');
            }

            // Índices útiles
            $table->index('azure_subscription_id', 'cfg_azure_subscription_idx');
            $table->index('azure_resource_group', 'cfg_azure_rg_idx');
        });
    }

    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            // Borrar índices por nombre (seguro aunque no existan)
            try { $table->dropIndex('cfg_azure_subscription_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('cfg_azure_rg_idx'); } catch (\Throwable $e) {}

            if (Schema::hasColumn('configurations', 'azure_resource_group')) {
                $table->dropColumn('azure_resource_group');
            }
            if (Schema::hasColumn('configurations', 'azure_subscription_id')) {
                $table->dropColumn('azure_subscription_id');
            }
        });
    }
};
