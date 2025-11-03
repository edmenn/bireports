<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_subscriptions', function (Blueprint $table) {
            if (! Schema::hasColumn('report_subscriptions', 'weekly_days')) {
                $table->string('weekly_days', 50)->nullable()->after('recurrence');
            }

            if (! Schema::hasColumn('report_subscriptions', 'weekly_interval')) {
                $table->unsignedTinyInteger('weekly_interval')->default(1)->after('weekly_days');
            }

            if (! Schema::hasColumn('report_subscriptions', 'monthly_day')) {
                $table->unsignedTinyInteger('monthly_day')->nullable()->after('weekly_interval');
            }

            if (! Schema::hasColumn('report_subscriptions', 'monthly_use_last_day')) {
                $table->boolean('monthly_use_last_day')->default(false)->after('monthly_day');
            }
        });
    }

    public function down(): void
    {
        Schema::table('report_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('report_subscriptions', 'monthly_use_last_day')) {
                $table->dropColumn('monthly_use_last_day');
            }

            if (Schema::hasColumn('report_subscriptions', 'monthly_day')) {
                $table->dropColumn('monthly_day');
            }

            if (Schema::hasColumn('report_subscriptions', 'weekly_interval')) {
                $table->dropColumn('weekly_interval');
            }

            if (Schema::hasColumn('report_subscriptions', 'weekly_days')) {
                $table->dropColumn('weekly_days');
            }
        });
    }
};
