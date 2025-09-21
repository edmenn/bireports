<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configuracion_theme_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuracion_id')->constrained('configuraciones')->cascadeOnDelete();
            $table->string('name', 50);
            $table->json('config');
            $table->timestamps();

            $table->unique(['configuracion_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_theme_presets');
    }
};
