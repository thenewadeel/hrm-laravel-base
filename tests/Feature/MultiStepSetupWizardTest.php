<?php
// tests/Feature/MultiStepSetupWizardTest.php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MultiStepSetupWizardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_organization_step_for_new_users()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/setup');

        $response->assertStatus(200)
            ->assertSee('Organization Setup')
            ->assertSee('Step 1 of 3');
    }

    #[Test]
    public function it_can_store_organization_and_proceed_to_store_step()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/setup/organization', [
                'name' => 'Test Organization',
            ]);

        $response->assertRedirect('/setup/stores');
        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
        ]);
    }

    #[Test]
    public function it_shows_store_setup_step_after_organization()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => null
        ]);

        $response = $this->actingAs($user)
            ->get('/setup/stores');

        $response->assertStatus(200)
            ->assertSee('Store Setup')
            ->assertSee('Step 2 of 3');
    }

    #[Test]
    public function it_can_create_first_store_and_proceed_to_accounting()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => null
        ]);

        $response = $this->actingAs($user)
            ->post('/setup/stores', [
                'name' => 'Main Store',
                'location' => 'Head Office',
            ]);

        $response->assertRedirect('/setup/accounts');
        $this->assertDatabaseHas('inventory_stores', [
            'name' => 'Main Store',
            'organization_id' => $organization->id,
        ]);
    }

    #[Test]
    public function it_shows_accounting_setup_step_after_store()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => null
        ]);

        $response = $this->actingAs($user)
            ->get('/setup/accounts');

        $response->assertStatus(200)
            ->assertSee('Accounting Setup')
            ->assertSee('Step 3 of 3');
    }

    #[Test]
    public function it_can_setup_chart_of_accounts_and_complete_wizard()
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
        $this->assertDatabaseHas('chart_of_accounts', [
            'organization_id' => $organization->id,
        ]);
    }

    #[Test]
    public function it_redirects_to_correct_step_based_on_progress()
    {
        $user = User::factory()->create();

        // No organization - should redirect to organization step
        $response = $this->actingAs($user)->get('/setup/stores');
        $response->assertRedirect('/setup');

        $response = $this->actingAs($user)->get('/setup/accounts');
        $response->assertRedirect('/setup');

        // Has organization but no store - should redirect to store step
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => null
        ]);

        $response = $this->actingAs($user)->get('/setup/accounts');
        $response->assertRedirect('/setup/stores');
    }
}
