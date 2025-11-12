<?php

namespace Tests\Feature\Inventory;

use Tests\TestCase;
use App\Models\Inventory\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupInventory;
use Tests\Traits\SetupOrganization;

class InventoryItemControllerTest extends TestCase
{
    use RefreshDatabase, SetupOrganization, SetupInventory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
    }

    #[Test]
    public function it_displays_items_index()
    {
        $response = $this->get(route('inventory.items.index'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.items.index');
        $response->assertViewHas('items');
        $response->assertViewHas('categories');

        $items = $response->viewData('items');
        $this->assertCount(5, $items);
    }

    #[Test]
    public function it_filters_items_by_search_term()
    {
        $specificItem = Item::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Special Test Item',
            'sku' => 'UNIQUE123'
        ]);

        $response = $this->get(route('inventory.items.index', ['search' => 'Special Test']));

        $items = $response->viewData('items');
        $this->assertTrue($items->contains('name', 'Special Test Item'));
    }

    #[Test]
    public function it_shows_item_creation_form()
    {
        $response = $this->get(route('inventory.items.create'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.items.form');
        $response->assertViewHas('organizations');
    }

    #[Test]
    public function it_stores_new_item_with_valid_data()
    {
        $itemData = [
            'organization_id' => $this->organization->id,
            'name' => 'New Test Item',
            'sku' => 'NEWSKU123',
            'description' => 'Test description',
            'category' => 'Test Category',
            'unit' => 'pcs',
            'cost_price' => 1000,
            'selling_price' => 1500,
            'reorder_level' => 10,
            'is_active' => true,
        ];

        $response = $this->post(route('inventory.items.store'), $itemData);

        $response->assertRedirect(route('inventory.items.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_items', [
            'name' => 'New Test Item',
            'sku' => 'NEWSKU123'
        ]);
    }

    #[Test]
    public function it_validates_required_fields_when_storing_item()
    {
        $response = $this->post(route('inventory.items.store'), []);

        $response->assertSessionHasErrors(['organization_id', 'name', 'sku', 'unit']);
    }
}
