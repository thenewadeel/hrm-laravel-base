<?php

namespace Tests\Traits;

use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\TransactionItem;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Roles\InventoryRoles;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Artisan;

trait SetupInventory
{
    use SetupOrganization;
    // public $user;
    // public User $user;
    // public Organization $organization;
    public OrganizationUnit $organizationUnit;
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
        // dd('ppp');
        $this->migrateInventory();
        $setup = $this->createInventorySetup();

        $this->inventoryService = app(InventoryService::class);

        $this->organization = $setup['organization'];
        $this->organizationUnit = $setup['organization_unit'];
        $this->user = $setup['user'];
        $this->store = $setup['store'];
        $this->item = $setup['items']->first();

        // Set authenticated user for Gate system
        // $this->actingAs($this->user);
    }
    protected function createUserWithInventoryPermissions($user = null, $role = InventoryRoles::INVENTORY_ADMIN, $organization = null)
    {
        $setup = $this->createInventorySetup();
        $user = $user ?: $setup['user'];
        $organization = $organization ?: $setup['organization'];
        // Get permissions for the role
        $permissions = \App\Roles\InventoryRoles::getPermissionsForRole($role);

        // Assign permissions to user for the specific organization
        $user->givePermissionTo($permissions, $organization);
        $user->assignRole($role, $organization);

        $setup['user'] = $user;
        $setup['organization'] = $organization;
        // dd('cp');
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
        $organization = $this->organization ?? Organization::factory()->create();
        $organization_unit = OrganizationUnit::factory()->create([
            'organization_id' => $organization->id
        ]);
        $user = $this->user ?? User::factory()->create();

        $organization->users()->attach($user, [
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
        $this->actingAs($user);

        return $result;
    }

    protected function createTempInventorySetup(array $roles = [InventoryRoles::INVENTORY_ADMIN])
    {
        $organization = Organization::factory()->create();
        $organization_unit = OrganizationUnit::factory()->create([
            'organization_id' => $organization->id
        ]);
        $user =  User::factory()->create();

        $organization->users()->attach($user, [
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

        $result = [
            'organization' => $organization,
            'organization_unit' => $organization_unit,
            'user' => $user,
            'store' => $store,
            // 'items' => $items
        ];

        $this->actingAs($user);

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

    protected function createMultipleStoresWithInventory($user = null)
    {
        $setup = $this->createInventorySetup();

        $additionalStores = Store::factory()->count(2)->create([
            'organization_unit_id' => $setup['organization_unit']->id
        ]);

        // Add inventory to all stores
        $allStores = collect([$setup['store']])->merge($additionalStores);

        foreach ($allStores as $store) {
            foreach ($setup['items']->take(3) as $item) {
                $store->items()->attach($item->id, [
                    'quantity' => rand(50, 200)
                ]);
            }
        }

        $setup['stores'] = $allStores;
        return $setup;
    }
}
