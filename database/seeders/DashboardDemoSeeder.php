<?php

namespace Database\Seeders;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\LedgerEntry;
use App\Models\Accounting\Voucher;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use App\Models\Membership\Member;
use App\Models\Membership\MemberSubscription;
use App\Models\Membership\SubscriptionPlan;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a self-contained demo organization with realistic, globally-shaped
 * KPI data for the Executive (Eagle Eye) dashboard's first iteration.
 *
 * The seeded organization (Demo Organization) lives alongside existing data,
 * is isolated from it, and can be re-run safely thanks to idempotent lookups.
 */
class DashboardDemoSeeder extends Seeder
{
    private const DEMO_EMAIL = 'demo@demo.com';

    private const DEMO_ORG = 'Demo Organization';

    public function run(): void
    {
        $this->command->info('🚀 Seeding Dashboard demo data...');

        DB::transaction(function () {
            $user = $this->createDemoUser();
            $organization = $this->createDemoOrganization();

            $this->wipeDemoOrganization($organization);

            $this->attachUser($user, $organization);

            $units = $this->createUnits($organization);
            $this->createChartOfAccounts($organization);
            $this->createBankAccounts($organization);

            $employees = $this->createEmployees($organization, $units);
            $this->createAttendance($organization, $employees);

            $this->createLedger($organization);
            $this->createVouchers($organization);

            $this->createInventory($organization);
            $this->createInventoryTransactions($organization);

            $this->createMemberships($organization);
        });

        $this->command->info('✅ Dashboard demo data seeded!');
        $this->command->info("📧 Login: {$this->getCredential()['email']} / {$this->getCredential()['password']}");
        $this->command->info('🏢 Organization: '.self::DEMO_ORG);
    }

    /**
     * Remove all previously seeded demo rows so the seeder stays deterministic.
     */
    private function wipeDemoOrganization(Organization $organization): void
    {
        $unitIds = $organization->units()->pluck('id');

        $inventoryIds = DB::table('inventory_transactions')
            ->whereIn('store_id', DB::table('inventory_stores')->whereIn('organization_unit_id', $unitIds)->pluck('id'))
            ->pluck('id');

        DB::table('inventory_transaction_items')->whereIn('transaction_id', $inventoryIds)->delete();
        DB::table('inventory_transactions')->whereIn('id', $inventoryIds)->delete();
        DB::table('inventory_store_items')->delete();
        DB::table('inventory_items')->where('organization_id', $organization->id)->delete();
        DB::table('inventory_stores')->whereIn('organization_unit_id', $unitIds)->delete();

        MemberSubscription::where('organization_id', $organization->id)->forceDelete();
        Member::where('organization_id', $organization->id)->forceDelete();
        SubscriptionPlan::where('organization_id', $organization->id)->forceDelete();

        AttendanceRecord::where('organization_id', $organization->id)->forceDelete();
        Employee::where('organization_id', $organization->id)->forceDelete();

        LedgerEntry::where('organization_id', $organization->id)->forceDelete();
        Voucher::where('organization_id', $organization->id)->forceDelete();
        BankAccount::where('organization_id', $organization->id)->forceDelete();
        ChartOfAccount::where('organization_id', $organization->id)->forceDelete();

        OrganizationUnit::where('organization_id', $organization->id)->forceDelete();
    }

    /**
     * @return array{email: string, password: string}
     */
    private function getCredential(): array
    {
        return ['email' => self::DEMO_EMAIL, 'password' => 'password'];
    }

