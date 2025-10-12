<?php
// app/Services/AccountingReportService.php

namespace App\Services;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\LedgerEntry;
use Illuminate\Support\Collection;

class AccountingReportService
{
    public function generateTrialBalance(\DateTimeInterface $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now(); // Default to 'now' if no date is provided

        $accounts = ChartOfAccount::select(['id', 'code', 'name', 'type'])
            ->get();

        $accountBalances = $accounts->map(function (ChartOfAccount $account) use ($asOfDate) {
            // Calculate total debits up to the specified date
            $debits = LedgerEntry::where('chart_of_account_id', $account->id)
                ->where('type', 'debit')
                ->where('entry_date', '<=', $asOfDate)
                ->sum('amount');

            // Calculate total credits up to the specified date
            $credits = LedgerEntry::where('chart_of_account_id', $account->id)
                ->where('type', 'credit')
                ->where('entry_date', '<=', $asOfDate)
                ->sum('amount');

            // Calculate balance based on account type normal balance
            $balance = in_array($account->type, ['asset', 'expense'])
                ? $debits - $credits
                : $credits - $debits;

            return [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'debits' => $debits, // These now represent the total historical debits
                'credits' => $credits, // These now represent the total historical credits
                'balance' => $balance
            ];
        });

        $totalDebits = $accountBalances->sum('debits');
        $totalCredits = $accountBalances->sum('credits');

        return [
            'accounts' => $accountBalances,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.001,
            'generated_at' => now()
        ];
    }

    // In app/Services/AccountingReportService.php

    // In app/Services/AccountingReportService.php

    // In app/Services/AccountingReportService.php - generateBalanceSheet method

    public function generateBalanceSheet(\DateTimeInterface $asOfDate): array
    {
        $trialBalance = $this->generateTrialBalance();

        $assets = collect($trialBalance['accounts'])
            ->filter(fn($acc) => $acc['type'] === 'asset')
            ->values();

        $liabilities = collect($trialBalance['accounts'])
            ->filter(fn($acc) => $acc['type'] === 'liability')
            ->values();

        $equity = collect($trialBalance['accounts'])
            ->filter(fn($acc) => $acc['type'] === 'equity')
            ->values();

        // Calculate net income for the period (from beginning of year to asOfDate)
        $yearStart = now()->startOfYear();
        $netIncome = $this->calculateNetIncome($yearStart, $asOfDate);

        // Add net income to retained earnings (or create retained earnings if it doesn't exist)
        $totalEquity = $equity->sum('balance') + $netIncome;

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'retained_earnings' => $netIncome,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.001,
            'as_of_date' => $asOfDate->format('Y-m-d'),
            'generated_at' => now()
        ];
    }

    // Helper method to calculate net income
    private function calculateNetIncome(\DateTimeInterface $startDate, \DateTimeInterface $endDate): float
    {
        $incomeStatement = $this->generateIncomeStatement($startDate, $endDate);
        return $incomeStatement['net_income'];
    }

    // In app/Services/AccountingReportService.php

    // In app/Services/AccountingReportService.php - generateIncomeStatement method

    public function generateIncomeStatement(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {


        \Log::debug('Income statement date range:', [
            'start' => $startDate->format('Y-m-d H:i:s'),
            'end' => $endDate->format('Y-m-d H:i:s')
        ]);

        // Get all revenue and expense accounts with their ledger entries
        $accounts = ChartOfAccount::whereIn('type', ['revenue', 'expense'])
            ->with(['ledgerEntries' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('entry_date', [$startDate, $endDate]);
            }])
            ->get();

        \Log::debug('Accounts with ledger entries:', $accounts->toArray());



        $revenueAccounts = ChartOfAccount::where('type', 'revenue')
            ->withSum([
                'ledgerEntries as period_credits' => function ($query) use ($startDate, $endDate) {
                    $query->where('type', 'credit')
                        ->whereBetween('entry_date', [$startDate, $endDate]);
                }
            ], 'amount')
            ->withSum([
                'ledgerEntries as period_debits' => function ($query) use ($startDate, $endDate) {
                    $query->where('type', 'debit')
                        ->whereBetween('entry_date', [$startDate, $endDate]);
                }
            ], 'amount')
            ->get();

        $expenseAccounts = ChartOfAccount::where('type', 'expense')
            ->withSum([
                'ledgerEntries as period_debits' => function ($query) use ($startDate, $endDate) {
                    $query->where('type', 'debit')
                        ->whereBetween('entry_date', [$startDate, $endDate]);
                }
            ], 'amount')
            ->withSum([
                'ledgerEntries as period_credits' => function ($query) use ($startDate, $endDate) {
                    $query->where('type', 'credit')
                        ->whereBetween('entry_date', [$startDate, $endDate]);
                }
            ], 'amount')
            ->get();

        $totalRevenue = $revenueAccounts->sum(function ($account) {
            return ($account->period_credits ?? 0) - ($account->period_debits ?? 0);
        });

        $totalExpenses = $expenseAccounts->sum(function ($account) {
            return ($account->period_debits ?? 0) - ($account->period_credits ?? 0);
        });

        $netIncome = $totalRevenue - $totalExpenses;

        return [
            'revenue' => $revenueAccounts->map(function ($account) {
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => ($account->period_credits ?? 0) - ($account->period_debits ?? 0)
                ];
            }),
            'expenses' => $expenseAccounts->map(function ($account) {
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'amount' => ($account->period_debits ?? 0) - ($account->period_credits ?? 0)
                ];
            }),
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ],
            'generated_at' => now()
        ];
    }

    /**
     * Generates a summary of key financial metrics for the dashboard.
     *
     * @return array
     */
    public function getDashboardSummary(): array
    {
        // Define the period for income statement metrics (e.g., current month)
        $endDate = now();
        $startDate = (clone $endDate)->startOfMonth();

        // 1. Get Income Statement figures for the period (e.g., current month)
        $incomeStatement = $this->generateIncomeStatement($startDate, $endDate);

        $totalRevenue = $incomeStatement['total_revenue'];
        $totalExpenses = $incomeStatement['total_expenses'];
        $netIncome = $incomeStatement['net_income'];

        // 2. Get current account balances from the Trial Balance up to today
        // Note: If you implement the change in generateTrialBalance, this will be more efficient.
        $trialBalance = $this->generateTrialBalance($endDate);
        $accountBalances = collect($trialBalance['accounts']);

        // Directly calculate balances by filtering the collection
        $cashOnHand = $accountBalances->where('code', '1010')->sum('balance');
        $accountsReceivable = $accountBalances->where('code', '1100')->sum('balance');
        $accountsPayable = $accountBalances->where('code', '2010')->sum('balance');

        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'cash_on_hand' => $cashOnHand,
            'accounts_receivable' => $accountsReceivable,
            'accounts_payable' => $accountsPayable,
            // 'period_start' => $startDate->format('Y-m-d'),
            // 'period_end' => $endDate->format('Y-m-d'),
            // 'generated_at' => now(),
        ];
    }
}
