<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('companies');
    }

    public function down(): void
    {
        // Si quisieras poder revertir (no necesario), podrías recrearla mínima.
        // Lo dejamos vacío a propósito para no reintroducir confusión.
    }
};
