<?php

use App\Http\Controllers\Api\Membership\MemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Membership API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Member Export API Route (must come before resource routes)
    Route::get('/members/export', [MemberController::class, 'export'])->name('api.members.export');

    // Member API Routes
    Route::get('/members', [MemberController::class, 'index'])->name('api.members.index');
    Route::post('/members', [MemberController::class, 'store'])->name('api.members.store');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('api.members.show');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('api.members.update');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('api.members.destroy');

    // Member Statistics API Routes
    Route::get('/members/statistics', [MemberController::class, 'statistics'])->name('api.members.statistics');
    Route::get('/members/expiring', [MemberController::class, 'expiring'])->name('api.members.expiring');
    Route::get('/members/search', [MemberController::class, 'search'])->name('api.members.search');
});
