<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use Tests\Traits\SetupInventory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class StoreTest extends TestCase
{
    use RefreshDatabase, SetupInventory;

    #[Test]
    public function it_can_create_a_store()
    {
        $setup = $this->createInventorySetup();

        $storeData = [
            'name' => 'Main Warehouse',
            'code' => 'WH001',
            'location' => 'Building A',
            'description' => 'Primary storage facility',
            'organization_unit_id' => $setup['organization_unit']->id
        ];
        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/stores', $storeData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Main Warehouse',
                'code' => 'WH001'
            ]);

        // dd('asd');
        $this->assertDatabaseHas('inventory_stores', [
            'name' => 'Main Warehouse',
            'code' => 'WH001',
            'organization_unit_id' => $setup['organization_unit']->id
        ]);
    }

    #[Test]
    public function it_can_add_items_to_store()
    {
        $setup = $this->createInventorySetup();

        $response = $this->actingAs($setup['user'])
            ->postJson("/api/inventory/stores/{$setup['store']->id}/items", [
                'item_id' => $setup['items']->first()->id,
                'quantity' => 100
            ]);

        // dd($response->json());
        $response->assertStatus(201);
        $this->assertDatabaseHas('inventory_store_items', [
            'store_id' => $setup['store']->id,
            'item_id' => $setup['items']->first()->id,
            'quantity' => 100
        ]);
    }

    #[Test]
    public function it_can_retrieve_store_with_items()
    {
        $setup = $this->createInventorySetup();

        // Add items to store
        foreach ($setup['items']->take(3) as $item) {
            $setup['store']->items()->attach($item->id, ['quantity' => 50]);
        }

        $response = $this->actingAs($setup['user'])
            ->getJson("/api/inventory/stores/{$setup['store']->id}");
        // dd($response->json());
        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.items')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'code',
                    'items' => [
                        '*' => [
                            'id',
                            'name',
                            'pivot' => ['quantity']
                        ]
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_can_update_item_quantity_in_store()
    {
        $setup = $this->createInventorySetup();
        $item = $setup['items']->first();

        $setup['store']->items()->attach($item->id, ['quantity' => 50]);

        $response = $this->actingAs($setup['user'])
            ->putJson("/api/inventory/stores/{$setup['store']->id}/items/{$item->id}", [
                'quantity' => 75
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventory_store_items', [
            'store_id' => $setup['store']->id,
            'item_id' => $item->id,
            'quantity' => 75
        ]);
    }

    #[Test]
    public function it_can_list_all_stores_for_organization()
    {
        $setup = $this->createMultipleStoresWithInventory();

        $response = $this->actingAs($setup['user'])
            ->getJson("/api/inventory/stores?organization_id={$setup['organization']->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data') // Should have 3 stores
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'organization_id', // Now available through relationship
                        'organization_unit_id',
                        'items_count',
                    ]
                ]
            ]);
    }
    #[Test]
    public function it_can_use_organization_scope()
    {
        $setup = $this->createMultipleStoresWithInventory();

        // Test the scope directly
        $stores = Store::forOrganization($setup['organization']->id)->get();
        $this->assertCount(3, $stores);
    }

    #[Test]
    public function it_can_use_search_scope()
    {
        $setup = $this->createInventorySetup();

        $store = Store::factory()->create([
            'organization_unit_id' => $setup['organization_unit']->id,
            'name' => 'Main Warehouse',
            'code' => 'WH-MAIN'
        ]);

        $results = Store::search('Main')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Main Warehouse', $results->first()->name);

        $results = Store::search('WH-')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('WH-MAIN', $results->first()->code);
    }

    #[Test]
    public function it_can_use_active_scope()
    {
        $setup = $this->createInventorySetup();

        $inactiveStore = Store::factory()->create([
            'organization_unit_id' => $setup['organization_unit']->id,
            'is_active' => false
        ]);

        $activeStores = Store::active()->get();
        $inactiveStores = Store::active(false)->get();

        $this->assertTrue($activeStores->every->is_active);
        $this->assertFalse($inactiveStores->every->is_active);
    }
}
