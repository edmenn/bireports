<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\AuditAdminController; // ← NUEVO (reset auditoría)
use App\Http\Controllers\ConfiguracionesController;
use App\Http\Controllers\PowerBiImportController;
use App\Http\Controllers\WorkspacesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ReportViewerController;
use App\Http\Controllers\AuditAnalyticsController;
use App\Http\Controllers\CapacityController;
use App\Http\Controllers\Api\AuditController;

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
        Route::post('/{capacity}/pause', [CapacityController::class, 'pause'])->name('capacities.pause');
        Route::post('/{capacity}/resume', [CapacityController::class, 'resume'])->name('capacities.resume');
    });

    // Workspaces + Reports (vista pública interna por workspace)
    Route::get('/espacios-trabajo', [WorkspacesController::class, 'index'])
        ->name('workspaces.index');

    Route::get('/espacios-trabajo/{workspace}', [WorkspacesController::class, 'show'])
        ->whereUuid('workspace')
        ->name('workspaces.show');

    Route::get('/espacios-trabajo/{workspace}/reportes/{report}', [ReportsController::class, 'show'])
        ->whereUuid('workspace')
        ->whereUuid('report')
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

    // Auditoría (analítica y export)
    Route::get('/auditoria', [AuditAnalyticsController::class, 'index'])
        ->name('auditoria.index');

    Route::get('/auditoria/export', [AuditAnalyticsController::class, 'export'])
        ->name('auditoria.export');

    // Configuraciones (controller)
    Route::get('/configuraciones', [ConfiguracionesController::class, 'index'])
        ->name('configuraciones.index');

    Route::put('/configuraciones/{configuracion}', [ConfiguracionesController::class, 'update'])
        ->name('configuraciones.update');

    // ✅ Endpoint de auditoría llamado desde Blade → route('api.audit.store')
    Route::post('/api/audits', [AuditController::class, 'store'])
        ->name('api.audit.store');

    // ✅ Rutas de perfil (Breeze)
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ SHIM de seguridad: si por alguna razón profile.edit no quedó registrado
    if (! Route::has('profile.edit')) {
        Route::get('/mi-perfil', fn () => redirect()->route('dashboards.index'))
            ->name('profile.edit');
    }
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

        // CRUD de imagen/descripcion de Report
        Route::get('/reportes/{report}/edit', [ReportsController::class, 'edit'])
            ->whereUuid('report')
            ->name('reports.edit');

        Route::put('/reportes/{report}', [ReportsController::class, 'update'])
            ->whereUuid('report')
            ->name('reports.update');

        // 🔒 Reset total de auditoría (solo super_admin)
        Route::get('/auditoria/reset', [AuditAdminController::class, 'showReset'])
            ->name('admin.audit.reset.show');

        Route::post('/auditoria/reset', [AuditAdminController::class, 'reset'])
            ->name('admin.audit.reset.run');

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
    if (! in_array($locale, ['es', 'pt', 'en'], true)) {
        abort(404);
    }
    session(['locale' => $locale]);
    return back();
})->name('lang.switch');

// Auth (Breeze)
require __DIR__ . '/auth.php';
