<?php

use App\Http\Controllers\Api\AuditController;
use Illuminate\Support\Facades\Route;

// API stateless: solo si necesitas registrar desde un cliente externo sin sesión.
// Nota: la ruta con nombre oficial usada por el visor es la de web.php (/api/audits).
// Para evitar colisión de nombres, dejamos este endpoint con otro name.
Route::post('/audit', [AuditController::class, 'store'])->name('api.audit.store.stateless');
