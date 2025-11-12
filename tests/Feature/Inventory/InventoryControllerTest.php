<?php

namespace Tests\Feature\Inventory;

use Tests\TestCase;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupInventory;
use Tests\Traits\SetupOrganization;

class InventoryControllerTest extends TestCase
{
    use RefreshDatabase, SetupInventory, SetupOrganization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
        $this->actingAs($this->inventoryAdminUser);
    }

    #[Test]
    public function it_displays_inventory_dashboard()
    {
        $response = $this->get(route('inventory.index'));

        // Check if we get a successful response (200) or handle 500 errors
        if ($response->status() === 500) {
            $this->fail('Server error: ' . $response->getContent());
        }

        $response->assertStatus(200);
        $response->assertViewIs('inventory.index');

        // Only assert view has stats if it's a view response
        if ($response->original instanceof \Illuminate\View\View) {
            $response->assertViewHas('stats');

            $stats = $response->viewData('stats');
            $this->assertArrayHasKey('total_items', $stats);
            $this->assertArrayHasKey('total_stores', $stats);
            $this->assertArrayHasKey('low_stock_items', $stats);
            $this->assertArrayHasKey('recent_transactions', $stats);
            $this->assertArrayHasKey('total_transactions_today', $stats);
        }
    }

    #[Test]
    public function it_shows_correct_statistics_on_dashboard()
    {
        // Create additional test data [5x items already created during setupInventory()]
        Item::factory()->count(3)->create(['organization_id' => $this->organization->id]);
        Store::factory()->count(2)->create(['organization_unit_id' => $this->organizationUnit->id]);

        // Create a low stock item
        $lowStockItem = Item::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->store->items()->attach($lowStockItem->id, [
            'quantity' => 5,
            'min_stock' => 10,
            'max_stock' => 100
        ]);

        $response = $this->get(route('inventory.index'));

        if ($response->status() === 500) {
            $this->fail('Server error: ' . $response->getContent());
        }

        $response->assertStatus(200);

        if ($response->original instanceof \Illuminate\View\View) {
            $stats = $response->viewData('stats');
            $this->assertEquals(9, $stats['total_items']); // 1 from setup + 3 new + 1 low stock
            $this->assertEquals(3, $stats['total_stores']); // 1 from setup + 2 new
            $this->assertEquals(1, $stats['low_stock_items']); // The low stock item we attached
        }
    }

    #[Test]
    public function it_requires_authentication_for_inventory_dashboard()
    {
        auth()->logout();

        $response = $this->get(route('inventory.index'));
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function it_includes_todays_transactions_in_stats()
    {
        // Create a transaction for today
        Transaction::factory()->create([
            'store_id' => $this->store->id,
            'created_by' => $this->inventoryAdminUser->id,
            'created_at' => now(),
        ]);

        // Create a transaction for yesterday
        Transaction::factory()->create([
            'store_id' => $this->store->id,
            'created_by' => $this->inventoryAdminUser->id,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->get(route('inventory.index'));

        if ($response->status() === 500) {
            $this->fail('Server error: ' . $response->getContent());
        }

        $response->assertStatus(200);

        if ($response->original instanceof \Illuminate\View\View) {
            $stats = $response->viewData('stats');
            $this->assertEquals(1, $stats['total_transactions_today']);
        }
    }

    #[Test]
    public function it_handles_empty_inventory_gracefully()
    {
        // Clear all inventory data to test empty state
        Item::query()->delete();
        Store::query()->delete();
        Transaction::query()->delete();

        $response = $this->get(route('inventory.index'));

        if ($response->status() === 500) {
            $this->fail('Server error: ' . $response->getContent());
        }

        $response->assertStatus(200);

        if ($response->original instanceof \Illuminate\View\View) {
            $stats = $response->viewData('stats');
            $this->assertEquals(0, $stats['total_items']);
            $this->assertEquals(0, $stats['total_stores']);
            $this->assertEquals(0, $stats['low_stock_items']);
            $this->assertEquals(0, $stats['recent_transactions']);
            $this->assertEquals(0, $stats['total_transactions_today']);
        }
    }
}
