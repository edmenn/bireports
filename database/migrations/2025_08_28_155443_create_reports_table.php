<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabla para guardar los Reports de cada Workspace
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();            // reportId de Power BI
            $table->uuid('workspace_id');             // FK lógico (sin constraint duro)
            $table->string('name');
            $table->string('dataset_id')->nullable();
            $table->string('web_url')->nullable();
            $table->timestamps();

            $table->index('workspace_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
