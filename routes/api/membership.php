<?php

use App\Http\Controllers\Api\Membership\MemberController;
use App\Http\Controllers\Api\Membership\ScanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Membership API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
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
    
    // Barcode Scanning API Routes
    Route::post('/scan/member', [ScanController::class, 'scanMember'])->name('api.scan.member');
    Route::post('/scan/family-member', [ScanController::class, 'scanFamilyMember'])->name('api.scan.familyMember');
    Route::post('/scan', [ScanController::class, 'scan'])->name('api.scan.generic');
    Route::get('/scan/validate-barcode', [ScanController::class, 'validateBarcode'])->name('api.scan.validateBarcode');
    Route::get('/scan/history', [ScanController::class, 'scanHistory'])->name('api.scan.history');
});