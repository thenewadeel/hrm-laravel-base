<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function can_create_organization()
    {
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

    /** @test */
    public function debug_factory_output()
    {
        $organizations = Organization::factory()->count(3)->create();

        // Dump the created organizations
        dump($organizations->toArray());

        $this->assertTrue(true); // Dummy assertion
    }

    /** @test */
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

    /** @test */
    public function can_list_organizations()
    {
        Organization::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/organizations');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_show_organization()
    {
        $org = Organization::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/organizations/{$org->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $org->id,
                    'name' => $org->name
                ]
            ]);
    }

    /** @test */
    public function can_update_organization()
    {
        $org = Organization::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/organizations/{$org->id}", [
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

    /** @test */
    public function can_delete_organization()
    {
        $org = Organization::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/organizations/{$org->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted($org);
    }
    /** @test */
    public function guests_cannot_access_organizations()
    {
        $org = Organization::factory()->create();

        // Index
        $this->getJson('/api/organizations')->assertUnauthorized();

        // Show
        $this->getJson("/api/organizations/{$org->id}")->assertUnauthorized();

        // Store
        $this->postJson('/api/organizations')->assertUnauthorized();

        // Update
        $this->putJson("/api/organizations/{$org->id}")->assertUnauthorized();

        // Delete
        $this->deleteJson("/api/organizations/{$org->id}")->assertUnauthorized();
    }

    /** @test */
    public function name_is_required_for_creation()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/organizations', [
                'description' => 'No name provided'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
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
}
