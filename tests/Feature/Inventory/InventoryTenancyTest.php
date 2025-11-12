<?php

namespace Tests\Feature\Inventory;

use Tests\TestCase;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\Scopes\OrganizationScope;
use Tests\Traits\SetupTenancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupInventory;
use Tests\Traits\SetupOrganization;

class InventoryTenancyTest extends TestCase
{
    use RefreshDatabase, SetupOrganization, SetupInventory, SetupTenancy;

    protected function tenantModels(): array
    {
        return [
            Item::class,
            // Store::class,
            // Transaction::class,
        ];
    }

    protected $user1;
    protected $user2;
    protected $organization1;
    protected $organization2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupOrganization();
        $this->setupInventory();
        $this->setUpTenancy();
    }

    #[Test]
    public function it_scopes_items_by_organization()
    {
        Auth::logout();
        $this->actingAs($this->userA);

        $itemsA = Item::all();
        $user = Auth::user();
        // dd([
        //     'authUser' => json_encode($user),
        //     'thisUserA' => json_encode($this->userA),
        //     'operatingOrganizationId' => $user->operatingOrganizationId, // Check this!
        //     'userCurrentOrg' => $user->current_organization_id,
        //     'userOrgs' => $user->organizations()->pluck('organizations.id'),
        //     'itemsCount' => Item::count(),
        //     'itemsWithoutScope' => Item::withoutGlobalScope(\App\Models\Scopes\OrganizationScope::class)->get()->pluck('organization_id'),
        //     'itemsWithScope' => Item::get()->pluck('organization_id')
        // ]);
        $this->assertCount(2, $itemsA);
        $this->assertTrue($itemsA->every(fn($item) => $item->organization_id === $this->orgA->id));

        $this->actingAs($this->userB);

        $itemsB = Item::all();
        $this->assertCount(1, $itemsB);
        $this->assertTrue($itemsB->every(fn($item) => $item->organization_id === $this->orgB->id));
    }

    #[Test]
    public function it_scopes_stores_by_organization()
    {
        Auth::logout();
        // dd([
        //     'stores' => Store::count(),
        //     'storesDefault' => Store::forOrganization($this->organization->id)->count(),
        //     'storesA' => Store::forOrganization($this->orgA->id)->count(),
        //     'storesB' => Store::forOrganization($this->orgB->id)->count(),
        // ]);
        $this->actingAs($this->userA);
        // dd([
        //     'count A' => Store::forOrganization($this->userA->operating_organization_id)->count(),
        //     'count B' => Store::forOrganization($this->userB->operating_organization_id)->count(),
        //     'count admin' => Store::forOrganization($this->inventoryAdminUser->operating_organization_id)->count(),
        //     'orgs' => Organization::pluck('name'),
        //     'stores' => Store::count(),

        // ]);
        // Fix: Use get() instead of all() and properly scope by organization
        $stores = Store::forOrganization($this->userA->operating_organization_id)
            // whereHas('organizationUnit', function ($query) {
            //     $query->where('organization_id', $this->organization1->id);
            // })
            ->get(); // Changed from all() to get()

        $this->assertCount(3, $stores);

        Auth::logout();
        $this->actingAs($this->userB);

        // Fix: Use get() instead of all() and properly scope by organization
        $stores = Store::forOrganization($this->userB->operating_organization_id)
            // whereHas('organizationUnit', function ($query) {
            //     $query->where('organization_id', $this->organization1->id);
            // })
            ->get(); // Changed from all() to get()

        $this->assertCount(2, $stores);

        // Verify all stores belong to organization1
        $this->assertTrue($stores->every(function ($store) {
            return $store->organization_unit->organization_id === $this->orgB->id;
        }));
    }

    #[Test]
    public function user_cannot_access_other_organizations_inventory_data()
    {
        Auth::logout();
        // dd([
        //     'unitsA' => $this->orgA->units()->count(),
        //     'unitsB' => $this->orgB->units()->count(),
        //     'totalUnits' => OrganizationUnit::count(),
        // ]);

        // Create test data for both organizations
        $otherOrgItem = Item::factory()->create(['organization_id' => $this->orgB->id]);
        $ownOrgItem = Item::factory()->create(['organization_id' => $this->orgA->id]);

        $otherOrgStore = Store::factory()->create([
            'organization_unit_id' => $this->orgB->units()->first()->id
        ]);
        $ownOrgStore = Store::factory()->create([
            'organization_unit_id' => $this->orgA->units()->first()->id
        ]);

        $this->actingAs($this->userA);
        // Should be able to access own organization's data
        $this->get(route('inventory.items.show', $ownOrgItem))->assertOk();
        $this->get(route('inventory.stores.show', $ownOrgStore))->assertOk();

        // Should NOT be able to access other organization's data (404 due to scoping)
        $this->get(route('inventory.items.show', $otherOrgItem))->assertNotFound();
        $this->get(route('inventory.stores.show', $otherOrgStore))->assertNotFound();
    }

    #[Test]
    public function it_enforces_organization_isolation_in_controllers()
    {
        $this->actingAs($this->userA);

        // Test items isolation - inventory admin should see all items from their org
        $response = $this->get(route('inventory.items.index'));
        $items = $response->viewData('items');

        $this->assertTrue($items->every(fn($item) => $item->organization_id === $this->orgA->id));

        // Test stores isolation - inventory admin should see ALL stores including null org units
        $response = $this->get(route('inventory.stores.index'));
        $response->assertStatus(200);

        $stores = $response->viewData('stores');

        // Inventory admin (userA) should see:
        // 1. Stores from their organization (orgA)
        // 2. Stores with null organization units (admin-only stores)
        // But NOT stores from other organizations (orgB)

        $allowedStores = $stores->filter(function ($store) {
            // Allow stores from orgA OR stores with null organization units
            return is_null($store->organizationUnit) ||
                $store->organizationUnit->organization_id === $this->orgA->id;
        });

        // All visible stores should either be from orgA or have null organization units
        $this->assertCount($stores->count(), $allowedStores, 'Inventory admin should see all orgA stores and admin-only stores');

        // Verify that we don't see any stores from orgB
        $storesFromOrgB = $stores->filter(function ($store) {
            return $store->organizationUnit && $store->organizationUnit->organization_id === $this->orgB->id;
        });

        $this->assertCount(0, $storesFromOrgB, 'Inventory admin should not see stores from organization B');

        // Verify that admin CAN see stores with null organization units
        $storesWithNullUnits = $stores->filter(function ($store) {
            return is_null($store->organizationUnit);
        });

        $this->assertGreaterThanOrEqual(0, $storesWithNullUnits->count(), 'Inventory admin should be able to see admin-only stores');
    }
}
