<?php

namespace Tests\Traits;

use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\TransactionItem;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\OrganizationUser;
use App\Models\User;
use App\Roles\InventoryRoles;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

trait SetupInventory
{
    use SetupOrganization;
    // public $user;
    // public User $user;
    public User $inventoryAdminUser;
    // public Organization $organization;
    // public OrganizationUnit $organizationUnit;
    public Store $store;
    public Item $item;
    protected function migrateInventory()
    {
        // Run migrations from the custom path for testing
        Artisan::call('migrate', [
            // '--database' => 'sqlite', // Or your desired test database connection
            '--path' => 'database/migrations/inventory',
            // '--force' => true, // Essential for production environments, though not strictly needed for in-memory SQLite
        ]);
    }
    protected function setupInventory(): void
    {
        // parent::setUp();
        $this->migrateInventory();
        $setup = $this->createInventorySetup();
        // dd('ppp');

        $this->inventoryService = app(InventoryService::class);

        // $this->organization = $setup['organization'];
        // $this->organizationUnit = $setup['organization_unit'];
        $this->inventoryAdminUser = $setup['user'];
        $this->store = $setup['store'];
        $this->item = $setup['items']->first();
        // Set authenticated user for Gate system
        Auth::logout();
        $this->actingAs($this->inventoryAdminUser);
    }
    protected function createUserWithInventoryPermissions($user = null, $role = InventoryRoles::INVENTORY_ADMIN, $organization = null)
    {
        // dd('createUserWithInventoryPermissions');
        $setup = $this->createInventorySetup([$role]);
        $user = $user ?: $setup['user'];
        $organization = $organization ?: $setup['organization'];
        // Get permissions for the role
        $permissions = \App\Roles\InventoryRoles::getPermissionsForRole($role);

        // Assign permissions to user for the specific organization
        $user->givePermissionTo($permissions, $organization);
        $user->assignRole($role, $organization);

        $setup['user'] = $user;
        $setup['organization'] = $organization;
        // dd([
        //     $user,
        //     // $user->organizations->pluck('pivot'),
        //     $permissions,
        //     $role,
        //     $setup['user']->getAllPermissions(),
        //     $setup['organization']->id,
        //     $setup['user']->can('delete', $setup['store'])
        // ]);
        return $setup;
    }

    protected function createStoreManager($organization = null)
    {
        return $this->createUserWithInventoryPermissions(null, InventoryRoles::STORE_MANAGER, $organization);
    }

    protected function createInventoryClerk($organization = null)
    {
        return $this->createUserWithInventoryPermissions(null, InventoryRoles::INVENTORY_CLERK, $organization);
    }

    protected function createAuditor($organization = null)
    {
        return $this->createUserWithInventoryPermissions(null, InventoryRoles::AUDITOR, $organization);
    }

    // Helper method to check permissions in tests
    protected function userHasPermissionInOrganization(User $user, string $permission, Organization $organization): bool
    {
        return $user->hasPermission($permission, $organization);
    }

    protected function createInventorySetup(array $roles = [InventoryRoles::INVENTORY_ADMIN])
    {
        $organization = $this->organization; // ?? Organization::factory()->create();
        $organization_unit = $this->organizationUnit; // ??
        $user = User::factory()->create(['current_organization_id' => $organization->id]); //$this->user; // ?? User::factory()->create();

        $user->organizations()->attach($organization, [
            'roles' => json_encode($roles),
            'organization_id' => $organization->id
        ]);
        $user->assignRole($roles[0], $organization);
        $permissions = \App\Roles\InventoryRoles::getPermissionsForRole($roles[0]);

        // Assign permissions to user for the specific organization
        $user->givePermissionTo($permissions, $organization);
        // dd(['createInventorySetup', $organization, $organization_unit, $user->organizations->pluck('pivot.roles'),$permissions, $organization, $user->organizations]);
        // dd($this->user->organizations);

        // dd([
        //     'authUser' => json_encode($user),
        //     'operatingOrganizationId' => $user->operatingOrganizationId, // Check this!
        //     'userCurrentOrg' => $user->current_organization_id,
        //     'userOrgs' => $user->organizations()->pluck('organizations.id'),
        //     'itemsCount' => Item::count(),
        //     'itemsWithoutScope' => Item::withoutGlobalScope(\App\Models\Scopes\OrganizationScope::class)->get()->pluck('organization_id'),
        //     'itemsWithScope' => Item::get()->pluck('organization_id')
        // ]);
        $store = Store::factory()->create([
            'name' => 'test store',
            'organization_unit_id' => $organization_unit->id
        ]);

        $result = [
            'organization' => $organization,
            'organization_unit' => $organization_unit,
            'user' => $user,
            'store' => $store,
            // 'items' => $items
        ];
        // if ($noItems == true) {
        // } else {
        $items = Item::factory()->count(5)->create([
            'organization_id' => $organization->id
        ]);
        $result['items'] = $items;
        // }
        // $this->user = $user;
        // dd($user);
        // $this->actingAs($user);

        return $result;
    }

