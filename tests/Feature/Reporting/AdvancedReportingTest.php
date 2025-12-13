<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
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
    
    // Create journal entries for testing
    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'date' => now()->subDays(30),
        'description' => 'Test Revenue Entry',
        'amount' => 10000,
    ]);
    
    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'date' => now()->subDays(30),
        'description' => 'Test Expense Entry',
        'amount' => 8000,
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
    expect($profitLoss)->toHaveKey('net_profit');
    expect($profitLoss['revenue'])->toBeGreaterThan(0);
    expect($profitLoss['net_profit'])->toBe(2000); // 10000 - 8000
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
    
    // Create journal entries
    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'date' => now(),
        'description' => 'Asset Increase',
        'amount' => 50000,
    ]);
    
    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'date' => now(),
        'description' => 'Liability Increase',
        'amount' => 30000,
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
    
    // Create balanced journal entries
    foreach ($accounts as $index => $account) {
        JournalEntry::factory()->create([
            'organization_id' => $organization->id,
            'date' => now(),
            'description' => "Test Entry $index",
            'amount' => ($index % 2 === 0) ? 1000 : -1000, // Alternate debits and credits
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
    
    // Create entries for current period
    JournalEntry::factory()->count(10)->create([
        'organization_id' => $organization->id,
        'date' => $currentPeriod,
        'amount' => 1000,
    ]);
    
    // Create entries for previous period
    JournalEntry::factory()->count(8)->create([
        'organization_id' => $organization->id,
        'date' => $previousPeriod,
        'amount' => 800,
    ]);
    
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
    
    // Create employees in different departments
    $hrEmployees = Employee::factory()->count(5)->create([
        'organization_id' => $organization->id,
        'department' => 'HR',
    ]);
    
    $salesEmployees = Employee::factory()->count(3)->create([
        'organization_id' => $organization->id,
        'department' => 'Sales',
    ]);
    
    // Create payroll data for employees
    foreach ($hrEmployees as $employee) {
        \App\Models\Payroll\PayrollRecord::factory()->create([
            'organization_id' => $organization->id,
            'employee_id' => $employee->id,
            'basic_salary' => 50000,
            'pay_period' => now()->format('Y-m'),
        ]);
    }
    
    foreach ($salesEmployees as $employee) {
        \App\Models\Payroll\PayrollRecord::factory()->create([
            'organization_id' => $organization->id,
            'employee_id' => $employee->id,
            'basic_salary' => 60000,
            'pay_period' => now()->format('Y-m'),
        ]);
    }
    
    $reportService = new AccountingReportService();
    
    // Test department performance report
    $departmentReport = $reportService->generateDepartmentPerformance(
        $organization->id,
        now()->subMonth(),
        now()
    );
    
    expect($departmentReport)->toHaveKey('departments');
    expect($departmentReport)->toHaveKey('total_payroll');
    expect($departmentReport)->toHaveKey('employee_counts');
    
    // Verify department data
    $departments = $departmentReport['departments'];
    expect($departments)->toHaveKey('HR');
    expect($departments)->toHaveKey('Sales');
    
    expect($departments['HR']['employee_count'])->toBe(5);
    expect($departments['Sales']['employee_count'])->toBe(3);
    expect($departments['HR']['total_payroll'])->toBe(250000); // 5 * 50000
    expect($departments['Sales']['total_payroll'])->toBe(180000); // 3 * 60000
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
    expect($buckets)->toHaveKey('0-30');
    expect($buckets)->toHaveKey('31-60');
    expect($buckets)->toHaveKey('61-90');
    expect($buckets)->toHaveKey('91-120');
    expect($buckets)->toHaveKey('121+');
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