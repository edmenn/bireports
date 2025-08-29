<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('capacities', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->uuid('capacity_uuid')->unique()->index();
            $table->string('display_name')->index();
            $table->string('sku', 32)->nullable();
            $table->string('type', 32)->nullable();
            $table->string('region', 64)->nullable();

            $table->boolean('supports_pause')->default(true);
            $table->boolean('is_active')->default(false);

            $table->boolean('cost_optimization_enabled')->default(false);
            $table->unsignedSmallInteger('inactivity_timeout_minutes')->nullable();
            $table->boolean('force_shutdown_on_schedule')->default(false);

            $table->timestamp('last_state_change_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capacities');
    }
};
