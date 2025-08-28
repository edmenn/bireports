<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // Nombre del cliente
            $table->string('slug')->unique();             // slug para urls/subdominio
            $table->string('custom_domain')->nullable();  // dominio personalizado (futuro)
            $table->string('ga_tracking_code')->nullable();
            $table->enum('default_view_type', ['folders','icons','list'])->default('folders');
            $table->string('default_email_lang', 10)->default('es');

            // JSON de configuraciones (branding, login, horarios)
            $table->json('branding')->nullable();         // logos/colores
            $table->json('login')->nullable();            // imágenes/método auth
            $table->json('access_schedule')->nullable();  // control por día/horario

            // Power BI/Fabric (se moverá a KeyVault más adelante)
            $table->string('powerbi_client_id')->nullable();
            $table->string('powerbi_tenant_id')->nullable();
            $table->string('powerbi_client_secret_hint')->nullable(); // sólo pista, no secreto

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
