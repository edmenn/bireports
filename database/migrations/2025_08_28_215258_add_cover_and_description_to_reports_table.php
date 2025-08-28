<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega los campos cover_url (imagen) y description (texto) a la tabla reports
     */
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // URL de la imagen de portada (opcional)
            $table->string('cover_url')->nullable()->after('name');

            // Descripción del reporte (opcional)
            $table->text('description')->nullable()->after('cover_url');
        });
    }

    /**
     * Revertir los cambios
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['cover_url', 'description']);
        });
    }
};
