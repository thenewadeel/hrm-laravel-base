<?php

use App\Http\Controllers\Api\Accounting\ChartOfAccountsController;
use App\Http\Controllers\Api\Accounting\JournalEntriesController;
use App\Http\Controllers\Api\Accounting\FinancialReportsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {

    // Chart of Accounts routes
    Route::apiResource('accounts', ChartOfAccountsController::class);

    // Journal Entries routes
    Route::apiResource('journal-entries', JournalEntriesController::class);
    Route::put('journal-entries/{journal_entry}/post', [JournalEntriesController::class, 'post']);
    Route::put('journal-entries/{journal_entry}/void', [JournalEntriesController::class, 'void']);

    // Financial Reports routes
    Route::prefix('reports')->group(function () {
        Route::get('trial-balance', [FinancialReportsController::class, 'trialBalance']);
        Route::get('balance-sheet', [FinancialReportsController::class, 'balanceSheet']);
        Route::get('income-statement', [FinancialReportsController::class, 'incomeStatement']);
    });
});
