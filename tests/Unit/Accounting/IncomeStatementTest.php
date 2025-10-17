<?php
// tests/Unit/Accounting/IncomeStatementTest.php

namespace Tests\Unit\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\LedgerEntry;
use App\Services\AccountingReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeStatementTest extends TestCase
{
    use RefreshDatabase;

    protected AccountingReportService $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService = app(AccountingReportService::class);
    }

    /** @test */
    public function it_generates_income_statement_with_no_transactions()
    {
        $startDate = now()->subMonth();
        $endDate = now();

        $incomeStatement = $this->reportService->generateIncomeStatement($startDate, $endDate);

        $this->assertEquals(0, $incomeStatement['total_revenue']);
        $this->assertEquals(0, $incomeStatement['total_expenses']);
        $this->assertEquals(0, $incomeStatement['net_income']);
    }

    /** @test */
    /** @test */
    public function it_generates_income_statement_with_revenue_and_expenses()
    {
        $revenueAccount = ChartOfAccount::factory()->create(['type' => 'revenue', 'code' => '4010', 'name' => 'Sales Revenue']);
        $cogsAccount = ChartOfAccount::factory()->create(['type' => 'expense', 'code' => '5010', 'name' => 'Cost of Goods Sold']);
        $salaryAccount = ChartOfAccount::factory()->create(['type' => 'expense', 'code' => '5020', 'name' => 'Salaries Expense']);

        // Use fixed dates to avoid timezone issues
        $startDate = now()->subMonth()->startOfDay();
        $endDate = now()->endOfDay();

        // Revenue transactions - use fixed dates within the range
        LedgerEntry::factory()->create([
            'chart_of_account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 10000.00,
            'entry_date' => $startDate->copy()->addDays(5)
        ]);

        // Expense transactions
        LedgerEntry::factory()->create([
            'chart_of_account_id' => $cogsAccount->id,
            'type' => 'debit',
            'amount' => 6000.00,
            'entry_date' => $startDate->copy()->addDays(10)
        ]);

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $salaryAccount->id,
            'type' => 'debit',
            'amount' => 2000.00,
            'entry_date' => $startDate->copy()->addDays(15)
        ]);

        // Debug: Check what ledger entries actually exist
        $allEntries = LedgerEntry::all();
        \Log::debug('All ledger entries:', $allEntries->toArray());

        $incomeStatement = $this->reportService->generateIncomeStatement($startDate, $endDate);

        \Log::debug('Income statement result:', $incomeStatement);

        $this->assertEquals(10000.00, $incomeStatement['total_revenue']);
        $this->assertEquals(8000.00, $incomeStatement['total_expenses']); // 6000 + 2000
        $this->assertEquals(2000.00, $incomeStatement['net_income']); // 10000 - 8000
    }

    /** @test */
    /** @test */
    public function it_excludes_transactions_outside_date_range()
    {
        $revenueAccount = ChartOfAccount::factory()->create(['type' => 'revenue']);
        $expenseAccount = ChartOfAccount::factory()->create(['type' => 'expense']);

        // Use fixed dates
        $startDate = now()->subMonth()->startOfDay();
        $endDate = now()->endOfDay();

        // Included in date range - use COPY to avoid modifying original dates
        LedgerEntry::factory()->create([
            'chart_of_account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 5000.00,
            'entry_date' => $startDate->copy()->addDays(1) // Use copy()
        ]);

        // Excluded - before date range (use copy and go backwards)
        LedgerEntry::factory()->create([
            'chart_of_account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 3000.00,
            'entry_date' => $startDate->copy()->subDays(10) // Before start date
        ]);

        // Excluded - after date range (use copy and go forwards)
        LedgerEntry::factory()->create([
            'chart_of_account_id' => $expenseAccount->id,
            'type' => 'debit',
            'amount' => 1000.00,
            'entry_date' => $endDate->copy()->addDays(10) // After end date
        ]);

        $incomeStatement = $this->reportService->generateIncomeStatement($startDate, $endDate);

        $this->assertEquals(5000.00, $incomeStatement['total_revenue']);
        $this->assertEquals(0, $incomeStatement['total_expenses']);
        $this->assertEquals(5000.00, $incomeStatement['net_income']);
    }
}
