<?php

namespace Tests\Unit\Services;

use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use App\Models\Inventory\Transaction;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\SetupInventory;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupOrganization;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase, SetupOrganization, SetupInventory;

    private InventoryService $inventoryService;
    // private User $user;
    // private Organization $organization;
    // private OrganizationUnit $organizationUnit;
    // private Store $store;
    // private Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
    }

    #[Test]
    public function it_can_create_a_store()
    {
        $storeData = [
            'name' => 'New Warehouse',
            'code' => 'WH002',
            'location' => 'Building B',
            // 'organization_id' => $this->organization->id,
            'organization_unit_id' => $this->organizationUnit->id
        ];

        $store = $this->inventoryService->createStore($storeData, $this->user);

        $this->assertInstanceOf(Store::class, $store);
        $this->assertEquals('New Warehouse', $store->name);
        $this->assertEquals('WH002', $store->code);
        $this->assertEquals($this->organization->id, $store->organization->id);
        $this->assertEquals($this->organizationUnit->id, $store->organization_unit_id);
    }

    #[Test]
    public function it_can_update_store_inventory()
    {
        $this->inventoryService->updateStoreInventory($this->store, $this->item, 100, $this->user);

        $this->assertDatabaseHas('inventory_store_items', [
            'store_id' => $this->store->id,
            'item_id' => $this->item->id,
            'quantity' => 100
        ]);
    }

    #[Test]
    public function it_can_update_store_inventory_with_min_max_stock()
    {
        $this->inventoryService->updateStoreInventory(
            $this->store,
            $this->item,
            100,
            $this->user,
            10,  // min_stock
            200  // max_stock
        );

        $this->assertDatabaseHas('inventory_store_items', [
            'store_id' => $this->store->id,
            'item_id' => $this->item->id,
            'quantity' => 100,
            'min_stock' => 10,
            'max_stock' => 200
        ]);
    }

    #[Test]
    public function it_can_adjust_store_inventory()
    {
        // First set initial quantity
        $this->store->items()->attach($this->item->id, ['quantity' => 50]);

        // Adjust by +30
        $this->inventoryService->adjustStoreInventory($this->store, $this->item, 30, $this->user);

        $this->assertDatabaseHas('inventory_store_items', [
            'store_id' => $this->store->id,
            'item_id' => $this->item->id,
            'quantity' => 80
        ]);

        // Adjust by -20
        $this->inventoryService->adjustStoreInventory($this->store, $this->item, -20, $this->user);

        $this->assertDatabaseHas('inventory_store_items', [
            'store_id' => $this->store->id,
            'item_id' => $this->item->id,
            'quantity' => 60
        ]);
    }

    #[Test]
    public function it_can_create_and_finalize_transaction()
    {
        $transactionData = [
            'store_id' => $this->store->id,
            'type' => 'incoming',
            'reference' => 'TRX001',
            'transaction_date' => now(),
        ];

        // Create transaction
        $transaction = $this->inventoryService->createTransaction($transactionData, $this->user);
        $this->assertEquals('draft', $transaction->status);

        // Add items
        $items = [[
            'item_id' => $this->item->id,
            'quantity' => 25,
            'unit_price' => 15.50,
            'notes' => 'Test item'
        ]];

        $transaction = $this->inventoryService->addItemsToTransaction($transaction, $items, $this->user);
        $this->assertCount(1, $transaction->items);

        // Finalize transaction
        $transaction = $this->inventoryService->finalizeTransaction($transaction, $this->user);
        $this->assertEquals('finalized', $transaction->status);
        $this->assertNotNull($transaction->finalized_at);

        // Verify store inventory was updated
        $this->assertDatabaseHas('inventory_store_items', [
            'store_id' => $this->store->id,
            'item_id' => $this->item->id,
            'quantity' => 25
        ]);
    }

    #[Test]
    public function it_can_cancel_transaction()
    {
        $transactionData = [
            'store_id' => $this->store->id,
            'type' => 'incoming',
            'reference' => 'TRX002',
            'transaction_date' => now(),
        ];

        $transaction = $this->inventoryService->createTransaction($transactionData, $this->user);

        // Cancel transaction
        $transaction = $this->inventoryService->cancelTransaction($transaction, $this->user);

        $this->assertEquals('cancelled', $transaction->status);
    }

    #[Test]
    public function it_can_get_store_stock_levels()
    {
        // Set up some inventory
        $this->store->items()->attach($this->item->id, ['quantity' => 15]);

        $lowStockItem = Item::factory()->create([
            'organization_id' => $this->organization->id,
            // 'organization_unit_id' => $this->organizationUnit->id,
            'reorder_level' => 20
        ]);
        $this->store->items()->attach($lowStockItem->id, ['quantity' => 10]);

        $stockLevels = $this->inventoryService->getStoreStockLevels($this->store, $this->user);
        // dd($stockLevels);
        $this->assertEquals($this->store->id, $stockLevels['store']['id']);
        $this->assertEquals(2, $stockLevels['summary']['total_items']);
        $this->assertEquals(1, $stockLevels['summary']['low_stock_items']);
        $this->assertCount(2, $stockLevels['items']);
    }

    #[Test]
    public function it_can_get_item_availability()
    {
        // Create multiple stores and add items
        $store2 = Store::factory()->create([
            // 'organization_id' => $this->organization->id,
            'organization_unit_id' => $this->organizationUnit->id,
            'name' => 'Secondary Store',
            'code' => 'WH002'
        ]);

        $this->store->items()->attach($this->item->id, ['quantity' => 50]);
        $store2->items()->attach($this->item->id, ['quantity' => 25]);

        $availability = $this->inventoryService->getItemAvailability($this->item, $this->user);

        $this->assertEquals($this->item->id, $availability['item']['id']);
        $this->assertEquals(75, $availability['item']['total_quantity']);
        $this->assertEquals(2, $availability['summary']['stores_count']);
        $this->assertEquals(2, $availability['summary']['stores_with_stock']);
        $this->assertCount(2, $availability['availability']);
    }

    #[Test]
    public function it_prevents_modifying_finalized_transaction()
    {
        $transaction = Transaction::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'finalized',
            'type' => 'incoming',
            'created_by' => $this->user->id
        ]);

        $items = [[
            'item_id' => $this->item->id,
            'quantity' => 10,
            'unit_price' => 10.00,
            'notes' => 'Test'
        ]];

        // ✅ Change to expect AuthorizationException instead of generic Exception
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('This action is unauthorized.');

        $this->inventoryService->addItemsToTransaction($transaction, $items, $this->user);
    }

    #[Test]
    public function it_prevents_finalizing_transaction_with_no_items()
    {
        $transactionData = [
            'store_id' => $this->store->id,
            'type' => 'incoming',
            'reference' => 'TRX003',
            'transaction_date' => now(),
        ];

        $transaction = $this->inventoryService->createTransaction($transactionData, $this->user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot finalize transaction with no items');

        $this->inventoryService->finalizeTransaction($transaction, $this->user);
    }

    #[Test]
    public function it_prevents_cancelling_finalized_transaction()
    {
        $transaction = Transaction::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'finalized',
            'type' => 'incoming',
            'created_by' => $this->user->id
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot cancel finalized transaction');

        $this->inventoryService->cancelTransaction($transaction, $this->user);
    }

    #[Test]
    public function it_calculates_low_stock_correctly()
    {
        $lowStockItem = Item::factory()->create([
            'organization_id' => $this->organization->id,
            // 'organization_unit_id' => $this->organizationUnit->id,
            'reorder_level' => 20
        ]);

        $this->store->items()->attach($lowStockItem->id, ['quantity' => 15]); // Below reorder level

        $stockLevels = $this->inventoryService->getStoreStockLevels($this->store, $this->user);

        $lowStockItemData = collect($stockLevels['items'])->firstWhere('item_id', $lowStockItem->id);

        $this->assertTrue($lowStockItemData['is_low_stock']);
        $this->assertEquals('low_stock', $lowStockItemData['status']);
    }

    #[Test]
    public function it_calculates_overstock_correctly()
    {
        $itemWithMaxStock = Item::factory()->create([
            'organization_id' => $this->organization->id,
            // 'organization_unit_id' => $this->organizationUnit->id,
        ]);

        $this->store->items()->attach($itemWithMaxStock->id, [
            'quantity' => 150,
            'max_stock' => 100
        ]);

        $stockLevels = $this->inventoryService->getStoreStockLevels($this->store, $this->user);

        $overstockItemData = collect($stockLevels['items'])->firstWhere('item_id', $itemWithMaxStock->id);

        $this->assertTrue($overstockItemData['is_overstock']);
        $this->assertEquals('overstock', $overstockItemData['status']);
    }
}
