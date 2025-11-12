<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Permissions\OrganizationPermissions;
use App\Roles\OrganizationRoles;
use Illuminate\Container\Attributes\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\App;
use Tests\TestCase;
use Tests\Traits\SetupOrganization;
use PHPUnit\Framework\Attributes\Test;

class OrganizationApiTest extends TestCase
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
    public function can_create_organization()
    {
        $user = $this->user;
        // dd([
        //     $user->organizations,
        //     $user->organizations->pluck('pivot.permissions'),
        //     $user->getAllRoles(),
        //     $user->getAllPermissions(),
        //     $user->hasPermission(OrganizationPermissions::CREATE_ORGANIZATION),
        //     $user->hasRole(OrganizationRoles::ORGANIZATION_ADMIN)
        // ]);
        $response = $this->actingAs($this->user)
            ->postJson('/api/organizations', [
                'name' => 'Test Org',
                'description' => 'Test Description'
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Test Org',
                    'description' => 'Test Description'
                ]
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Org'
        ]);
    }

    #[Test]
    public function debug_factory_output()
    {
        $organizations = Organization::factory()->count(3)->create();

        // Dump the created organizations
        // dump($organizations->toArray());

        $this->assertTrue(true); // Dummy assertion
    }

    #[Test]
    public function verify_organization_factory_works()
    {
        $organization = Organization::factory()->create();

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => $organization->name,
        ]);

        // Create 3 organizations and verify count
        $organizations = Organization::factory()->count(3)->create();
        $this->assertCount(3, $organizations);
    }

    #[Test]
    public function can_list_organizations()
    {
        $this->user->organizations()->syncWithoutDetaching(

            Organization::factory()->count(3)->create()
        );

        $response = $this->actingAs($this->user)
            ->getJson('/api/organizations');
        // dd($response->json());
        $response->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    #[Test]
    public function can_show_organization()
    {
        // $org = Organization::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/organizations/{$this->organization->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $this->organization->id,
                    'name' => $this->organization->name
                ]
            ]);
    }

    #[Test]
    public function can_update_organization()
    {
        // $org = Organization::factory()->create();
        $user = $this->user;
        $this->actingAs($this->user);
        // dd([
        //     // $user->organizations,
        //     $user->organizations->pluck('pivot.permissions')->map(function ($t) {
        //         return $t;
        //     }),
        //     $user->getAllRoles(),
        //     $user->getAllPermissions(),
        //     $user->hasPermission(OrganizationPermissions::EDIT_ORGANIZATION),
        //     $user->hasRole(OrganizationRoles::ORGANIZATION_ADMIN)
        // ]);
        $response = $this->actingAs($this->user)
            ->putJson("/api/organizations/{$this->organization->id}", [
                'name' => 'Updated Name',
                'description' => 'Updated Description'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Name',
                    'description' => 'Updated Description'
                ]
            ]);
    }

    #[Test]
    public function can_delete_organization()
    { {
            $organization = $this->organization; //Organization::factory()->create();
            $user = $this->user; //User::factory()->create();

            // Ensure user has proper permissions
            $user->addRole('admin', $organization);

            $response = $this->actingAs($user)
                ->deleteJson("/api/organizations/{$organization->id}");

            $response->assertStatus(204); // or 200 depending on your implementation

            // Use soft delete check if applicable
            $this->assertSoftDeleted('organizations', ['id' => $organization->id]);
        }
    }
    #[Test]
    public function guests_cannot_access_organizations()
    {
        $organization = Organization::factory()->create();

        // Make sure no user is authenticated
        auth()->logout();

        $response = $this->getJson('/api/organizations');
        $response->assertStatus(401); // Unauthorized
    }

    #[Test]
    public function name_is_required_for_creation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/organizations', [
                'description' => 'No name provided'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function name_must_be_unique()
    {
        Organization::factory()->create(['name' => 'Existing Org']);

        $response = $this->actingAs($this->user)
            ->postJson('/api/organizations', [
                'name' => 'Existing Org'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    #[Test]
    public function user_can_view_their_organizations()
    {
        // [$org, $user] = $this->createOrganizationWithUser();

        $response = $this->actingAs($this->user)
            ->getJson('/api/users/me/organizations');

        // Get the pivot data through the user's relationship
        $pivotData = $this->user->organizations()->first()->pivot;
        // dd($response);
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->organization->id)
        ;
    }
    #[Test]
    public function organization_admin_can_list_members()
    {
        // [$org, $admin] = $this->createOrganizationWithUser();
        $member = User::factory()->create();


        // Attach with properly formatted JSON
        $this->organization->users()->attach($member, [
            'roles' => json_encode(['member'])
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/organizations/{$this->organization->id}/members");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['email' => $this->user->email])
            ->assertJsonFragment(['email' => $member->email]);
    }

    #[Test]
    public function admin_can_invite_new_members()
    {
        // [$org, $admin] = $this->createOrganizationWithUser();
        $newUser = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/organizations/{$this->organization->id}/invitations", [
                'email' => $newUser->email,
                // 'role' => 'manager' // Send as plain string
                'roles' => 'manager'

            ]);

        $response->assertStatus(201);

        // Verify database record exists
        $this->assertDatabaseHas('organization_user', [
            'organization_id' => $this->organization->id,
            'user_id' => $newUser->id,
        ]);

        // Verify roles were stored correctly (raw JSON check)
        $this->assertDatabaseHas('organization_user', [
            'organization_id' => $this->organization->id,
            'user_id' => $newUser->id,
            'roles' => json_encode(['manager']) // Check for exact JSON string
        ]);
    }

    #[Test]
    public function cannot_invite_existing_member()
    {
        // [$org, $admin] = $this->createOrganizationWithUser();
        $existingMember = User::factory()->create();
        $this->organization->users()->attach($existingMember, ['roles' => json_encode(['member'])]);
        // dd();
        $response = $this->actingAs($this->user)
            ->postJson("/api/organizations/{$this->organization->id}/invitations", [
                'email' => $existingMember->email,
                'roles' => json_encode(['manager'])
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function non_admin_cannot_invite_members()
    {
        // [$org, $member] = $this->createOrganizationWithUser(null, $roles = ['member']);
        $newUser = User::factory()->create();

        $response = $this->actingAs($newUser)
            ->postJson("/api/organizations/{$this->organization->id}/invitations", [
                'email' => $newUser->email
            ]);

        $response->assertStatus(403);
    }
}
