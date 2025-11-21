<?php

use App\Http\Controllers\AccountsController;
use App\Livewire\Accounting\CashPayments\Create as CreateCashPayment;
use App\Livewire\Accounting\CashReceipts\Create as CreateCashReceipt;
use App\Livewire\Accounting\ExpenseVoucherForm;
use App\Livewire\Accounting\PurchaseVoucherForm;
use App\Livewire\Accounting\SalaryVoucherForm;
use App\Livewire\Accounting\SalesVoucherForm;
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

    // Specialized Voucher Management
    Route::prefix('/vouchers')->name('vouchers.')->group(function () {
        // Sales Vouchers
        Route::prefix('/sales')->name('sales.')->group(function () {
            Route::get('/create', SalesVoucherForm::class)->name('create');
        });

        // Purchase Vouchers
        Route::prefix('/purchase')->name('purchase.')->group(function () {
            Route::get('/create', PurchaseVoucherForm::class)->name('create');
        });

        // Salary Vouchers
        Route::prefix('/salary')->name('salary.')->group(function () {
            Route::get('/create', SalaryVoucherForm::class)->name('create');
        });

        // Expense Vouchers
        Route::prefix('/expense')->name('expense.')->group(function () {
            Route::get('/create', ExpenseVoucherForm::class)->name('create');
        });
    });

    // Outstanding Statements
    Route::prefix('/outstanding')->name('outstanding.')->group(function () {
        Route::get('/receivables', \App\Livewire\Accounting\ReceivablesOutstanding::class)->name('receivables');
        Route::get('/payables', \App\Livewire\Accounting\PayablesOutstanding::class)->name('payables');
    });


    // PDF Downloads
    Route::get('/download/trial-balance', [AccountsController::class, 'downloadTrialBalance'])->name('download.trial-balance');
    Route::get('/download/income-statement', [AccountsController::class, 'downloadIncomeStatement'])->name('download.income-statement');
    Route::get('/download/balance-sheet', [AccountsController::class, 'downloadBalanceSheet'])->name('download.balance-sheet');
    Route::get('/download/receivables-outstanding', [AccountsController::class, 'downloadReceivablesOutstanding'])->name('download.receivables-outstanding');
    Route::get('/download/payables-outstanding', [AccountsController::class, 'downloadPayablesOutstanding'])->name('download.payables-outstanding');
});
