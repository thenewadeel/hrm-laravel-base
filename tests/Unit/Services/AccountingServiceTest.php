<?php
// tests/Unit/Accounting/AccountingServiceTest.php

namespace Tests\Unit\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AccountingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AccountingService $accountingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountingService = app(AccountingService::class);
    }

    #[Test]
    public function it_throws_an_exception_for_unbalanced_transactions()
    {
        $unbalancedEntries = [
            ['account' => ChartOfAccount::factory()->create(['type' => 'asset']), 'type' => 'debit', 'amount' => 100.00],
            // Missing a corresponding credit entry. Debits (100) != Credits (0)
        ];

        $this->expectException(\App\Exceptions\UnbalancedTransactionException::class);
        $this->accountingService->postTransaction($unbalancedEntries, 'Test transaction that will fail');
    }

    #[Test]
    public function it_successfully_posts_a_balanced_transaction_and_creates_ledger_entries()
    {
        // Arrange: Create the accounts we need
        $cashAccount = ChartOfAccount::factory()->create(['type' => 'asset', 'code' => '1010']);
        $revenueAccount = ChartOfAccount::factory()->create(['type' => 'revenue', 'code' => '4010']);

        $balancedEntries = [
            ['account' => $cashAccount, 'type' => 'debit', 'amount' => 100.00],
            ['account' => $revenueAccount, 'type' => 'credit', 'amount' => 100.00],
        ];

        // Act
        $this->accountingService->postTransaction($balancedEntries, 'Test Sale');

        // Assert: Check that ledger entries were created
        $this->assertDatabaseCount('ledger_entries', 2);

        $this->assertDatabaseHas('ledger_entries', [
            'chart_of_account_id' => $cashAccount->id,
            'type' => 'debit',
            'amount' => 100.00,
            'description' => 'Test Sale'
        ]);

        $this->assertDatabaseHas('ledger_entries', [
            'chart_of_account_id' => $revenueAccount->id,
            'type' => 'credit',
            'amount' => 100.00,
            'description' => 'Test Sale'
        ]);
    }

    #[Test]
    public function it_can_handle_complex_but_balanced_transactions()
    {
        $cashAccount = ChartOfAccount::factory()->create(['type' => 'asset']);
        $arAccount = ChartOfAccount::factory()->create(['type' => 'asset']);
        $revenueAccount = ChartOfAccount::factory()->create(['type' => 'revenue']);
        $taxAccount = ChartOfAccount::factory()->create(['type' => 'liability']);

        $complexEntries = [
            ['account' => $cashAccount, 'type' => 'debit', 'amount' => 50.00],   // Partial cash receipt
            ['account' => $arAccount, 'type' => 'debit', 'amount' => 65.00],     // The rest is on account
            ['account' => $revenueAccount, 'type' => 'credit', 'amount' => 100.00], // Total revenue
            ['account' => $taxAccount, 'type' => 'credit', 'amount' => 15.00],   // Tax collected
        ];
        // Total Debits: 50 + 65 = 115. Total Credits: 100 + 15 = 115. Balanced.

        // This should not throw an exception
        try {
            $this->accountingService->postTransaction($complexEntries, 'Complex sale with tax and partial payment');
            $this->assertTrue(true); // If we get here, the test passes
        } catch (\Exception $e) {
            $this->fail('Expected no exception but got: ' . $e->getMessage());
        }
    }
}
