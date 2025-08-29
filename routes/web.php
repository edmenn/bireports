<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\ConfiguracionesController;
use App\Http\Controllers\PowerBiImportController;
use App\Http\Controllers\WorkspacesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ReportViewerController;
use App\Http\Controllers\AuditAnalyticsController;
use App\Http\Controllers\CapacityController;

/*
|--------------------------------------------------------------------------
| Rutas web (AdminLTE + Breeze)
|--------------------------------------------------------------------------
*/

// Home -> Informes
Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboards.index');

// /dashboard (compatibilidad Breeze)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grupo interno protegido
Route::middleware(['auth'])->group(function () {
    // Menú principal (views placeholder o controllers)
    Route::view('/conjuntos-datos', 'datasets.index')->name('datasets.index');
    Route::view('/aplicaciones',    'apps.index')->name('apps.index');

    // Capacidades (LISTAR / PAUSAR / REANUDAR / RECARGAR)
    Route::prefix('capacidades')->group(function () {
        Route::get('/', [CapacityController::class, 'index'])->name('capacities.index');
        Route::post('/refresh', [CapacityController::class, 'refresh'])->name('capacities.refresh');
        Route::post('/{capacity}/toggle', [CapacityController::class, 'toggle'])->name('capacities.toggle');
    });

    // Workspaces + Reports (vista pública interna por workspace)
    Route::get('/espacios-trabajo', [WorkspacesController::class, 'index'])->name('workspaces.index');
    Route::get('/espacios-trabajo/{workspace}', [WorkspacesController::class, 'show'])->name('workspaces.show');
    Route::get('/espacios-trabajo/{workspace}/reportes/{report}', [ReportsController::class, 'show'])
        ->name('reports.show');

    // 🚀 Vista embebida con powerbi-client + auditoría
    Route::get('/reports/{report_uuid}', [ReportViewerController::class, 'show'])
        ->whereUuid('report_uuid')
        ->name('reports.viewer');

    // Empresas (resource limitado)
    Route::resource('empresas', EmpresasController::class)->parameters([
        'empresas' => 'empresa',
    ])->only(['index','create','store','edit','update','destroy'])->names([
        'index'   => 'empresas.index',
        'create'  => 'empresas.create',
        'store'   => 'empresas.store',
        'edit'    => 'empresas.edit',
        'update'  => 'empresas.update',
        'destroy' => 'empresas.destroy',
    ]);

    // Otras vistas
    Route::view('/usuarios',  'users.index')->name('users.index');
    Route::view('/grupos',    'grupos.index')->name('grupos.index');
    Route::view('/carpetas',  'folders.index')->name('folders.index');

    Route::get('/auditoria', [AuditAnalyticsController::class, 'index'])
        ->name('auditoria.index');

    // Configuraciones (controller)
    Route::get('/configuraciones', [ConfiguracionesController::class, 'index'])
        ->name('configuraciones.index');
    Route::put('/configuraciones/{configuracion}', [ConfiguracionesController::class, 'update'])
        ->name('configuraciones.update');

    // Exportar CSV con filtros actuales
    Route::get('/auditoria/export', [AuditAnalyticsController::class, 'export'])
        ->name('auditoria.export');

    // Perfil (Breeze)
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Área ADMIN (solo super_admin)
Route::prefix('admin')
    ->middleware(['auth', 'role:super_admin'])
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

        Route::resource('clients', ClientController::class)
            ->names('admin.clients');

        // Listado global de reportes
        Route::get('/reportes', [ReportsController::class, 'index'])
            ->name('reports.index');

        /*
         * NUEVO: CRUD de imagen/descripcion de Report
         * - /admin/reportes/{report}/edit -> formulario
         * - /admin/reportes/{report}      -> update (PUT)
         */
        Route::get('/reportes/{report}/edit', [ReportsController::class, 'edit'])
            ->name('reports.edit');
        Route::put('/reportes/{report}', [ReportsController::class, 'update'])
            ->name('reports.update');

        // Diagnóstico rápido del token/alcance
        Route::get('dev/powerbi/ping', function (\App\Services\PowerBiService $svc) {
            return response()->json($svc->ping());
        })->name('powerbi.ping');

        // Importación Workspaces + Reports desde Power BI
        Route::get('dev/import/powerbi/run', [PowerBiImportController::class, 'importWorkspacesAndReports'])
            ->name('powerbi.import.run');
    });

// Idiomas
Route::get('/lang/{locale}', function (string $locale) {
    if (! in_array($locale, ['es', 'pt', 'en'])) abort(404);
    session(['locale' => $locale]);
    return back();
})->name('lang.switch');

// Auth (Breeze)
require __DIR__.'/auth.php';
