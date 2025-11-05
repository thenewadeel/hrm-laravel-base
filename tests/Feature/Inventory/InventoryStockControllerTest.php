<?php

namespace Tests\Feature\Inventory;

use Tests\TestCase;
use App\Models\Inventory\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupInventory;
use Tests\Traits\SetupOrganization;

class InventoryStockControllerTest extends TestCase
{
    use RefreshDatabase, SetupOrganization, SetupInventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
    }

    #[Test]
    public function it_shows_stock_adjustment_form()
    {
        $response = $this->get(route('inventory.stock.adjustment'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.stock.adjustment');
        $response->assertViewHas('stores');
        $response->assertViewHas('items');
    }

    #[Test]
    public function it_processes_stock_adjustment()
    {
        $store = $this->store;
        $items = $this->createInventorySetup()['items']->take(2);

        // Add items to store first
        foreach ($items as $item) {
            $store->items()->attach($item->id, ['quantity' => 10]);
        }

        $adjustmentData = [
            'store_id' => $store->id,
            'notes' => 'Stock adjustment test',
            'adjustments' => [
                [
                    'item_id' => $items[0]->id,
                    'quantity' => 5, // Positive adjustment
                    'reason' => 'Found extra stock'
                ],
                [
                    'item_id' => $items[1]->id,
                    'quantity' => -3, // Negative adjustment
                    'reason' => 'Damaged goods'
                ]
            ]
        ];

        $response = $this->post(route('inventory.stock.process-adjustment'), $adjustmentData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_transactions', [
            'type' => 'adjustment',
            'status' => 'completed'
        ]);

        // Check if quantities were updated
        $storeItem1 = $store->items()->where('item_id', $items[0]->id)->first();
        $this->assertEquals(15, $storeItem1->pivot->quantity); // 10 + 5

        $storeItem2 = $store->items()->where('item_id', $items[1]->id)->first();
        $this->assertEquals(7, $storeItem2->pivot->quantity); // 10 - 3
    }

    #[Test]
    public function it_shows_stock_count_form()
    {
        $response = $this->get(route('inventory.stock.count'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.stock.count');
        $response->assertViewHas('stores');
    }

    #[Test]
    public function it_processes_stock_count()
    {
        $store = $this->store;
        $items = $this->createInventorySetup()['items']->take(2);

        // Add items to store with initial quantities
        foreach ($items as $item) {
            $store->items()->attach($item->id, ['quantity' => 10]);
        }

        $countData = [
            'store_id' => $store->id,
            'notes' => 'Physical count test',
            'counts' => [
                [
                    'item_id' => $items[0]->id,
                    'counted_quantity' => 12 // Should create +2 adjustment
                ],
                [
                    'item_id' => $items[1]->id,
                    'counted_quantity' => 8 // Should create -2 adjustment
                ]
            ]
        ];

        $response = $this->post(route('inventory.stock.process-count'), $countData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_transactions', [
            'type' => 'count',
            'status' => 'completed'
        ]);

        // Check if quantities were updated to counted values
        $storeItem1 = $store->items()->where('item_id', $items[0]->id)->first();
        $this->assertEquals(12, $storeItem1->pivot->quantity);

        $storeItem2 = $store->items()->where('item_id', $items[1]->id)->first();
        $this->assertEquals(8, $storeItem2->pivot->quantity);
    }

    #[Test]
    public function it_shows_stock_transfer_form()
    {
        $response = $this->get(route('inventory.stock.transfer'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.stock.transfer');
        $response->assertViewHas('stores');
        $response->assertViewHas('items');
    }

    #[Test]
    public function it_processes_stock_transfer()
    {
        $setup = $this->createMultipleStoresWithInventory();
        $fromStore = $setup['store'];
        $toStore = $setup['stores'][1];
        $items = $setup['items']->take(2);

        // Set initial quantities
        $fromStore->items()->sync([
            $items[0]->id => ['quantity' => 20],
            $items[1]->id => ['quantity' => 15]
        ]);

        $toStore->items()->sync([
            $items[0]->id => ['quantity' => 5],
            $items[1]->id => ['quantity' => 10]
        ]);

        $transferData = [
            'from_store_id' => $fromStore->id,
            'to_store_id' => $toStore->id,
            'notes' => 'Inter-store transfer',
            'transfers' => [
                [
                    'item_id' => $items[0]->id,
                    'quantity' => 5
                ],
                [
                    'item_id' => $items[1]->id,
                    'quantity' => 3
                ]
            ]
        ];

        $response = $this->post(route('inventory.stock.process-transfer'), $transferData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check if out transaction was created
        $this->assertDatabaseHas('inventory_transactions', [
            'store_id' => $fromStore->id,
            'type' => 'out',
            'reference' => 'TRF-OUT-'
        ]);

        // Check if in transaction was created
        $this->assertDatabaseHas('inventory_transactions', [
            'store_id' => $toStore->id,
            'type' => 'in',
            'reference' => 'TRF-IN-'
        ]);

        // Check quantity updates
        $fromStoreItem1 = $fromStore->items()->where('item_id', $items[0]->id)->first();
        $this->assertEquals(15, $fromStoreItem1->pivot->quantity); // 20 - 5

        $toStoreItem1 = $toStore->items()->where('item_id', $items[0]->id)->first();
        $this->assertEquals(10, $toStoreItem1->pivot->quantity); // 5 + 5
    }
}