    protected function createTempInventorySetup(array $roles = [InventoryRoles::INVENTORY_ADMIN])
    {
        $organization = Organization::factory()->create();
        $organization_unit = OrganizationUnit::factory()->create([
            'organization_id' => $organization->id
        ]);
        $user =  User::factory()->create();

        $user->organizations()->attach($organization, [
            'roles' => json_encode($roles),
            'organization_id' => $organization->id,
            'organization_unit_id' => $organization_unit->id
        ]);
        // $organization_unit->users()->attach($user, [
        //     'roles' => json_encode($roles),
        //     'organization_id' => $organization->id
        // ]);
        $store = Store::factory()->create([
            'name' => 'test store',
            'organization_unit_id' => $organization_unit->id
        ]);
        $permissions = \App\Roles\InventoryRoles::getPermissionsForRole($roles[0]);

        // Assign permissions to user for the specific organization
        $user->givePermissionTo($permissions, $organization);
        $user->assignRole($roles[0], $organization);
        $org_user = OrganizationUser::where('user_id', $user->id)->first();
        // dd(OrganizationUser::where('user_id', $user->id)->first());

        $result = [
            'organization' => $organization,
            'organization_unit' => $organization_unit,
            'user' => $user,
            'store' => $store,
            // 'items' => $items
        ];

        // $this->actingAs($user);

        return $result;
    }
    protected function createDraftTransactionWithItems($store = null, $user = null, $itemCount = 3)
    {
        if (!$store) {
            $setup = $this->createInventorySetup();
            $store = $setup['store'];
            $items = $setup['items']->take($itemCount);
        } else {
            $items = Item::factory()->count($itemCount)->create([
                'organization_id' => $store->organization->id
            ]);
        }

        $user = $user ?: auth()->user();

        $transaction = Transaction::factory()->create([
            'store_id' => $store->id,
            'status' => Transaction::STATUS_DRAFT,
            'type' => Transaction::TYPE_INCOMING,
            'created_by' => $user->id, // ✅ Make sure created_by is set
            'transaction_date' => now(),
        ]);


        $transactionItems = [];
        foreach ($items as $item) {
            $transactionItem = TransactionItem::create([
                'transaction_id' => $transaction->id,
                'item_id' => $item->id,
                'quantity' => rand(10, 100),
                'unit_price' => rand(1000, 5000) // in cents
            ]);
            $transactionItems[] = $transactionItem;
        }

        $transaction->load('items');

        return [
            'transaction' => $transaction,
            'items' => $items,
            'transactionItems' => $transactionItems,
            'store' => $store
        ];
    }

    protected function createFinalizedTransaction($store = null, $user = null)
    {
        $setup = $this->createDraftTransactionWithItems($store, $user);
        $transaction = $setup['transaction'];

        $transaction->update(['status' => Transaction::STATUS_FINALIZED]);

        // Update store inventory (simulating the finalize process)
        foreach ($setup['transactionItems'] as $transactionItem) {
            $store->items()->syncWithoutDetaching([
                $transactionItem->item_id => ['quantity' => $transactionItem->quantity]
            ]);
        }

        $setup['transaction'] = $transaction->fresh();
        return $setup;
    }

    // In SetupInventory trait, update the createMultipleStoresWithInventory method
    protected function createMultipleStoresWithInventory($user = null)
    {
        $setup = $this->createInventorySetup();

        $additionalStores = Store::factory()->count(2)->create([
            'organization_unit_id' => $setup['organization_unit']->id
        ]);

        // Add inventory to all stores with unique items
        $allStores = collect([$setup['store']])->merge($additionalStores);

        // Create separate items for each store to avoid unique constraint issues
        foreach ($allStores as $index => $store) {
            $storeItems = Item::factory()->count(3)->create([
                'organization_id' => $setup['organization']->id
            ]);

            foreach ($storeItems as $item) {
                $store->items()->attach($item->id, [
                    'quantity' => rand(50, 200),
                    'min_stock' => 10,
                    'max_stock' => 500
                ]);
            }
        }

        $setup['stores'] = $allStores;
        return $setup;
    }
}
