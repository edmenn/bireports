<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('capacity_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('capacity_id')->constrained('capacities')->cascadeOnDelete();

            $table->unsignedTinyInteger('weekday')->index(); // 0=Dom, 6=Sab
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('enabled')->default(true);

            $table->timestamps();

            $table->unique(['capacity_id', 'weekday', 'start_time', 'end_time'], 'uniq_capacity_day_interval');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacity_schedules');
    }
};
