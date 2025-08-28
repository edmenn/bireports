<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('powerbi_client_id')->nullable();
            $table->text('powerbi_client_secret')->nullable();
            $table->string('powerbi_tenant_id')->nullable();
            $table->string('powerbi_redirect_uri')->nullable();
            $table->string('powerbi_scope')->default('https://analysis.windows.net/powerbi/api/.default');
            $table->integer('powerbi_token_refresh_minutes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
