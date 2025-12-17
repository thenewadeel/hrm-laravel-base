<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\LedgerEntry;
use App\Models\Employee;
use App\Models\Inventory\Item;
use App\Models\Membership\Member;
use App\Models\Organization;
use App\Models\User;
use App\Services\Accounting\AccountingReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Advanced Reporting Tests
test('generates comprehensive financial reports with filters', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Create chart of accounts
    $revenueAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
        'name' => 'Sales Revenue',
        'code' => '4000',
    ]);
    
    $expenseAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
        'name' => 'Operating Expenses',
        'code' => '5000',
    ]);
    
    // Create journal entries with ledger entries for testing
    $revenueEntry = JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'entry_date' => now()->subDays(30),
        'description' => 'Test Revenue Entry',
    ]);
    
    LedgerEntry::factory()->create([
        'organization_id' => $organization->id,
        'chart_of_account_id' => $revenueAccount->id,
        'transactionable_type' => 'App\Models\Accounting\JournalEntry',
        'transactionable_id' => $revenueEntry->id,
        'type' => 'credit',
        'amount' => 10000,
        'entry_date' => now()->subDays(30),
    ]);
    
    $expenseEntry = JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'entry_date' => now()->subDays(30),
        'description' => 'Test Expense Entry',
    ]);
    
    LedgerEntry::factory()->create([
        'organization_id' => $organization->id,
        'chart_of_account_id' => $expenseAccount->id,
        'transactionable_type' => 'App\Models\Accounting\JournalEntry',
        'transactionable_id' => $expenseEntry->id,
        'type' => 'debit',
        'amount' => 8000,
        'entry_date' => now()->subDays(30),
    ]);
    
    $reportService = new AccountingReportService();
    
    // Test profit and loss report
    $profitLoss = $reportService->generateProfitLoss(
        $organization->id,
        now()->subMonths(6),
        now()
    );
    
    expect($profitLoss)->toHaveKey('revenue');
    expect($profitLoss)->toHaveKey('expenses');
    expect($profitLoss)->toHaveKey('net_income');
    expect($profitLoss['revenue'])->toBeGreaterThan(0);
    expect($profitLoss['net_income'])->toBe(2000); // 10000 - 8000
});

test('generates balance sheet with asset liability equity verification', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Create accounts for balance sheet
    $assetAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash',
        'code' => '1000',
    ]);
    
    $liabilityAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'liability',
        'name' => 'Accounts Payable',
        'code' => '2000',
    ]);
    
    $equityAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'equity',
        'name' => 'Owner\'s Equity',
        'code' => '3000',
    ]);
    
    // Create journal entries with ledger entries
    $journalEntry1 = JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'entry_date' => now(),
        'description' => 'Asset Increase',
    ]);
    
        LedgerEntry::factory()->create([
            'organization_id' => $organization->id,
            'chart_of_account_id' => $assetAccount->id,
            'transactionable_type' => 'App\Models\Accounting\JournalEntry',
            'transactionable_id' => $journalEntry1->id,
            'type' => 'debit',
            'amount' => 50000,
            'entry_date' => now(),
        ]);
    
    $journalEntry2 = JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'entry_date' => now(),
        'description' => 'Liability Increase',
    ]);
    
    LedgerEntry::factory()->create([
        'organization_id' => $organization->id,
        'chart_of_account_id' => $liabilityAccount->id,
        'transactionable_type' => 'App\Models\Accounting\JournalEntry',
        'transactionable_id' => $journalEntry2->id,
        'type' => 'credit',
        'amount' => 30000,
        'entry_date' => now(),
        ]);
    
    $reportService = new AccountingReportService();
    
    // Test balance sheet
    $balanceSheet = $reportService->generateBalanceSheet($organization->id, now());
    
    expect($balanceSheet)->toHaveKey('assets');
    expect($balanceSheet)->toHaveKey('liabilities');
    expect($balanceSheet)->toHaveKey('equity');
    
    // Verify accounting equation: Assets = Liabilities + Equity
    $totalAssets = $balanceSheet['assets']['total'];
    $totalLiabilities = $balanceSheet['liabilities']['total'];
    $totalEquity = $balanceSheet['equity']['total'];
    
    expect($totalAssets)->toBe($totalLiabilities + $totalEquity);
});

test('generates trial balance with debits credits verification', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Create multiple accounts and entries
    $accounts = ChartOfAccount::factory()->count(5)->create([
        'organization_id' => $organization->id,
    ]);
    
    // Create balanced journal entries with ledger entries
    foreach ($accounts as $index => $account) {
        $journalEntry = JournalEntry::factory()->create([
            'organization_id' => $organization->id,
            'entry_date' => now(),
            'description' => "Test Entry $index",
        ]);
        
        $type = ($index % 2 === 0) ? 'debit' : 'credit';
        LedgerEntry::factory()->create([
            'organization_id' => $organization->id,
            'chart_of_account_id' => $account->id,
            'transactionable_type' => 'App\Models\Accounting\JournalEntry',
            'transactionable_id' => $journalEntry->id,
            'type' => $type,
            'amount' => 1000,
            'entry_date' => now(),
        ]);
    }
    
    $reportService = new AccountingReportService();
    
    // Test trial balance
    $trialBalance = $reportService->generateTrialBalance($organization->id, now());
    
    expect($trialBalance)->toHaveKey('accounts');
    expect($trialBalance)->toHaveKey('total_debits');
    expect($trialBalance)->toHaveKey('total_credits');
    
    // Verify trial balance is balanced
    expect($trialBalance['total_debits'])->toBe($trialBalance['total_credits']);
    
    // Verify all accounts are included
    expect(count($trialBalance['accounts']))->toBe(5);
});

