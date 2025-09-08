<?php

use App\Http\Controllers\Api\AuditController;
use Illuminate\Support\Facades\Route;

// API stateless: solo endpoints verdaderamente sin sesión
Route::post('/audit', [AuditController::class, 'store'])->name('api.audit.store');
