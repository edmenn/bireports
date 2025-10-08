<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuditAdminController; // reset auditoría
use App\Http\Controllers\Admin\AuditChangesController;
use App\Http\Controllers\Admin\EntityAuditController;
use App\Http\Controllers\ConfiguracionesController;
use App\Http\Controllers\PowerBiImportController;
use App\Http\Controllers\WorkspacesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DatasetsController;
use App\Http\Controllers\ReportViewerController;
use App\Http\Controllers\AuditAnalyticsController;
use App\Http\Controllers\CapacityController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\GruposController;
use App\Http\Controllers\FoldersController;
use App\Http\Controllers\ApplicationsController;
use App\Http\Controllers\ApplicationSectionsController;
use App\Http\Controllers\UserPortalController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\UsersSyncController; // importación desde Entra
use App\Http\Middleware\EnsureUserIsNotBlocked;
use App\Http\Middleware\ForbidExternalPasswordChange;

/*
|--------------------------------------------------------------------------
| Rutas web (AdminLTE + Breeze)
|--------------------------------------------------------------------------
*/

// Home -> Mi espacio (portal de usuario)
Route::get('/', function () {
    return redirect()->route('portal.index');
})->middleware(['auth', EnsureUserIsNotBlocked::class])->name('dashboards.index');

// /dashboard (compatibilidad Breeze) -> redirige al portal
Route::get('/dashboard', function () {
    return redirect()->route('portal.index');
})->middleware(['auth', EnsureUserIsNotBlocked::class, 'verified'])->name('dashboard');

