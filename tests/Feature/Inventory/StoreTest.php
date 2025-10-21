<?php

namespace Tests\Feature\Inventory;

use Tests\Traits\SetupInventory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreTest extends TestCase
{
    use RefreshDatabase, SetupInventory;

    /** @test */
    public function it_can_create_a_store()
    {
        $setup = $this->createInventorySetup();

        $storeData = [
            'name' => 'Main Warehouse',
            'code' => 'WH001',
            'location' => 'Building A',
            'description' => 'Primary storage facility',
            'organization_id' => $setup['organization']->id
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/stores', $storeData);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Main Warehouse',
                'code' => 'WH001'
            ]);

        $this->assertDatabaseHas('inventory_stores', [
            'name' => 'Main Warehouse',
            'code' => 'WH001',
            'organization_id' => $setup['organization']->id
        ]);
    }

    /** @test */
    public function it_can_add_items_to_store()
    {
        $setup = $this->createInventorySetup();

        $response = $this->actingAs($setup['user'])
            ->postJson("/api/inventory/stores/{$setup['store']->id}/items", [
                'item_id' => $setup['items']->first()->id,
                'quantity' => 100
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('inventory_store_items', [
            'store_id' => $setup['store']->id,
            'item_id' => $setup['items']->first()->id,
            'quantity' => 100
        ]);
    }

    /** @test */
    public function it_can_retrieve_store_with_items()
    {
        $setup = $this->createInventorySetup();

        // Add items to store
        foreach ($setup['items']->take(3) as $item) {
            $setup['store']->items()->attach($item->id, ['quantity' => 50]);
        }

        $response = $this->actingAs($setup['user'])
            ->getJson("/api/inventory/stores/{$setup['store']->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'items')
            ->assertJsonStructure([
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
            ]);
    }

    /** @test */
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

    /** @test */
    public function it_can_list_all_stores_for_organization()
    {
        $setup = $this->createMultipleStoresWithInventory();

        $response = $this->actingAs($setup['user'])
            ->getJson("/api/inventory/stores?organization_id={$setup['organization']->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'items_count',
                        'total_quantity'
                    ]
                ]
            ]);
    }
}
