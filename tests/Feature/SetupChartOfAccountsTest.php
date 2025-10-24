<?php
// tests/Feature/SetupChartOfAccountsTest.php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SetupChartOfAccountsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_default_chart_of_accounts()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => null
        ]);

        $response = $this->actingAs($user)
            ->post('/setup/accounts', [
                'setup_default_accounts' => true,
            ]);

        $response->assertRedirect('/dashboard');

        // Check that default accounts were created
        $accounts = ChartOfAccount::where('organization_id', $organization->id)->get();
        $this->assertTrue($accounts->count() > 0);

        // Check for specific default accounts
        $this->assertDatabaseHas('chart_of_accounts', [
            'organization_id' => $organization->id,
            'name' => 'Cash',
            'type' => 'asset',
        ]);

        $this->assertDatabaseHas('chart_of_accounts', [
            'organization_id' => $organization->id,
            'name' => 'Accounts Payable',
            'type' => 'liability',
        ]);
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

        $this->actingAs($user)
            ->post('/setup/accounts', [
                'setup_default_accounts' => true,
            ]);

        $this->assertDatabaseHas('organization_units', [
            'organization_id' => $organization->id,
            'name' => 'Accounting Department',
            'type' => 'department',
        ]);
    }
}
