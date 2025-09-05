<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('config_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip', 64)->nullable();
            $table->json('changes'); // [{field, old, new}]
            $table->timestamps();

            $table->index('created_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config_audits');
    }
};

