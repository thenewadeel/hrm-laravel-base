<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupInventory;

class InventoryScopeTest extends TestCase
{
    use RefreshDatabase, SetupInventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
    }

    #[Test]
    public function it_finds_low_stock_items_using_scope()
    {
        $store = $this->store;
        $items = $this->createInventorySetup()['items']->take(2);

        // Set up items with different stock levels - use sync to avoid duplicates
        $store->items()->sync([
            $items[0]->id => [
                'quantity' => 5,
                'min_stock' => 10,
                'max_stock' => 100
            ],
            $items[1]->id => [
                'quantity' => 15,
                'min_stock' => 10,
                'max_stock' => 100
            ]
        ]);

        $lowStockItems = Item::lowInStock()->get();

        $this->assertCount(1, $lowStockItems);
        $this->assertEquals($items[0]->id, $lowStockItems->first()->id);
    }

    #[Test]
    public function it_finds_low_stock_items_in_specific_store()
    {
        $setup = $this->createInventorySetup();
        $store1 = $setup['store'];
        $store2 = Store::factory()->create([
            'organization_unit_id' => $setup['organization_unit']->id
        ]);

        $items = $setup['items']->take(2);

        // Store 1 has low stock
        $store1->items()->sync([
            $items[0]->id => [
                'quantity' => 5,
                'min_stock' => 10,
                'max_stock' => 100
            ]
        ]);

        // Store 2 has adequate stock with different items to avoid unique constraint
        $store2Items = Item::factory()->count(2)->create([
            'organization_id' => $setup['organization']->id
        ]);

        $store2->items()->sync([
            $store2Items[0]->id => [
                'quantity' => 15,
                'min_stock' => 10,
                'max_stock' => 100
            ]
        ]);

        $lowStockItems = Item::lowInStockInStore($store1->id)->get();

        $this->assertCount(1, $lowStockItems);
        $this->assertEquals($items[0]->id, $lowStockItems->first()->id);
    }

    #[Test]
    public function it_finds_out_of_stock_items_using_scope()
    {
        $store = $this->store;
        $items = $this->createInventorySetup()['items']->take(2);

        $store->items()->sync([
            $items[0]->id => [
                'quantity' => 0,
                'min_stock' => 10,
                'max_stock' => 100
            ],
            $items[1]->id => [
                'quantity' => 5,
                'min_stock' => 10,
                'max_stock' => 100
            ]
        ]);

        $outOfStockItems = Item::outOfStock()->get();

        $this->assertCount(1, $outOfStockItems);
        $this->assertEquals($items[0]->id, $outOfStockItems->first()->id);
    }

    #[Test]
    public function store_can_retrieve_its_low_stock_items()
    {
        $store = $this->store;
        $items = $this->createInventorySetup()['items']->take(3);

        // Set up different stock levels using sync
        $store->items()->sync([
            $items[0]->id => ['quantity' => 5, 'min_stock' => 10, 'max_stock' => 100],
            $items[1]->id => ['quantity' => 15, 'min_stock' => 10, 'max_stock' => 100],
            $items[2]->id => ['quantity' => 3, 'min_stock' => 10, 'max_stock' => 100]
        ]);

        $lowStockItems = $store->lowStockItems()->get();

        $this->assertCount(2, $lowStockItems);
        $this->assertTrue($lowStockItems->contains('id', $items[0]->id));
        $this->assertTrue($lowStockItems->contains('id', $items[2]->id));
        $this->assertFalse($lowStockItems->contains('id', $items[1]->id));
    }

    #[Test]
    public function store_can_retrieve_its_out_of_stock_items()
    {
        $store = $this->store;
        $items = $this->createInventorySetup()['items']->take(2);

        $store->items()->sync([
            $items[0]->id => ['quantity' => 0, 'min_stock' => 10, 'max_stock' => 100],
            $items[1]->id => ['quantity' => 5, 'min_stock' => 10, 'max_stock' => 100]
        ]);

        $outOfStockItems = $store->outOfStockItems()->get();

        $this->assertCount(1, $outOfStockItems);
        $this->assertEquals($items[0]->id, $outOfStockItems->first()->id);
    }

    #[Test]
    public function it_calculates_stock_statistics_for_store()
    {
        $store = $this->store;
        $items = $this->createInventorySetup()['items']->take(4);

        $store->items()->sync([
            $items[0]->id => ['quantity' => 5, 'min_stock' => 10, 'max_stock' => 100],  // low stock (5 < 10)
            $items[1]->id => ['quantity' => 0, 'min_stock' => 10, 'max_stock' => 100],  // out of stock (0 <= 0)
            $items[2]->id => ['quantity' => 15, 'min_stock' => 10, 'max_stock' => 100], // adequate stock (15 >= 10)
            $items[3]->id => ['quantity' => 2, 'min_stock' => 10, 'max_stock' => 100],  // low stock (2 < 10)
        ]);

        // Use the reliable method to avoid relationship overlap issues
        $stats = $store->stock_stats_reliable;

        $this->assertEquals(4, $stats['total_items']);
        $this->assertEquals(2, $stats['low_stock_items']); // Items 0 and 3
        $this->assertEquals(1, $stats['out_of_stock_items']); // Item 1
        $this->assertEquals(1, $stats['adequate_stock_items']); // Item 2

        // Verify the math
        $this->assertEquals(4, $stats['verification_total']);
    }

    #[Test]
    public function it_properly_categorizes_stock_levels()
    {
        $store = $this->store;
        $items = $this->createInventorySetup()['items']->take(5);

        $store->items()->sync([
            $items[0]->id => ['quantity' => 5, 'min_stock' => 10],  // Low stock
            $items[1]->id => ['quantity' => 0, 'min_stock' => 10],  // Out of stock
            $items[2]->id => ['quantity' => 15, 'min_stock' => 10], // Adequate
            $items[3]->id => ['quantity' => 10, 'min_stock' => 10], // Adequate (equal to min)
            $items[4]->id => ['quantity' => 8, 'min_stock' => 10],  // Low stock
        ]);

        $stats = $store->stock_stats_reliable;

        $this->assertEquals(5, $stats['total_items']);
        $this->assertEquals(2, $stats['low_stock_items']); // Items 0 and 4
        $this->assertEquals(1, $stats['out_of_stock_items']); // Item 1
        $this->assertEquals(2, $stats['adequate_stock_items']); // Items 2 and 3
    }
}
