<?php

namespace Tests\Browser\Utilities;

use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardTestDataFactory
{
    /**
     * Create complete dashboard test data for an organization.
     */
    public static function createForOrganization(Organization $organization, array $options = []): array
    {
        $options = array_merge([
            'stores_count' => 3,
            'items_count' => 15,
            'transactions_count' => 8,
            'low_stock_items' => 2,
            'create_users' => true,
            'user_count' => 2,
        ], $options);

        // Create stores
        $stores = Store::factory()->count($options['stores_count'])->create([
            'organization_id' => $organization->id,
        ]);

        // Create items
        $items = Item::factory()->count($options['items_count'])->create([
            'organization_id' => $organization->id,
        ]);

        // Attach items to stores with quantities
        foreach ($items as $item) {
            $storesToAttach = $stores->random(rand(1, min(3, $stores->count())));

            foreach ($storesToAttach as $store) {
                $quantity = rand(1, 100);

                // Create low stock items if requested
                if ($options['low_stock_items'] > 0) {
                    $lowStockItems = $items->take($options['low_stock_items']);
                    if ($lowStockItems->contains($item)) {
                        $quantity = rand(1, $item->reorder_level - 1);
                    }
                }

                $item->stores()->attach($store->id, [
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Create transactions
        $transactions = Transaction::factory()->count($options['transactions_count'])->create([
            'organization_id' => $organization->id,
            'store_id' => $stores->random()->id,
        ]);

        // Create users if requested
        $users = [];
        if ($options['create_users']) {
            for ($i = 0; $i < $options['user_count']; $i++) {
                $user = User::factory()->create(['email_verified_at' => now()]);
                $organization->users()->attach($user->id, [
                    'roles' => $i === 0 ? 'admin' : 'member',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $users[] = $user;
            }
        }

        return [
            'organization' => $organization,
            'stores' => $stores,
            'items' => $items,
            'transactions' => $transactions,
            'users' => $users,
            'low_stock_items' => $options['low_stock_items'] > 0
                ? $items->take($options['low_stock_items'])
                : collect(),
        ];
    }

    /**
     * Create multi-tenant test data.
     */
    public static function createMultiTenantData(int $organizationCount = 2): array
    {
        $organizations = [];
        $data = [];

        for ($i = 1; $i <= $organizationCount; $i++) {
            $org = Organization::factory()->create(['name' => "Test Organization {$i}"]);
            $organizations[] = $org;
            $data[$org->id] = self::createForOrganization($org, [
                'stores_count' => rand(2, 4),
                'items_count' => rand(10, 20),
                'transactions_count' => rand(5, 10),
            ]);
        }

        return [
            'organizations' => collect($organizations),
            'data' => $data,
        ];
    }

    /**
     * Create organization with specific scenarios.
     */
    public static function createScenario(string $scenario, Organization $organization): array
    {
        switch ($scenario) {
            case 'empty':
                return self::createForOrganization($organization, [
                    'stores_count' => 0,
                    'items_count' => 0,
                    'transactions_count' => 0,
                    'low_stock_items' => 0,
                ]);

            case 'low_stock_heavy':
                return self::createForOrganization($organization, [
                    'stores_count' => 2,
                    'items_count' => 10,
                    'transactions_count' => 3,
                    'low_stock_items' => 8, // Most items are low stock
                ]);

            case 'high_activity':
                return self::createForOrganization($organization, [
                    'stores_count' => 5,
                    'items_count' => 50,
                    'transactions_count' => 25,
                    'low_stock_items' => 1,
                ]);

            case 'single_store':
                return self::createForOrganization($organization, [
                    'stores_count' => 1,
                    'items_count' => 20,
                    'transactions_count' => 10,
                    'low_stock_items' => 3,
                ]);

            default:
                return self::createForOrganization($organization);
        }
    }

    /**
     * Clean up test data.
     */
    public static function cleanup(Organization $organization): void
    {
        DB::transaction(function () use ($organization) {
            // Delete related data in correct order to respect foreign keys
            Transaction::where('organization_id', $organization->id)->delete();

            // Detach items from stores
            $items = Item::where('organization_id', $organization->id)->get();
            foreach ($items as $item) {
                $item->stores()->detach();
            }

            Item::where('organization_id', $organization->id)->delete();
            Store::where('organization_id', $organization->id)->delete();

            // Detach users
            $organization->users()->detach();
        });
    }

    /**
     * Get dashboard statistics for verification.
     */
    public static function getDashboardStats(Organization $organization): array
    {
        return [
            'stores_count' => Store::where('organization_id', $organization->id)->count(),
            'items_count' => Item::where('organization_id', $organization->id)->count(),
            'transactions_count' => Transaction::where('organization_id', $organization->id)->count(),
            'low_stock_items_count' => self::getLowStockItemsCount($organization),
        ];
    }

    /**
     * Get low stock items count.
     */
    private static function getLowStockItemsCount(Organization $organization): int
    {
        $items = Item::where('organization_id', $organization->id)->get();
        $lowStockCount = 0;

        foreach ($items as $item) {
            foreach ($item->stores as $store) {
                if ($store->pivot->quantity <= $item->reorder_level) {
                    $lowStockCount++;
                    break; // Count each item only once
                }
            }
        }

        return $lowStockCount;
    }

    /**
     * Create performance test data.
     */
    public static function createPerformanceTestData(Organization $organization): array
    {
        return self::createForOrganization($organization, [
            'stores_count' => 10,
            'items_count' => 100,
            'transactions_count' => 50,
            'low_stock_items' => 15,
        ]);
    }

    /**
     * Create stress test data.
     */
    public static function createStressTestData(Organization $organization): array
    {
        return self::createForOrganization($organization, [
            'stores_count' => 20,
            'items_count' => 500,
            'transactions_count' => 200,
            'low_stock_items' => 50,
        ]);
    }

    /**
     * Verify data integrity.
     */
    public static function verifyDataIntegrity(Organization $organization): bool
    {
        $stats = self::getDashboardStats($organization);

        // Verify all data belongs to the organization
        $storeOrgs = Store::where('organization_id', $organization->id)->pluck('organization_id')->unique();
        $itemOrgs = Item::where('organization_id', $organization->id)->pluck('organization_id')->unique();
        $transactionOrgs = Transaction::where('organization_id', $organization->id)->pluck('organization_id')->unique();

        return $storeOrgs->count() === 1 &&
               $itemOrgs->count() === 1 &&
               $transactionOrgs->count() === 1 &&
               $storeOrgs->first() === $organization->id &&
               $itemOrgs->first() === $organization->id &&
               $transactionOrgs->first() === $organization->id;
    }

    /**
     * Create user with specific permissions.
     */
    public static function createUserWithPermissions(Organization $organization, string $role = 'admin'): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $organization->users()->attach($user->id, [
            'roles' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    /**
     * Create test data for specific time periods.
     */
    public static function createDataForTimePeriod(Organization $organization, string $period): array
    {
        $now = now();
        $transactionDate = match ($period) {
            'today' => $now,
            'yesterday' => $now->subDay(),
            'last_week' => $now->subWeek(),
            'last_month' => $now->subMonth(),
            default => $now,
        };

        $data = self::createForOrganization($organization);

        // Update transaction dates
        Transaction::where('organization_id', $organization->id)->update([
            'created_at' => $transactionDate,
            'updated_at' => $transactionDate,
        ]);

        return $data;
    }
}
