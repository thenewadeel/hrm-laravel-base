<?php

use App\Http\Controllers\AccountsController;
use Illuminate\Support\Facades\Route;

Route::prefix('/accounts')->name('accounting.')->group(function () {
    Route::get('/', [AccountsController::class, 'index'])->name('index');
    
    // PDF Downloads
    Route::get('/download/trial-balance', [AccountsController::class, 'downloadTrialBalance'])->name('download.trial-balance');
    Route::get('/download/income-statement', [AccountsController::class, 'downloadIncomeStatement'])->name('download.income-statement');
    Route::get('/download/balance-sheet', [AccountsController::class, 'downloadBalanceSheet'])->name('download.balance-sheet');
});
