<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('capacity_activity', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('capacity_id')->constrained('capacities')->cascadeOnDelete();

            $table->timestamp('last_seen_at')->nullable()->index();
            $table->unsignedInteger('hits_last_window')->default(0);

            $table->timestamps();

            $table->unique('capacity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacity_activity');
    }
};
