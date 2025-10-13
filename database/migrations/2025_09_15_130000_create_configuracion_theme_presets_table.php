<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configuration_theme_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuration_id')->constrained('configurations')->cascadeOnDelete();
            $table->string('name', 50);
            $table->json('config');
            $table->timestamps();

            $table->unique(['configuration_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuration_theme_presets');
    }
};
