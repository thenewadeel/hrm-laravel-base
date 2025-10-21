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
use Illuminate\Support\Facades\Artisan;

trait SetupInventory
{
    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations from the custom path for testing
        Artisan::call('migrate', [
            // '--database' => 'sqlite', // Or your desired test database connection
            '--path' => 'database/migrations/inventory',
            // '--force' => true, // Essential for production environments, though not strictly needed for in-memory SQLite
        ]);

        // Optional: Seed your test database if needed
        // Artisan::call('db:seed', [
        //     '--class' => 'TestDatabaseSeeder',
        //     '--force' => true,
        // ]);
    }
    protected function createUserWithInventoryPermissions($user = null, $role = InventoryRoles::INVENTORY_ADMIN, $organization = null)
    {
        $setup = $this->createInventorySetup($user);
        $user = $setup['user'];
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

    protected function createInventorySetup($user = null, array $roles = [InventoryRoles::INVENTORY_ADMIN])
    {
        $organization = Organization::factory()->create();
        $organization_unit = OrganizationUnit::factory()->create([
            'organization_id' => $organization->id
        ]);
        $user = $user ?: User::factory()->create();

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

        $items = Item::factory()->count(5)->create();
        // dd('cp');

        return [
            'organization' => $organization,
            'organization_unit' => $organization_unit,
            'user' => $user,
            'store' => $store,
            'items' => $items
        ];
    }

    protected function createDraftTransactionWithItems($store = null, $user = null, $itemCount = 3)
    {
        if (!$store) {
            $setup = $this->createInventorySetup($user);
            $store = $setup['store'];
            $items = $setup['items']->take($itemCount);
        } else {
            $items = Item::factory()->count($itemCount)->create([
                // 'organization_id' => $store->organization_id
            ]);
        }

        $transaction = Transaction::factory()->create([
            'store_id' => $store->id,
            'status' => Transaction::STATUS_DRAFT,
            'type' => Transaction::TYPE_INCOMING,
            'created_by' => $user?->id
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
        $setup = $this->createInventorySetup($user);

        $additionalStores = Store::factory()->count(2)->create([
            'organization_id' => $setup['organization']->id
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
