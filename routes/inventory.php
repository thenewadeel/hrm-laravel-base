<?php

use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Inventory\InventoryItemController;
use App\Http\Controllers\Inventory\InventoryReportController;
use App\Http\Controllers\Inventory\InventoryStockController;
use App\Http\Controllers\Inventory\InventoryStoreController;
use App\Http\Controllers\Inventory\InventoryTransactionController;
use Illuminate\Support\Facades\Route;

// Inventory Base Routes - API routes moved to api.php
// Web routes for inventory management only

Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');

    // Inventory Items - matches nav: route('inventory.items.index')
    Route::prefix('/items')->name('items.')->group(function () {
        Route::get('/', [InventoryItemController::class, 'index'])->name('index');
        Route::get('/create', [InventoryItemController::class, 'create'])->name('create');
        Route::post('/', [InventoryItemController::class, 'store'])->name('store');
        Route::get('/{item}', [InventoryItemController::class, 'show'])->name('show');
        Route::get('/{item}/edit', [InventoryItemController::class, 'edit'])->name('edit');
        Route::put('/{item}', [InventoryItemController::class, 'update'])->name('update');
        Route::delete('/{item}', [InventoryItemController::class, 'destroy'])->name('destroy');
    });

    // Inventory Stores - matches nav: route('inventory.stores.index')
    Route::prefix('/stores')->name('stores.')->group(function () {
        Route::get('/', [InventoryStoreController::class, 'index'])->name('index');
        Route::get('/create', [InventoryStoreController::class, 'create'])->name('create');
        Route::post('/', [InventoryStoreController::class, 'store'])->name('store');
        Route::get('/{store}', [InventoryStoreController::class, 'show'])->name('show');
        Route::get('/{store}/edit', [InventoryStoreController::class, 'edit'])->name('edit');
        Route::put('/{store}', [InventoryStoreController::class, 'update'])->name('update');
        Route::delete('/{store}', [InventoryStoreController::class, 'destroy'])->name('destroy');
    });

    // Inventory Transactions - matches nav: route('inventory.transactions.index')
    Route::prefix('/transactions')->name('transactions.')->group(function () {
        Route::get('/', [InventoryTransactionController::class, 'index'])->name('index');
        Route::get('/create', [InventoryTransactionController::class, 'create'])->name('create');
        Route::get('/wizard', [InventoryTransactionController::class, 'wizard'])->name('wizard');
        Route::post('/', [InventoryTransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}', [InventoryTransactionController::class, 'show'])->name('show');
    });

    // Inventory Reports - fixed to match your nav structure
    Route::prefix('/reports')->name('reports.')->group(function () {
        Route::get('/', [InventoryReportController::class, 'index'])->name('index');
        Route::get('/low-stock', [InventoryReportController::class, 'lowStock'])->name('low-stock');
        Route::get('/movement', [InventoryReportController::class, 'movement'])->name('movement');
        Route::get('/stock-levels', [InventoryReportController::class, 'stockLevels'])->name('stock-levels');
        
        // PDF Downloads
        Route::get('/download/low-stock', [InventoryReportController::class, 'downloadLowStock'])->name('download.low-stock');
        Route::get('/download/stock-levels', [InventoryReportController::class, 'downloadStockLevels'])->name('download.stock-levels');
        Route::get('/download/movement', [InventoryReportController::class, 'downloadMovement'])->name('download.movement');
    });

    // Inventory Stock Operations
    Route::prefix('/stock')->name('stock.')->group(function () {
        Route::get('/adjustment', [InventoryStockController::class, 'adjustment'])->name('adjustment');
        Route::post('/adjustment', [InventoryStockController::class, 'processAdjustment'])->name('process-adjustment');
        Route::get('/count', [InventoryStockController::class, 'count'])->name('count');
        Route::post('/count', [InventoryStockController::class, 'processCount'])->name('process-count');
        Route::get('/transfer', [InventoryStockController::class, 'transfer'])->name('transfer');
        Route::post('/transfer', [InventoryStockController::class, 'processTransfer'])->name('process-transfer');
    });
});

    // Mobile Inventory
    // Route::prefix('mobile/inventory')->name('mobile.inventory.')->group(function () {
    //     Route::get('/dashboard', [MobileInventoryController::class, 'dashboard'])->name('mobile.dashboard');
    //     Route::get('/stock-count', [MobileInventoryController::class, 'stockCount'])->name('mobile.stock-count');
    // });
