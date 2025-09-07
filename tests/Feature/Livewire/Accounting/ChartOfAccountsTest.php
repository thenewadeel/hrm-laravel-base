<?php

namespace Tests\Feature\Livewire\Accounting;

use App\Livewire\Accounting\ChartOfAccounts;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChartOfAccountsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the chart of accounts page can be rendered.
     */
    public function test_chart_of_accounts_page_renders()
    {
        Livewire::test(ChartOfAccounts::class)
            ->assertStatus(200);
    }

    /**
     * Test that the chart of accounts page displays the correct data.
     */
    public function test_it_displays_a_list_of_accounts()
    {
        // Create some sample accounts
        $accounts = ChartOfAccount::factory()->count(3)->create();

        Livewire::test(ChartOfAccounts::class)
            ->assertSee($accounts[0]->name)
            ->assertSee($accounts[1]->name)
            ->assertSee($accounts[2]->name);
    }
    public function it_renders_successfully()
    {
        Livewire::test('accounting.chart-of-accounts')
            ->assertStatus(200);
    }

    /** @test */
    public function it_displays_a_list_of_chart_of_accounts()
    {
        // Arrange: Create some accounts
        $accounts = ChartOfAccount::factory()->count(3)->create();

        // Act: Render the Livewire component
        $component = Livewire::test('accounting.chart-of-accounts');

        // Assert: The component displays the accounts
        foreach ($accounts as $account) {
            $component->assertSee($account->code);
            $component->assertSee($account->name);
        }
    }

    /** @test */
    public function it_can_create_a_new_chart_of_account_record()
    {
        // Define the data for the new account
        $newAccountData = [
            'code' => '1010',
            'name' => 'Cash',
            'type' => 'asset',
            'description' => 'Petty cash on hand.',
        ];

        // Test the creation process via the Livewire component
        Livewire::test('accounting.chart-of-accounts')
            ->set('code', $newAccountData['code'])
            ->set('name', $newAccountData['name'])
            ->set('type', $newAccountData['type'])
            ->set('description', $newAccountData['description'])
            ->call('create')
            ->assertHasNoErrors()
            ->assertSee('Cash'); // Check if the new account name is visible

        // Assert that the record was created in the database
        $this->assertDatabaseHas('chart_of_accounts', [
            'code' => '1010',
            'name' => 'Cash'
        ]);
    }

    /** @test */
    public function it_can_update_an_existing_chart_of_account_record()
    {
        // Arrange: Create an account to be updated
        $account = ChartOfAccount::factory()->create([
            'code' => '1020',
            'name' => 'Accounts Receivable'
        ]);

        // Act: Simulate the update process
        Livewire::test('accounting.chart-of-accounts')
            ->call('edit', $account->id)
            ->set('code', '1021')
            ->set('name', 'New Accounts Receivable')
            ->call('update')
            ->assertHasNoErrors();

        // Assert: The record in the database is updated
        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $account->id,
            'code' => '1021',
            'name' => 'New Accounts Receivable'
        ]);
    }
}
