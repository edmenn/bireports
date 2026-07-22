<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('configurations')->whereNull('site_logo_width')->orWhere('site_logo_width', '<', 16)->update(['site_logo_width' => 120]);
        DB::table('configurations')->whereNull('site_logo_height')->orWhere('site_logo_height', '<', 16)->update(['site_logo_height' => 40]);
        DB::table('configurations')->whereNull('site_logo_compact_width')->orWhere('site_logo_compact_width', '<', 16)->update(['site_logo_compact_width' => 40]);
        DB::table('configurations')->whereNull('site_logo_compact_height')->orWhere('site_logo_compact_height', '<', 16)->update(['site_logo_compact_height' => 40]);
        DB::table('configurations')->whereNull('preloader_logo_width')->orWhere('preloader_logo_width', '<', 16)->update(['preloader_logo_width' => 60]);
        DB::table('configurations')->whereNull('preloader_logo_height')->orWhere('preloader_logo_height', '<', 16)->update(['preloader_logo_height' => 60]);
    }

    public function down(): void
    {
    }
};
