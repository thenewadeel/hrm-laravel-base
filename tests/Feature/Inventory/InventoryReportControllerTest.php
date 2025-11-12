<?php

namespace Tests\Feature\Inventory;

use Tests\TestCase;
use App\Models\Inventory\Item;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupInventory;
use Tests\Traits\SetupOrganization;

class InventoryReportControllerTest extends TestCase
{
    use RefreshDatabase, SetupOrganization, SetupInventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
        Auth::logout();
        $this->actingAs($this->inventoryAdminUser);
        // Force web middleware
        $this->withMiddleware(['web']);
    }

    #[Test]
    public function it_displays_reports_dashboard()
    {
        $response = $this->get(route('inventory.reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.reports.index');
    }

    #[Test]
    public function it_displays_low_stock_report()
    {
        // Create a low stock item in the current user's organization
        $lowStockItem = Item::factory()->create([
            'organization_id' => $this->organization->id,
            'reorder_level' => 10
        ]);

        // Attach to store with low quantity
        $this->store->items()->attach($lowStockItem->id, [
            'quantity' => 5, // Below reorder level of 10
            'min_stock' => 5,
            'max_stock' => 50
        ]);

        $response = $this->get(route('inventory.reports.low-stock'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.reports.low-stock');

        // Check for the correct view variables
        $response->assertViewHas('lowStockItems');
        $response->assertViewHas('outOfStockItems');
        $response->assertViewHas('reorderSuggestions');
        $response->assertViewHas('stores');
        $response->assertViewHas('categories');

        $lowStockItems = $response->viewData('lowStockItems');
        $this->assertTrue($lowStockItems->contains('id', $lowStockItem->id));
    }

    #[Test]
    public function it_filters_low_stock_by_store()
    {
        $otherStore = $this->createMultipleStoresWithInventory()['stores'][1];
        $lowStockItem = Item::factory()->create([
            'organization_id' => $this->organization->id,
            // 'quantity' => 5,
            'reorder_level' => 10
        ]);

        // Attach to store with low quantity
        $this->store->items()->attach($lowStockItem->id, [
            'quantity' => 5, // Below reorder level of 10
            'min_stock' => 5,
            'max_stock' => 50
        ]);

        // Attach item to specific store
        $otherStore->items()->attach($lowStockItem->id, ['quantity' => 5]);

        $response = $this->get(route('inventory.reports.low-stock', [
            'store_id' => $otherStore->id
        ]));

        $items = $response->viewData('lowStockItems');
        $this->assertTrue($items->contains('id', $lowStockItem->id));
    }
    #[Test]
    public function it_displays_stock_movement_report()
    {
        // Create a transaction with items
        $transaction = Transaction::factory()->create([
            'store_id' => $this->store->id,
            'type' => 'receipt',
            'status' => 'completed',
            'finalized_at' => now(),
        ]);

        $item = Item::factory()->create(['organization_id' => $this->organization->id]);

        // Create the transaction item (this was missing!)
        $transaction->items()->create([
            'item_id' => $item->id,
            'quantity' => 10,
            'unit_price' => 1000,
            'notes' => 'Test transaction',
        ]);

        $response = $this->get('/inventory/reports/movement');
        $response->assertStatus(200);

        // Debug what's returned
        // dd([
        //     'movements_count' => $response->viewData('movements')->count(),
        //     'top_received' => $response->viewData('topReceived'),
        //     'top_issued' => $response->viewData('topIssued'),
        //     'summary' => $response->viewData('summary'),
        // ]);
    }

    #[Test]
    public function it_filters_movement_report_by_date_range()
    {
        $transaction = Transaction::factory()->create([
            'store_id' => $this->store->id,
            'type' => 'receipt',
            'status' => 'completed',
            'finalized_at' => now(),
            'transaction_date' => now(),
        ]);

        $item = Item::factory()->create(['organization_id' => $this->organization->id]);
        $transaction->items()->create([
            'item_id' => $item->id,
            'quantity' => 10,
            'unit_price' => 1000,
        ]);
        $response = $this->get(route('inventory.reports.movement', [
            'start_date' => now()->subWeek()->format('Y-m-d'),
            'end_date' => now()->addWeek()->format('Y-m-d')
        ]));

        // dd(['transactions' => Transaction::with('items')->get()]);
        $response->assertStatus(200);

        $transactions = $response->viewData('movements');
        $this->assertGreaterThan(0, $transactions->count());
    }

    #[Test]
    public function it_displays_stock_levels_report()
    {
        $this->actingAs($this->inventoryAdminUser);
        // Ensure we have some items
        $items = $this->createInventorySetup()['items'];
        // dd([
        //     'org' => $this->organization,
        //     'store' => $this->store,
        //     'items' => Item::get()
        // ]);
        $response = $this->get(route('inventory.reports.stock-levels'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.reports.stock-levels');
        $response->assertViewHas('items');
        $response->assertViewHas('stores');
        $response->assertViewHas('categories');
    }

    #[Test]
    public function it_filters_stock_levels_by_category()
    {
        $electronicItem = Item::factory()->create([
            'organization_id' => $this->organization->id,
            'category' => 'Electronics'
        ]);

        // Attach to store
        $this->store->items()->attach($electronicItem->id, [
            'quantity' => 10,
            'min_stock' => 5,
            'max_stock' => 50
        ]);

        $response = $this->get(route('inventory.reports.stock-levels', [
            'category' => 'Electronics'
        ]));

        $response->assertStatus(200);

        $items = $response->viewData('items');
        $this->assertTrue($items->contains('category', 'Electronics'));
    }

    #[Test]
    public function it_handles_empty_reports_gracefully()
    {
        // Clear any existing data
        Transaction::query()->delete();
        Item::query()->delete();

        $response = $this->get(route('inventory.reports.low-stock'));
        $response->assertStatus(200);

        // Check that view variables exist even when empty
        $response->assertViewHas('lowStockItems');
        $response->assertViewHas('outOfStockItems');
        $response->assertViewHas('reorderSuggestions');

        $response = $this->get(route('inventory.reports.movement'));
        $response->assertStatus(200);

        $response = $this->get(route('inventory.reports.stock-levels'));
        $response->assertStatus(200);
    }
    // #[Test]
    // public function debug_403_errors()
    // {
    //     $response = $this->get(route('inventory.reports.movement'));
    //     $this->actingAs($this->inventoryAdminUser);

    //     if ($response->getStatusCode() === 403) {
    //         // Check if there's an authorization message
    //         $content = $response->getContent();
    //         // dd([
    //         //     'status' => $response->getStatusCode(),
    //         //     'content' => $content,
    //         //     'user_permissions' => $this->inventoryAdminUser->getAllPermissions(),
    //         //     'user_roles' => $this->inventoryAdminUser->getAllRoles(),
    //         // ]);
    //     }

    //     $response->assertStatus(200);
    // }

    // #[Test]
    // public function debug_403_detailed()
    // {
    //     // Check if there's a specific policy
    //     $user = $this->inventoryAdminUser;

    //     // dd([
    //     //     'user_has_reports_view' => $user->hasPermission('inventory.reports.view'),
    //     //     'user_can_view_reports' => $user->can('view inventory reports'), // Common gate name
    //     //     'user_can_view_any_reports' => $user->can('view any reports'),
    //     //     'policies_registered' => \Illuminate\Support\Facades\Gate::policies(),
    //     //     'route_middleware' => \Route::getRoutes()->getByName('inventory.reports.movement')?->gatherMiddleware(),
    //     // ]);
    // }
    // #[Test]
    // public function debug_transaction_items_schema()
    // {
    //     $columns = \Schema::getColumnListing('inventory_transaction_items');
    //     // dd([
    //     //     'transaction_items_columns' => $columns,
    //     //     'first_transaction_item' => \App\Models\Inventory\TransactionItem::first(),
    //     // ]);
    // }
}
