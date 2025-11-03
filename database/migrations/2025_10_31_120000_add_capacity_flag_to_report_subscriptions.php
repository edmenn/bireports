<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('report_subscriptions', 'ensure_capacity_active')) {
                $table->boolean('ensure_capacity_active')
                    ->default(false)
                    ->after('is_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('report_subscriptions', 'ensure_capacity_active')) {
                $table->dropColumn('ensure_capacity_active');
            }
        });
    }
};
