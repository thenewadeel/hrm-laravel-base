<?php

namespace Tests\Traits;

use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Roles\InventoryRoles;
use App\Roles\OrganizationRoles;
use Illuminate\Support\Facades\Auth;

trait SetupTenancy
{
    use SetupOrganization, SetupInventory;
    /**
     * Define the array of models that should be tested for tenancy scope.
     * Each model MUST use the BelongsToOrganization trait and have a factory.
     *
     * @var array<class-string<\Illuminate\Database\Eloquent\Model>>
     */
    abstract protected function tenantModels(): array;

    protected Organization $orgA;
    protected Organization $orgB;
    protected User $userA;
    protected User $userB;

    /**
     * Executes the setup required for tenancy testing.
     * Should be called from the parent setUp() method.
     */
    protected function setUpTenancy(): void
    {
        Auth::logout();
        $this->userA = User::factory()->create();
        $this->userB = User::factory()->create();

        // Org A Setup
        $this->actingAs($this->userA);
        $this->orgA = Organization::factory()->create(['name' => 'Org A']);
        $this->orgUnitA = OrganizationUnit::factory()->create(['organization_id' => $this->orgA->id]);

        // Use the helper method
        $this->attachUserToOrganization($this->userA, $this->orgA, [OrganizationRoles::ORGANIZATION_ADMIN]);

        $this->storesA = Store::factory(3)->create([
            'name' => 'test store',
            'organization_unit_id' => OrganizationUnit::factory()->create([
                'organization_id' => $this->orgA->id
            ])->id
        ]);
        $this->userA->givePermissionTo(InventoryRoles::getPermissionsForRole(InventoryRoles::INVENTORY_ADMIN), $this->orgA);

        // Org B Setup
        Auth::logout();
        $this->actingAs($this->userB);
        $this->orgB = Organization::factory()->create(['name' => 'Org B']);
        $this->orgUnitB = OrganizationUnit::factory()->create(['organization_id' => $this->orgB->id]);

        // Use the helper method
        $this->attachUserToOrganization($this->userB, $this->orgB, [OrganizationRoles::ORGANIZATION_ADMIN]);

        $this->userB->givePermissionTo(InventoryRoles::getPermissionsForRole(InventoryRoles::INVENTORY_ADMIN), $this->orgB);
        $this->storesB = Store::factory(2)->create([
            'name' => 'test store',
            'organization_unit_id' => OrganizationUnit::factory()->create([
                'organization_id' => $this->orgB->id
            ])->id
        ]);
        Auth::logout();
        $this->populateTenantData();
        // dd([
        //     'unitsA' => $this->orgA->units()->count(),
        //     'unitsB' => $this->orgB->units()->count(),
        //     'totalUnits' => OrganizationUnit::count(),
        //     'itemsA' => Item::ofOrganization($this->orgA->id)->count(),
        //     'itemsB' => Item::ofOrganization($this->orgB->id)->count(),
        // ]);
    }

    /**
     * Creates specific test data for all models listed in $tenantModels.
     */
    protected function populateTenantData(): void
    {
        // FIX: Access the model list via the required method, not the static property.
        // Clean up any setup data for tenant models first
        foreach ($this->tenantModels() as $modelClass) {
            $modelClass::query()->delete();
        }

        foreach ($this->tenantModels() as $modelClass) {
            // Create 2 records for Org A
            $this->actingAs($this->userA);
            $modelClass::factory()->count(2)->create([
                'organization_id' => $this->orgA->id,
            ]);

            $this->actingAs($this->userB);
            // Create 1 record for Org B (Used as a 'foreign' record for isolation checks)
            $modelClass::factory()->create([
                'organization_id' => $this->orgB->id,
            ]);
        }
    }
}