    private function createDemoUser(): User
    {
        return User::firstOrCreate(
            ['email' => self::DEMO_EMAIL],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }

    private function createDemoOrganization(): Organization
    {
        return Organization::firstOrCreate(
            ['name' => self::DEMO_ORG],
            ['description' => 'Self-contained demo organization powering the Executive Dashboard demo.']
        );
    }

    private function attachUser(User $user, Organization $organization): void
    {
        $exists = DB::table('organization_user')
            ->where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->exists();

        if (! $exists) {
            DB::table('organization_user')->insert([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'roles' => json_encode(['admin']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createUnits(Organization $organization): Collection
    {
        $units = collect();

        foreach (['Head Office', 'Operations', 'Sales', 'Warehouse'] as $name) {
            $units->put($name, OrganizationUnit::firstOrCreate(
                ['organization_id' => $organization->id, 'name' => $name],
                ['type' => 'department']
            ));
        }

        return $units;
    }

    private function createChartOfAccounts(Organization $organization): void
    {
        $accounts = [
            ['code' => '1000', 'name' => 'Cash at Bank', 'type' => 'asset'],
            ['code' => '4000', 'name' => 'Service Revenue', 'type' => 'revenue'],
            ['code' => '4100', 'name' => 'Product Sales', 'type' => 'revenue'],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
            ['code' => '5100', 'name' => 'Salaries & Wages', 'type' => 'expense'],
            ['code' => '5200', 'name' => 'Rent & Utilities', 'type' => 'expense'],
            ['code' => '5300', 'name' => 'Marketing', 'type' => 'expense'],
            ['code' => '5400', 'name' => 'General Expenses', 'type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::firstOrCreate(
                ['organization_id' => $organization->id, 'code' => $account['code']],
                $account
            );
        }
    }

    private function createBankAccounts(Organization $organization): void
    {
        $cashAccount = ChartOfAccount::where('organization_id', $organization->id)
            ->where('code', '1000')
            ->firstOrFail();

        $accounts = [
            ['account_name' => 'Primary Checking', 'bank_name' => 'First National', 'balance' => 284500.00, 'type' => 'checking'],
            ['account_name' => 'Growth Savings', 'bank_name' => 'First National', 'balance' => 125000.00, 'type' => 'savings'],
            ['account_name' => 'Operations Reserve', 'bank_name' => 'Meridian Bank', 'balance' => 62000.00, 'type' => 'money_market'],
        ];

        foreach ($accounts as $index => $account) {
            BankAccount::firstOrCreate(
                ['organization_id' => $organization->id, 'account_name' => $account['account_name']],
                [
                    'chart_of_account_id' => $cashAccount->id,
                    'account_number' => '4'.$index.rand(1000000, 9999999),
                    'bank_name' => $account['bank_name'],
                    'currency' => 'USD',
                    'opening_balance' => $account['balance'],
                    'current_balance' => $account['balance'],
                    'opening_balance_date' => now()->subYear(),
                    'account_type' => $account['type'],
                    'status' => 'active',
                ]
            );
        }
    }

    private function createEmployees(Organization $organization, Collection $units): Collection
    {
        $employees = collect();

        $people = [
            ['first_name' => 'Alex', 'last_name' => 'Morgan', 'unit' => 'Head Office'],
            ['first_name' => 'Priya', 'last_name' => 'Sharma', 'unit' => 'Head Office'],
            ['first_name' => 'Diego', 'last_name' => 'Ramirez', 'unit' => 'Operations'],
            ['first_name' => 'Fatima', 'last_name' => 'Al-Sayed', 'unit' => 'Sales'],
            ['first_name' => 'Ethan', 'last_name' => 'Brooks', 'unit' => 'Warehouse'],
            ['first_name' => 'Nina', 'last_name' => 'Petrova', 'unit' => 'Warehouse'],
            ['first_name' => 'Liam', 'last_name' => 'Cheng', 'unit' => 'Operations'],
            ['first_name' => 'Sofia', 'last_name' => 'Novak', 'unit' => 'Sales'],
        ];

        foreach ($people as $person) {
            $employee = Employee::firstOrCreate(
                ['organization_id' => $organization->id, 'email' => strtolower($person['first_name'].'.'.$person['last_name']).'@demo.com'],
                [
                    'first_name' => $person['first_name'],
                    'last_name' => $person['last_name'],
                    'organization_unit_id' => $units[$person['unit']]->id,
                    'is_active' => true,
                    'basic_salary' => rand(3800, 7800),
                    'pay_frequency' => 'monthly',
                ]
            );
            $employees->push($employee);
        }

        return $employees;
    }

    private function createAttendance(Organization $organization, Collection $employees): void
    {
        $start = today()->subDays(13);

        for ($day = 0; $day < 14; $day++) {
            $date = $start->copy()->addDays($day);
            $isWeekend = $date->isWeekend();

            foreach ($employees as $employee) {
                $present = ! $isWeekend ? rand(0, 9) > 0 : rand(0, 9) > 6;

                if (! $present) {
                    continue;
                }

                AttendanceRecord::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'record_date' => $date->format('Y-m-d'),
                    ],
                    [
                        'organization_id' => $organization->id,
                        'punch_in' => $date->copy()->setTime(8, rand(0, 45)),
                        'punch_out' => $date->copy()->setTime(16, rand(30, 59)),
                        'total_hours' => 8 + round(rand(-10, 20) / 10, 1),
                        'status' => 'present',
                        'late_minutes' => rand(0, 30),
                    ]
                );
            }
        }
    }

    private function createLedger(Organization $organization): void
    {
        $revenueAccounts = ChartOfAccount::where('organization_id', $organization->id)
            ->where('type', 'revenue')
            ->pluck('id', 'code')
            ->all();

        $expenseAccounts = ChartOfAccount::where('organization_id', $organization->id)
            ->where('type', 'expense')
            ->pluck('id', 'code')
            ->all();

        $start = now()->startOfMonth()->subMonths(11);

        for ($month = 0; $month < 12; $month++) {
            $monthDate = $start->copy()->addMonths($month);
            $growth = 1 + ($month * 0.04);

            $revenue = [
                '4000' => (int) round(45000 * $growth * rand(95, 108) / 100),
                '4100' => (int) round(28000 * $growth * rand(90, 112) / 100),
            ];

            $expense = [
                '5000' => (int) round(31000 * $growth * rand(92, 106) / 100),
                '5100' => (int) round(22000 * $growth * rand(96, 104) / 100),
                '5200' => (int) round(6500 * rand(90, 110) / 100),
                '5300' => (int) round(4200 * rand(80, 120) / 100),
                '5400' => (int) round(3800 * rand(85, 115) / 100),
            ];

            foreach ($revenue as $code => $amount) {
                LedgerEntry::firstOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'chart_of_account_id' => $revenueAccounts[$code],
                        'entry_date' => $monthDate->copy()->endOfMonth()->format('Y-m-d'),
                        'type' => 'credit',
                        'description' => 'Revenue accrual for '.$monthDate->format('M Y'),
                    ],
                    ['amount' => $amount]
                );
            }

            foreach ($expense as $code => $amount) {
                LedgerEntry::firstOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'chart_of_account_id' => $expenseAccounts[$code],
                        'entry_date' => $monthDate->copy()->endOfMonth()->format('Y-m-d'),
                        'type' => 'debit',
                        'description' => 'Expense for '.$monthDate->format('M Y'),
                    ],
                    ['amount' => $amount]
                );
            }
        }
    }

