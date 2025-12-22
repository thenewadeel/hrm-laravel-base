<?php

namespace App\Services\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\LedgerEntry;
use Carbon\Carbon;

class AccountingReportService
{
    /**
     * Generate trial balance report
     */
    public function generateTrialBalance(?int $organizationId = null, ?Carbon $asOfDate = null): array
    {
        $query = LedgerEntry::query();

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        if ($asOfDate) {
            $query->where('entry_date', '<=', $asOfDate);
        }

        // Group by account and calculate balances
        $accountBalances = $query->with('account')
            ->get()
            ->groupBy('chart_of_account_id')
            ->map(function ($entries) {
                $debits = $entries->where('type', 'debit')->sum('amount');
                $credits = $entries->where('type', 'credit')->sum('amount');
                $account = $entries->first()->account;
                
                // Skip if account is not found
                if (!$account) {
                    return null;
                }

                $balance = $debits - $credits;

                // Determine normal balance based on account type
                $isDebitNormal = in_array($account->type, ['asset', 'expense']);
                $normalBalance = $isDebitNormal ? $balance : -$balance;

                return [
                    'account' => $account,
                    'debits' => $debits,
                    'credits' => $credits,
                    'balance' => $balance,
                    'normal_balance' => $normalBalance,
                    'debit_balance' => $isDebitNormal && $normalBalance > 0 ? $normalBalance : 0,
                    'credit_balance' => ! $isDebitNormal && $normalBalance > 0 ? $normalBalance : 0,
                ];
            })->filter(); // Remove null entries

        $totalDebits = $accountBalances->sum('debit_balance');
        $totalCredits = $accountBalances->sum('credit_balance');

        return [
            'accounts' => $accountBalances->sortBy('account.code')->values(),
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
            'as_of_date' => $asOfDate ?? now(),
        ];
    }

    /**
     * Generate balance sheet report
     */
    public function generateBalanceSheet(?int $organizationId = null, ?Carbon $asOfDate = null): array
    {
        $trialBalance = $this->generateTrialBalance($organizationId, $asOfDate);

        $accounts = $trialBalance['accounts'];

        // Categorize accounts
        $assets = $accounts->filter(fn ($account) => $account['account']->type === 'asset');
        $liabilities = $accounts->filter(fn ($account) => $account['account']->type === 'liability');
        $equity = $accounts->filter(fn ($account) => $account['account']->type === 'equity');
        $revenue = $accounts->filter(fn ($account) => $account['account']->type === 'revenue');
        $expenses = $accounts->filter(fn ($account) => $account['account']->type === 'expense');

        // Calculate totals
        $totalAssets = $assets->sum('normal_balance');
        $totalLiabilities = $liabilities->sum('normal_balance');
        $totalEquity = $equity->sum('normal_balance');
        $totalRevenue = $revenue->sum('normal_balance');
        $totalExpenses = $expenses->sum('normal_balance');

        // Calculate retained earnings (Revenue - Expenses)
        $retainedEarnings = $totalRevenue - $totalExpenses;

        // Total equity includes retained earnings
        $totalEquityWithRetained = $totalEquity + $retainedEarnings;

        return [
            'assets' => [
                'accounts' => $assets,
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'accounts' => $liabilities,
                'total' => $totalLiabilities,
            ],
            'equity' => [
                'accounts' => $equity,
                'total' => $totalEquity,
                'retained_earnings' => $retainedEarnings,
                'total_with_retained' => $totalEquityWithRetained,
            ],
            'revenue' => [
                'accounts' => $revenue,
                'total' => $totalRevenue,
            ],
            'expenses' => [
                'accounts' => $expenses,
                'total' => $totalExpenses,
            ],
            'is_balanced' => abs($totalAssets - ($totalLiabilities + $totalEquityWithRetained)) < 0.01,
            'as_of_date' => $asOfDate ?? now(),
        ];
    }

