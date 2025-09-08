<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'allow_resume_outside_window')) {
                $table->boolean('allow_resume_outside_window')->default(false)->after('description');
            }
        });

        Schema::table('capacities', function (Blueprint $table) {
            if (!Schema::hasColumn('capacities', 'outside_window_grace_until')) {
                $table->timestamp('outside_window_grace_until')->nullable()->after('last_state_change_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'allow_resume_outside_window')) {
                $table->dropColumn('allow_resume_outside_window');
            }
        });

        Schema::table('capacities', function (Blueprint $table) {
            if (Schema::hasColumn('capacities', 'outside_window_grace_until')) {
                $table->dropColumn('outside_window_grace_until');
            }
        });
    }
};

