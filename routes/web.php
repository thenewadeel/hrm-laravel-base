<?php

use App\Http\Controllers\AccountsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified',
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return view('dashboard');
//     })->name('dashboard');
// });


// routes/web.php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');
    // -------------------
    // Setup Wizard Routes
    // -------------------
    require __DIR__ . '/setup.php';

    // -------------------
    // Organization routes
    // -------------------
    require __DIR__ . '/organization.php';

    // -------------------
    // HRM routes
    // -------------------
    require __DIR__ . '/hrm.php';

    // -------------------
    // Accounting routes
    // -------------------
    require __DIR__ . '/accounts.php';

    // -------------------
    // Inventory routes
    // -------------------
    require __DIR__ . '/inventory.php';
});
// -----------------------
// Temporary debug routes
// -----------------------
require __DIR__ . '/debug.php';
