<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->decimal('cover_scale_x', 5, 2)->default(1.00)->after('cover_scale');
            $table->decimal('cover_scale_y', 5, 2)->default(1.00)->after('cover_scale_x');
        });

        DB::table('reports')->update([
            'cover_scale_x' => DB::raw('COALESCE(cover_scale, 1.00)'),
            'cover_scale_y' => DB::raw('COALESCE(cover_scale, 1.00)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['cover_scale_x', 'cover_scale_y']);
        });
    }
};
