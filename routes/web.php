<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Static documentation routes
Route::get('/docs', function () {
    return view('docs');
});

Route::get('/docs/{path?}', function ($path = null) {
    $docsPath = public_path('docs');
    $filePath = $docsPath . '/' . $path;
    
    // Security: prevent directory traversal
    if (str_contains($path, '..') || !file_exists($filePath)) {
        abort(404);
    }
    
    // If directory requested, serve index.html
    if (is_dir($filePath) && file_exists($filePath . '/index.html')) {
        $filePath = $filePath . '/index.html';
    }
    
    return response()->file($filePath);
})->where('path', '.*');

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
    require __DIR__.'/setup.php';

    // -------------------
    // Organization routes
    // -------------------
    require __DIR__.'/organization.php';

    // -------------------
    // HRM routes
    // -------------------
    require __DIR__.'/hrm.php';

    // -------------------
    // Accounting routes
    // -------------------
    require __DIR__.'/accounts.php';

    // -------------------
    // Inventory routes
    // -------------------
    require __DIR__.'/inventory.php';
});
// -----------------------
// -----------------------
// Demo routes
// -----------------------
require __DIR__.'/demo.php';

// Temporary debug routes
// -----------------------
require __DIR__.'/debug.php';
