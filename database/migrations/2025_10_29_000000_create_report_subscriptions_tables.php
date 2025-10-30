<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('report_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 150);
            $table->boolean('is_enabled')->default(true);
            $table->enum('format', ['pdf', 'ppt'])->default('pdf');
            $table->enum('recurrence', ['daily', 'weekly', 'monthly', 'cron'])->default('daily');
            $table->string('cron_expression', 100)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedTinyInteger('hour')->nullable();
            $table->unsignedTinyInteger('minute')->nullable();
            $table->string('timezone', 64)->nullable();
            $table->uuid('report_id');
            $table->string('mail_subject', 255)->nullable();
            $table->text('mail_body')->nullable();
            $table->unsignedTinyInteger('max_attachment_size_mb')->nullable();
            $table->boolean('notify_workspace_admins')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('reports')->cascadeOnDelete();
            $table->index(['recurrence', 'is_enabled']);
            $table->index(['report_id', 'is_enabled']);
            $table->index(['start_date', 'end_date']);
        });

        Schema::create('report_subscription_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('report_subscription_id')->constrained('report_subscriptions')->cascadeOnDelete();
            $table->string('page_name', 255);
            $table->string('display_name', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['report_subscription_id', 'page_name']);
        });

        Schema::create('report_subscription_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('report_subscription_id')->constrained('report_subscriptions')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['report_subscription_id', 'group_id']);
        });

        Schema::create('report_subscription_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('report_subscription_id')->constrained('report_subscriptions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['report_subscription_id', 'user_id']);
        });

        Schema::create('report_subscription_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('report_subscription_id')->constrained('report_subscriptions')->cascadeOnDelete();
            $table->string('channel')->default('mail');
            $table->string('notifiable_type', 40);
            $table->unsignedBigInteger('notifiable_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['report_subscription_id', 'notifiable_type']);
        });

        Schema::create('report_subscription_runs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('report_subscription_id')->constrained('report_subscriptions')->cascadeOnDelete();
            $table->dateTime('scheduled_time');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('finish_time')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedInteger('recipients_total')->default(0);
            $table->unsignedInteger('recipients_sent')->default(0);
            $table->unsignedInteger('recipients_failed')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->unique(['report_subscription_id', 'scheduled_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_subscription_runs');
        Schema::dropIfExists('report_subscription_notifications');
        Schema::dropIfExists('report_subscription_users');
        Schema::dropIfExists('report_subscription_groups');
        Schema::dropIfExists('report_subscription_pages');
        Schema::dropIfExists('report_subscriptions');
    }
};
