<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('configurations', 'nombre')) {
                $table->string('nombre', 255)->nullable()->after('id');
            }

            if (!Schema::hasColumn('configurations', 'dominio_personalizado')) {
                $table->string('dominio_personalizado', 255)->nullable()->after('nombre');
            }

            if (!Schema::hasColumn('configurations', 'google_analytics_code')) {
                $table->string('google_analytics_code', 255)->nullable()->after('dominio_personalizado');
            }

            if (!Schema::hasColumn('configurations', 'tipo_visualizacion')) {
                $table->string('tipo_visualizacion', 20)->default('pastas')->after('google_analytics_code');
            }

            if (!Schema::hasColumn('configurations', 'email_locale')) {
                $table->string('email_locale', 5)->default('es')->after('tipo_visualizacion');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (Schema::hasColumn('configurations', 'email_locale')) {
                $table->dropColumn('email_locale');
            }
            if (Schema::hasColumn('configurations', 'tipo_visualizacion')) {
                $table->dropColumn('tipo_visualizacion');
            }
            if (Schema::hasColumn('configurations', 'google_analytics_code')) {
                $table->dropColumn('google_analytics_code');
            }
            if (Schema::hasColumn('configurations', 'dominio_personalizado')) {
                $table->dropColumn('dominio_personalizado');
            }
            if (Schema::hasColumn('configurations', 'nombre')) {
                $table->dropColumn('nombre');
            }
        });
    }
};

