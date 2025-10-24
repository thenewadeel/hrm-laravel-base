<?php
// tests/Feature/SetupStoreTest.php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Inventory\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SetupStoreTest extends TestCase
{
    use RefreshDatabase;

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
        $this->assertDatabaseHas('inventory_stores', [
            'name' => 'Main Store',
            'organization_id' => $organization->id,
            'code' => 'STORE001',
        ]);
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

        $this->assertDatabaseHas('inventory_stores', [
            'name' => 'Main Store',
            'organization_id' => $organization->id,
        ]);

        $store = Store::first();
        $this->assertNotNull($store->code);
    }
}
