<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dataset_schedules', function (Blueprint $table) {
            $table->id();
            $table->uuid('dataset_id');
            $table->json('days');       // array de días: Sunday..Saturday
            $table->json('times');      // array de strings HH:mm
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->index(['dataset_id','enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dataset_schedules');
    }
};

