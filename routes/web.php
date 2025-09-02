<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
// Eliminado: use App\Http\Controllers\EmpresasController;
// Eliminado: use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\AuditAdminController; // ← NUEVO (reset auditoría)
use App\Http\Controllers\ConfiguracionesController;
use App\Http\Controllers\PowerBiImportController;
use App\Http\Controllers\WorkspacesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ReportViewerController;
use App\Http\Controllers\AuditAnalyticsController;
use App\Http\Controllers\CapacityController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\GruposController;
use App\Http\Controllers\FoldersController;
use App\Http\Controllers\UserPortalController;
use App\Http\Controllers\Api\AuditController;
// use App\Http\Controllers\Admin\AuditDiagController; // eliminado: diagnóstico de auditoría
/*
|--------------------------------------------------------------------------
| Rutas web (AdminLTE + Breeze)
|--------------------------------------------------------------------------
*/

// Home -> Mi espacio (portal de usuario)
Route::get('/', function () {
    return redirect()->route('portal.index');
})->middleware(['auth'])->name('dashboards.index');

// /dashboard (compatibilidad Breeze) -> redirige al portal
Route::get('/dashboard', function () {
    return redirect()->route('portal.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grupo interno protegido
Route::middleware(['auth'])->group(function () {

    // Menú principal (limpiado: sin conjuntos de datos)
    Route::view('/aplicaciones',    'apps.index')->name('apps.index');

    // Capacidades (solo admin/editor)
    Route::prefix('capacidades')->middleware('role:admin|editor')->group(function () {
        Route::get('/', [CapacityController::class, 'index'])->name('capacities.index');
        Route::post('/refresh', [CapacityController::class, 'refresh'])->name('capacities.refresh');
        Route::post('/{capacity}/pause', [CapacityController::class, 'pause'])->name('capacities.pause');
        Route::post('/{capacity}/resume', [CapacityController::class, 'resume'])->name('capacities.resume');
    });

    // Workspaces (solo admin/editor)
    Route::get('/espacios-trabajo', [WorkspacesController::class, 'index'])
        ->middleware('role:admin|editor')
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

    // (Sección eliminada)

    // ABM Usuarios (solo admin)
    Route::resource('usuarios', UsersController::class)->middleware('role:admin')->parameters([
        'usuarios' => 'usuario',
    ])->names([
        'index' => 'users.index',
        'create'=> 'users.create',
        'store' => 'users.store',
        'edit'  => 'users.edit',
        'update'=> 'users.update',
        'destroy'=>'users.destroy',
        'show'  => 'users.show',
    ])->except(['show']);

    // ABM Grupos (solo admin)
    Route::resource('grupos', GruposController::class)->middleware('role:admin')->parameters([
        'grupos' => 'grupo',
    ])->names([
        'index' => 'grupos.index',
        'create'=> 'grupos.create',
        'store' => 'grupos.store',
        'edit'  => 'grupos.edit',
        'update'=> 'grupos.update',
        'destroy'=>'grupos.destroy',
        'show'  => 'grupos.show',
    ])->except(['show']);

    // ABM Carpetas (solo admin)
    Route::resource('carpetas', FoldersController::class)->middleware('role:admin')->parameters([
        'carpetas' => 'carpeta',
    ])->names([
        'index' => 'folders.index',
        'create'=> 'folders.create',
        'store' => 'folders.store',
        'edit'  => 'folders.edit',
        'update'=> 'folders.update',
        'destroy'=>'folders.destroy',
        'show'  => 'folders.show',
    ])->except(['show']);

    // Portal de usuario (rol usuario)
    Route::prefix('mi')->group(function () {
        Route::get('/', [UserPortalController::class, 'index'])->name('portal.index');
        Route::get('/favoritos', [UserPortalController::class, 'favorites'])->name('portal.favoritos');
        Route::post('/favoritos/{report}/toggle', [UserPortalController::class, 'toggleFavorite'])->name('portal.fav.toggle');
        Route::get('/mas-accedidos', [UserPortalController::class, 'mostAccessed'])->name('portal.mas');
        Route::get('/reportes', [UserPortalController::class, 'myReports'])->name('portal.reportes');
    });

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

// Área ADMIN (solo admin)
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
        
        // Eliminado: Diagnóstico de auditoría

        // (Sección eliminada)

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

        // 🔒 Reset total de auditoría (solo admin)
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

// Tema (light/dark) por sesión
Route::post('/theme', function (Request $request) {
    $current = optional($request->user())->theme ?? session('theme', 'light');
    $requested = $request->string('theme')->lower()->toString();
    $to = in_array($requested, ['light', 'dark'], true)
        ? $requested
        : ($current === 'dark' ? 'light' : 'dark');

    // Persist per user when available
    if ($request->user()) {
        $request->user()->forceFill(['theme' => $to])->save();
    }

    // Keep session in sync for immediate effect
    session(['theme' => $to]);
    return back();
})->middleware('auth')->name('theme.switch');

// Auth (Breeze)
require __DIR__ . '/auth.php';
