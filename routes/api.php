<?php

use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\CapacityActivityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/audit', [AuditController::class, 'store'])->name('api.audit.store');
    Route::post('/capacities/{capacity}/ping', [CapacityActivityController::class, 'ping'])
    ->name('capacities.ping');
});

