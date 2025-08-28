<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresasController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\ConfiguracionesController;
use App\Http\Controllers\PowerBiImportController;

/*
|--------------------------------------------------------------------------
| Rutas web
|--------------------------------------------------------------------------
| Usamos AdminLTE para todas las pantallas internas.
| Las rutas se protegen con el middleware 'auth' (Breeze).
|
*/

// Home -> Informes (AdminLTE)
Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboards.index');

// /dashboard (compatibilidad con Breeze), también AdminLTE
Route::get('/dashboard', function () {
    return view('dashboard'); // resources/views/dashboard.blade.php (AdminLTE)
})->middleware(['auth', 'verified'])->name('dashboard');

// Grupo de rutas internas protegidas
Route::middleware(['auth'])->group(function () {
    // Rutas con nombre que usa el menú de AdminLTE (todas existen)
    Route::view('/conjuntos-datos',  'datasets.index')->name('datasets.index');
    Route::view('/aplicaciones',     'apps.index')->name('apps.index');
    Route::view('/espacios-trabajo', 'workspaces.index')->name('workspaces.index');
    Route::view('/capacidades',      'capacities.index')->name('capacities.index');

    Route::resource('empresas', EmpresasController::class)->parameters([
        'empresas' => 'empresa'
    ])->only(['index','create','store','edit','update','destroy'])->names([
        'index'   => 'empresas.index',
        'create'  => 'empresas.create',
        'store'   => 'empresas.store',
        'edit'    => 'empresas.edit',
        'update'  => 'empresas.update',
        'destroy' => 'empresas.destroy',
    ]);

    Route::view('/usuarios', 'users.index')->name('users.index');
    Route::view('/grupos',   'grupos.index')->name('grupos.index');
    Route::view('/carpetas', 'folders.index')->name('folders.index');

    Route::view('/auditoria', 'auditoria.index')->name('auditoria.index');

    // ✅ Configuraciones (controlador, no Route::view)
    Route::get('/configuraciones', [ConfiguracionesController::class, 'index'])
        ->name('configuraciones.index');
    Route::put('/configuraciones/{configuracion}', [ConfiguracionesController::class, 'update'])
        ->name('configuraciones.update');

    // Perfil (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// Área ADMIN (solo super_admin)
Route::prefix('admin')
    ->middleware(['auth', 'role:super_admin'])
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

        Route::resource('clients', ClientController::class)
            ->names('admin.clients'); // admin.clients.index, create, store, etc.
        Route::get('/dev/powerbi/ping', function (\App\Services\PowerBiService $svc) {
            return response()->json($svc->ping());
        })->name('powerbi.ping')->middleware(['auth','role:super_admin']);
        // 👇 OJO: SIN barra inicial y SIN repetir "admin"
        Route::get('dev/import/powerbi/workspaces', [PowerBiImportController::class, 'importWorkspaces'])
            ->name('powerbi.import.workspaces.dev');
    });
    

// Idiomas
Route::get('/lang/{locale}', function (string $locale) {
    if (! in_array($locale, ['es', 'pt', 'en'])) abort(404);
    session(['locale' => $locale]);
    return back();
})->name('lang.switch');

// Rutas de autenticación (login/register/etc.)
require __DIR__.'/auth.php';
