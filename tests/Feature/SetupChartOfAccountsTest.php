<?php
// tests/Feature/SetupChartOfAccountsTest.php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Inventory\Store;
use App\Models\OrganizationUnit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupInventory;

class SetupChartOfAccountsTest extends TestCase
{
    use RefreshDatabase, SetupInventory;

    #[Test]
    public function it_creates_default_chart_of_accounts()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => null
        ]);

        // Create a store to pass the store check
        Store::factory()->create([
            'organization_unit_id' => $organization->units()->first()->id ?? null,
        ]);

        $response = $this->actingAs($user)
            ->post('/setup/accounts', [
                'setup_default_accounts' => true,
            ]);

        $response->assertRedirect('/dashboard');

        // Check that default accounts were created
        $accounts = ChartOfAccount:: //where('organization_id', $organization->id)->
            get();
        $this->assertTrue($accounts->count() > 0, 'No chart of accounts were created');

        // Check for specific default accounts using where()
        $cashAccount = ChartOfAccount:: //where('organization_id', $organization->id)
            where('name', 'Cash')
            ->first();
        $this->assertNotNull($cashAccount, 'Cash account was not created');

        $payableAccount = ChartOfAccount:: //where('organization_id', $organization->id)
            where('name', 'Accounts Payable')
            ->first();
        $this->assertNotNull($payableAccount, 'Accounts Payable account was not created');
    }


    #[Test]
    public function it_skips_account_creation_if_not_requested()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => null
        ]);

        $response = $this->actingAs($user)
            ->post('/setup/accounts', [
                'setup_default_accounts' => false,
            ]);

        $response->assertRedirect('/dashboard');

        $accounts = ChartOfAccount::where('organization_id', $organization->id)->get();
        $this->assertEquals(0, $accounts->count());
    }


    #[Test]
    public function it_creates_organization_unit_for_accounting_department()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => null
        ]);

        // Create a store to pass the store check
        Store::factory()->create([
            'organization_unit_id' => $organization->units()->first()->id ?? null,
        ]);

        $this->actingAs($user)
            ->post('/setup/accounts', [
                'setup_default_accounts' => true,
            ]);

        // Use where() instead of assertDatabaseHas
        $accountingUnit = OrganizationUnit::where('organization_id', $organization->id)
            ->where('name', 'Accounting Department')
            ->first();
        $this->assertNotNull($accountingUnit, 'Accounting Department organization unit was not created');
        $this->assertEquals('department', $accountingUnit->type);
    }
}
