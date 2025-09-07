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
}
