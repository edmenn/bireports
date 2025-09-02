<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            if (!Schema::hasColumn('configuraciones', 'nombre')) {
                $table->string('nombre', 255)->nullable()->after('id');
            }

            if (!Schema::hasColumn('configuraciones', 'dominio_personalizado')) {
                $table->string('dominio_personalizado', 255)->nullable()->after('nombre');
            }

            if (!Schema::hasColumn('configuraciones', 'google_analytics_code')) {
                $table->string('google_analytics_code', 255)->nullable()->after('dominio_personalizado');
            }

            if (!Schema::hasColumn('configuraciones', 'tipo_visualizacion')) {
                $table->string('tipo_visualizacion', 20)->default('pastas')->after('google_analytics_code');
            }

            if (!Schema::hasColumn('configuraciones', 'email_locale')) {
                $table->string('email_locale', 5)->default('es')->after('tipo_visualizacion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            if (Schema::hasColumn('configuraciones', 'email_locale')) {
                $table->dropColumn('email_locale');
            }
            if (Schema::hasColumn('configuraciones', 'tipo_visualizacion')) {
                $table->dropColumn('tipo_visualizacion');
            }
            if (Schema::hasColumn('configuraciones', 'google_analytics_code')) {
                $table->dropColumn('google_analytics_code');
            }
            if (Schema::hasColumn('configuraciones', 'dominio_personalizado')) {
                $table->dropColumn('dominio_personalizado');
            }
            if (Schema::hasColumn('configuraciones', 'nombre')) {
                $table->dropColumn('nombre');
            }
        });
    }
};

