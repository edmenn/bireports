<?php

use App\Http\Controllers\Api\AuditController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/audit', [AuditController::class, 'store'])->name('api.audit.store');
});