test('generates comparative period analysis reports', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Create data for two periods
    $currentPeriod = now()->subMonths(3);
    $previousPeriod = now()->subMonths(15);
    
    $accounts = ChartOfAccount::factory()->count(3)->create([
        'organization_id' => $organization->id,
    ]);
    
    // Create entries for current period
    foreach (range(1, 10) as $index) {
        $journalEntry = JournalEntry::factory()->create([
            'organization_id' => $organization->id,
            'entry_date' => $currentPeriod,
        ]);
        
        LedgerEntry::factory()->create([
            'organization_id' => $organization->id,
            'chart_of_account_id' => $accounts[$index % 3]->id,
            'transactionable_type' => 'App\Models\Accounting\JournalEntry',
            'transactionable_id' => $journalEntry->id,
            'type' => $index % 2 === 0 ? 'debit' : 'credit',
            'amount' => 1000,
            'entry_date' => $currentPeriod,
        ]);
    }
    
    // Create entries for previous period
    foreach (range(1, 8) as $index) {
        $journalEntry = JournalEntry::factory()->create([
            'organization_id' => $organization->id,
            'entry_date' => $previousPeriod,
        ]);
        
        LedgerEntry::factory()->create([
            'organization_id' => $organization->id,
            'chart_of_account_id' => $accounts[$index % 3]->id,
            'transactionable_type' => 'App\Models\Accounting\JournalEntry',
            'transactionable_id' => $journalEntry->id,
            'type' => $index % 2 === 0 ? 'debit' : 'credit',
            'amount' => 800,
            'entry_date' => $previousPeriod,
        ]);
    }
    
    $reportService = new AccountingReportService();
    
    // Test comparative analysis
    $comparativeReport = $reportService->generateComparativeAnalysis(
        $organization->id,
        $previousPeriod,
        $currentPeriod
    );
    
    expect($comparativeReport)->toHaveKey('current_period');
    expect($comparativeReport)->toHaveKey('previous_period');
    expect($comparativeReport)->toHaveKey('variance');
    expect($comparativeReport)->toHaveKey('variance_percentage');
    
    // Verify calculations
    $currentTotal = $comparativeReport['current_period']['total'];
    $previousTotal = $comparativeReport['previous_period']['total'];
    $expectedVariance = $currentTotal - $previousTotal;
    
    expect($comparativeReport['variance'])->toBe($expectedVariance);
    expect($comparativeReport['variance_percentage'])->toBe(
        round(($expectedVariance / $previousTotal) * 100, 2)
    );
});

test('generates department-wise performance reports', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Create employees with different salaries
    $employees = Employee::factory()->count(8)->create([
        'organization_id' => $organization->id,
        'basic_salary' => function () {
            return rand(40000, 70000);
        },
    ]);
    
    $reportService = new AccountingReportService();
    
    // Test department performance report
    $departmentReport = $reportService->generateDepartmentReport($organization->id);
    
    expect($departmentReport)->toHaveKey('departments');
    expect($departmentReport)->toHaveKey('total_payroll');
    expect($departmentReport)->toHaveKey('total_employees');
    
    // Check totals
    expect($departmentReport['total_employees'])->toBe(8);
    expect($departmentReport['total_payroll'])->toBeGreaterThan(0);
});

test('generates aging analysis with configurable periods', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    $reportService = new AccountingReportService();
    
    // Test aging analysis with custom periods
    $agingReport = $reportService->generateAgingAnalysis(
        $organization->id,
        now(),
        [30, 60, 90, 120] // Custom aging periods
    );
    
    expect($agingReport)->toHaveKey('aging_buckets');
    expect($agingReport)->toHaveKey('total_outstanding');
    
    // Verify aging buckets
    $buckets = $agingReport['aging_buckets'];
    expect($buckets)->toHaveKey('0_30_days');
    expect($buckets)->toHaveKey('30_60_days');
    expect($buckets)->toHaveKey('60_90_days');
    expect($buckets)->toHaveKey('90_120_days');
    expect($buckets)->toHaveKey('over_120_days');
});

test('generates cash flow statements with operating investing financing activities', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    $reportService = new AccountingReportService();
    
    // Test cash flow statement
    $cashFlow = $reportService->generateCashFlowStatement(
        $organization->id,
        now()->subMonths(6),
        now()
    );
    
    expect($cashFlow)->toHaveKey('operating_activities');
    expect($cashFlow)->toHaveKey('investing_activities');
    expect($cashFlow)->toHaveKey('financing_activities');
    expect($cashFlow)->toHaveKey('net_cash_flow');
    expect($cashFlow)->toHaveKey('beginning_cash');
    expect($cashFlow)->toHaveKey('ending_cash');
    
    // Verify cash flow equation
    $netOperating = $cashFlow['operating_activities']['net'];
    $netInvesting = $cashFlow['investing_activities']['net'];
    $netFinancing = $cashFlow['financing_activities']['net'];
    
    expect($cashFlow['net_cash_flow'])->toBe(
        $netOperating + $netInvesting + $netFinancing
    );
});