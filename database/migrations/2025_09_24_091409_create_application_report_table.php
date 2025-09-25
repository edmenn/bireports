<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('application_report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->uuid('report_id');
            $table->foreignId('section_id')->nullable()->constrained('application_sections')->nullOnDelete();
            $table->unsignedInteger('display_order')->nullable();
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('reports')->cascadeOnDelete();
            $table->unique(['application_id', 'report_id']);
            $table->index(['application_id', 'section_id']);
            $table->index(['application_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_report');
    }
};