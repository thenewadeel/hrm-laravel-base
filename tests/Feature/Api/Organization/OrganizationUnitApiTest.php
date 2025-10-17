<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\OrganizationUnit;
use Tests\TestCase;
use tests\Traits\SetupOrganization;

class OrganizationUnitApiTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // $this->user = User::factory()->create();
    }




    /** @test */
    public function can_create_organization_unit()
    {
        [$organization, $admin] = $this->createOrganizationWithUser();

        // $organization = Organization::factory()->create();
        // $admin = User::factory()->create();
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
        [$organization, $admin] = $this->createOrganizationWithUser();

        // $organization = Organization::factory()->create();
        $parentUnit = OrganizationUnit::factory()->create(['organization_id' => $organization->id]);
        // dd($parentUnit);
        $response = $this->actingAs($admin)
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
        [$organization, $admin] = $this->createOrganizationWithUser();
        $unit = OrganizationUnit::factory()
            ->for($organization)
            ->has(OrganizationUnit::factory()->count(2), 'children')
            ->create();

        $response = $this->actingAs($admin)
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

        // dd(json_encode($unit));
        $response = $this->actingAs($admin)
            ->putJson("/api/organizations/{$org->id}/units/{$unit->id}/assign", [
                'user_id' => $user->id,
                'position' => 'Developer'
            ]);

        // dd($response->json());
        $response->assertStatus(200);
        $this->assertDatabaseHas('organization_user', [
            'organization_unit_id' => $unit->id,
            'user_id' => $user->id
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
        // dd($response);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // Should return both direct and nested members
    }

    /** @test */
    public function cannot_create_organization_unit_as_non_admin()
    {
        [$organization, $regularUser] = $this->createOrganizationWithUser(roles: ['user']);

        $response = $this->actingAs($regularUser)
            ->postJson("/api/organizations/{$organization->id}/units", [
                'name' => 'Support Team',
                'type' => 'team'
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_assign_user_as_non_admin()
    {
        [$org, $regularUser] = $this->createOrganizationWithUser(roles: ['user']);
        $unit = OrganizationUnit::factory()->for($org)->create();
        $user = User::factory()->create();
        $org->users()->attach($user);

        $response = $this->actingAs($regularUser)
            ->putJson("/api/organizations/{$org->id}/units/{$unit->id}/assign", [
                'user_id' => $user->id
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_create_unit_with_invalid_data()
    {
        [$organization, $admin] = $this->createOrganizationWithUser();

        $response = $this->actingAs($admin)
            ->postJson("/api/organizations/{$organization->id}/units", [
                'type' => 'team' // Missing 'name' field
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function cannot_assign_non_existent_user_to_unit()
    {
        [$org, $admin] = $this->createOrganizationWithUser();
        $unit = OrganizationUnit::factory()->for($org)->create();

        $response = $this->actingAs($admin)
            ->putJson("/api/organizations/{$org->id}/units/{$unit->id}/assign", [
                'user_id' => 9999, // A non-existent user ID
                'position' => 'Developer'
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function cannot_assign_user_not_in_organization()
    {
        [$org, $admin] = $this->createOrganizationWithUser();
        $unit = OrganizationUnit::factory()->for($org)->create();
        $userFromOtherOrg = User::factory()->create();

        $response = $this->actingAs($admin)
            ->putJson("/api/organizations/{$org->id}/units/{$unit->id}/assign", [
                'user_id' => $userFromOtherOrg->id,
                'position' => 'Developer'
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function cannot_get_hierarchy_for_non_existent_unit()
    {
        [$org, $admin] = $this->createOrganizationWithUser();

        $response = $this->actingAs($admin)
            ->getJson("/api/organizations/{$org->id}/units/9999/hierarchy"); // Non-existent unit ID

        $response->assertStatus(404);
    }

    /** @test */
    public function can_get_hierarchy_for_unit_with_no_children()
    {
        [$organization, $admin] = $this->createOrganizationWithUser();
        $unit = OrganizationUnit::factory()->for($organization)->create();

        $response = $this->actingAs($admin)
            ->getJson("/api/organizations/{$organization->id}/units/{$unit->id}/hierarchy");

        $response->assertStatus(200)
            ->assertJsonPath('data.children', []);
    }
}
