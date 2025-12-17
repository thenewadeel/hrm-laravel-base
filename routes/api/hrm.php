<?php

use App\Http\Controllers\Api\HRM\EmployeeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HRM API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Employee Export API Route (must come before resource routes)
    Route::get('/employees/export', [EmployeeController::class, 'export'])->name('api.employees.export');

    // Employee API Routes
    Route::get('/employees', [EmployeeController::class, 'index'])->name('api.employees.index');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('api.employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('api.employees.show');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('api.employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('api.employees.destroy');
});
