<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\OrganizationUnit;
use Tests\TestCase;

class OrganizationUnitApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
    protected function createOrganizationWithUser($user = null, array $roles = ['admin'])
    {
        $organization = Organization::factory()->create();
        $user = $user ?: User::factory()->create();

        $organization->users()->attach($user, [
            'roles' => json_encode($roles)
        ]);

        return [$organization, $user];
    }



    /** @test */
    public function can_create_organization_unit()
    {
        $organization = Organization::factory()->create();
        $admin = User::factory()->create();
        $organization->users()->attach($admin, ['roles' => json_encode(['admin'])]);

        $response = $this->actingAs($admin)
            ->postJson("/api/organizations/{$organization->id}/units", [
                'name' => 'Development Department',
                'type' => 'department',
                'parent_id' => null,
                'custom_fields' => ['cost_center' => 'DEV-001']
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Development Department',
                    'type' => 'department'
                ]
            ]);
    }

    /** @test */
    public function can_create_nested_unit()
    {
        $organization = Organization::factory()->create();
        $parentUnit = OrganizationUnit::factory()->create(['organization_id' => $organization->id]);

        $response = $this->actingAs($this->createOrganizationAdmin($organization))
            ->postJson("/api/organizations/{$organization->id}/units", [
                'name' => 'Backend Team',
                'type' => 'team',
                'parent_id' => $parentUnit->id
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.parent_id', $parentUnit->id);
    }

    /** @test */
    public function can_get_unit_hierarchy()
    {
        $organization = Organization::factory()->create();
        $unit = OrganizationUnit::factory()
            ->for($organization)
            ->has(OrganizationUnit::factory()->count(2), 'children')
            ->create();

        $response = $this->actingAs($this->createOrganizationAdmin($organization))
            ->getJson("/api/organizations/{$organization->id}/units/{$unit->id}/hierarchy");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.children');
    }


    /** @test */
    public function can_assign_user_to_unit()
    {
        [$org, $admin] = $this->createOrganizationWithUser();
        $unit = OrganizationUnit::factory()->for($org)->create();
        $user = User::factory()->create();
        $org->users()->attach($user);

        $response = $this->actingAs($admin)
            ->putJson("/api/organizations/{$org->id}/units/{$unit->id}/assign", [
                'user_id' => $user->id,
                'position' => 'Developer'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('organization_user', [
            'organization_unit_id' => $unit->id,
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function can_assign_user_to_organization_unit()
    {
        [$org, $admin] = $this->createOrganizationWithUser();
        $user = User::factory()->create();
        $unit = OrganizationUnit::factory()->create(['organization_id' => $org->id]);

        $org->users()->attach($user);

        $response = $this->actingAs($admin)
            ->putJson("/api/organizations/{$org->id}/members/{$user->id}/assign", [
                'organization_unit_id' => $unit->id
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('organization_user', [
            'user_id' => $user->id,
            'organization_unit_id' => $unit->id
        ]);
    }

    /** @test */
    public function can_list_users_by_organization_unit()
    {
        [$org, $admin] = $this->createOrganizationWithUser();

        // Create hierarchy: Department → Team
        $dept = OrganizationUnit::factory()->create([
            'organization_id' => $org->id,
            'type' => 'department'
        ]);

        $team = OrganizationUnit::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $dept->id,
            'type' => 'team'
        ]);

        // Create users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $org->users()->attach($user1, ['organization_unit_id' => $dept->id]);
        $org->users()->attach($user2, ['organization_unit_id' => $team->id]);

        // Test department level
        $response = $this->actingAs($admin)
            ->getJson("/api/organizations/{$org->id}/units/{$dept->id}/members");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // Should return both direct and nested members
    }
}
