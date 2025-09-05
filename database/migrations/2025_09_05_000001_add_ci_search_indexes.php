<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // users
            DB::statement('CREATE INDEX IF NOT EXISTS idx_users_lower_name ON users ((LOWER(name)))');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_users_lower_email ON users ((LOWER(email)))');

            // reports (id/workspace_id/dataset_id: UUID -> text)
            DB::statement('CREATE INDEX IF NOT EXISTS idx_reports_lower_name ON reports ((LOWER(name)))');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_reports_lower_id ON reports ((LOWER(id::text)))');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_reports_lower_workspace_id ON reports ((LOWER(workspace_id::text)))');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_reports_lower_dataset_id ON reports ((LOWER(dataset_id::text)))');

            // groups
            DB::statement('CREATE INDEX IF NOT EXISTS idx_groups_lower_name ON groups ((LOWER(name)))');

            // folders
            DB::statement('CREATE INDEX IF NOT EXISTS idx_folders_lower_name ON folders ((LOWER(name)))');

            // capacities (capacity_uuid puede ser UUID)
            DB::statement('CREATE INDEX IF NOT EXISTS idx_capacities_lower_display_name ON capacities ((LOWER(display_name)))');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_capacities_lower_capacity_uuid ON capacities ((LOWER(capacity_uuid::text)))');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_capacities_lower_region ON capacities ((LOWER(region)))');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_capacities_lower_sku ON capacities ((LOWER(sku)))');
            return;
        }

        // En MySQL la colación suele ser case-insensitive por defecto; no creamos índices funcionales aquí
        // para evitar incompatibilidades de sintaxis. Si necesitas índices específicos, podemos agregar
        // columnas generadas e índices sobre ellas.
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_users_lower_name');
            DB::statement('DROP INDEX IF EXISTS idx_users_lower_email');

            DB::statement('DROP INDEX IF EXISTS idx_reports_lower_name');
            DB::statement('DROP INDEX IF EXISTS idx_reports_lower_id');
            DB::statement('DROP INDEX IF EXISTS idx_reports_lower_workspace_id');
            DB::statement('DROP INDEX IF EXISTS idx_reports_lower_dataset_id');

            DB::statement('DROP INDEX IF EXISTS idx_groups_lower_name');
            DB::statement('DROP INDEX IF EXISTS idx_folders_lower_name');

            DB::statement('DROP INDEX IF EXISTS idx_capacities_lower_display_name');
            DB::statement('DROP INDEX IF EXISTS idx_capacities_lower_capacity_uuid');
            DB::statement('DROP INDEX IF EXISTS idx_capacities_lower_region');
            DB::statement('DROP INDEX IF EXISTS idx_capacities_lower_sku');
            return;
        }

        if ($driver === 'mysql') {
            // En MySQL: DROP INDEX requiere la tabla
            DB::statement('DROP INDEX IF EXISTS idx_users_lower_name ON users');
            DB::statement('DROP INDEX IF EXISTS idx_users_lower_email ON users');

            DB::statement('DROP INDEX IF EXISTS idx_reports_lower_name ON reports');
            DB::statement('DROP INDEX IF EXISTS idx_reports_lower_id ON reports');
            DB::statement('DROP INDEX IF EXISTS idx_reports_lower_workspace_id ON reports');
            DB::statement('DROP INDEX IF EXISTS idx_reports_lower_dataset_id ON reports');

            DB::statement('DROP INDEX IF EXISTS idx_groups_lower_name ON `groups`');
            DB::statement('DROP INDEX IF EXISTS idx_folders_lower_name ON folders');

            DB::statement('DROP INDEX IF EXISTS idx_capacities_lower_display_name ON capacities');
            DB::statement('DROP INDEX IF EXISTS idx_capacities_lower_capacity_uuid ON capacities');
            DB::statement('DROP INDEX IF EXISTS idx_capacities_lower_region ON capacities');
            DB::statement('DROP INDEX IF EXISTS idx_capacities_lower_sku ON capacities');
            return;
        }
    }
};
