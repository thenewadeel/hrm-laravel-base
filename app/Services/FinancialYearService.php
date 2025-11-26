<?php

namespace App\Services;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\ClosingEntry;
use App\Models\Accounting\FinancialYear;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\LedgerEntry;
use App\Models\Accounting\OpeningBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialYearService
{
    public function createFinancialYear(array $data): FinancialYear
    {
        return DB::transaction(function () use ($data) {
            $financialYear = FinancialYear::create([
                'organization_id' => $data['organization_id'],
                'name' => $data['name'],
                'code' => $data['code'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);

            Log::info('Financial year created', [
                'financial_year_id' => $financialYear->id,
                'organization_id' => $data['organization_id'],
                'user_id' => Auth::id(),
            ]);

            return $financialYear;
        });
    }

    public function activateFinancialYear(FinancialYear $financialYear): FinancialYear
    {
        return DB::transaction(function () use ($financialYear) {
            // Deactivate other active financial years for the organization
            FinancialYear::where('organization_id', $financialYear->organization_id)
                ->where('status', 'active')
                ->update(['status' => 'draft']);

            $financialYear->update(['status' => 'active']);

            Log::info('Financial year activated', [
                'financial_year_id' => $financialYear->id,
                'organization_id' => $financialYear->organization_id,
                'user_id' => Auth::id(),
            ]);

            return $financialYear;
        });
    }

    public function setOpeningBalances(FinancialYear $financialYear, array $balances): array
    {
        return DB::transaction(function () use ($financialYear, $balances) {
            $results = [];

            foreach ($balances as $balanceData) {
                $openingBalance = OpeningBalance::updateOrCreate(
                    [
                        'financial_year_id' => $financialYear->id,
                        'chart_of_account_id' => $balanceData['chart_of_account_id'],
                    ],
                    [
                        'organization_id' => $financialYear->organization_id,
                        'debit_amount' => $balanceData['debit_amount'] ?? 0,
                        'credit_amount' => $balanceData['credit_amount'] ?? 0,
                        'description' => $balanceData['description'] ?? null,
                        'created_by' => Auth::id(),
                    ]
                );

                $results[] = $openingBalance;
            }

            Log::info('Opening balances set', [
                'financial_year_id' => $financialYear->id,
                'count' => count($results),
                'user_id' => Auth::id(),
            ]);

            return $results;
        });
    }

    public function closeFinancialYear(FinancialYear $financialYear): array
    {
        if (! $financialYear->canBeClosed()) {
            throw new \InvalidArgumentException('Financial year cannot be closed');
        }

        return DB::transaction(function () use ($financialYear) {
            $closingEntries = [];
            $organizationId = $financialYear->organization_id;

            // Get revenue and expense accounts
            $revenueAccounts = ChartOfAccount::where('organization_id', $organizationId)
                ->revenues()
                ->get();

            $expenseAccounts = ChartOfAccount::where('organization_id', $organizationId)
                ->expenses()
                ->get();

            // Calculate total revenue and expenses
            $totalRevenue = $this->calculateAccountBalance($revenueAccounts, $financialYear, 'revenue');
            $totalExpenses = $this->calculateAccountBalance($expenseAccounts, $financialYear, 'expense');
            $netIncome = $totalRevenue - $totalExpenses;

            // Close revenue accounts
            if ($totalRevenue > 0) {
                $revenueClosingEntry = $this->createRevenueClosingEntry($financialYear, $revenueAccounts, $totalRevenue);
                $closingEntries[] = $revenueClosingEntry;
            }

            // Close expense accounts
            if ($totalExpenses > 0) {
                $expenseClosingEntry = $this->createExpenseClosingEntry($financialYear, $expenseAccounts, $totalExpenses);
                $closingEntries[] = $expenseClosingEntry;
            }

            // Transfer net income to retained earnings
            if ($netIncome != 0) {
                $profitTransferEntry = $this->createProfitTransferEntry($financialYear, $netIncome);
                $closingEntries[] = $profitTransferEntry;
            }

            // Mark financial year as closed
            $financialYear->close();

            Log::info('Financial year closed', [
                'financial_year_id' => $financialYear->id,
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
                'net_income' => $netIncome,
                'closing_entries_count' => count($closingEntries),
                'user_id' => Auth::id(),
            ]);

            return [
                'financial_year' => $financialYear,
                'closing_entries' => $closingEntries,
                'summary' => [
                    'total_revenue' => $totalRevenue,
                    'total_expenses' => $totalExpenses,
                    'net_income' => $netIncome,
                ],
            ];
        });
    }

    public function carryForwardBalances(FinancialYear $fromYear, FinancialYear $toYear): array
    {
        return DB::transaction(function () use ($fromYear, $toYear) {
            $carriedForward = [];

            // Get balance sheet accounts (assets, liabilities, equity)
            $balanceSheetAccounts = ChartOfAccount::where('organization_id', $fromYear->organization_id)
                ->whereIn('type', ['asset', 'liability', 'equity'])
                ->get();

            foreach ($balanceSheetAccounts as $account) {
                $balance = $this->getAccountClosingBalance($account, $fromYear);

                if ($balance != 0) {
                    $openingBalance = OpeningBalance::updateOrCreate(
                        [
                            'financial_year_id' => $toYear->id,
                            'chart_of_account_id' => $account->id,
                        ],
                        [
                            'organization_id' => $toYear->organization_id,
                            'debit_amount' => $balance > 0 && in_array($account->type, ['asset', 'expense']) ? abs($balance) : 0,
                            'credit_amount' => $balance > 0 && in_array($account->type, ['liability', 'equity', 'revenue']) ? abs($balance) : 0,
                            'description' => "Carried forward from {$fromYear->name}",
                            'created_by' => Auth::id(),
                        ]
                    );

                    $carriedForward[] = $openingBalance;
                }
            }

            Log::info('Balances carried forward', [
                'from_year_id' => $fromYear->id,
                'to_year_id' => $toYear->id,
                'accounts_count' => count($carriedForward),
                'user_id' => Auth::id(),
            ]);

            return $carriedForward;
        });
    }

    private function calculateAccountBalance($accounts, FinancialYear $financialYear, string $type): float
    {
        $total = 0;

        foreach ($accounts as $account) {
            $balance = $this->getAccountClosingBalance($account, $financialYear);
            $total += abs($balance);
        }

        return $total;
    }

    private function getAccountClosingBalance(ChartOfAccount $account, FinancialYear $financialYear): float
    {
        $debits = LedgerEntry::where('chart_of_account_id', $account->id)
            ->where('financial_year_id', $financialYear->id)
            ->where('type', 'debit')
            ->sum('amount');

        $credits = LedgerEntry::where('chart_of_account_id', $account->id)
            ->where('financial_year_id', $financialYear->id)
            ->where('type', 'credit')
            ->sum('amount');

        // Add opening balance
        $openingBalance = OpeningBalance::where('financial_year_id', $financialYear->id)
            ->where('chart_of_account_id', $account->id)
            ->first();

        if ($openingBalance) {
            $debits += $openingBalance->debit_amount;
            $credits += $openingBalance->credit_amount;
        }

        return in_array($account->type, ['asset', 'expense'])
            ? $debits - $credits
            : $credits - $debits;
    }

    private function createRevenueClosingEntry(FinancialYear $financialYear, $revenueAccounts, float $totalRevenue): ClosingEntry
    {
        $journalEntry = JournalEntry::create([
            'organization_id' => $financialYear->organization_id,
            'reference_number' => 'CLOSE-REV-'.$financialYear->code,
            'entry_date' => $financialYear->end_date,
            'description' => "Closing revenue accounts for {$financialYear->name}",
            'voucher_type' => 'CLOSING',
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => Auth::id(),
        ]);

        // Create ledger entries for closing revenue accounts
        foreach ($revenueAccounts as $account) {
            $balance = $this->getAccountClosingBalance($account, $financialYear);

            if ($balance > 0) {
                LedgerEntry::create([
                    'organization_id' => $financialYear->organization_id,
                    'financial_year_id' => $financialYear->id,
                    'entry_date' => $financialYear->end_date,
                    'chart_of_account_id' => $account->id,
                    'type' => 'debit', // Debit revenue accounts to close them
                    'amount' => $balance,
                    'description' => 'Closing revenue account',
                    'transactionable_type' => JournalEntry::class,
                    'transactionable_id' => $journalEntry->id,
                ]);
            }
        }

        // Credit the income summary account (or retained earnings)
        $retainedEarningsAccount = ChartOfAccount::where('organization_id', $financialYear->organization_id)
            ->where('type', 'equity')
            ->where('name', 'like', '%Retained Earnings%')
            ->first();

        if (! $retainedEarningsAccount) {
            throw new \Exception('Retained Earnings account not found');
        }

        LedgerEntry::create([
            'organization_id' => $financialYear->organization_id,
            'financial_year_id' => $financialYear->id,
            'entry_date' => $financialYear->end_date,
            'chart_of_account_id' => $retainedEarningsAccount->id,
            'type' => 'credit',
            'amount' => $totalRevenue,
            'description' => "Revenue closure for {$financialYear->name}",
            'transactionable_type' => JournalEntry::class,
            'transactionable_id' => $journalEntry->id,
        ]);

        return ClosingEntry::create([
            'organization_id' => $financialYear->organization_id,
            'financial_year_id' => $financialYear->id,
            'journal_entry_id' => $journalEntry->id,
            'type' => 'revenue_closure',
            'amount' => $totalRevenue,
            'description' => "Revenue closure for {$financialYear->name}",
            'created_by' => Auth::id(),
        ]);
    }

    private function createExpenseClosingEntry(FinancialYear $financialYear, $expenseAccounts, float $totalExpenses): ClosingEntry
    {
        $journalEntry = JournalEntry::create([
            'organization_id' => $financialYear->organization_id,
            'reference_number' => 'CLOSE-EXP-'.$financialYear->code,
            'entry_date' => $financialYear->end_date,
            'description' => "Closing expense accounts for {$financialYear->name}",
            'voucher_type' => 'CLOSING',
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => Auth::id(),
        ]);

        // Credit expense accounts to close them
        foreach ($expenseAccounts as $account) {
            $balance = $this->getAccountClosingBalance($account, $financialYear);

            if ($balance > 0) {
                LedgerEntry::create([
                    'organization_id' => $financialYear->organization_id,
                    'financial_year_id' => $financialYear->id,
                    'entry_date' => $financialYear->end_date,
                    'chart_of_account_id' => $account->id,
                    'type' => 'credit', // Credit expense accounts to close them
                    'amount' => $balance,
                    'description' => 'Closing expense account',
                    'transactionable_type' => JournalEntry::class,
                    'transactionable_id' => $journalEntry->id,
                ]);
            }
        }

        // Debit the income summary account (or retained earnings)
        $retainedEarningsAccount = ChartOfAccount::where('organization_id', $financialYear->organization_id)
            ->where('type', 'equity')
            ->where('name', 'like', '%Retained Earnings%')
            ->first();

        if (! $retainedEarningsAccount) {
            throw new \Exception('Retained Earnings account not found');
        }

        LedgerEntry::create([
            'organization_id' => $financialYear->organization_id,
            'financial_year_id' => $financialYear->id,
            'entry_date' => $financialYear->end_date,
            'chart_of_account_id' => $retainedEarningsAccount->id,
            'type' => 'debit',
            'amount' => $totalExpenses,
            'description' => "Expense closure for {$financialYear->name}",
            'transactionable_type' => JournalEntry::class,
            'transactionable_id' => $journalEntry->id,
        ]);

        return ClosingEntry::create([
            'organization_id' => $financialYear->organization_id,
            'financial_year_id' => $financialYear->id,
            'journal_entry_id' => $journalEntry->id,
            'type' => 'expense_closure',
            'amount' => $totalExpenses,
            'description' => "Expense closure for {$financialYear->name}",
            'created_by' => Auth::id(),
        ]);
    }

    private function createProfitTransferEntry(FinancialYear $financialYear, float $netIncome): ClosingEntry
    {
        if ($netIncome == 0) {
            return null;
        }

        $journalEntry = JournalEntry::create([
            'organization_id' => $financialYear->organization_id,
            'reference_number' => 'CLOSE-PROFIT-'.$financialYear->code,
            'entry_date' => $financialYear->end_date,
            'description' => "Net income transfer for {$financialYear->name}",
            'voucher_type' => 'CLOSING',
            'status' => 'posted',
            'posted_at' => now(),
            'created_by' => Auth::id(),
        ]);

        $retainedEarningsAccount = ChartOfAccount::where('organization_id', $financialYear->organization_id)
            ->where('type', 'equity')
            ->where('name', 'like', '%Retained Earnings%')
            ->first();

        if (! $retainedEarningsAccount) {
            throw new \Exception('Retained Earnings account not found');
        }

        // Create the appropriate entry based on profit or loss
        if ($netIncome > 0) {
            // Profit: Credit retained earnings
            LedgerEntry::create([
                'organization_id' => $financialYear->organization_id,
                'financial_year_id' => $financialYear->id,
                'entry_date' => $financialYear->end_date,
                'chart_of_account_id' => $retainedEarningsAccount->id,
                'type' => 'credit',
                'amount' => $netIncome,
                'description' => "Net income transfer for {$financialYear->name}",
                'transactionable_type' => JournalEntry::class,
                'transactionable_id' => $journalEntry->id,
            ]);
        } else {
            // Loss: Debit retained earnings
            LedgerEntry::create([
                'organization_id' => $financialYear->organization_id,
                'financial_year_id' => $financialYear->id,
                'entry_date' => $financialYear->end_date,
                'chart_of_account_id' => $retainedEarningsAccount->id,
                'type' => 'debit',
                'amount' => abs($netIncome),
                'description' => "Net loss transfer for {$financialYear->name}",
                'transactionable_type' => JournalEntry::class,
                'transactionable_id' => $journalEntry->id,
            ]);
        }

        return ClosingEntry::create([
            'organization_id' => $financialYear->organization_id,
            'financial_year_id' => $financialYear->id,
            'journal_entry_id' => $journalEntry->id,
            'type' => 'profit_transfer',
            'amount' => abs($netIncome),
            'description' => 'Net '.($netIncome > 0 ? 'income' : 'loss')." transfer for {$financialYear->name}",
            'created_by' => Auth::id(),
        ]);
    }

    public function getFinancialYearTrialBalance(FinancialYear $financialYear): array
    {
        $accounts = ChartOfAccount::where('organization_id', $financialYear->organization_id)
            ->with(['openingBalances' => function ($query) use ($financialYear) {
                $query->where('financial_year_id', $financialYear->id);
            }])
            ->get();

        $trialBalance = [];

        foreach ($accounts as $account) {
            $openingBalance = $account->openingBalances->first();
            $openingDebit = $openingBalance ? $openingBalance->debit_amount : 0;
            $openingCredit = $openingBalance ? $openingBalance->credit_amount : 0;

            $currentDebits = LedgerEntry::where('chart_of_account_id', $account->id)
                ->where('financial_year_id', $financialYear->id)
                ->where('type', 'debit')
                ->sum('amount');

            $currentCredits = LedgerEntry::where('chart_of_account_id', $account->id)
                ->where('financial_year_id', $financialYear->id)
                ->where('type', 'credit')
                ->sum('amount');

            $totalDebits = $openingDebit + $currentDebits;
            $totalCredits = $openingCredit + $currentCredits;

            $closingBalance = in_array($account->type, ['asset', 'expense'])
                ? $totalDebits - $totalCredits
                : $totalCredits - $totalDebits;

            if ($totalDebits > 0 || $totalCredits > 0) {
                $trialBalance[] = [
                    'account' => $account,
                    'opening_debit' => $openingDebit,
                    'opening_credit' => $openingCredit,
                    'current_debit' => $currentDebits,
                    'current_credit' => $currentCredits,
                    'total_debit' => $totalDebits,
                    'total_credit' => $totalCredits,
                    'closing_balance' => $closingBalance,
                    'balance_type' => $closingBalance >= 0
                        ? (in_array($account->type, ['asset', 'expense']) ? 'debit' : 'credit')
                        : (in_array($account->type, ['asset', 'expense']) ? 'credit' : 'debit'),
                ];
            }
        }

        return $trialBalance;
    }
}
