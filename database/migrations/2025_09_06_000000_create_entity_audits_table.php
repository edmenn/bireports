<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entity_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action', 32)->index(); // created|updated|deleted|attached|detached|synced
            $table->string('auditable_type');
            $table->string('auditable_id');
            $table->json('changes')->nullable(); // {before:{}, after:{}}
            $table->json('meta')->nullable();    // extra: ip, route, user_agent, context specifics
            $table->timestamps();

            $table->index(['auditable_type','auditable_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_audits');
    }
};