    private function createVouchers(Organization $organization): void
    {
        $user = User::where('email', self::DEMO_EMAIL)->first();

        $vouchers = [
            ['type' => 'sales', 'number' => 'SALES-'.now()->format('Y').'-0001', 'date' => now()->subDays(2), 'amount' => 18750.50, 'description' => 'Monthly service billing', 'status' => 'posted'],
            ['type' => 'expense', 'number' => 'EXPENSE-'.now()->format('Y').'-0001', 'date' => now()->subDays(5), 'amount' => 4250.00, 'description' => 'Office equipment maintenance', 'status' => 'posted'],
            ['type' => 'salary', 'number' => 'SALARY-'.now()->format('Y').'-0001', 'date' => now()->subDays(9), 'amount' => 48600.00, 'description' => 'Monthly payroll run', 'status' => 'posted'],
            ['type' => 'purchase', 'number' => 'PURCHASE-'.now()->format('Y').'-0001', 'date' => now()->subDays(12), 'amount' => 12300.75, 'description' => 'Wholesale inventory restock', 'status' => 'posted'],
            ['type' => 'sales', 'number' => 'SALES-'.now()->format('Y').'-0002', 'date' => now()->subDays(1), 'amount' => 9420.00, 'description' => 'Retail point-of-sale batch', 'status' => 'posted'],
            ['type' => 'expense', 'number' => 'EXPENSE-'.now()->format('Y').'-0002', 'date' => now()->subDays(3), 'amount' => 1150.99, 'description' => 'Utilities settlement', 'status' => 'posted'],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::firstOrCreate(
                ['organization_id' => $organization->id, 'number' => $voucher['number']],
                array_merge($voucher, ['created_by' => $user->id, 'updated_by' => $user->id])
            );
        }
    }

