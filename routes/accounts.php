<?php

use App\Http\Controllers\Accounting\FixedAssetController;
use App\Http\Controllers\AccountsController;
use App\Livewire\Accounting\AssetDisposalForm;
use App\Livewire\Accounting\AssetMaintenanceForm;
use App\Livewire\Accounting\AssetTransferForm;
use App\Livewire\Accounting\BankAccounts\Create as CreateBankAccount;
use App\Livewire\Accounting\BankAccounts\Index as BankAccountsIndex;
use App\Livewire\Accounting\BankReconciliation\Reconcile as BankReconciliation;
use App\Livewire\Accounting\BankStatements\Import as ImportBankStatement;
use App\Livewire\Accounting\BankStatements\Index as BankStatementsIndex;
use App\Livewire\Accounting\BankTransactions\Index as BankTransactionsIndex;
use App\Livewire\Accounting\CashPayments\Create as CreateCashPayment;
use App\Livewire\Accounting\CashReceipts\Create as CreateCashReceipt;
use App\Livewire\Accounting\DepreciationPosting;
use App\Livewire\Accounting\ExpenseVoucherForm;
use App\Livewire\Accounting\FixedAssetForm;
use App\Livewire\Accounting\FixedAssetIndex;
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

    // Bank Accounts Management
    Route::prefix('/bank-accounts')->name('bank-accounts.')->group(function () {
        Route::get('/', BankAccountsIndex::class)->name('index');
        Route::get('/create', CreateBankAccount::class)->name('create');
        Route::post('/', [CreateBankAccount::class, 'save'])->name('store');
    });

    // Bank Statements Management
    Route::prefix('/bank-statements')->name('bank-statements.')->group(function () {
        Route::get('/', BankStatementsIndex::class)->name('index');
        Route::get('/import', ImportBankStatement::class)->name('import');
    });

    // Bank Transactions Management
    Route::prefix('/bank-transactions')->name('bank-transactions.')->group(function () {
        Route::get('/', BankTransactionsIndex::class)->name('index');
    });

    // Bank Reconciliation
    Route::prefix('/bank-reconciliation')->name('bank-reconciliation.')->group(function () {
        Route::get('/', BankReconciliation::class)->name('index');
        Route::get('/reconcile/{bankAccountId}/{bankStatementId?}', BankReconciliation::class)->name('reconcile');
    });

    // Fixed Assets Management
    Route::prefix('/fixed-assets')->name('fixed-assets.')->group(function () {
        Route::get('/', FixedAssetIndex::class)->name('index');
        Route::get('/create', FixedAssetForm::class)->name('create');
        Route::post('/', [FixedAssetController::class, 'store'])->name('store');
        Route::get('/edit/{asset}', FixedAssetForm::class)->name('edit');
        Route::put('/{asset}', [FixedAssetController::class, 'update'])->name('update');
        Route::delete('/{asset}', [FixedAssetController::class, 'destroy'])->name('destroy');
        Route::get('/depreciation', DepreciationPosting::class)->name('depreciation');
        Route::get('/transfer/{asset}', AssetTransferForm::class)->name('transfer');
        Route::get('/maintenance/{asset}', AssetMaintenanceForm::class)->name('maintenance');
        Route::get('/dispose/{asset}', AssetDisposalForm::class)->name('dispose');
    });

    // PDF Downloads
    Route::get('/download/trial-balance', [AccountsController::class, 'downloadTrialBalance'])->name('download.trial-balance');
    Route::get('/download/income-statement', [AccountsController::class, 'downloadIncomeStatement'])->name('download.income-statement');
    Route::get('/download/balance-sheet', [AccountsController::class, 'downloadBalanceSheet'])->name('download.balance-sheet');
    Route::get('/download/receivables-outstanding', [AccountsController::class, 'downloadReceivablesOutstanding'])->name('download.receivables-outstanding');
    Route::get('/download/payables-outstanding', [AccountsController::class, 'downloadPayablesOutstanding'])->name('download.payables-outstanding');
    Route::get('/download/bank-statement/{bankStatement}', [AccountsController::class, 'downloadBankStatement'])->name('download.bank-statement');
    Route::get('/download/bank-transactions/{bankAccount}', [AccountsController::class, 'downloadBankTransactions'])->name('download.bank-transactions');
    Route::get('/download/bank-reconciliation/{bankAccount}', [AccountsController::class, 'downloadBankReconciliation'])->name('download.bank-reconciliation');

    // Fixed Assets PDF Downloads
    Route::get('/download/asset-register', [FixedAssetController::class, 'downloadAssetRegister'])->name('fixed-assets.download.asset-register');
    Route::get('/download/depreciation-schedule', [FixedAssetController::class, 'downloadDepreciationSchedule'])->name('fixed-assets.download.depreciation-schedule');

    // Tax Management
    Route::prefix('/tax')->name('tax.')->group(function () {
        // Tax Rates
        Route::prefix('/tax-rates')->name('tax-rates.')->group(function () {
            Route::get('/', \App\Livewire\Accounting\TaxRateIndex::class)->name('index');
            Route::get('/create', \App\Livewire\Accounting\TaxRateForm::class)->name('create');
            Route::get('/edit/{taxRate}', \App\Livewire\Accounting\TaxRateForm::class)->name('edit');
        });

        // Tax Exemptions
        Route::prefix('/tax-exemptions')->name('tax-exemptions.')->group(function () {
            Route::get('/', \App\Livewire\Accounting\TaxExemptionIndex::class)->name('index');
            Route::get('/create', \App\Livewire\Accounting\TaxExemptionForm::class)->name('create');
            Route::get('/edit/{taxExemption}', \App\Livewire\Accounting\TaxExemptionForm::class)->name('edit');
        });

        // Tax Reporting
        Route::prefix('/reporting')->name('reporting.')->group(function () {
            Route::get('/', \App\Livewire\Accounting\TaxReportingDashboard::class)->name('dashboard');
        });

        // Tax Filings
        Route::prefix('/filings')->name('filings.')->group(function () {
            Route::get('/', \App\Livewire\Accounting\TaxFilingManager::class)->name('index');
        });

        // Tax Downloads
        Route::get('/download/tax-report', [\App\Http\Controllers\TaxController::class, 'downloadTaxReport'])->name('download.tax-report');
        Route::get('/download/tax-liability', [\App\Http\Controllers\TaxController::class, 'downloadTaxLiability'])->name('download.tax-liability');
        Route::get('/download/filing-schedule', [\App\Http\Controllers\TaxController::class, 'downloadFilingSchedule'])->name('download.filing-schedule');
    });

    // Financial Years Management
    Route::prefix('/financial-years')->name('financial-years.')->group(function () {
        Route::get('/', \App\Livewire\Accounting\FinancialYears\FinancialYearIndex::class)->name('index');
        Route::get('/create', \App\Livewire\Accounting\FinancialYears\FinancialYearForm::class)->name('create');
        Route::post('/', [\App\Http\Controllers\FinancialYearController::class, 'store'])->name('store');
        Route::get('/edit/{financialYear}', \App\Livewire\Accounting\FinancialYears\FinancialYearForm::class)->name('edit');
        Route::put('/{financialYear}', [\App\Http\Controllers\FinancialYearController::class, 'update'])->name('update');
        Route::delete('/{financialYear}', [\App\Http\Controllers\FinancialYearController::class, 'destroy'])->name('destroy');
        Route::post('/{financialYear}/activate', [\App\Http\Controllers\FinancialYearController::class, 'activate'])->name('activate');
        Route::post('/{financialYear}/lock', [\App\Http\Controllers\FinancialYearController::class, 'lock'])->name('lock');
        Route::post('/{financialYear}/unlock', [\App\Http\Controllers\FinancialYearController::class, 'unlock'])->name('unlock');
        Route::get('/opening-balances/{financialYear}', \App\Livewire\Accounting\FinancialYears\OpeningBalanceForm::class)->name('opening-balances');
        Route::get('/close/{financialYear}', \App\Livewire\Accounting\FinancialYears\YearEndClosing::class)->name('close');
    });
});
