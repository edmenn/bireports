<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('configurations', 'mail_driver')) {
                $table->string('mail_driver', 20)->nullable()->after('email_locale');
            }
            if (!Schema::hasColumn('configurations', 'mail_host')) {
                $table->string('mail_host', 255)->nullable()->after('mail_driver');
            }
            if (!Schema::hasColumn('configurations', 'mail_port')) {
                $table->unsignedInteger('mail_port')->nullable()->after('mail_host');
            }
            if (!Schema::hasColumn('configurations', 'mail_encryption')) {
                $table->string('mail_encryption', 10)->nullable()->after('mail_port');
            }
            if (!Schema::hasColumn('configurations', 'mail_username')) {
                $table->string('mail_username', 255)->nullable()->after('mail_encryption');
            }
            if (!Schema::hasColumn('configurations', 'mail_password')) {
                $table->string('mail_password', 255)->nullable()->after('mail_username');
            }
            if (!Schema::hasColumn('configurations', 'mail_from_address')) {
                $table->string('mail_from_address', 255)->nullable()->after('mail_password');
            }
            if (!Schema::hasColumn('configurations', 'mail_from_name')) {
                $table->string('mail_from_name', 255)->nullable()->after('mail_from_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            $columns = [
                'mail_from_name',
                'mail_from_address',
                'mail_password',
                'mail_username',
                'mail_encryption',
                'mail_port',
                'mail_host',
                'mail_driver',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('configurations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
