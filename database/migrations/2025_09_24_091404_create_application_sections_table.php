<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('application_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('parent_id')->nullable()->constrained('application_sections')->nullOnDelete();
            $table->unsignedInteger('display_order')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'slug']);
            $table->index(['application_id', 'name']);
            $table->index(['application_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_sections');
    }
};