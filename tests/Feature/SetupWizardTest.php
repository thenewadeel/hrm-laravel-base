<?php
// tests/Feature/SetupWizardTest.php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Roles\InventoryRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\SetupInventory;
use Tests\Traits\SetupOrganization;

class SetupWizardTest extends TestCase
{
    use RefreshDatabase, SetupOrganization, SetupInventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
    }

    #[Test]
    public function it_shows_setup_wizard_for_new_users()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/setup');

        $response->assertStatus(200)
            ->assertSee('Organization Setup'); // Updated text
    }

    #[Test]
    public function it_redirects_to_dashboard_if_organization_exists()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();

        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => null
        ]);

        $response = $this->actingAs($user)
            ->get('/setup');

        $response->assertRedirect('/setup/stores'); // Updated redirect
    }

    #[Test]
    public function it_can_create_organization_through_wizard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/setup/organization', [
                'name' => 'Test Organization',
            ]);

        $response->assertRedirect('/setup/stores'); // Updated redirect
        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_validates_organization_creation()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/setup/organization', []);

        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function it_assigns_admin_role_to_creator()
    {
        // dd('$user');
        $user = User::factory()->create();
        $this->actingAs($user)
            ->post('/setup/organization', [
                'name' => 'Test Org',
            ]);

        $organization = $user->organizations()->first();
        $this->assertNotNull($organization);
        $this->assertEquals('Test Org', $organization->name);

        // Check the pivot data
        $pivot = $user->organizations()->first()->pivot;
        $this->assertEquals([InventoryRoles::INVENTORY_ADMIN], $pivot->roles);

        // Check that organization unit was created with correct organization_id
        $this->assertDatabaseHas('organization_units', [
            'name' => 'Head Office',
            'organization_id' => $organization->id,
            'type' => 'head_office',
        ]);
    }
}
