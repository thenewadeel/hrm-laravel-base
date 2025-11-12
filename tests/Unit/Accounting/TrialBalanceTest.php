<?php
// tests/Unit/Accounting/TrialBalanceTest.php

namespace Tests\Unit\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\LedgerEntry;
use App\Services\AccountingReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupOrganization;

class TrialBalanceTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;

    protected AccountingReportService $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService = app(AccountingReportService::class);
        $this->setupOrganization();
    }

    #[Test]
    public function it_generates_trial_balance_with_zero_entries()
    {
        $trialBalance = $this->reportService->generateTrialBalance();

        $this->assertEquals(0, $trialBalance['total_debits']);
        $this->assertEquals(0, $trialBalance['total_credits']);
        $this->assertTrue($trialBalance['is_balanced']);
    }

    #[Test]
    public function it_generates_trial_balance_with_balanced_entries()
    {
        $cashAccount = ChartOfAccount::factory()->create(['type' => 'asset', 'code' => '1010']);
        $revenueAccount = ChartOfAccount::factory()->create(['type' => 'revenue', 'code' => '4010']);

        // Create balanced entries
        LedgerEntry::factory()->create([
            'chart_of_account_id' => $cashAccount->id,
            'type' => 'debit',
            'amount' => 100.00
        ]);

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 100.00
        ]);

        $trialBalance = $this->reportService->generateTrialBalance();

        $this->assertEquals(100.00, $trialBalance['total_debits']);
        $this->assertEquals(100.00, $trialBalance['total_credits']);
        $this->assertTrue($trialBalance['is_balanced']);
    }

    #[Test]
    public function it_shows_account_balances_in_trial_balance()
    {
        $cashAccount = ChartOfAccount::factory()->create(['type' => 'asset', 'code' => '1010']);
        $revenueAccount = ChartOfAccount::factory()->create(['type' => 'revenue', 'code' => '4010']);
        $arAccount = ChartOfAccount::factory()->create(['type' => 'asset', 'code' => '1020']);

        // Scenario: Make a sale, part cash, part credit
        LedgerEntry::factory()->create(['chart_of_account_id' => $cashAccount->id, 'type' => 'debit', 'amount' => 100.00]);
        LedgerEntry::factory()->create(['chart_of_account_id' => $revenueAccount->id, 'type' => 'credit', 'amount' => 100.00]);
        LedgerEntry::factory()->create(['chart_of_account_id' => $arAccount->id, 'type' => 'debit', 'amount' => 50.00]);
        LedgerEntry::factory()->create(['chart_of_account_id' => $revenueAccount->id, 'type' => 'credit', 'amount' => 50.00]);

        $trialBalance = $this->reportService->generateTrialBalance();

        $this->assertEquals(150.00, $trialBalance['total_debits']);
        $this->assertEquals(150.00, $trialBalance['total_credits']);
        $this->assertTrue($trialBalance['is_balanced']);

        // Check individual account balances
        $cashBalance = collect($trialBalance['accounts'])->firstWhere('id', $cashAccount->id);
        $arBalance = collect($trialBalance['accounts'])->firstWhere('id', $arAccount->id);
        $revenueBalance = collect($trialBalance['accounts'])->firstWhere('id', $revenueAccount->id);

        $this->assertEquals(100.00, $cashBalance['balance']); // Asset: debit normal (100 debit - 0 credit)
        $this->assertEquals(50.00, $arBalance['balance']);    // Asset: debit normal (50 debit - 0 credit)
        $this->assertEquals(150.00, $revenueBalance['balance']); // Revenue: credit normal (150 credit - 0 debit = POSITIVE 150)
    }
}
