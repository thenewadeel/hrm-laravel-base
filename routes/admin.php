<?php

use App\Http\Controllers\Admin\AdminPortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [AdminPortalController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/attach-user', [AdminPortalController::class, 'showAttachUserForm'])
        ->name('attach-user.form');

    Route::post('/attach-user', [AdminPortalController::class, 'attachUserToOrganization'])
        ->name('attach-user');

    Route::post('/detach-user', [AdminPortalController::class, 'detachUserFromOrganization'])
        ->name('detach-user');

    Route::post('/fix-user-organization', [AdminPortalController::class, 'fixUserCurrentOrganization'])
        ->name('fix-user-organization');
});
