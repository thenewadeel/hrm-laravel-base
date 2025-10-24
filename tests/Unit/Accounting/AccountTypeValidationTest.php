<?php
// tests/Unit/Accounting/AccountTypeValidationTest.php

namespace Tests\Unit\Accounting;

use App\Exceptions\InvalidAccountTypeException;
use App\Models\Accounting\ChartOfAccount;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AccountTypeValidationTest extends TestCase
{
    use RefreshDatabase;

    protected AccountingService $accountingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountingService = app(AccountingService::class);
    }

    #[Test]
    public function it_allows_debit_to_asset_accounts()
    {
        $assetAccount = ChartOfAccount::factory()->create(['type' => 'asset']);

        // This should not throw an exception
        $this->expectNotToPerformAssertions();
        $this->callPrivateMethod($this->accountingService, 'validateAccountType', [$assetAccount, 'debit']);
    }

    #[Test]
    public function it_allows_debit_to_expense_accounts()
    {
        $expenseAccount = ChartOfAccount::factory()->create(['type' => 'expense']);

        $this->expectNotToPerformAssertions();
        $this->callPrivateMethod($this->accountingService, 'validateAccountType', [$expenseAccount, 'debit']);
    }

    #[Test]
    public function it_throws_exception_when_debiting_liability_account()
    {
        $liabilityAccount = ChartOfAccount::factory()->create(['type' => 'liability']);

        $this->expectException(InvalidAccountTypeException::class);
        $this->expectExceptionMessage('Cannot debit a liability account');

        $this->callPrivateMethod($this->accountingService, 'validateAccountType', [$liabilityAccount, 'debit']);
    }

    #[Test]
    public function it_allows_credit_to_liability_accounts()
    {
        $liabilityAccount = ChartOfAccount::factory()->create(['type' => 'liability']);

        $this->expectNotToPerformAssertions();
        $this->callPrivateMethod($this->accountingService, 'validateAccountType', [$liabilityAccount, 'credit']);
    }

    #[Test]
    public function it_allows_credit_to_revenue_accounts()
    {
        $revenueAccount = ChartOfAccount::factory()->create(['type' => 'revenue']);

        $this->expectNotToPerformAssertions();
        $this->callPrivateMethod($this->accountingService, 'validateAccountType', [$revenueAccount, 'credit']);
    }

    #[Test]
    public function it_throws_exception_when_crediting_asset_account()
    {
        $assetAccount = ChartOfAccount::factory()->create(['type' => 'asset']);

        $this->expectException(InvalidAccountTypeException::class);
        $this->expectExceptionMessage('Cannot credit a asset account');

        $this->callPrivateMethod($this->accountingService, 'validateAccountType', [$assetAccount, 'credit']);
    }

    #[Test]
    public function it_validates_account_types_during_transaction_posting()
    {
        $assetAccount = ChartOfAccount::factory()->create(['type' => 'asset']);
        $liabilityAccount = ChartOfAccount::factory()->create(['type' => 'liability']);

        $invalidEntries = [
            ['account' => $liabilityAccount, 'type' => 'debit', 'amount' => 100.00], // Can't debit liability
            ['account' => $assetAccount, 'type' => 'credit', 'amount' => 100.00],     // Can't credit asset
        ];

        $this->expectException(InvalidAccountTypeException::class);
        $this->accountingService->postTransaction($invalidEntries, 'Invalid transaction test');
    }

    /**
     * Call a private method on an object.
     */
    private function callPrivateMethod($object, string $method, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
