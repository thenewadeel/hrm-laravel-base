<?php
// tests/Feature/SetupStoreTest.php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Inventory\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupInventory;
use Tests\Traits\SetupOrganization;

class SetupStoreTest extends TestCase
{
    use RefreshDatabase, SetupOrganization, SetupInventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
    }

    #[Test]
    public function it_creates_first_store_for_organization()
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
                'location' => 'Headquarters',
                'code' => 'STORE001',
            ]);

        $response->assertRedirect();

        // Use where() instead of assertDatabaseHas
        $store = Store::where('name', 'Main Store')->first();
        $this->assertNotNull($store);
        $this->assertEquals($organization->id, $store->organization->id);
        $this->assertEquals('STORE001', $store->code);
    }

    #[Test]
    public function it_validates_store_creation()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => null
        ]);

        $response = $this->actingAs($user)
            ->post('/setup/stores', []);

        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function it_auto_generates_store_code_if_not_provided()
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
                'location' => 'Headquarters',
                // No code provided
            ]);

        // Use where() instead of assertDatabaseHas
        $store = Store::where('name', 'Main Store')->first();
        $this->assertNotNull($store);
        $this->assertEquals($organization->id, $store->organization->id);
        $this->assertNotNull($store->code);
    }
}
