<?php
// database/seeders/FullSystemDemoSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\Employee;
use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use App\Models\Inventory\Head;
use App\Models\Inventory\Transaction;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\LedgerEntry;
use App\Models\Accounting\Voucher;
use App\Models\PayrollRuns;
use App\Models\PayrollSlip;
use App\Models\Shift;
use App\Models\AttendanceRecord;
use App\Traits\ReadsCsvData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class FullSystemDemoSeeder extends Seeder
{
    use ReadsCsvData;

    public function run(): void
    {
        $this->command->info('🚀 Creating comprehensive full system demo...');

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
        $items = $this->createInventoryItems($organization, $heads);

        // Create chart of accounts from CSV
        $accounts = $this->createChartOfAccounts($organization);

        // Create employees with positions
        $employees = $this->createEmployees($organization, $units);

        // Create shifts
        $shifts = $this->createShifts($organization);

        // Create attendance records
        $this->createAttendanceRecords($employees, $shifts);

        // Create inventory transactions
        $this->createInventoryTransactions($stores, $items, $employees);

        // Create accounting vouchers and transactions
        $this->createAccountingTransactions($organization, $accounts, $employees);

        // Create payroll data
        $this->createPayrollData($organization, $employees, $accounts);

        $this->command->info('🎉 Full system demo completed!');
        $this->command->info('📧 Login: admin@demo.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('👥 Created ' . $employees->count() . ' employees');
        $this->command->info('💰 Created accounting vouchers and transactions');
        $this->command->info('📦 Created inventory transactions');
        $this->command->info('💼 Created payroll data');
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
            'name' => 'Demo Corporation Ltd',
            'description' => 'A comprehensive demo organization showcasing all HRM ERP features',
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
            'roles' => json_encode(['admin', 'hr_manager', 'inventory_manager', 'accountant']),
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
            ['name' => 'Electronics', 'description' => 'Electronic items and gadgets'],
            ['name' => 'Office Supplies', 'description' => 'Office stationery and supplies'],
            ['name' => 'Furniture', 'description' => 'Office furniture and equipment'],
            ['name' => 'Raw Materials', 'description' => 'Production raw materials'],
        ];

        foreach ($headData as $headInfo) {
            $head = Head::create($headInfo);
            $heads[$headInfo['name']] = $head;
        }

        $this->command->info('✅ Created inventory categories');
        return $heads;
    }

    protected function createInventoryItems(Organization $organization, Collection $heads): Collection
    {
        $itemsData = $this->readCsvData('demoData/inventory_items.csv');
        $items = collect();

        foreach ($itemsData as $itemData) {
            $item = Item::create([
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
            $items[$itemData['sku']] = $item;
        }

        $this->command->info('✅ Created ' . $items->count() . ' inventory items');
        return $items;
    }

    protected function createChartOfAccounts(Organization $organization): Collection
    {
        $accountsData = $this->readCsvData('demoData/chart_of_accounts.csv');
        $accounts = collect();

        foreach ($accountsData as $accountData) {
            $account = ChartOfAccount::create([
                'code' => $accountData['code'],
                'name' => $accountData['name'],
                'type' => $accountData['type'],
                'description' => $accountData['description'],
                'organization_id' => $organization->id,
            ]);
            $accounts[$accountData['code']] = $account;
        }

        $this->command->info('✅ Created ' . $accounts->count() . ' chart of accounts');
        return $accounts;
    }

    protected function createEmployees(Organization $organization, Collection $units): Collection
    {
        $employees = collect();
        
        // Create realistic employee data
        $employeeData = [
            [
                'first_name' => 'John', 'last_name' => 'Smith', 'email' => 'john.smith@demo.com',
                'position' => 'Sales Manager', 'department' => 'Sales Department', 'basic_salary' => 85000
            ],
            [
                'first_name' => 'Sarah', 'last_name' => 'Johnson', 'email' => 'sarah.johnson@demo.com',
                'position' => 'Accountant', 'department' => 'Accounting Department', 'basic_salary' => 75000
            ],
            [
                'first_name' => 'Michael', 'last_name' => 'Brown', 'email' => 'michael.brown@demo.com',
                'position' => 'Warehouse Supervisor', 'department' => 'Warehouse Department', 'basic_salary' => 65000
            ],
            [
                'first_name' => 'Emily', 'last_name' => 'Davis', 'email' => 'emily.davis@demo.com',
                'position' => 'HR Specialist', 'department' => 'HR Department', 'basic_salary' => 60000
            ],
            [
                'first_name' => 'David', 'last_name' => 'Wilson', 'email' => 'david.wilson@demo.com',
                'position' => 'Sales Executive', 'department' => 'Sales Department', 'basic_salary' => 55000
            ],
            [
                'first_name' => 'Lisa', 'last_name' => 'Anderson', 'email' => 'lisa.anderson@demo.com',
                'position' => 'Inventory Clerk', 'department' => 'Warehouse Department', 'basic_salary' => 45000
            ],
            [
                'first_name' => 'James', 'last_name' => 'Taylor', 'email' => 'james.taylor@demo.com',
                'position' => 'Junior Accountant', 'department' => 'Accounting Department', 'basic_salary' => 50000
            ],
            [
                'first_name' => 'Jennifer', 'last_name' => 'Thomas', 'email' => 'jennifer.thomas@demo.com',
                'position' => 'HR Assistant', 'department' => 'HR Department', 'basic_salary' => 40000
            ],
        ];

        foreach ($employeeData as $index => $data) {
            // Create user for employee
            $user = User::factory()->create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
            ]);

            // Create employee
            $employee = Employee::create([
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'organization_unit_id' => $units[$data['department']]->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => '+1-555-' . str_pad($index + 100, 4, '0', STR_PAD_LEFT),
                'address' => ($index + 1) . ' Demo Street, Test City, TC 12345',
                'date_of_birth' => Carbon::now()->subYears(rand(25, 45))->subDays(rand(1, 365)),
                'gender' => rand(0, 1) ? 'Male' : 'Female',
                'basic_salary' => $data['basic_salary'],
                'is_active' => true,
                'is_admin' => $index < 3, // First 3 employees are admins
            ]);

            // Attach user to organization
            $user->organizations()->attach($organization->id, [
                'roles' => json_encode($index < 3 ? ['manager', 'employee'] : ['employee']),
                'organization_unit_id' => $units[$data['department']]->id,
                'position' => $data['position']
            ]);

            $employees[$data['email']] = $employee;
        }

        $this->command->info('✅ Created ' . $employees->count() . ' employees');
        return $employees;
    }

    protected function createShifts(Organization $organization): Collection
    {
        $shifts = collect();
        
        $shiftData = [
            ['name' => 'Morning Shift', 'code' => 'MS', 'start_time' => '08:00', 'end_time' => '16:00', 'days_of_week' => [1,2,3,4,5]],
            ['name' => 'Evening Shift', 'code' => 'ES', 'start_time' => '16:00', 'end_time' => '00:00', 'days_of_week' => [1,2,3,4,5]],
            ['name' => 'Night Shift', 'code' => 'NS', 'start_time' => '00:00', 'end_time' => '08:00', 'days_of_week' => [1,2,3,4,5]],
        ];

        foreach ($shiftData as $data) {
            $shift = Shift::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'days_of_week' => $data['days_of_week'],
                'organization_id' => $organization->id,
                'is_active' => true,
            ]);
            $shifts[$data['name']] = $shift;
        }

        $this->command->info('✅ Created ' . $shifts->count() . ' shifts');
        return $shifts;
    }

    protected function createAttendanceRecords(Collection $employees, Collection $shifts): void
    {
        $attendanceCount = 0;
        $startDate = Carbon::now()->subDays(30);

        foreach ($employees as $employee) {
            // Create attendance records for the last 30 days
            for ($i = 0; $i < 30; $i++) {
                $date = $startDate->copy()->addDays($i);
                
                // Skip weekends (70% chance)
                if ($date->isWeekend() && rand(1, 10) > 3) {
                    continue;
                }

                // Random shift assignment
                $shift = $shifts->random();
                
                // Check in and check out times
                $checkIn = $date->copy()->setTimeFromTimeString($shift->start_time)->addMinutes(rand(-15, 30));
                $checkOut = $date->copy()->setTimeFromTimeString($shift->end_time)->addMinutes(rand(-30, 45));

                AttendanceRecord::create([
                    'employee_id' => $employee->id,
                    'organization_id' => $employee->organization_id,
                    'record_date' => $date->toDateString(),
                    'punch_in' => $checkIn,
                    'punch_out' => $checkOut,
                    'status' => 'present',
                    'overtime_minutes' => rand(0, 120),
                    'late_minutes' => max(0, $checkIn->diffInMinutes($date->copy()->setTimeFromTimeString($shift->start_time))),
                ]);
                $attendanceCount++;
            }
        }

        $this->command->info('✅ Created ' . $attendanceCount . ' attendance records');
    }

    protected function createInventoryTransactions(Collection $stores, Collection $items, Collection $employees): void
    {
        $transactionCount = 0;
        $transactionTypes = ['IN', 'OUT', 'TRANSFER', 'ADJUST'];

        for ($i = 0; $i < 50; $i++) {
            $transaction = Transaction::create([
                'transaction_date' => Carbon::now()->subDays(rand(1, 60)),
                'type' => $transactionTypes[array_rand($transactionTypes)],
                'store_id' => $stores->random()->id,
                'reference' => 'TRX-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'notes' => 'Demo transaction ' . ($i + 1),
                'created_by' => $employees->random()->id,
                'status' => 'approved',
                'approved_by' => $employees->random()->id,
                'finalized_at' => Carbon::now(),
            ]);
            $transactionCount++;
        }

        $this->command->info('✅ Created ' . $transactionCount . ' inventory transactions');
    }

    protected function createAccountingTransactions(Organization $organization, Collection $accounts, Collection $employees): void
    {
        $voucherCount = 0;
        $voucherTypes = ['SALES', 'PURCHASE', 'EXPENSE', 'JOURNAL'];

        for ($i = 0; $i < 20; $i++) {
            $voucherType = $voucherTypes[array_rand($voucherTypes)];
            $amount = rand(1000, 50000);
            
            $voucher = Voucher::create([
                'type' => $voucherType,
                'number' => $voucherType . '-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'date' => Carbon::now()->subDays(rand(1, 60))->toDateString(),
                'organization_id' => $organization->id,
                'amount' => $amount,
                'description' => 'Demo ' . strtolower($voucherType) . ' voucher',
                'notes' => 'Demo ' . strtolower($voucherType) . ' voucher with amount ' . $amount,
                'created_by' => $employees->random()->id,
                'updated_by' => $employees->random()->id,
                'status' => 'approved',
            ]);

            // Create journal entries for the voucher
            $assetAccounts = $accounts->where('type', 'asset');
            $liabilityRevenueAccounts = $accounts->whereIn('type', ['liability', 'revenue']);
            
            if ($assetAccounts->isEmpty() || $liabilityRevenueAccounts->isEmpty()) {
                continue;
            }
            
            $debitAccount = $assetAccounts->random();
            $creditAccount = $liabilityRevenueAccounts->random();

            // Debit entry
            LedgerEntry::create([
                'entry_date' => $voucher->date,
                'chart_of_account_id' => $debitAccount->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => 'Debit entry for ' . $voucher->number,
                'transactionable_type' => Voucher::class,
                'transactionable_id' => $voucher->id,
                'organization_id' => $organization->id,
            ]);

            // Credit entry
            LedgerEntry::create([
                'entry_date' => $voucher->date,
                'chart_of_account_id' => $creditAccount->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => 'Credit entry for ' . $voucher->number,
                'transactionable_type' => Voucher::class,
                'transactionable_id' => $voucher->id,
                'organization_id' => $organization->id,
            ]);

            $voucherCount++;
        }

        $this->command->info('✅ Created ' . $voucherCount . ' accounting vouchers with entries');
    }

    protected function createPayrollData(Organization $organization, Collection $employees, Collection $accounts): void
    {
        // Create payroll runs for the last 3 months
        for ($month = 0; $month < 3; $month++) {
            $payrollDate = Carbon::now()->subMonths($month)->endOfMonth();
            $startDate = $payrollDate->copy()->startOfMonth();
            
            $payrollRun = \App\Models\PayrollRuns::create([
                'organization_id' => $organization->id,
                'period' => $payrollDate->format('Y-m'),
                'start_date' => $startDate,
                'end_date' => $payrollDate,
                'status' => 'completed',
                'total_gross' => $employees->sum('basic_salary') * 1.2, // With allowances
                'total_net' => $employees->sum('basic_salary') * 1.02, // After deductions
            ]);

            // Create salary slips for each employee
            foreach ($employees as $employee) {
                $basicSalary = $employee->basic_salary;
                $allowances = $basicSalary * 0.2; // 20% allowances
                $deductions = $basicSalary * 0.15; // 15% deductions
                $grossPay = $basicSalary + $allowances;
                $netPay = $grossPay - $deductions;

                \App\Models\PayrollSlip::create([
                    'payroll_run_id' => $payrollRun->id,
                    'employee_id' => $employee->id,
                    'organization_id' => $organization->id,
                    'gross_pay' => $grossPay,
                    'deductions' => $deductions,
                    'net_pay' => $netPay,
                ]);
            }
        }

        $this->command->info('✅ Created payroll data for 3 months with ' . $employees->count() . ' employees each');
    }
}