    private function createInventory(Organization $organization): void
    {
        $warehouseUnit = OrganizationUnit::where('organization_id', $organization->id)
            ->where('name', 'Warehouse')
            ->first();

        $stores = [];

        foreach (['Main Warehouse', 'Retail Outlet'] as $name) {
            $stores[$name] = Store::firstOrCreate(
                ['name' => $name],
                ['organization_unit_id' => $warehouseUnit?->id, 'code' => strtoupper(substr($name, 0, 4)).rand(100, 999), 'location' => 'Demo HQ', 'is_active' => true]
            );
        }

        $items = [
            ['name' => 'Desk Chair Ergonomic', 'sku' => 'SCH-001', 'category' => 'Furniture', 'cost_price' => 145, 'selling_price' => 210, 'reorder_level' => 10, 'qty' => [28, 12]],
            ['name' => 'Standing Desk', 'sku' => 'DSK-002', 'category' => 'Furniture', 'cost_price' => 320, 'selling_price' => 460, 'reorder_level' => 8, 'qty' => [5, 2]],
            ['name' => 'Noise-Cancelling Headset', 'sku' => 'AUD-003', 'category' => 'Electronics', 'cost_price' => 89, 'selling_price' => 149, 'reorder_level' => 20, 'qty' => [42, 15]],
            ['name' => '4K Webcam', 'sku' => 'CAM-004', 'category' => 'Electronics', 'cost_price' => 120, 'selling_price' => 189, 'reorder_level' => 15, 'qty' => [0, 0]],
            ['name' => 'USB-C Docking Station', 'sku' => 'DOK-005', 'category' => 'Electronics', 'cost_price' => 155, 'selling_price' => 239, 'reorder_level' => 12, 'qty' => [7, 4]],
            ['name' => 'Printer Paper (500pk)', 'sku' => 'PPR-006', 'category' => 'Office Supplies', 'cost_price' => 9, 'selling_price' => 14, 'reorder_level' => 50, 'qty' => [200, 36]],
            ['name' => 'Laser Toner Cartridge', 'sku' => 'TNR-007', 'category' => 'Office Supplies', 'cost_price' => 64, 'selling_price' => 95, 'reorder_level' => 25, 'qty' => [0, 8]],
            ['name' => 'Ergonomic Keyboard', 'sku' => 'KEY-008', 'category' => 'Electronics', 'cost_price' => 65, 'selling_price' => 109, 'reorder_level' => 15, 'qty' => [30, 11]],
            ['name' => 'Dual Monitor Arm', 'sku' => 'ARM-009', 'category' => 'Furniture', 'cost_price' => 110, 'selling_price' => 169, 'reorder_level' => 10, 'qty' => [9, 6]],
            ['name' => 'Premium Office Coffee', 'sku' => 'COF-010', 'category' => 'Pantry', 'cost_price' => 18, 'selling_price' => 29, 'reorder_level' => 30, 'qty' => [85, 22]],
        ];

        foreach ($items as $itemData) {
            $quantities = $itemData['qty'];
            unset($itemData['qty']);

            $item = Item::firstOrCreate(
                ['organization_id' => $organization->id, 'sku' => $itemData['sku']],
                $itemData
            );

            foreach ($stores as $store) {
                $quantity = array_shift($quantities);

                $item->stores()->syncWithoutDetaching([
                    $store->id => ['quantity' => $quantity, 'min_stock' => 5, 'max_stock' => 500],
                ]);
            }
        }
    }

    private function createInventoryTransactions(Organization $organization): void
    {
        $user = User::where('email', self::DEMO_EMAIL)->first();
        $store = Store::where('name', 'Main Warehouse')->first();
        $outlet = Store::where('name', 'Retail Outlet')->first();

        if (! $store) {
            return;
        }

        $receipts = [
            ['reference' => 'RCV-'.now()->format('Ymd').'-001', 'transaction_date' => now()->subDays(4), 'type' => 'receipt', 'status' => 'finalized', 'supplier' => 'Furnitura Ltd.'],
            ['reference' => 'ISU-'.now()->format('Ymd').'-001', 'transaction_date' => now()->subDays(2), 'type' => 'issue', 'status' => 'finalized', 'recipient' => 'Head Office'],
            ['reference' => 'ADJ-'.now()->format('Ymd').'-001', 'transaction_date' => now()->subDays(1), 'type' => 'adjustment', 'status' => 'finalized', 'adjustment_reason' => 'Cycle count variance'],
            ['reference' => 'RCV-'.now()->format('Ymd').'-002', 'transaction_date' => now()->subHours(5), 'type' => 'receipt', 'status' => 'draft', 'supplier' => 'TechNova Wholesale'],
            ['reference' => 'TRF-'.now()->format('Ymd').'-001', 'transaction_date' => now()->subHours(2), 'type' => 'transfer', 'status' => 'finalized', 'to_store_id' => $outlet?->id, 'notes' => 'Replenishment to Retail Outlet'],
        ];

        foreach ($receipts as $receipt) {
            Transaction::firstOrCreate(
                ['reference' => $receipt['reference']],
                array_merge($receipt, [
                    'store_id' => $store->id,
                    'created_by' => $user->id,
                    'approved_by' => $user->id,
                    'finalized_at' => $receipt['status'] === 'finalized' ? $receipt['transaction_date'] : null,
                    'notes' => $receipt['notes'] ?? 'Seeded demo transaction',
                ])
            );
        }
    }

