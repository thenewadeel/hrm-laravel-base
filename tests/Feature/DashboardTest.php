<?php
// tests/Feature/SetupWizardTest.php

namespace Tests\Feature;

use Tests\Traits\SetupInventory;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use App\Models\Inventory\Transaction;
use App\Roles\InventoryRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tests\Traits\SetupOrganization;

class DashboardTest extends TestCase
{
    use RefreshDatabase, SetupOrganization, SetupInventory;
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
        // $this->actingAs($this->inventoryAdminUser);
    }
    #[Test]
    public function it_redirects_to_setup_for_users_without_organization()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertRedirect('/setup');
    }

    #[Test]
    public function it_shows_dashboard_for_users_with_organization()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => OrganizationUnit::factory()->create([
                'organization_id' => $organization->id
            ])->id
        ]);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('Dashboard')
            ->assertSee($organization->name);
    }

    #[Test]
    public function it_shows_dashboard_sections()
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => OrganizationUnit::factory()->create([
                'organization_id' => $organization->id
            ])->id
        ]);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('Stores Overview') // Actual section title
            ->assertSee('Low Stock Alerts') // Actual section title
            ->assertSee('Stores') // Stats card
            ->assertSee('Total Items') // Stats card
            ->assertSee('Low Stock Items') // Stats card
            ->assertSee('Recent Transactions'); // Stats card
    }

    #[Test]
    public function it_shows_store_inventory_summary()
    {
        $user = $this->user;
        $organization = $this->organization;
        $orgUnit = $this->organizationUnit;

        $store = $this->store;
        $items = Item::factory()->count(3)->create(['organization_id' => $organization->id]);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('Stores Overview')
            ->assertSee($store->name)
            ->assertSee('Total Items')
            ->assertSee('3'); // item count from stats
    }

    #[Test]
    public function it_shows_low_stock_alerts()
    {
        // $user = $this->user; //User::factory()->create();
        // $organization = $this->organization; //Organization::factory()->create();

        // // Setup user with organization and permissions
        // // $user->current_organization_id = $organization->id;
        // // $user->save();
        // // $user->addRole(InventoryRoles::INVENTORY_ADMIN, $organization);

        // // Create store and low stock item
        // $store = $this->store;
        // // Store::factory()->create([
        // //     'organization_id' => $organization->id,
        // // ]);
        Auth::logout();
        $user = User::factory()->create();
        $organization = Organization::factory()->create();
        $unit = OrganizationUnit::factory()->create([
            'organization_id' => $organization->id
        ]);
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin']),
            'organization_unit_id' => $unit->id
        ]);
        $store = Store::factory()->create([
            'organization_unit_id' => $organization->id,
        ]);

        $lowStockItem = Item::factory()->create([
            'organization_id' => $organization->id,
            'reorder_level' => 10,
        ]);

        // Create store item with low quantity
        $store->items()->attach($lowStockItem->id, [
            'quantity' => 5, // Below reorder level
            'min_stock' => 10,
        ]);
        // dd([$store->items]);
        $response = $this->actingAs($user)
            ->get('/dashboard');
        // dd($response);
        $response->assertStatus(200)
            ->assertSee('Low Stock Alerts')
            ->assertSee($lowStockItem->name);
    }

    #[Test]
    public function it_shows_recent_transactions()
    {
        $user = $this->user;
        $organization = $this->organization;
        $orgUnit = $this->organizationUnit;

        $store = $this->store;
        $transactions = Transaction::factory()->count(2)->create([
            'store_id' => $store->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('Recent Transactions')
            ->assertSee($transactions->first()->reference);
    }

    #[Test]
    public function it_shows_quick_action_buttons()
    {
        $user = $this->user;
        $organization = $this->organization;
        $orgUnit = $this->organizationUnit;

        $store = $this->store;
        $items = Item::factory()->count(3)->create(['organization_id' => $organization->id]);


        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('Add Store')
            ->assertSee('New Transaction')
            ->assertSee('Add Item');
    }

    #[Test]
    public function it_shows_stats_cards()
    {
        $user = $this->user;
        $organization = $this->organization;
        $orgUnit = $this->organizationUnit;

        $store = $this->store;
        $items = Item::factory()->count(2)->create(['organization_id' => $organization->id]);


        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('Stores')
            ->assertSee('Total Items')
            ->assertSee('Low Stock Items')
            ->assertSee('Recent Transactions');
    }

    #[Test]
    public function it_shows_empty_state_for_no_stores()
    {
        $user = $this->user;
        $organization = $this->organization;
        $orgUnit = $this->organizationUnit;

        $orgUnit->stores()->each(fn($store) => $store->delete());
        // No stores created

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('No stores yet')
            ->assertSee('Add your first store'); // Fixed: removed extra space
    }


    #[Test]
    public function it_shows_all_stocked_message_when_no_low_stock()
    {
        $user = $this->user;
        $organization = $this->organization;
        $orgUnit = $this->organizationUnit;

        $store = $this->store;
        // Create item and attach with sufficient quantity
        $item = Item::factory()->create([
            'organization_id' => $organization->id,
            'reorder_level' => 5,
        ]);

        // Attach item to store with sufficient quantity
        $item->stores()->attach($store->id, [
            'quantity' => 20, // Well above reorder level
            'min_stock' => 5,
            'max_stock' => 50,
        ]);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('All items are well stocked');
    }

    #[Test]
    public function it_shows_store_with_item_counts()
    {
        $user = $this->user;
        $organization = $this->organization;
        $orgUnit = $this->organizationUnit;

        $store = $this->store;
        $items = Item::factory()->count(3)->create(['organization_id' => $organization->id]);

        // Attach items to store
        foreach ($items as $item) {
            $item->stores()->attach($store->id, [
                'quantity' => 10,
                'min_stock' => 5,
                'max_stock' => 50,
            ]);
        }

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('Stores Overview')
            ->assertSee($store->name)
            ->assertSee('3 items'); // Should show the item count
    }

    // Remove the role-based test for now since we don't have that logic implemented
    // #[Test]
    // public function it_shows_different_content_based_on_user_roles()
    // {
    //     // This test requires role-based UI logic that we haven't implemented yet
    // }
}