// Grupo interno protegido
Route::middleware(['auth', EnsureUserIsNotBlocked::class])->group(function () {

    // Forzar que {usuario} sea numérico para evitar capturar strings como "bulk-destroy"
    Route::pattern('usuario', '[0-9]+');

    // Menú principal

    // Capacidades (solo admin/editor)
    Route::prefix('capacidades')->middleware('role:admin|editor')->group(function () {
        Route::get('/', [CapacityController::class, 'index'])->name('capacities.index');
        Route::post('/refresh', [CapacityController::class, 'refresh'])->name('capacities.refresh');
        Route::post('/{capacity}/pause', [CapacityController::class, 'pause'])->name('capacities.pause');
        Route::post('/{capacity}/resume', [CapacityController::class, 'resume'])->name('capacities.resume');
        // Programación y auditoría
        Route::get('/{capacity}/schedule', [CapacityController::class, 'schedule'])->name('capacities.schedule');
        Route::post('/{capacity}/schedule', [CapacityController::class, 'scheduleStore'])->name('capacities.schedule.store');
        Route::post('/{capacity}/schedule/{slot}/toggle', [CapacityController::class, 'scheduleToggle'])->name('capacities.schedule.toggle');
        Route::delete('/{capacity}/schedule/{slot}', [CapacityController::class, 'scheduleDelete'])->name('capacities.schedule.delete');
        Route::put('/{capacity}/settings', [CapacityController::class, 'settingsUpdate'])->name('capacities.settings.update');
        Route::get('/{capacity}/audit', [CapacityController::class, 'audit'])->name('capacities.audit');
        Route::post('/{capacity}/audit/refresh', [CapacityController::class, 'auditRefresh'])->name('capacities.audit.refresh');
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

    // ✅ Eliminación masiva de usuarios (solo admin) — DEFINIR PRIMERO
    Route::delete('/usuarios/bulk-destroy', [UsersController::class, 'bulkDestroy'])
        ->middleware('role:admin')
        ->name('users.bulk-destroy');

    // ABM Usuarios (solo admin)
    Route::resource('usuarios', UsersController::class)
        ->middleware('role:admin')
        ->parameters(['usuarios' => 'usuario'])
        ->names([
            'index'   => 'users.index',
            'create'  => 'users.create',
            'store'   => 'users.store',
            'edit'    => 'users.edit',
            'update'  => 'users.update',
            'destroy' => 'users.destroy',
            'show'    => 'users.show',
        ])
        ->except(['show']);

    // Toggle bloqueo individual
    Route::post('/usuarios/{usuario}/toggle-block', [UsersController::class, 'toggleBlock'])
        ->middleware('role:admin')
        ->where(['usuario' => '[0-9]+'])
        ->name('users.toggle-block');

    // Bloqueo/Desbloqueo masivo de usuarios (solo admin)
    Route::post('/usuarios/bulk-block', [UsersController::class, 'bulkBlock'])
        ->middleware('role:admin')
        ->name('users.bulk-block');
    Route::post('/usuarios/bulk-unblock', [UsersController::class, 'bulkUnblock'])
        ->middleware('role:admin')
        ->name('users.bulk-unblock');

    // ABM Perfiles/Roles (solo admin)
    Route::resource('perfiles', RoleController::class)
        ->middleware('role:admin')
        ->parameters(['perfiles' => 'rol'])
        ->names([
            'index'   => 'roles.index',
            'create'  => 'roles.create',
            'store'   => 'roles.store',
            'edit'    => 'roles.edit',
            'update'  => 'roles.update',
            'destroy' => 'roles.destroy',
            'show'    => 'roles.show',
        ])
        ->except(['show']);

    // Importación Entra (solo admin)
    Route::post('/usuarios/sync-entra', [UsersSyncController::class, 'sync'])
        ->middleware('role:admin')
        ->name('users.sync-entra');

    // Previsualización/paginación Entra (AJAX)
    Route::get('/usuarios/sync-entra/preview', [UsersSyncController::class, 'preview'])
        ->middleware('role:admin')
        ->name('users.sync-entra.preview');

    // Importar SOLO los seleccionados desde el modal
    Route::post('/usuarios/sync-entra/selected', [UsersSyncController::class, 'importSelected'])
        ->middleware('role:admin')
        ->name('users.sync-entra.selected');
    // Actualizar usuarios existentes desde Entra (no crea nuevos)
    Route::post('/usuarios/sync-entra/refresh-existing', [UsersSyncController::class, 'refreshExisting'])
        ->middleware('role:admin')
        ->name('users.sync-entra.refresh-existing');

    // ABM Grupos (solo admin)
    Route::resource('grupos', GruposController::class)
        ->middleware('role:admin')
        ->parameters(['grupos' => 'grupo'])
        ->names([
            'index'   => 'grupos.index',
            'create'  => 'grupos.create',
            'store'   => 'grupos.store',
            'edit'    => 'grupos.edit',
            'update'  => 'grupos.update',
            'destroy' => 'grupos.destroy',
            'show'    => 'grupos.show',
        ])
        ->except(['show']);

    // ABM Carpetas (solo admin)
    Route::resource('carpetas', FoldersController::class)
        ->middleware('role:admin')
        ->parameters(['carpetas' => 'carpeta'])
        ->names([
            'index'   => 'folders.index',
            'create'  => 'folders.create',
            'store'   => 'folders.store',
            'edit'    => 'folders.edit',
            'update'  => 'folders.update',
            'destroy' => 'folders.destroy',
            'show'    => 'folders.show',
        ])
        ->except(['show']);
    // Applications (admin)
    Route::resource('applications', ApplicationsController::class)
        ->middleware('role:admin')
        ->except(['show']);
    // Portal de usuario
    Route::prefix('mi')->group(function () {
        Route::get('/', [UserPortalController::class, 'index'])->name('portal.index');
        Route::get('/favoritos', [UserPortalController::class, 'favorites'])->name('portal.favoritos');
        Route::post('/favoritos/{report}/toggle', [UserPortalController::class, 'toggleFavorite'])->name('portal.fav.toggle');
        Route::get('/mas-accedidos', [UserPortalController::class, 'mostAccessed'])->name('portal.mas');
        Route::get('/aplicaciones', [UserPortalController::class, 'apps'])->name('portal.apps');
        Route::get('/aplicaciones/{application:slug}', [UserPortalController::class, 'application'])->name('portal.applications.show');
    });

    // Auditoría (analítica y export)
    Route::get('/auditoria', [AuditAnalyticsController::class, 'index'])
        ->name('auditoria.index');

    Route::get('/auditoria/export', [AuditAnalyticsController::class, 'export'])
        ->name('auditoria.export');

    // Configuraciones (solo admin)
    Route::middleware('role:admin')->group(function () {
        Route::get('/configuraciones', [ConfiguracionesController::class, 'index'])
            ->name('configuraciones.index');
    // Legacy: redirige a auditoría unificada
    Route::get('/configuraciones/auditoria', function(){
        return redirect()->route('auditoria.changes.index');
    })->name('config.audit.index');

        Route::get('/configuraciones/tema', [ConfiguracionesController::class, 'theme'])
            ->name('configuraciones.tema');
        Route::post('/configuraciones/tema/presets', [ConfiguracionesController::class, 'storeThemePreset'])
            ->name('configuraciones.tema.presets.store');
        Route::delete('/configuraciones/tema/presets/{preset}', [ConfiguracionesController::class, 'destroyThemePreset'])
            ->name('configuraciones.tema.presets.destroy');

        Route::get('/configuraciones/login', [ConfiguracionesController::class, 'login'])
            ->name('configuraciones.login');

        Route::post('/configuraciones/tema/reset', function (Request $request) {
            $cfg = \App\Models\Configuracion::firstOrCreate(['id' => 1]);
            $cfg->forceFill([
                'theme_config'        => null,
                'theme_name'          => null,
                'theme_navbar_class'  => null,
                'theme_sidebar_class' => null,
            ])->save();

            return redirect()
                ->route('configuraciones.tema')
                ->with('success', 'Colores restablecidos a valores de fábrica.');
        })->name('configuraciones.tema.reset');

        Route::put('/configuraciones/{configuracion}', [ConfiguracionesController::class, 'update'])
            ->name('configuraciones.update');
    });

    // ✅ Endpoint de auditoría llamado desde Blade → route('api.audit.store')
    Route::post('/api/audits', [AuditController::class, 'store'])
        ->name('api.audit.store');

    // Ping de capacidad (viewer)
    Route::post('/api/capacities/{capacity}/ping', [\App\Http\Controllers\CapacityActivityController::class, 'ping'])
        ->name('capacities.ping');

    // ✅ Rutas de perfil (Breeze/propio).
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware(ForbidExternalPasswordChange::class)
        ->name('profile.update');

});

// Área ADMIN (solo admin)
Route::prefix('admin')
    ->middleware(['auth', EnsureUserIsNotBlocked::class, 'role:admin'])
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

        // Listado global de reportes
        Route::get('/reportes', [ReportsController::class, 'index'])
            ->name('reports.index');

        // Datasets (conjuntos de datos)
        Route::get('/datasets', [DatasetsController::class, 'index'])
            ->name('datasets.index');
        // Vista combinada de programaciones (local + Fabric)
        Route::get('/datasets/schedules', [DatasetsController::class, 'schedulesIndex'])
            ->name('datasets.schedules.index');
        Route::get('/datasets/schedules/export', [DatasetsController::class, 'schedulesExport'])
            ->name('datasets.schedules.export');
        Route::post('/datasets/{workspace}/{dataset}/refresh', [DatasetsController::class, 'refresh'])
            ->whereUuid('workspace')->whereUuid('dataset')
            ->name('datasets.refresh');
        Route::post('/datasets/{workspace}/{dataset}/metadata-refresh', [DatasetsController::class, 'metadataRefresh'])
            ->whereUuid('workspace')->whereUuid('dataset')
            ->name('datasets.metadata.refresh');
        Route::get('/datasets/{workspace}/{dataset}/history', [DatasetsController::class, 'history'])
            ->whereUuid('workspace')->whereUuid('dataset')
            ->name('datasets.history');
        Route::get('/datasets/{workspace}/{dataset}/schedule', [DatasetsController::class, 'schedule'])
            ->whereUuid('workspace')->whereUuid('dataset')
            ->name('datasets.schedule');
        // Programaciones locales
        Route::post('/datasets/{workspace}/{dataset}/local-schedules', [DatasetsController::class, 'localScheduleStore'])
            ->whereUuid('workspace')->whereUuid('dataset')
            ->name('datasets.local.store');
        Route::post('/datasets/{workspace}/{dataset}/local-schedules/{schedule}/toggle', [DatasetsController::class, 'localScheduleToggle'])
            ->whereUuid('workspace')->whereUuid('dataset')
            ->name('datasets.local.toggle');
        Route::delete('/datasets/{workspace}/{dataset}/local-schedules/{schedule}', [DatasetsController::class, 'localScheduleDelete'])
            ->whereUuid('workspace')->whereUuid('dataset')
            ->name('datasets.local.delete');

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

        // Auditoría unificada de cambios (sistema + configuración)
        Route::get('/auditoria/cambios', [AuditChangesController::class, 'index'])
            ->name('auditoria.changes.index');

        // Legacy: /auditoria/entidades -> unificada
        Route::get('/auditoria/entidades', function(){
            return redirect()->route('auditoria.changes.index');
        })->name('auditoria.entities.index');

        // Diagnóstico rápido del token/alcance
        Route::get('dev/powerbi/ping', function (\App\Services\PowerBiService $svc) {
            return response()->json($svc->ping());
        })->name('powerbi.ping');

        // Importación Workspaces + Reports desde Power BI
        Route::get('dev/import/powerbi/run', [PowerBiImportController::class, 'importWorkspacesAndReports'])
            ->name('powerbi.import.run');

        // Importación de Datasets desde Power BI
        Route::get('dev/import/powerbi/datasets', [PowerBiImportController::class, 'importDatasets'])
            ->name('powerbi.import.datasets');
    });

// Idiomas (cambiado de /lang a /locale para evitar reglas del proxy)
Route::get('/locale/{locale}', function (Request $request, string $locale) {
    $supported = config('app.supported_locales', ['en', 'es', 'pt']);
    if (! in_array($locale, $supported, true)) {
        abort(404);
    }
    $request->session()->put('locale', $locale);
    Cookie::queue('locale', $locale, 60 * 24 * 365);

    $user = $request->user();
    if ($user && $user->locale !== $locale) {
        $user->forceFill(['locale' => $locale])->save();
    }

    return back();
})->name('lang.switch');

// Tema (light/dark) por sesión
Route::post('/theme', function (Request $request) {
    $current   = optional($request->user())->theme ?? session('theme', 'light');
    $requested = $request->string('theme')->lower()->toString();

    $to = in_array($requested, ['light', 'dark'], true)
        ? $requested
        : ($current === 'dark' ? 'light' : 'dark');

    if ($request->user()) {
        $request->user()->forceFill(['theme' => $to])->save();
    }

    session(['theme' => $to]);
    return back();
})->middleware('auth')->name('theme.switch');

// Auth (Breeze u otro starter)
require __DIR__ . '/auth.php';











