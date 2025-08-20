<?php
// tests/Unit/Accounting/BalanceSheetTest.php

namespace Tests\Unit\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\LedgerEntry;
use App\Services\AccountingReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceSheetTest extends TestCase
{
    use RefreshDatabase;

    protected AccountingReportService $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService = app(AccountingReportService::class);
    }

    /** @test */
    public function it_generates_balance_sheet_with_no_transactions()
    {
        $balanceSheet = $this->reportService->generateBalanceSheet(now());

        $this->assertEquals(0, $balanceSheet['total_assets']);
        $this->assertEquals(0, $balanceSheet['total_liabilities']);
        $this->assertEquals(0, $balanceSheet['total_equity']);
        $this->assertTrue($balanceSheet['is_balanced']);
    }

    /** @test */
    public function it_generates_balanced_balance_sheet()
    {
        // Create accounts
        $cash = ChartOfAccount::factory()->create(['type' => 'asset', 'code' => '1010', 'name' => 'Cash']);
        $equipment = ChartOfAccount::factory()->create(['type' => 'asset', 'code' => '1020', 'name' => 'Equipment']);
        $loan = ChartOfAccount::factory()->create(['type' => 'liability', 'code' => '2010', 'name' => 'Bank Loan']);
        $equity = ChartOfAccount::factory()->create(['type' => 'equity', 'code' => '3010', 'name' => 'Owner\'s Equity']);

        // Scenario: Company starts with 15,000 cash from equity investment
        // Buys 5,000 equipment with 2,000 loan and 3,000 cash
        LedgerEntry::factory()->create(['chart_of_account_id' => $cash->id, 'type' => 'debit', 'amount' => 15000.00]); // Equity investment
        LedgerEntry::factory()->create(['chart_of_account_id' => $equity->id, 'type' => 'credit', 'amount' => 15000.00]); // Equity increases

        LedgerEntry::factory()->create(['chart_of_account_id' => $equipment->id, 'type' => 'debit', 'amount' => 5000.00]); // Buy equipment
        LedgerEntry::factory()->create(['chart_of_account_id' => $cash->id, 'type' => 'credit', 'amount' => 3000.00]); // Pay cash
        LedgerEntry::factory()->create(['chart_of_account_id' => $loan->id, 'type' => 'credit', 'amount' => 2000.00]); // Take loan

        $balanceSheet = $this->reportService->generateBalanceSheet(now());

        // Assets: Cash (15000 - 3000) = 12000 + Equipment 5000 = 17000
        // Liabilities: Loan = 2000
        // Equity: 15000
        // Assets (17000) = Liabilities (2000) + Equity (15000) = 17000 ✅

        $this->assertEquals(17000.00, $balanceSheet['total_assets']);
        $this->assertEquals(2000.00, $balanceSheet['total_liabilities']);
        $this->assertEquals(15000.00, $balanceSheet['total_equity']);
        $this->assertTrue($balanceSheet['is_balanced']);
    }

    /** @test */
    /** @test */
    public function it_includes_retained_earnings_from_income_statement()
    {
        // Create equity account for retained earnings
        $retainedEarnings = ChartOfAccount::factory()->create([
            'type' => 'equity',
            'code' => '3020',
            'name' => 'Retained Earnings'
        ]);

        $cash = ChartOfAccount::factory()->create(['type' => 'asset', 'code' => '1010']);
        $revenue = ChartOfAccount::factory()->create(['type' => 'revenue', 'code' => '4010']);
        $expense = ChartOfAccount::factory()->create(['type' => 'expense', 'code' => '5010']);

        // Initial equity investment
        LedgerEntry::factory()->create(['chart_of_account_id' => $cash->id, 'type' => 'debit', 'amount' => 10000.00]);
        LedgerEntry::factory()->create(['chart_of_account_id' => $retainedEarnings->id, 'type' => 'credit', 'amount' => 10000.00]);

        // Business operations: revenue and expenses
        $startDate = now()->subMonth();
        $endDate = now();

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $cash->id,
            'type' => 'debit',
            'amount' => 5000.00,
            'entry_date' => $startDate->copy()->addDays(1)
        ]);
        LedgerEntry::factory()->create([
            'chart_of_account_id' => $revenue->id,
            'type' => 'credit',
            'amount' => 5000.00,
            'entry_date' => $startDate->copy()->addDays(1)
        ]);

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $cash->id,
            'type' => 'credit',
            'amount' => 2000.00,
            'entry_date' => $startDate->copy()->addDays(5)
        ]);
        LedgerEntry::factory()->create([
            'chart_of_account_id' => $expense->id,
            'type' => 'debit',
            'amount' => 2000.00,
            'entry_date' => $startDate->copy()->addDays(5)
        ]);

        $balanceSheet = $this->reportService->generateBalanceSheet($endDate);

        // Initial equity: 10000
        // Net income: Revenue 5000 - Expenses 2000 = 3000
        // Total equity should be: 10000 + 3000 = 13000

        $this->assertEquals(13000.00, $balanceSheet['total_equity']);

        // Also verify assets = liabilities + equity
        // Assets: Cash (10000 + 5000 - 2000) = 13000
        // Liabilities: 0
        // Equity: 13000
        $this->assertEquals(13000.00, $balanceSheet['total_assets']);
        $this->assertEquals(0, $balanceSheet['total_liabilities']);
        $this->assertTrue($balanceSheet['is_balanced']);
    }
}
