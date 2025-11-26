<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Roles\InventoryRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\SetupOrganization;

class OrganizationUnitApiTest extends TestCase
{
    use RefreshDatabase, SetupOrganization;

    // protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        // $this->user = User::factory()->create();
    }

    #[Test]
    public function can_create_organization_unit()
    {
        [$organization, $admin] = [$this->organization, $this->user]; // $this->createOrganizationWithUser();

        // $organization = Organization::factory()->create();
        // $admin = User::factory()->create();
        // $organization->users()->attach($admin, ['roles' => json_encode(['admin'])]);
        // dd([$admin->organizations]);
        $response = $this->actingAs($admin)
            ->postJson("/api/organizations/{$organization->id}/units", [
                'name' => 'Development Department',
                'type' => 'department',
                'parent_id' => null,
                'custom_fields' => ['cost_center' => 'DEV-001'],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Development Department',
                    'type' => 'department',
                ],
            ]);
    }

    #[Test]
    public function can_create_nested_unit()
    {
        [$organization, $admin] = [$this->organization, $this->user]; // $this->createOrganizationWithUser();

        // $organization = Organization::factory()->create();
        $parentUnit = OrganizationUnit::factory()->create(['organization_id' => $organization->id]);
        // dd($parentUnit);
        $response = $this->actingAs($admin)
            ->postJson("/api/organizations/{$organization->id}/units", [
                'name' => 'Backend Team',
                'type' => 'team',
                'parent_id' => $parentUnit->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.parent_id', $parentUnit->id);
    }

    #[Test]
    public function can_get_unit_hierarchy()
    {
        [$organization, $admin] = [$this->organization, $this->user];

        // Create parent unit
        $unit = OrganizationUnit::create([
            'name' => 'Parent Unit',
            'type' => 'department',
            'organization_id' => $organization->id,
        ]);

        // Create child units manually
        $child1 = OrganizationUnit::create([
            'name' => 'Child Unit 1',
            'type' => 'team',
            'organization_id' => $organization->id,
            'parent_id' => $unit->id,
        ]);

        $child2 = OrganizationUnit::create([
            'name' => 'Child Unit 2',
            'type' => 'team',
            'organization_id' => $organization->id,
            'parent_id' => $unit->id,
        ]);

        $response = $this->actingAs($admin)
            ->getJson("/api/organizations/{$organization->id}/units/{$unit->id}/hierarchy");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.children');
    }

    #[Test]
    public function can_assign_user_to_unit()
    {
        [$org, $admin] = [$this->organization, $this->user]; // $this->createOrganizationWithUser();
        $user = User::factory()->create();
        $unit = OrganizationUnit::factory()->create(['organization_id' => $org->id]);
        $unit->save();
        // $org->users()->attach($user);
        $org->users()->attach($user, [
            'roles' => json_encode([InventoryRoles::INVENTORY_ADMIN]),
            'organization_id' => $org->id,
            // 'organization_unit_id' => $this->organizationUnit->id
        ]);
        // dd(json_encode($unit));
        $response = $this->actingAs($admin)
            ->putJson("/api/organizations/{$org->id}/units/{$unit->id}/assign", [
                'user_id' => $user->id,
                'position' => 'Developer',
            ]);

        // dd($response->json());
        $response->assertStatus(200);
        $this->assertDatabaseHas('organization_user', [
            'organization_unit_id' => $unit->id,
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function can_list_users_by_organization_unit()
    {
        [$org, $admin] = [$this->organization, $this->user]; // $this->createOrganizationWithUser();

        // Create hierarchy: Department → Team
        $dept = OrganizationUnit::factory()->create([
            'organization_id' => $org->id,
            'type' => 'department',
        ]);

        $team = OrganizationUnit::factory()->create([
            'organization_id' => $org->id,
            'parent_id' => $dept->id,
            'type' => 'team',
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

    #[Test]
    public function cannot_create_organization_unit_as_non_admin()
    {
        // [$organization, $regularUser] = $this->createOrganizationWithUser(user: null, roles: ['user']);
        $organization = Organization::factory()->create();
        $regularUser = User::factory()->create();
        $organization->users()->attach($regularUser, [
            'roles' => ['user'],
            'organization_id' => $organization->id,
            // 'organization_unit_id' => $unit->id
        ]);
        // dd($regularUser->getAllRoles());
        $response = $this->actingAs($regularUser)
            ->postJson("/api/organizations/{$organization->id}/units", [
                'name' => 'Support Team',
                'type' => 'team',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function cannot_assign_user_as_non_admin()
    {
        $organization = Organization::factory()->create();
        $regularUser = User::factory()->create();
        $regularUser->organizations()->attach($organization, [
            'roles' => ['user'],
            'organization_id' => $organization->id,
        ]);
        $unit = OrganizationUnit::create([
            'name' => 'Test Unit',
            'type' => 'department',
            'organization_id' => $organization->id,
        ]);

        $response = $this->actingAs($regularUser)
            ->putJson("/api/organizations/{$organization->id}/units/{$unit->id}/assign", [
                'user_id' => $regularUser->id,
                'position' => 'Mermaid Trainer',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function cannot_create_unit_with_invalid_data()
    {
        [$organization, $admin] = [$this->organization, $this->user]; // $this->createOrganizationWithUser();

        $response = $this->actingAs($admin)
            ->postJson("/api/organizations/{$organization->id}/units", [
                'type' => 'team', // Missing 'name' field
            ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function cannot_assign_non_existent_user_to_unit()
    {
        [$org, $admin] = [$this->organization, $this->user]; // $this->createOrganizationWithUser();
        $unit = OrganizationUnit::factory()->for($org)->create();

        $response = $this->actingAs($admin)
            ->putJson("/api/organizations/{$org->id}/units/{$unit->id}/assign", [
                'user_id' => 9999, // A non-existent user ID
                'position' => 'Developer',
            ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function cannot_assign_user_not_in_organization()
    {
        [$org, $admin] = [$this->organization, $this->user]; // $this->createOrganizationWithUser();
        $unit = OrganizationUnit::factory()->for($org)->create();
        $userFromOtherOrg = User::factory()->create();

        $response = $this->actingAs($admin)
            ->putJson("/api/organizations/{$org->id}/units/{$unit->id}/assign", [
                'user_id' => $userFromOtherOrg->id,
                'position' => 'Developer',
            ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function cannot_get_hierarchy_for_non_existent_unit()
    {
        [$org, $admin] = [$this->organization, $this->user]; // $this->createOrganizationWithUser();

        $response = $this->actingAs($admin)
            ->getJson("/api/organizations/{$org->id}/units/9999/hierarchy"); // Non-existent unit ID

        $response->assertStatus(404);
    }

    #[Test]
    public function can_get_hierarchy_for_unit_with_no_children()
    {
        [$organization, $admin] = [$this->organization, $this->user]; // $this->createOrganizationWithUser();
        $unit = OrganizationUnit::factory()->for($organization)->create();

        $response = $this->actingAs($admin)
            ->getJson("/api/organizations/{$organization->id}/units/{$unit->id}/hierarchy");

        $response->assertStatus(200)
            ->assertJsonPath('data.children', []);
    }
}
