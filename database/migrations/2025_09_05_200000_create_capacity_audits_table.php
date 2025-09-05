<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('capacity_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capacity_id')->constrained('capacities')->cascadeOnDelete();
            $table->string('action', 50);   // pause|resume|schedule_pause|schedule_resume|inactivity_pause|settings_update
            $table->string('result', 20)->nullable(); // success|error
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacity_audits');
    }
};

