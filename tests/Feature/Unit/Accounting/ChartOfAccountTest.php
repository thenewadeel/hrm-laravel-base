<?php

namespace Tests\Unit\Accounting;

use App\Models\Accounting\ChartOfAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartOfAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
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

    /** @test */
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

    /** @test */
    public function account_code_must_be_unique()
    {
        // Arrange
        ChartOfAccount::factory()->create(['code' => '1010']);

        // Act & Assert
        $this->expectException(\Illuminate\Database\QueryException::class);
        ChartOfAccount::factory()->create(['code' => '1010']);
    }

    /** @test */
    public function it_can_calculate_its_current_balance()
    {
        // This test will be implemented AFTER we have LedgerEntries.
        // It will sum all debits and credits posted to this account.
        $this->markTestIncomplete('Pending ledger entry implementation.');
    }
}
