<?php

namespace Tests\Unit\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\LedgerEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupOrganization;

class ChartOfAccountTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
    }
    #[Test]
    public function it_has_a_code_name_and_type()
    {
        // Arrange & Act
        $account = ChartOfAccount::factory()->create([
            'code' => '1010',
            'name' => 'Cash on Hand',
            'type' => 'asset'
        ]);

        // Assert
        $this->assertEquals('1010', $account->code);
        $this->assertEquals('Cash on Hand', $account->name);
        $this->assertEquals('asset', $account->type);
    }

    #[Test]
    public function it_can_scope_accounts_by_type()
    {
        // Arrange
        ChartOfAccount::factory()->create(['type' => 'asset']);
        ChartOfAccount::factory()->create(['type' => 'expense']);
        ChartOfAccount::factory()->create(['type' => 'asset']);

        // Act
        $assetAccounts = ChartOfAccount::assets()->get();
        $expenseAccounts = ChartOfAccount::expenses()->get();

        // Assert
        $this->assertCount(2, $assetAccounts);
        $this->assertCount(1, $expenseAccounts);
        $this->assertTrue($assetAccounts->every(fn($account) => $account->type === 'asset'));
    }

    #[Test]
    public function account_code_must_be_unique()
    {
        // Arrange
        ChartOfAccount::factory()->create(['code' => '1010']);

        // Act & Assert
        $this->expectException(\Illuminate\Database\QueryException::class);
        ChartOfAccount::factory()->create(['code' => '1010']);
    }

    #[Test]
    public function it_can_calculate_its_current_balance()
    {
        $account = ChartOfAccount::factory()->create(['type' => 'asset']);

        // Create ledger entries for this account
        LedgerEntry::factory()->create([
            'chart_of_account_id' => $account->id,
            'type' => 'debit',
            'amount' => 1000.00
        ]);

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $account->id,
            'type' => 'credit',
            'amount' => 300.00
        ]);

        LedgerEntry::factory()->create([
            'chart_of_account_id' => $account->id,
            'type' => 'debit',
            'amount' => 200.00
        ]);

        // For asset accounts: balance = debits - credits
        $expectedBalance = (1000.00 + 200.00) - 300.00; // = 900.00

        $this->assertEquals($expectedBalance, $account->balance);
    }
}