    /**
     * Generate income statement report
     */
    public function generateIncomeStatement(?int $organizationId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = LedgerEntry::with('account');

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        if ($startDate) {
            $query->where('entry_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('entry_date', '<=', $endDate);
        }

        $entries = $query->get();

        // Group by account
        $accountBalances = $entries->groupBy('chart_of_account_id')
            ->map(function ($entries) {
                $debits = $entries->where('type', 'debit')->sum('amount');
                $credits = $entries->where('type', 'credit')->sum('amount');
                $account = $entries->first()->account;
                
                // Skip if account is not found
                if (!$account) {
                    return null;
                }

                $balance = $credits - $debits; // Revenue accounts have credit balance, expenses have debit balance

                return [
                    'account' => $account,
                    'balance' => $balance,
                ];
            })->filter(); // Remove null entries

        // Categorize accounts
        $revenue = $accountBalances->filter(fn ($account) => $account['account']->type === 'revenue');
        $expenses = $accountBalances->filter(fn ($account) => $account['account']->type === 'expense');

        $totalRevenue = $revenue->sum('balance');
        $totalExpenses = abs($expenses->sum('balance')); // Take absolute value for expenses
        $netIncome = $totalRevenue - $totalExpenses;

        return [
            'revenue' => [
                'accounts' => $revenue,
                'total' => $totalRevenue,
            ],
            'expenses' => [
                'accounts' => $expenses,
                'total' => $totalExpenses,
            ],
            'net_income' => $netIncome,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }

    /**
     * Generate cash flow statement
     */
    public function generateCashFlowStatement(?int $organizationId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        // This is a simplified cash flow statement
        // In a real implementation, you'd need to categorize cash flows more carefully
        $incomeStatement = $this->generateIncomeStatement($organizationId, $startDate, $endDate);

        // Get cash account changes
        $cashAccounts = ChartOfAccount::where('type', 'asset')
            ->where(function ($query) {
                $query->where('category', 'cash_and_bank')
                    ->orWhere('code', 'like', '100%')
                    ->orWhere('name', 'like', '%cash%')
                    ->orWhere('name', 'like', '%bank%');
            });

        if ($organizationId) {
            $cashAccounts->where('organization_id', $organizationId);
        }

        $cashAccountIds = $cashAccounts->pluck('id');

        $query = LedgerEntry::whereIn('chart_of_account_id', $cashAccountIds);

        if ($startDate) {
            $query->where('entry_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('entry_date', '<=', $endDate);
        }

        $cashEntries = $query->get();

        $cashInflows = $cashEntries->where('type', 'debit')->sum('amount');
        $cashOutflows = $cashEntries->where('type', 'credit')->sum('amount');
        $netCashFlow = $cashInflows - $cashOutflows;

        return [
            'operating_activities' => [
                'net_income' => $incomeStatement['net_income'],
                'adjustments' => 0, // Simplified - would need depreciation, changes in working capital, etc.
                'net_cash_from_operations' => $incomeStatement['net_income'],
                'net' => $incomeStatement['net_income'],
            ],
            'investing_activities' => [
                'net_cash_from_investing' => 0, // Simplified
                'net' => 0,
            ],
            'financing_activities' => [
                'net_cash_from_financing' => 0, // Simplified
                'net' => 0,
            ],
            'net_change_in_cash' => $netCashFlow,
            'net_cash_flow' => $netCashFlow,
            'beginning_cash' => 0, // Simplified - would need opening balance
            'ending_cash' => $netCashFlow, // Simplified - ending cash = beginning + net change
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }



    /**
     * Helper method to process aging for specific accounts
     */
    private function processAgingForAccounts(\Illuminate\Support\Collection $accountIds, string $entryType, Carbon $asOfDate, ?int $organizationId = null): \Illuminate\Support\Collection
    {
        $query = LedgerEntry::whereIn('chart_of_account_id', $accountIds)
            ->where('type', $entryType);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        return $query->get()->map(function ($entry) use ($asOfDate) {
            $daysOverdue = $asOfDate->diffInDays($entry->entry_date);
            
            $agingBucket = match(true) {
                $daysOverdue <= 0 => 'current',
                $daysOverdue <= 30 => '1_30_days',
                $daysOverdue <= 60 => '31_60_days',
                $daysOverdue <= 90 => '61_90_days',
                default => 'over_90_days',
            };

            return [
                'entry' => $entry,
                'amount' => $entry->amount,
                'days_overdue' => $daysOverdue,
                'aging_bucket' => $agingBucket,
            ];
        });
    }

    /**
     * Generate profit and loss statement (alias for income statement)
     */
    public function generateProfitLoss(?int $organizationId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        return $this->generateIncomeStatement($organizationId, $startDate, $endDate);
    }

    /**
     * Generate aging analysis with configurable periods
     */
    public function generateAgingAnalysis(?int $organizationId = null, ?Carbon $asOfDate = null, array $periods = null): array
    {
        $asOfDate = $asOfDate ?? now();
        $periods = $periods ?? [30, 60, 90, 120];
        
        // Get receivable accounts (typically asset accounts with receivable nature)
        $receivableAccounts = ChartOfAccount::where('type', 'asset')
            ->where(function ($query) {
                $query->where('name', 'like', '%receivable%')
                    ->orWhere('name', 'like', '%debtors%')
                    ->orWhere('category', 'accounts_receivable');
            });

        if ($organizationId) {
            $receivableAccounts->where('organization_id', $organizationId);
        }

        $receivableAccountIds = $receivableAccounts->pluck('id');

        // Get payable accounts (typically liability accounts with payable nature)
        $payableAccounts = ChartOfAccount::where('type', 'liability')
            ->where(function ($query) {
                $query->where('name', 'like', '%payable%')
                    ->orWhere('name', 'like', '%creditors%')
                    ->orWhere('category', 'accounts_payable');
            });

        if ($organizationId) {
            $payableAccounts->where('organization_id', $organizationId);
        }

        $payableAccountIds = $payableAccounts->pluck('id');

        // Initialize aging buckets
        $agingBuckets = [];
        $previousDays = 0;
        foreach ($periods as $days) {
            $key = $previousDays . '_' . $days . '_days';
            $agingBuckets[$key] = 0;
            $previousDays = $days;
        }
        $agingBuckets['over_' . end($periods) . '_days'] = 0;

        // Process receivables
        $receivables = $this->processAgingForAccountsWithPeriods($receivableAccountIds, 'debit', $asOfDate, $organizationId, $periods);
        
        // Process payables
        $payables = $this->processAgingForAccountsWithPeriods($payableAccountIds, 'credit', $asOfDate, $organizationId, $periods);

        return [
            'aging_buckets' => $agingBuckets,
            'receivables' => [
                'total' => $receivables->sum('amount'),
                'aging' => $receivables->groupBy('aging_bucket')->map->sum('amount'),
                'details' => $receivables,
            ],
            'payables' => [
                'total' => $payables->sum('amount'),
                'aging' => $payables->groupBy('aging_bucket')->map->sum('amount'),
                'details' => $payables,
            ],
            'total_outstanding' => $receivables->sum('amount') + $payables->sum('amount'),
            'as_of_date' => $asOfDate,
        ];
    }

    /**
     * Helper method to process aging for specific accounts with custom periods
     */
    private function processAgingForAccountsWithPeriods(\Illuminate\Support\Collection $accountIds, string $entryType, Carbon $asOfDate, ?int $organizationId = null, array $periods): \Illuminate\Support\Collection
    {
        $query = LedgerEntry::whereIn('chart_of_account_id', $accountIds)
            ->where('type', $entryType);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        return $query->get()->map(function ($entry) use ($asOfDate, $periods) {
            $daysOverdue = $asOfDate->diffInDays($entry->entry_date);
            
            $agingBucket = 'over_' . end($periods) . '_days';
            $previousDays = 0;
            foreach ($periods as $days) {
                if ($daysOverdue <= $days) {
                    $agingBucket = $previousDays . '_' . $days . '_days';
                    break;
                }
                $previousDays = $days;
            }

            return [
                'entry' => $entry,
                'amount' => $entry->amount,
                'days_overdue' => $daysOverdue,
                'aging_bucket' => $agingBucket,
            ];
        });
    }

    /**
     * Generate department-wise performance reports
     */
    public function generateDepartmentReport(?int $organizationId = null): array
    {
        if (!$organizationId) {
            return [];
        }

        // Get employees grouped by organization unit (department)
        $employees = \App\Models\Employee::where('organization_id', $organizationId)
            ->with('organizationUnit')
            ->get()
            ->groupBy(function ($employee) {
                return $employee->organizationUnit?->name ?? 'Unassigned';
            });

        $departments = [];
        foreach ($employees as $departmentName => $departmentEmployees) {
            $departments[$departmentName] = [
                'employee_count' => $departmentEmployees->count(),
                'total_payroll' => $departmentEmployees->sum('basic_salary'),
                'average_salary' => $departmentEmployees->avg('basic_salary'),
                'employees' => $departmentEmployees,
            ];
        }

        return [
            'departments' => $departments,
            'total_employees' => $employees->flatten()->count(),
            'total_payroll' => $employees->flatten()->sum('basic_salary'),
        ];
    }

    /**
     * Generate comparative analysis between two periods
     */
    public function generateComparativeAnalysis(?int $organizationId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        // For simplicity, we'll compare income statements between two periods
        $midPoint = $startDate->copy()->addDays($startDate->diffInDays($endDate) / 2);
        
        $previousPeriod = $this->generateIncomeStatement($organizationId, $startDate, $midPoint);
        $currentPeriod = $this->generateIncomeStatement($organizationId, $midPoint->copy()->addDay(), $endDate);
        
        $previousTotal = $previousPeriod['net_income'];
        $currentTotal = $currentPeriod['net_income'];
        $variance = $currentTotal - $previousTotal;
        $variancePercentage = $previousTotal != 0 ? ($variance / $previousTotal) * 100 : 0;
        
        return [
            'previous_period' => [
                'start_date' => $startDate,
                'end_date' => $midPoint,
                'total' => abs($previousTotal),
                'net_income' => $previousTotal,
            ],
            'current_period' => [
                'start_date' => $midPoint->copy()->addDay(),
                'end_date' => $endDate,
                'total' => abs($currentTotal),
                'net_income' => $currentTotal,
            ],
            'variance' => $variance,
            'variance_percentage' => round($variancePercentage, 2),
        ];
    }

    /**
     * Generate general ledger report
     */
    public function generateGeneralLedger(?int $organizationId = null, ?int $chartOfAccountId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = LedgerEntry::with('chartOfAccount');

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        if ($chartOfAccountId) {
            $query->where('chart_of_account_id', $chartOfAccountId);
        }

        if ($startDate) {
            $query->where('entry_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('entry_date', '<=', $endDate);
        }

        $entries = $query->orderBy('entry_date')->orderBy('created_at')->get();

        // Group by account
        $ledgerByAccount = $entries->groupBy('chart_of_account_id')
            ->map(function ($entries) {
                $account = $entries->first()->chartOfAccount;

                $runningBalance = 0;
                $entriesWithBalance = $entries->map(function ($entry) use (&$runningBalance) {
                    if ($entry->type === 'debit') {
                        $runningBalance += $entry->amount;
                    } else {
                        $runningBalance -= $entry->amount;
                    }

                    return [
                        'entry' => $entry,
                        'running_balance' => $runningBalance,
                    ];
                });

                return [
                    'account' => $account,
                    'entries' => $entriesWithBalance,
                    'final_balance' => $runningBalance,
                ];
            });

        return [
            'accounts' => $ledgerByAccount->sortBy('account.code')->values(),
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }
}
