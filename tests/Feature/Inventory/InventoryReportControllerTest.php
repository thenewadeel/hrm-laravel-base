<?php

namespace Tests\Feature\Inventory;

use Tests\TestCase;
use App\Models\Inventory\Item;
use App\Models\Inventory\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $lowStockItem = Item::factory()->create([
            'organization_id' => $this->organization->id,
            'quantity' => 5,
            'reorder_level' => 10
        ]);

        $response = $this->get(route('inventory.reports.low-stock'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.reports.low-stock');
        $response->assertViewHas('items');
        $response->assertViewHas('stores');

        $items = $response->viewData('items');
        $this->assertTrue($items->contains('id', $lowStockItem->id));
    }

    #[Test]
    public function it_filters_low_stock_by_store()
    {
        $otherStore = $this->createMultipleStoresWithInventory()['stores'][1];
        $lowStockItem = Item::factory()->create([
            'organization_id' => $this->organization->id,
            'quantity' => 5,
            'reorder_level' => 10
        ]);

        // Attach item to specific store
        $otherStore->items()->attach($lowStockItem->id, ['quantity' => 5]);

        $response = $this->get(route('inventory.reports.low-stock', [
            'store_id' => $otherStore->id
        ]));

        $items = $response->viewData('items');
        $this->assertTrue($items->contains('id', $lowStockItem->id));
    }

    #[Test]
    public function it_displays_stock_movement_report()
    {
        $setup = $this->createFinalizedTransaction();

        $response = $this->get(route('inventory.reports.movement'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.reports.movement');
        $response->assertViewHas('transactions');
        $response->assertViewHas('stores');

        $transactions = $response->viewData('transactions');
        $this->assertCount(1, $transactions);
    }

    #[Test]
    public function it_filters_movement_report_by_date_range()
    {
        $setup = $this->createFinalizedTransaction();
        $transaction = $setup['transaction'];

        $response = $this->get(route('inventory.reports.movement', [
            'start_date' => now()->subWeek()->format('Y-m-d'),
            'end_date' => now()->addWeek()->format('Y-m-d')
        ]));

        $transactions = $response->viewData('transactions');
        $this->assertCount(1, $transactions);
    }

    #[Test]
    public function it_displays_stock_levels_report()
    {
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

        $response = $this->get(route('inventory.reports.stock-levels', [
            'category' => 'Electronics'
        ]));

        $items = $response->viewData('items');
        $this->assertTrue($items->contains('category', 'Electronics'));
    }
}
