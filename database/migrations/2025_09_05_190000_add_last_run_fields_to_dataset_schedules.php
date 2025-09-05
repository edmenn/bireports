<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dataset_schedules', function (Blueprint $table) {
            $table->string('last_run_status', 20)->nullable()->after('last_run_at'); // success|error
            $table->text('last_run_message')->nullable()->after('last_run_status');
        });
    }

    public function down(): void
    {
        Schema::table('dataset_schedules', function (Blueprint $table) {
            $table->dropColumn(['last_run_status', 'last_run_message']);
        });
    }
};

