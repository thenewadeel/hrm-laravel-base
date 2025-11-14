<?php

use App\Services\AccountingReportService;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'debug'], function () {
    Route::get('/orgs', function () {
        $user = auth()->user();
        return [
            'user_id' => $user->id,
            'org_count' => $user->organizations()->count(),
            'orgs' => $user->organizations->pluck('id')
        ];
    });
    Route::get('api-config', function () {
        return response()->json([
            'app_url' => config('app.url'),
            'api_url' => config('app.api_url'),
            'env_api_url' => env('API_URL'),
            'full_api_endpoint' => config('app.api_url') . '/journal-entries',
            'is_local' => app()->isLocal(),
            'environment' => app()->environment(),
            'cors_config' => config('cors'),
            'timezone' => config('app.timezone')
        ]);
    });

    Route::get('journal-entries', function () {
        try {
            $entries = \App\Models\Accounting\JournalEntry::with('ledgerEntries.account')->get();
            return response()->json($entries);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    });

    Route::get('test-sequence', function () {
        try {
            $sequenceService = app(App\Services\SequenceService::class);

            // Test 1: Check current value
            $current = $sequenceService->peek('journal_entry_ref');
            echo "Current value: " . $current . "<br>";

            // Test 2: Generate a new value
            // $ref1 = $sequenceService->generate('journal_entry_ref');
            // echo "Generated 1: " . $ref1 . "<br>";

            // Test 3: Reserve a value
            $ref2 = $sequenceService->reserve('journal_entry_ref');
            echo "Reserved: " . json_encode($ref2) . "<br>";

            // Test 4: Check value after generation
            $currentAfter = $sequenceService->peek('journal_entry_ref');
            echo "Value after generation: " . $currentAfter . "<br>";
        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    });

    Route::get('reports/all', function (AccountingReportService $service) {
        $trialBalance = $service->generateTrialBalance();
        $balanceSheet = $service->generateBalanceSheet(now());
        $incomeStatement = $service->generateIncomeStatement(now()->startOfYear(), now());

        return response()->json([
            'trial_balance' => $trialBalance,
            'balance_sheet' => $balanceSheet,
            'income_statement' => $incomeStatement,
        ]);
    });
});
