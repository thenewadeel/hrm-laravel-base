<?php

namespace Tests\Unit;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\LedgerEntry;
use App\Services\AccountingReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class AccountingReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AccountingReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Since the service does not use constructor injection, we can simply instantiate it
        $this->service = new AccountingReportService();
    }

    #[Test]
    public function it_generates_a_correct_trial_balance()
    {
        // Arrange
        $asset = ChartOfAccount::factory()->create(['type' => 'asset']);
        $liability = ChartOfAccount::factory()->create(['type' => 'liability']);
        $equity = ChartOfAccount::factory()->create(['type' => 'equity']);

        LedgerEntry::factory()->create(['chart_of_account_id' => $asset->id, 'type' => 'debit', 'amount' => 1000]);
        LedgerEntry::factory()->create(['chart_of_account_id' => $liability->id, 'type' => 'credit', 'amount' => 600]);
        LedgerEntry::factory()->create(['chart_of_account_id' => $equity->id, 'type' => 'credit', 'amount' => 400]);

        // Act
        $report = $this->service->generateTrialBalance();

        // Assert
        $this->assertEquals(1000, $report['total_debits']);
        $this->assertEquals(1000, $report['total_credits']);
        $this->assertTrue($report['is_balanced']);
    }

    #[Test]
    public function it_generates_a_correct_balance_sheet()
    {
        // Arrange
        $date = Carbon::now();
        ChartOfAccount::factory()->create(['type' => 'asset']);
        ChartOfAccount::factory()->create(['type' => 'liability']);
        ChartOfAccount::factory()->create(['type' => 'equity']);
        ChartOfAccount::factory()->create(['type' => 'revenue']);
        ChartOfAccount::factory()->create(['type' => 'expense']);

        // Act
        $report = $this->service->generateBalanceSheet($date);

        // Assert
        $this->assertArrayHasKey('assets', $report);
        $this->assertArrayHasKey('liabilities', $report);
        $this->assertArrayHasKey('equity', $report);
        $this->assertArrayHasKey('retained_earnings', $report);
    }
}