    private function createMemberships(Organization $organization): void
    {
        $plans = collect();

        $planData = [
            ['name' => 'Basic Monthly', 'plan_type' => 'individual', 'billing_frequency' => 'monthly', 'amount' => 29.99],
            ['name' => 'Premier Monthly', 'plan_type' => 'individual', 'billing_frequency' => 'monthly', 'amount' => 59.99],
            ['name' => 'Family Plan', 'plan_type' => 'family', 'billing_frequency' => 'monthly', 'amount' => 99.99],
        ];

        foreach ($planData as $data) {
            $plans->push(SubscriptionPlan::firstOrCreate(
                ['organization_id' => $organization->id, 'name' => $data['name']],
                array_merge($data, [
                    'description' => 'Demo membership plan',
                    'family_members_included' => $data['plan_type'] === 'family' ? 4 : 0,
                    'additional_family_member_fee' => 15,
                    'benefits' => json_encode(['Demo benefit A', 'Demo benefit B']),
                    'is_active' => true,
                ])
            ));
        }

        $memberData = [
            ['first_name' => 'Olivia', 'last_name' => 'Bennett', 'status' => 'active', 'plan' => 0, 'end_offset' => 60],
            ['first_name' => 'Marcus', 'last_name' => 'Wright', 'status' => 'active', 'plan' => 1, 'end_offset' => 20],
            ['first_name' => 'Aisha', 'last_name' => 'Khan', 'status' => 'active', 'plan' => 2, 'end_offset' => 12],
            ['first_name' => 'Tom', 'last_name' => 'Hale', 'status' => 'active', 'plan' => 0, 'end_offset' => 8],
            ['first_name' => 'Grace', 'last_name' => 'Liu', 'status' => 'active', 'plan' => 1, 'end_offset' => 25],
            ['first_name' => 'Omar', 'last_name' => 'Farouk', 'status' => 'active', 'plan' => 2, 'end_offset' => 45],
            ['first_name' => 'Julia', 'last_name' => 'Moreau', 'status' => 'inactive', 'plan' => 1, 'end_offset' => -30],
            ['first_name' => 'Victor', 'last_name' => 'Kowalski', 'status' => 'inactive', 'plan' => 0, 'end_offset' => -60],
            ['first_name' => 'Lena', 'last_name' => 'Fischer', 'status' => 'active', 'plan' => 0, 'end_offset' => 6],
            ['first_name' => 'Sam', 'last_name' => 'Turner', 'status' => 'active', 'plan' => 1, 'end_offset' => 15],
        ];

        $members = collect();

        foreach ($memberData as $index => $data) {
            $member = Member::firstOrCreate(
                ['organization_id' => $organization->id, 'email' => strtolower($data['first_name'].'.'.$data['last_name']).'@example.com'],
                [
                    'membership_number' => 'MEM-'.now()->format('Y').'-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'barcode_number' => '4'.rand(100000000, 999999999),
                    'status' => $data['status'],
                    'join_date' => now()->subMonths(15),
                ]
            );
            $members->push([$member, $data]);

            MemberSubscription::firstOrCreate(
                ['organization_id' => $organization->id, 'member_id' => $member->id],
                [
                    'subscription_plan_id' => $plans[$data['plan']]->id,
                    'start_date' => now()->subDays(45),
                    'end_date' => now()->addDays($data['end_offset']),
                    'status' => $data['status'] === 'active' ? 'active' : 'expired',
                    'total_amount' => $plans[$data['plan']]->amount,
                    'paid_amount' => $plans[$data['plan']]->amount,
                ]
            );
        }
    }
}
