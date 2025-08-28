<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();

            // Datos básicos de la empresa
            $table->string('nombre');                                // nombre de la empresa
            $table->string('dominio_personalizado')->nullable();     // p.ej. bi.miempresa.com
            $table->string('google_analytics_code')->nullable();     // G-XXXXXXX
            $table->enum('tipo_visualizacion', ['pastas','iconos','lista'])->default('pastas');
            $table->string('email_locale')->default('es');           // idioma por defecto de emails

            // --- Credenciales Power BI / Fabric ---
            $table->string('powerbi_client_id')->nullable();
            $table->text('powerbi_client_secret')->nullable();       // lo encriptamos desde el modelo
            $table->string('powerbi_tenant_id')->nullable();
            $table->string('powerbi_redirect_uri')->nullable();
            $table->string('powerbi_scope')->nullable()
                  ->default('https://analysis.windows.net/powerbi/api/.default');
            $table->unsignedInteger('powerbi_token_refresh_minutes')->default(0); // 0 = deshabilitado

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
