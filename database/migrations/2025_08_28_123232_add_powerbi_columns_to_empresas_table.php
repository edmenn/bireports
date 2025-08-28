<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->string('powerbi_client_id')->nullable()->after('id');
            $table->text('powerbi_client_secret')->nullable();        // la encriptamos en el modelo
            $table->string('powerbi_tenant_id')->nullable();
            $table->string('powerbi_redirect_uri')->nullable();
            $table->string('powerbi_scope')->nullable()->default('https://analysis.windows.net/powerbi/api/.default');
            $table->unsignedInteger('powerbi_token_refresh_minutes')->default(0); // 0 = deshabilitado
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'powerbi_client_id',
                'powerbi_client_secret',
                'powerbi_tenant_id',
                'powerbi_redirect_uri',
                'powerbi_scope',
                'powerbi_token_refresh_minutes',
            ]);
        });
    }
};
