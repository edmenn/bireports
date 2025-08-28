<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabla para guardar los Workspaces (Groups) de Power BI
        Schema::create('workspaces', function (Blueprint $table) {
            $table->uuid('id')->primary();      // groupId de Power BI
            $table->string('name');
            $table->string('type')->nullable(); // e.g. "Workspace"
            $table->boolean('is_read_only')->default(false);
            $table->string('capacity_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
