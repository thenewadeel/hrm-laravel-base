<?php

namespace Tests\Feature\Inventory;

use Tests\Traits\SetupInventory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemTest extends TestCase
{
    use RefreshDatabase, SetupInventory;

    /** @test */
    public function it_can_create_an_item()
    {
        $setup = $this->createInventorySetup();

        $itemData = [
            'name' => 'Laptop Dell XPS 13',
            'sku' => 'DLXPS13-2024',
            'description' => 'High-performance business laptop',
            'category' => 'electronics',
            'unit' => 'pcs',
            'organization_id' => $setup['organization']->id
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/items', $itemData);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'Laptop Dell XPS 13',
                'sku' => 'DLXPS13-2024',
                'category' => 'electronics'
            ]);

        $this->assertDatabaseHas('inventory_items', [
            'sku' => 'DLXPS13-2024',
            'organization_id' => $setup['organization']->id
        ]);
    }

    /** @test */
    public function it_can_search_items_by_name_or_sku()
    {
        $setup = $this->createInventorySetup();

        // Create specific items for searching
        $searchableItems = [
            ['name' => 'Wireless Mouse', 'sku' => 'WM-001'],
            ['name' => 'Gaming Keyboard', 'sku' => 'GK-002'],
        ];

        foreach ($searchableItems as $itemData) {
            \App\Models\Inventory\Item::factory()->create([
                ...$itemData,
                'organization_id' => $setup['organization']->id
            ]);
        }

        $response = $this->actingAs($setup['user'])
            ->getJson('/api/inventory/items?search=Wireless');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Wireless Mouse');
    }

    /** @test */
    public function it_can_get_item_availability_across_stores()
    {
        $setup = $this->createMultipleStoresWithInventory();
        $item = $setup['items']->first();

        $response = $this->actingAs($setup['user'])
            ->getJson("/api/inventory/items/{$item->id}/availability");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'item',
                'stores' => [
                    '*' => [
                        'store_id',
                        'store_name',
                        'quantity'
                    ]
                ],
                'total_quantity'
            ]);
    }
}
