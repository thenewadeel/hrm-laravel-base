<?php
// database/seeders/DemoOrganizationSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use App\Models\Inventory\Head;
use App\Models\Accounting\ChartOfAccount;
use App\Traits\ReadsCsvData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;

class DemoOrganizationSeeder extends Seeder
{
    use ReadsCsvData;

    public function run(): void
    {
        $this->command->info('🚀 Creating demo organization setup...');

        // Create demo admin user
        $admin = $this->createAdminUser();

        // Create organization structure
        $organization = $this->createOrganization();

        // Create organization units from CSV
        $units = $this->createOrganizationUnits($organization);

        // Attach admin to organization
        $this->attachUserToOrganization($admin, $organization, $units['Head Office']);

        // Create stores from CSV
        $stores = $this->createStores($organization, $units);

        // Create inventory categories
        $heads = $this->createInventoryHeads($stores);

        // Create items from CSV
        $this->createInventoryItems($organization, $heads);

        // Create chart of accounts from CSV
        $this->createChartOfAccounts($organization);

        $this->command->info('🎉 Demo setup completed!');
        $this->command->info('📧 Login: admin@demo.com');
        $this->command->info('🔑 Password: password');
    }

    protected function createAdminUser(): User
    {
        $admin = User::factory()->create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
        ]);

        $this->command->info('✅ Created demo admin user');
        return $admin;
    }

    protected function createOrganization(): Organization
    {
        $organization = Organization::create([
            'name' => 'Demo Corporation',
            'description' => 'A demo organization for testing purposes',
            'is_active' => true,
        ]);

        $this->command->info('✅ Created demo organization: ' . $organization->name);
        return $organization;
    }

    protected function createOrganizationUnits(Organization $organization): Collection
    {
        $unitsData = $this->readCsvData('demoData/organization_units.csv');
        $units = collect();

        // First pass - create all units
        foreach ($unitsData as $unitData) {
            $unit = OrganizationUnit::create([
                'name' => $unitData['name'],
                'type' => $unitData['type'],
                'organization_id' => $organization->id,
                'parent_id' => null, // Will update in second pass
            ]);
            $units[$unitData['name']] = $unit;
        }

        // Second pass - set parent relationships
        foreach ($unitsData as $unitData) {
            if (!empty($unitData['parent_name'])) {
                $unit = $units[$unitData['name']];
                $parent = $units[$unitData['parent_name']];
                $unit->update(['parent_id' => $parent->id]);
            }
        }

        $this->command->info('✅ Created ' . $units->count() . ' organization units');
        return $units;
    }

    protected function attachUserToOrganization(User $user, Organization $organization, OrganizationUnit $unit): void
    {
        $user->organizations()->attach($organization->id, [
            'roles' => json_encode(['admin', 'inventory_manager']),
            'organization_unit_id' => $unit->id,
            'position' => 'System Administrator'
        ]);

        $this->command->info('✅ Attached admin user to organization');
    }

    protected function createStores(Organization $organization, Collection $units): Collection
    {
        $storesData = $this->readCsvData('demoData/stores.csv');
        $stores = collect();

        foreach ($storesData as $storeData) {
            $store = Store::create([
                'name' => $storeData['name'],
                'code' => $storeData['code'],
                'location' => $storeData['location'],
                'description' => $storeData['description'],
                'organization_unit_id' => $units[$storeData['organization_unit']]->id,
                // 'organization_id' => $organization->id,
                'is_active' => true,
            ]);
            $stores[$storeData['name']] = $store;
        }

        $this->command->info('✅ Created ' . $stores->count() . ' stores');
        return $stores;
    }

    protected function createInventoryHeads(Collection $stores): Collection
    {
        $heads = collect();
        $headData = [
            'Main Store - Downtown' => [
                ['name' => 'Electronics', 'description' => 'Electronic items and gadgets'],
                ['name' => 'Office Supplies', 'description' => 'Office stationery and supplies'],
            ],
            'Central Warehouse' => [
                ['name' => 'Furniture', 'description' => 'Office furniture and equipment'],
                ['name' => 'Raw Materials', 'description' => 'Production raw materials'],
            ]
        ];

        foreach ($headData as $storeName => $storeHeads) {
            foreach ($storeHeads as $headInfo) {
                $head = Head::create(array_merge($headInfo, [
                    // 'store_id' => $stores[$storeName]->id,
                ]));
                $heads[$headInfo['name']] = $head;
            }
        }

        $this->command->info('✅ Created inventory categories');
        return $heads;
    }

    protected function createInventoryItems(Organization $organization, Collection $heads): void
    {
        $itemsData = $this->readCsvData('demoData/inventory_items.csv');

        foreach ($itemsData as $itemData) {
            Item::create([
                'name' => $itemData['name'],
                'sku' => $itemData['sku'],
                'description' => $itemData['description'],
                'category' => $itemData['category'],
                'unit' => $itemData['unit'],
                'cost_price' => $itemData['cost_price'],
                'selling_price' => $itemData['selling_price'],
                'reorder_level' => $itemData['reorder_level'],
                'is_active' => true,
                'organization_id' => $organization->id,
                'head_id' => $heads[$itemData['category']]->id,
            ]);
        }

        $this->command->info('✅ Created ' . count($itemsData) . ' inventory items');
    }

    protected function createChartOfAccounts(Organization $organization): void
    {
        $accountsData = $this->readCsvData('demoData/chart_of_accounts.csv');

        foreach ($accountsData as $accountData) {
            ChartOfAccount::create([
                'code' => $accountData['code'],
                'name' => $accountData['name'],
                'type' => $accountData['type'],
                'description' => $accountData['description'],
                // 'organization_id' => $organization->id,
            ]);
        }

        $this->command->info('✅ Created ' . count($accountsData) . ' chart of accounts');
    }
}
