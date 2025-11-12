<?php

namespace Tests\Feature\Inventory;

use App\Models\Inventory\Item;
use Tests\TestCase;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupInventory;
use Tests\Traits\SetupOrganization;

class InventoryStoreControllerTest extends TestCase
{
    use RefreshDatabase, SetupOrganization, SetupInventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
        $this->actingAs($this->inventoryAdminUser);
    }

    #[Test]
    public function it_displays_stores_index()
    {
        $response = $this->get(route('inventory.stores.index'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.stores.index');
        $response->assertViewHas('stores');

        $stores = $response->viewData('stores');
        $this->assertCount(1, $stores); // 1 store from setup
    }

    #[Test]
    public function it_filters_stores_by_search_term()
    {
        Store::factory()->create([
            'organization_unit_id' => $this->organizationUnit->id,
            'name' => 'Special Warehouse',
            'code' => 'WH001'
        ]);

        $response = $this->get(route('inventory.stores.index', ['search' => 'Warehouse']));

        $stores = $response->viewData('stores');
        $this->assertTrue($stores->contains('name', 'Special Warehouse'));
    }

    #[Test]
    public function it_shows_store_creation_form()
    {
        $response = $this->get(route('inventory.stores.create'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.stores.form');
        $response->assertViewHas('organizationUnits');
    }

    #[Test]
    public function it_stores_new_store_with_valid_data()
    {
        $storeData = [
            'organization_unit_id' => $this->organizationUnit->id,
            'name' => 'New Warehouse',
            'code' => 'NW001',
            'location' => 'Building A',
            'description' => 'New storage location',
            'is_active' => true,
        ];

        $response = $this->post(route('inventory.stores.store'), $storeData);

        $response->assertRedirect(route('inventory.stores.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_stores', [
            'name' => 'New Warehouse',
            'code' => 'NW001'
        ]);
    }

    #[Test]
    public function it_shows_store_detail_page()
    {
        $store = $this->store;

        // Create some related data to avoid empty relationships
        $item = Item::factory()->create([
            'organization_id' => $this->organization->id
        ]);
        $store->items()->attach($item->id, [
            'quantity' => 5,
            'min_stock' => 10,
            'max_stock' => 100
        ]);

        $transaction = Transaction::factory()->create([
            'store_id' => $store->id
        ]);

        $response = $this->get(route('inventory.stores.show', $store));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.stores.show');
        $response->assertViewHas('store', $store);
        $response->assertViewHas('lowStockItems');
        $response->assertViewHas('recentTransactions');
    }

    #[Test]
    public function it_updates_store_with_valid_data()
    {
        $store = $this->store;
        $updateData = [
            'organization_unit_id' => $this->organizationUnit->id,
            'name' => 'Updated Store Name',
            'code' => $store->code,
            'location' => 'Updated Location',
            'description' => 'Updated description',
            'is_active' => false,
        ];

        $response = $this->put(route('inventory.stores.update', $store), $updateData);

        $response->assertRedirect(route('inventory.stores.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_stores', [
            'id' => $store->id,
            'name' => 'Updated Store Name',
            'location' => 'Updated Location'
        ]);
    }

    #[Test]
    public function it_deletes_store_without_transactions_or_items()
    {
        $store = Store::factory()->create([
            'organization_unit_id' => $this->organizationUnit->id
        ]);

        $response = $this->delete(route('inventory.stores.destroy', $store));

        $response->assertRedirect(route('inventory.stores.index'));
        $response->assertSessionHas('success');

        // For soft deletes, check that deleted_at is not null
        $this->assertSoftDeleted('inventory_stores', ['id' => $store->id]);
    }

    #[Test]
    public function it_prevents_deletion_of_store_with_transactions()
    {
        $setup = $this->createDraftTransactionWithItems();
        $store = $setup['store'];

        $response = $this->delete(route('inventory.stores.destroy', $store));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('inventory_stores', ['id' => $store->id]);
    }
}
