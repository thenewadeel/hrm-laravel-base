<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory\Item;
use Tests\Traits\SetupInventory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class ItemTest extends TestCase
{
    use RefreshDatabase, SetupInventory;

    #[Test]
    public function it_can_create_an_item()
    {
        $setup = $this->createUserWithInventoryPermissions();

        $itemData = [
            'name' => 'Laptop Dell XPS 13',
            'sku' => 'DLXPS13-2024',
            'description' => 'High-performance business laptop',
            'category' => 'electronics',
            'unit' => 'pcs',
            'organization_id' => $setup['organization']->id,
            // 'organization_unit_id' => $setup['orgUnit']->id
        ];

        $response = $this->actingAs($setup['user'])
            ->postJson('/api/inventory/items', $itemData);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [ // ✅ Now check inside 'data' key
                    'name' => 'Laptop Dell XPS 13',
                    'sku' => 'DLXPS13-2024',
                    'category' => 'electronics'
                ]
            ]);

        $this->assertDatabaseHas('inventory_items', [
            'sku' => 'DLXPS13-2024',
            'organization_id' => $setup['organization']->id
        ]);
    }


    #[Test]
    public function it_can_search_items_by_name_or_sku()
    {
        $setup = $this->createUserWithInventoryPermissions();

        // Create specific items for searching
        $searchableItems = [
            ['name' => 'Wireless Mouse', 'sku' => 'WM-001', 'organization_id' => $setup['organization']->id],
            ['name' => 'Gaming Keyboard', 'sku' => 'GK-002', 'organization_id' => $setup['organization']->id],
            ['name' => 'Wireless Headphones', 'sku' => 'WH-003', 'organization_id' => $setup['organization']->id],
            ['name' => 'Wired Mouse', 'sku' => 'WM-004', 'organization_id' => $setup['organization']->id],
        ];

        foreach ($searchableItems as $itemData) {
            \App\Models\Inventory\Item::factory()->create($itemData);
        }

        // Test search by name
        $response = $this->actingAs($setup['user'])
            ->getJson('/api/inventory/items?search=Wireless');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data') // ✅ Now 'data' key exists with pagination
            ->assertJsonFragment(['name' => 'Wireless Mouse'])
            ->assertJsonFragment(['name' => 'Wireless Headphones'])
            ->assertJsonMissing(['name' => 'Gaming Keyboard']);

        // Test search by SKU
        $response = $this->actingAs($setup['user'])
            ->getJson('/api/inventory/items?search=WM-');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data') // ✅ Now 'data' key exists
            ->assertJsonFragment(['sku' => 'WM-001'])
            ->assertJsonFragment(['sku' => 'WM-004']);
    }

    #[Test]
    public function it_can_filter_items_by_category()
    {
        $setup = $this->createUserWithInventoryPermissions();

        $items = [
            ['name' => 'Laptop', 'category' => 'electronics', 'organization_id' => $setup['organization']->id],
            ['name' => 'Office Chair', 'category' => 'furniture', 'organization_id' => $setup['organization']->id],
            ['name' => 'Monitor', 'category' => 'electronics', 'organization_id' => $setup['organization']->id],
            ['name' => 'Notebook', 'category' => 'stationery', 'organization_id' => $setup['organization']->id],
        ];

        foreach ($items as $itemData) {
            \App\Models\Inventory\Item::factory()->create($itemData);
        }

        $response = $this->actingAs($setup['user'])
            ->getJson('/api/inventory/items?category=electronics');
        // dd($response->json());
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['name' => 'Laptop'])
            ->assertJsonFragment(['name' => 'Monitor'])
            ->assertJsonMissing(['name' => 'Office Chair']);
    }

    #[Test]
    public function it_can_sort_items()
    {
        $setup = $this->createUserWithInventoryPermissions();

        $items = [
            ['name' => 'Zebra Item', 'sku' => 'Z-001', 'organization_id' => $setup['organization']->id],
            ['name' => 'Alpha Item', 'sku' => 'A-001', 'organization_id' => $setup['organization']->id],
            ['name' => 'Beta Item', 'sku' => 'B-001', 'organization_id' => $setup['organization']->id],
        ];

        foreach ($items as $itemData) {
            \App\Models\Inventory\Item::factory()->create($itemData);
        }

        // Test ascending sort by name
        $response = $this->actingAs($setup['user'])
            ->getJson('/api/inventory/items?sort_field=name&sort_direction=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        // dd($data);
        $this->assertEquals('Alpha Item', $data[0]['name']);
        $this->assertEquals('Beta Item', $data[1]['name']);
        $this->assertEquals('Zebra Item', $data[2]['name']);

        // Test descending sort by name
        $response = $this->actingAs($setup['user'])
            ->getJson('/api/inventory/items?sort_field=name&sort_direction=desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Zebra Item', $data[0]['name']);
        $this->assertEquals('Beta Item', $data[1]['name']);
        $this->assertEquals('Alpha Item', $data[2]['name']);
    }

    #[Test]
    public function it_returns_only_items_from_users_organization()
    {
        $setupA = $this->createUserWithInventoryPermissions();
        $setupB = $this->createInventorySetup(); // Different organization

        // Create items in both organizations
        $itemA = \App\Models\Inventory\Item::factory()->create([
            'name' => 'Org A Item',
            'organization_id' => $setupA['organization']->id
        ]);

        $itemB = \App\Models\Inventory\Item::factory()->create([
            'name' => 'Org B Item',
            'organization_id' => $setupB['organization']->id
        ]);

        // User from Org A should only see Org A items
        $response = $this->actingAs($setupA['user'])
            ->getJson('/api/inventory/items');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') // Only the item from their organization
            ->assertJsonFragment(['name' => 'Org A Item'])
            ->assertJsonMissing(['name' => 'Org B Item']);
    }

    #[Test]
    public function it_can_get_item_availability_across_stores()
    {
        $setup = $this->createUserWithInventoryPermissions();

        // Create multiple stores
        $stores = [
            $setup['store'],
            \App\Models\Inventory\Store::factory()->create([
                // 'organization_id' => $setup['organization']->id,
                'organization_unit_id' => $setup['organization_unit']->id,
                'name' => 'Warehouse A',
                'code' => 'WH-A'
            ]),
            \App\Models\Inventory\Store::factory()->create([
                // 'organization_id' => $setup['organization']->id,
                'organization_unit_id' => $setup['organization_unit']->id,
                'name' => 'Warehouse B',
                'code' => 'WH-B'
            ]),
        ];

        $item = Item::factory()->create(['organization_id' => $setup['organization']->id]); //$setup['items']->first();

        // Add item to stores with different quantities
        $stores[0]->items()->attach($item->id, ['quantity' => 50]);
        $stores[1]->items()->attach($item->id, ['quantity' => 25]);
        $stores[2]->items()->attach($item->id, ['quantity' => 0]); // Out of stock

        $response = $this->actingAs($setup['user'])
            ->getJson("/api/inventory/items/{$item->id}/availability");
        // dd($response->json());
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'item' => [
                        'id',
                        'name',
                        'sku',
                        'total_quantity',
                        'is_low_stock_overall',
                        'is_out_of_stock_overall',
                    ],
                    'availability' => [
                        '*' => [
                            'store_id',
                            'store_name',
                            'store_code',
                            'quantity',
                            'is_low_stock',
                            'is_out_of_stock',
                        ]
                    ],
                    'summary' => [
                        'total_quantity',
                        'stores_count',
                        'stores_with_stock',
                        'low_stock_stores',
                        'out_of_stock_stores',
                    ]
                ]
            ])
            ->assertJsonPath('data.summary.total_quantity', 75)
            ->assertJsonPath('data.summary.stores_count', 3)
            ->assertJsonPath('data.summary.stores_with_stock', 2)
            ->assertJsonPath('data.summary.out_of_stock_stores', 1);
    }

    #[Test]
    public function it_shows_low_stock_status_correctly()
    {
        $setup = $this->createUserWithInventoryPermissions();

        $item = \App\Models\Inventory\Item::factory()->create([
            'organization_id' => $setup['organization']->id,
            // 'organization_unit_id' => $setup['orgUnit']->id,
            'reorder_level' => 20
        ]);

        // Add item with low stock
        $setup['store']->items()->attach($item->id, ['quantity' => 15]); // Below reorder level

        $response = $this->actingAs($setup['user'])
            ->getJson("/api/inventory/items/{$item->id}/availability");

        $response->assertStatus(200)
            ->assertJsonPath('data.item.is_low_stock_overall', true)
            ->assertJsonPath('data.availability.0.is_low_stock', true)
            ->assertJsonPath('data.availability.0.quantity', 15);
    }

    #[Test]
    public function it_returns_403_for_unauthorized_access()
    {
        $setupA = $this->createUserWithInventoryPermissions();
        $setupB = $this->createInventorySetup(); // Different organization

        $itemB = Item::factory()->create([
            'organization_id' => $setupB['organization']->id,
            // 'organization_unit_id' => $setupB['orgUnit']->id,
        ]);

        // User from Org A tries to access Org B's item availability
        $response = $this->actingAs($setupA['user'])
            ->getJson("/api/inventory/items/{$itemB->id}/availability");

        $response->assertStatus(403);
    }

    #[Test]
    public function it_handles_item_with_no_stores()
    {
        $setup = $this->createUserWithInventoryPermissions();

        $item = Item::factory()->create([
            'organization_id' => $setup['organization']->id,
            // 'organization_unit_id' => $setupB['orgUnit']->id,
        ]);;
        // Don't attach to any stores

        $response = $this->actingAs($setup['user'])
            ->getJson("/api/inventory/items/{$item->id}/availability");

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_quantity', 0)
            ->assertJsonPath('data.summary.stores_count', 0)
            ->assertJsonPath('data.summary.stores_with_stock', 0)
            ->assertJsonPath('data.item.is_out_of_stock_overall', true);
    }
}
