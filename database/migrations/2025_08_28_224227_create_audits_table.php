<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->uuid('report_uuid')->index();
            $table->uuid('workspace_uuid')->nullable()->index();
            $table->uuid('dataset_uuid')->nullable()->index();

            $table->string('action', 32)->index(); // view|render|export|error|filter_change|page_change|bookmark

            $table->string('rls_role', 128)->nullable()->index();
            $table->jsonb('filters')->nullable();
            $table->string('page_name', 128)->nullable();
            $table->string('bookmark', 128)->nullable();

            $table->ipAddress('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer', 512)->nullable();
            $table->integer('duration_ms')->nullable();

            $table->timestampTz('occurred_at')->index();
            $table->boolean('success')->default(true)->index();
            $table->text('error_message')->nullable();

            $table->timestampsTz();

            $table->index(['report_uuid', 'action', 'occurred_at']);
            $table->index(['user_id', 'report_uuid']);
        });

        $dayOfWeekExpression = DB::getDriverName() === 'sqlite'
            ? "CASE strftime('%w', occurred_at) WHEN '0' THEN 7 ELSE CAST(strftime('%w', occurred_at) AS INTEGER) END"
            : 'EXTRACT(ISODOW FROM occurred_at)';

        DB::statement("
            CREATE VIEW audits_dayofweek AS
            SELECT id,
                   {$dayOfWeekExpression} AS dow_iso,
                   occurred_at
            FROM audits
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS audits_dayofweek");
        Schema::dropIfExists('audits');
    }
};
