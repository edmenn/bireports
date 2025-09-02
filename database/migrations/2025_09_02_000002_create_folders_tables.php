<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('folder_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('folders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['folder_id', 'user_id']);
        });

        Schema::create('folder_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('folders')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->unique(['folder_id', 'group_id']);
        });

        Schema::create('folder_report', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('folders')->cascadeOnDelete();
            $table->uuid('report_id');
            $table->foreign('report_id')->references('id')->on('reports')->cascadeOnDelete();
            $table->unique(['folder_id', 'report_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folder_report');
        Schema::dropIfExists('folder_group');
        Schema::dropIfExists('folder_user');
        Schema::dropIfExists('folders');
    }
};
