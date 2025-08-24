<?php
// tests/Feature/Api/Accounting/FinancialReportsApiTest.php

namespace Tests\Feature\Api\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\LedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportsApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_generate_trial_balance()
    {
        // Create some accounts with transactions
        $cashAccount = ChartOfAccount::factory()->create(['type' => 'asset', 'code' => '1010']);
        $revenueAccount = ChartOfAccount::factory()->create(['type' => 'revenue', 'code' => '4010']);

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $cashAccount->id,
            'type' => 'debit',
            'amount' => 1000.00
        ]);

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 1000.00
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson('/api/reports/trial-balance');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'accounts',
                    'total_debits',
                    'total_credits',
                    'is_balanced',
                    'generated_at'
                ]
            ])
            ->assertJson([
                'data' => [
                    'is_balanced' => true,
                    'total_debits' => 1000.00,
                    'total_credits' => 1000.00
                ]
            ]);
    }

    /** @test */
    public function it_can_generate_balance_sheet()
    {
        // Setup assets and liabilities
        $cashAccount = ChartOfAccount::factory()->create(['type' => 'asset', 'code' => '1010']);
        $loanAccount = ChartOfAccount::factory()->create(['type' => 'liability', 'code' => '2010']);
        $equityAccount = ChartOfAccount::factory()->create(['type' => 'equity', 'code' => '3010']);

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $cashAccount->id,
            'type' => 'debit',
            'amount' => 5000.00
        ]);

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $loanAccount->id,
            'type' => 'credit',
            'amount' => 2000.00
        ]);

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $equityAccount->id,
            'type' => 'credit',
            'amount' => 3000.00
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson('/api/reports/balance-sheet');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'assets',
                    'liabilities',
                    'equity',
                    'total_assets',
                    'total_liabilities',
                    'total_equity',
                    'is_balanced',
                    'as_of_date'
                ]
            ]);
    }

    /** @test */
    public function it_can_generate_income_statement()
    {
        $revenueAccount = ChartOfAccount::factory()->create(['type' => 'revenue', 'code' => '4010']);
        $expenseAccount = ChartOfAccount::factory()->create(['type' => 'expense', 'code' => '5010']);

        $startDate = now()->subMonth();
        $endDate = now();

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 5000.00,
            'entry_date' => $startDate->copy()->addDays(1)
        ]);

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $expenseAccount->id,
            'type' => 'debit',
            'amount' => 3000.00,
            'entry_date' => $startDate->copy()->addDays(5)
        ]);

        $this->actingAs($this->user);

        $response = $this->getJson("/api/reports/income-statement?start_date={$startDate->format('Y-m-d')}&end_date={$endDate->format('Y-m-d')}");
        // dd($response->json());
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'revenue',
                    'expenses',
                    'total_revenue',
                    'total_expenses',
                    'net_income',
                    'period',
                    'generated_at'
                ]
            ])
            ->assertJson([
                'data' => [
                    'total_revenue' => 5000.00,
                    'total_expenses' => 3000.00,
                    'net_income' => 2000.00
                ]
            ]);
    }

    /** @test */
    public function it_validates_income_statement_date_range()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/reports/income-statement?start_date=invalid&end_date=also-invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    /** @test */
    public function it_rejects_end_date_before_start_date()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/reports/income-statement?start_date=2024-01-31&end_date=2024-01-01');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }
}
