<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AuditChangesController;
use App\Http\Controllers\Admin\EntityAuditController;
use App\Http\Controllers\ConfigurationsController;
use App\Http\Controllers\PowerBiImportController;
use App\Http\Controllers\WorkspacesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DatasetsController;
use App\Http\Controllers\ReportViewerController;
use App\Http\Controllers\AuditAnalyticsController;
use App\Http\Controllers\CapacityController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\GroupsController;
use App\Http\Controllers\FoldersController;
use App\Http\Controllers\ApplicationsController;
use App\Http\Controllers\ApplicationSectionsController;
use App\Http\Controllers\UserPortalController;
use App\Http\Controllers\ReportSubscriptionController;
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

    // Enforce that {user} captures only numeric identifiers for user routes.
    Route::pattern('user', '[0-9]+');

    // Menú principal

    // Capacidades (solo admin/editor)
    $registerCapacityRoutes = static function (string $prefix, bool $withNames = false): void {
        Route::prefix($prefix)
            ->middleware('role:admin|editor')
            ->group(function () use ($withNames): void {
                $route = Route::get('/', [CapacityController::class, 'index']);
                if ($withNames) { $route->name('capacities.index'); }

                $route = Route::post('/refresh', [CapacityController::class, 'refresh']);
                if ($withNames) { $route->name('capacities.refresh'); }

                $route = Route::post('/{capacity}/pause', [CapacityController::class, 'pause']);
                if ($withNames) { $route->name('capacities.pause'); }

                $route = Route::post('/{capacity}/resume', [CapacityController::class, 'resume']);
                if ($withNames) { $route->name('capacities.resume'); }

                // Scheduling and audit
                $route = Route::get('/{capacity}/schedule', [CapacityController::class, 'schedule']);
                if ($withNames) { $route->name('capacities.schedule'); }

                $route = Route::post('/{capacity}/schedule', [CapacityController::class, 'scheduleStore']);
                if ($withNames) { $route->name('capacities.schedule.store'); }

                $route = Route::post('/{capacity}/schedule/{slot}/toggle', [CapacityController::class, 'scheduleToggle']);
                if ($withNames) { $route->name('capacities.schedule.toggle'); }

                $route = Route::delete('/{capacity}/schedule/{slot}', [CapacityController::class, 'scheduleDelete']);
                if ($withNames) { $route->name('capacities.schedule.delete'); }

                $route = Route::put('/{capacity}/settings', [CapacityController::class, 'settingsUpdate']);
                if ($withNames) { $route->name('capacities.settings.update'); }

                $route = Route::get('/{capacity}/audit', [CapacityController::class, 'audit']);
                if ($withNames) { $route->name('capacities.audit'); }

                $route = Route::post('/{capacity}/audit/refresh', [CapacityController::class, 'auditRefresh']);
                if ($withNames) { $route->name('capacities.audit.refresh'); }
            });
    };

    $registerCapacityRoutes('capacities', true);
    $registerCapacityRoutes('capacidades');

    // Workspaces (admin/editor)
    Route::get('/workspaces', [WorkspacesController::class, 'index'])
        ->middleware('role:admin|editor')
        ->name('workspaces.index');

    Route::get('/workspaces/{workspace}', [WorkspacesController::class, 'show'])
        ->whereUuid('workspace')
        ->name('workspaces.show');

    Route::get('/workspaces/{workspace}/reports/{report}', [ReportsController::class, 'show'])
        ->whereUuid('workspace')
        ->whereUuid('report')
        ->name('reports.show');

    Route::prefix('espacios-trabajo')
        ->middleware('role:admin|editor')
        ->group(function () {
            Route::get('/', fn () => redirect()->route('workspaces.index'));
            Route::get('/{workspace}', [WorkspacesController::class, 'show'])->whereUuid('workspace');
            Route::get('/{workspace}/reportes/{report}', [ReportsController::class, 'show'])
                ->whereUuid('workspace')
                ->whereUuid('report');
        });

    // 🚀 Vista embebida con powerbi-client + auditoría
    Route::get('/reports/{report_uuid}', [ReportViewerController::class, 'show'])
        ->whereUuid('report_uuid')
        ->name('reports.viewer');

    // User management (admin only)
    Route::delete('/users/bulk-destroy', [UsersController::class, 'bulkDestroy'])
        ->middleware('role:admin')
        ->name('users.bulk-destroy');

    Route::resource('users', UsersController::class)
        ->middleware('role:admin')
        ->parameters(['users' => 'user'])
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

    Route::post('/users/{user}/toggle-block', [UsersController::class, 'toggleBlock'])
        ->middleware('role:admin')
        ->whereNumber('user')
        ->name('users.toggle-block');

    Route::post('/users/bulk-block', [UsersController::class, 'bulkBlock'])
        ->middleware('role:admin')
        ->name('users.bulk-block');
    Route::post('/users/bulk-unblock', [UsersController::class, 'bulkUnblock'])
        ->middleware('role:admin')
        ->name('users.bulk-unblock');

    // Role management (admin only)
    Route::resource('roles', RoleController::class)
        ->middleware('role:admin')
        ->parameters(['roles' => 'role'])
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

    Route::prefix('perfiles')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/', fn () => redirect()->route('roles.index'));
            Route::get('/create', fn () => redirect()->route('roles.create'));
            Route::post('/', [RoleController::class, 'store']);
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->whereNumber('role');
            Route::put('/{role}', [RoleController::class, 'update'])->whereNumber('role');
            Route::patch('/{role}', [RoleController::class, 'update'])->whereNumber('role');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->whereNumber('role');
        });

    // Entra synchronisation (admin only)
    Route::post('/users/sync-entra', [UsersSyncController::class, 'sync'])
        ->middleware('role:admin')
        ->name('users.sync-entra');

    Route::get('/users/sync-entra/preview', [UsersSyncController::class, 'preview'])
        ->middleware('role:admin')
        ->name('users.sync-entra.preview');

    Route::post('/users/sync-entra/selected', [UsersSyncController::class, 'importSelected'])
        ->middleware('role:admin')
        ->name('users.sync-entra.selected');

    Route::post('/users/sync-entra/refresh-existing', [UsersSyncController::class, 'refreshExisting'])
        ->middleware('role:admin')
        ->name('users.sync-entra.refresh-existing');

    // Legacy Spanish aliases for /usuarios*
    // Legacy alias: keep /usuarios endpoints pointing to the English controllers.
    Route::prefix('usuarios')
        ->middleware('role:admin')
        ->group(function () {
            Route::delete('/bulk-destroy', [UsersController::class, 'bulkDestroy']);

            Route::get('/', fn () => redirect()->route('users.index'));
            Route::get('/create', fn () => redirect()->route('users.create'));
            Route::post('/', [UsersController::class, 'store']);
            Route::get('/{user}/edit', [UsersController::class, 'edit'])->whereNumber('user');
            Route::put('/{user}', [UsersController::class, 'update'])->whereNumber('user');
            Route::patch('/{user}', [UsersController::class, 'update'])->whereNumber('user');
            Route::delete('/{user}', [UsersController::class, 'destroy'])->whereNumber('user');

            Route::post('/{user}/toggle-block', [UsersController::class, 'toggleBlock'])->whereNumber('user');
            Route::post('/bulk-block', [UsersController::class, 'bulkBlock']);
            Route::post('/bulk-unblock', [UsersController::class, 'bulkUnblock']);

            Route::post('/sync-entra', [UsersSyncController::class, 'sync']);
            Route::get('/sync-entra/preview', [UsersSyncController::class, 'preview']);
            Route::post('/sync-entra/selected', [UsersSyncController::class, 'importSelected']);
            Route::post('/sync-entra/refresh-existing', [UsersSyncController::class, 'refreshExisting']);
        });

    // ABM Grupos (solo admin)
    Route::resource('groups', GroupsController::class)
        ->middleware('role:admin')
        ->parameters(['groups' => 'group'])
        ->names([
            'index'   => 'groups.index',
            'create'  => 'groups.create',
            'store'   => 'groups.store',
            'edit'    => 'groups.edit',
            'update'  => 'groups.update',
            'destroy' => 'groups.destroy',
            'show'    => 'groups.show',
        ])
        ->except(['show']);

    // Legacy alias: keep /grupos endpoints pointing to the English controllers.
    Route::prefix('grupos')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/', fn () => redirect()->route('groups.index'));
            Route::get('/create', fn () => redirect()->route('groups.create'));
            Route::post('/', [GroupsController::class, 'store']);
            Route::get('/{group}/edit', [GroupsController::class, 'edit'])->whereNumber('group');
            Route::put('/{group}', [GroupsController::class, 'update'])->whereNumber('group');
            Route::patch('/{group}', [GroupsController::class, 'update'])->whereNumber('group');
            Route::delete('/{group}', [GroupsController::class, 'destroy'])->whereNumber('group');
        });

    // ABM Carpetas (solo admin)
    Route::resource('folders', FoldersController::class)
        ->middleware('role:admin')
        ->parameters(['folders' => 'folder'])
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
    // Legacy alias: keep /carpetas endpoints pointing to the English controllers.
    Route::prefix('carpetas')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/', fn () => redirect()->route('folders.index'));
            Route::get('/create', fn () => redirect()->route('folders.create'));
            Route::post('/', [FoldersController::class, 'store']);
            Route::get('/{folder}/edit', [FoldersController::class, 'edit'])->whereNumber('folder');
            Route::put('/{folder}', [FoldersController::class, 'update'])->whereNumber('folder');
            Route::patch('/{folder}', [FoldersController::class, 'update'])->whereNumber('folder');
            Route::delete('/{folder}', [FoldersController::class, 'destroy'])->whereNumber('folder');
        });
    // Applications (admin)
    Route::resource('applications', ApplicationsController::class)
        ->middleware('role:admin')
        ->except(['show']);
    // User portal
    Route::prefix('portal')->group(function () {
        Route::get('/', [UserPortalController::class, 'index'])->name('portal.index');
        Route::get('/favorites', [UserPortalController::class, 'favorites'])->name('portal.favorites');
        Route::post('/favorites/{report}/toggle', [UserPortalController::class, 'toggleFavorite'])->name('portal.favorites.toggle');
        Route::get('/most-accessed', [UserPortalController::class, 'mostAccessed'])->name('portal.most-accessed');
        Route::get('/applications', [UserPortalController::class, 'apps'])->name('portal.apps');
        Route::get('/applications/{application:slug}', [UserPortalController::class, 'application'])->name('portal.applications.show');
    });

    // Legacy alias: keep /mi endpoints pointing to the English portal routes.
    Route::prefix('mi')->group(function () {
        Route::get('/', fn () => redirect()->route('portal.index'));
        Route::get('/favoritos', fn () => redirect()->route('portal.favorites'));
        Route::post('/favoritos/{report}/toggle', [UserPortalController::class, 'toggleFavorite'])->name('legacy.portal.favorites.toggle');
        Route::get('/mas-accedidos', fn () => redirect()->route('portal.most-accessed'));
        Route::get('/aplicaciones', fn () => redirect()->route('portal.apps'));
        Route::get('/aplicaciones/{application:slug}', [UserPortalController::class, 'application'])->name('legacy.portal.applications.show');
    });

    // Auditoría (analítica y export)
    Route::get('/audit', [AuditAnalyticsController::class, 'index'])
        ->name('audit.index');

    Route::get('/audit/export', [AuditAnalyticsController::class, 'export'])
        ->name('audit.export');

    // Configurationes (solo admin)
    Route::middleware('role:admin')->group(function () {
        Route::get('/configurations', [ConfigurationsController::class, 'index'])
            ->name('configurations.index');
    // Legacy: redirige a auditoría unificada
    Route::get('/configurations/audit', function(){
        return redirect()->route('audit.changes.index');
    })->name('config.audit.index');

        Route::get('/configurations/theme', [ConfigurationsController::class, 'theme'])
            ->name('configurations.theme');
        Route::post('/configurations/theme/presets', [ConfigurationsController::class, 'storeThemePreset'])
            ->name('configurations.theme.presets.store');
        Route::delete('/configurations/theme/presets/{preset}', [ConfigurationsController::class, 'destroyThemePreset'])
            ->name('configurations.theme.presets.destroy');

        Route::get('/configurations/login', [ConfigurationsController::class, 'login'])
            ->name('configurations.login');
        Route::post('/configurations/mail/test', [ConfigurationsController::class, 'testMail'])
            ->name('configurations.mail.test');

        Route::post('/configurations/theme/reset', function (Request $request) {
            $cfg = \App\Models\Configuration::firstOrCreate(['id' => 1]);
            $cfg->forceFill([
                'theme_config'        => null,
                'theme_name'          => null,
                'theme_navbar_class'  => null,
                'theme_sidebar_class' => null,
            ])->save();

            return redirect()
                ->route('configurations.theme')
                ->with('success', __('configurations.alerts.theme_reset'));
        })->name('configurations.theme.reset');

        Route::put('/configurations/{configuration}', [ConfigurationsController::class, 'update'])
            ->name('configurations.update');
    });

    // Legacy alias: keep /configuraciones endpoints pointing to the English configuration routes.
    Route::prefix('configuraciones')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/', fn () => redirect()->route('configurations.index'));
            Route::get('/auditoria', fn () => redirect()->route('audit.changes.index'));
            Route::get('/tema', [ConfigurationsController::class, 'theme'])->name('legacy.configurations.theme');
            Route::post('/tema/presets', [ConfigurationsController::class, 'storeThemePreset'])->name('legacy.configurations.theme.presets.store');
            Route::delete('/tema/presets/{preset}', [ConfigurationsController::class, 'destroyThemePreset'])->name('legacy.configurations.theme.presets.destroy');
            Route::get('/login', [ConfigurationsController::class, 'login'])->name('legacy.configurations.login');
            Route::post('/mail/prueba', [ConfigurationsController::class, 'testMail'])->name('legacy.configurations.mail.test');
            Route::post('/tema/reset', function (Request $request) {
                $cfg = \App\Models\Configuration::firstOrCreate(['id' => 1]);
                $cfg->forceFill([
                    'theme_config'        => null,
                    'theme_name'          => null,
                    'theme_navbar_class'  => null,
                    'theme_sidebar_class' => null,
                ])->save();

                return redirect()
                    ->route('configurations.theme')
                    ->with('success', __('configurations.alerts.theme_reset'));
            })->name('legacy.configurations.theme.reset');
            Route::put('/{configuration}', [ConfigurationsController::class, 'update'])->name('legacy.configurations.update');
        });

    // ✅ Endpoint de auditoría llamado desde Blade → route('api.audit.store')
    Route::post('/api/audits', [AuditController::class, 'store'])
        ->name('api.audit.store');

    // Ping de capacidad (viewer)
    Route::post('/api/capacities/{capacity}/ping', [\App\Http\Controllers\CapacityActivityController::class, 'ping'])
        ->name('capacities.ping');

    // ✅ Rutas de perfil (Breeze/propio).
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [ReportSubscriptionController::class, 'index'])->name('index');
        Route::post('/', [ReportSubscriptionController::class, 'store'])->name('store');
        Route::get('/{reportSubscription}', [ReportSubscriptionController::class, 'show'])
            ->whereNumber('reportSubscription')
            ->name('show');
        Route::get('/{reportSubscription}/edit', [ReportSubscriptionController::class, 'edit'])
            ->whereNumber('reportSubscription')
            ->name('edit');
        Route::put('/{reportSubscription}', [ReportSubscriptionController::class, 'update'])
            ->whereNumber('reportSubscription')
            ->name('update');
        Route::delete('/{reportSubscription}', [ReportSubscriptionController::class, 'destroy'])
            ->whereNumber('reportSubscription')
            ->name('destroy');
        Route::post('/{reportSubscription}/send-now', [ReportSubscriptionController::class, 'sendNow'])
            ->whereNumber('reportSubscription')
            ->name('send');
    });

    Route::get('/reports/{report}/subscriptions/metadata', [ReportSubscriptionController::class, 'metadata'])
        ->whereUuid('report')
        ->name('reports.subscriptions.metadata');

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

        // Auditoría unificada de cambios (sistema + configuración)
        Route::get('/audit/cambios', [AuditChangesController::class, 'index'])
            ->name('audit.changes.index');

        // Legacy: /audit/entidades -> unificada
        Route::get('/audit/entidades', function(){
            return redirect()->route('audit.changes.index');
        })->name('audit.entities.index');

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








