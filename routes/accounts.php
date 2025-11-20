<?php

use App\Http\Controllers\AccountsController;
use App\Livewire\Accounting\CashReceipts\Create as CreateCashReceipt;
use App\Livewire\Accounting\CashPayments\Create as CreateCashPayment;
use Illuminate\Support\Facades\Route;

Route::prefix('/accounts')->name('accounting.')->group(function () {
    Route::get('/', [AccountsController::class, 'index'])->name('index');
    
    // Cash Receipts Management
    Route::prefix('/cash-receipts')->name('cash-receipts.')->group(function () {
        Route::get('/', function () {
            return view('accounting.cash-receipts.index');
        })->name('index');
        Route::get('/create', CreateCashReceipt::class)->name('create');
        // TODO: Add edit, show, delete routes when implemented
    });
    
    // Cash Payments Management
    Route::prefix('/cash-payments')->name('cash-payments.')->group(function () {
        Route::get('/', function () {
            return view('accounting.cash-payments.index');
        })->name('index');
        Route::get('/create', CreateCashPayment::class)->name('create');
        // TODO: Add edit, show, delete routes when implemented
    });
    
    // PDF Downloads
    Route::get('/download/trial-balance', [AccountsController::class, 'downloadTrialBalance'])->name('download.trial-balance');
    Route::get('/download/income-statement', [AccountsController::class, 'downloadIncomeStatement'])->name('download.income-statement');
    Route::get('/download/balance-sheet', [AccountsController::class, 'downloadBalanceSheet'])->name('download.balance-sheet');
});
