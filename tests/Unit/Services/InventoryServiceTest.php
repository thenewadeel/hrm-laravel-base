<?php

namespace Tests\Unit\Services;

use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use App\Models\Inventory\Transaction;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\SetupInventory;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase, SetupInventory;

    private InventoryService $inventoryService;
    private User $user;
    private Organization $organization;
    private OrganizationUnit $organizationUnit;
    private Store $store;
    private Item $item;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate', [
            // '--database' => 'sqlite', // Or your desired test database connection
            '--path' => 'database/migrations/inventory',
            // '--force' => true, // Essential for production environments, though not strictly needed for in-memory SQLite
        ]);

        $setup = $this->createInventorySetup();


        $this->inventoryService = app(InventoryService::class);

        $this->organization = $setup['organization']; //Organization::factory()->create();
        $this->organizationUnit = $setup['organization_unit']; //Organization::factory()->create();
        $this->user = $setup['user']; // User::factory()->create();
        $this->store = $setup['store']; // Store::factory()->create(['organization_id' => $this->organization->id]);
        $this->item = Item::factory()->create();


        // ✅ CRITICAL: Set the authenticated user for Gate system
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_create_a_store()
    {
        $storeData = [
            'name' => 'New Warehouse',
            'code' => 'WH002',
            'location' => 'Building B',
            // 'organization_id' => $this->organization->id
        ];
        $store = $this->inventoryService->createStore($storeData, $this->user);

        // dd($this->user);
        $this->assertInstanceOf(Store::class, $store);
        $this->assertEquals('New Warehouse', $store->name);
        $this->assertEquals('WH002', $store->code);
        // $this->assertEquals($this->organizationUnit->id, $store->organization_unit->id);
    }

    /** @test */
    public function it_can_update_store_inventory()
    {
        $this->inventoryService->updateStoreInventory($this->store, $this->item, 100, $this->user);

        $this->assertDatabaseHas('inventory_store_items', [
            'store_id' => $this->store->id,
            'item_id' => $this->item->id,
            'quantity' => 100
        ]);
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_can_get_store_stock_levels()
    {
        // Set up some inventory
        $this->store->items()->attach($this->item->id, ['quantity' => 15]);

        $lowStockItem = Item::factory()->create([
            'reorder_level' => 20
        ]);
        $this->store->items()->attach($lowStockItem->id, ['quantity' => 10]);

        $stockLevels = $this->inventoryService->getStoreStockLevels($this->store, $this->user);

        $this->assertEquals($this->store->id, $stockLevels['store']['id']);
        $this->assertEquals(2, $stockLevels['total_items']);
        $this->assertEquals(1, $stockLevels['low_stock_items']);
        $this->assertCount(2, $stockLevels['items']);
    }

    /** @test */
    public function it_prevents_modifying_finalized_transaction()
    {
        $transaction = Transaction::factory()->create([
            'store_id' => $this->store->id,
            'status' => 'finalized',
            'type' => 'incoming',
        ]);

        $items = [[
            'item_id' => $this->item->id,
            'quantity' => 10,
            'unit_price' => 10.00,
        ]];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot modify finalized or cancelled transaction');

        $this->inventoryService->addItemsToTransaction($transaction, $items, $this->user);
    }
}
