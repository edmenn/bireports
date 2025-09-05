<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->uuid('id')->primary();           // datasetId de Power BI
            $table->uuid('workspace_id');            // groupId
            $table->string('name');
            $table->string('configured_by')->nullable();
            $table->boolean('is_refreshable')->default(false);
            $table->boolean('add_rows_api_enabled')->default(false);
            $table->timestamps();

            $table->index('workspace_id');
            $table->index(['workspace_id','name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datasets');
    }
};

