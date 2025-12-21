<?php

namespace Tests\Browser\E2E\Fixtures;

use App\Models\Organization;
use App\Models\User;
use App\Models\Employee;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
// use App\Models\Membership\Fee; // Commented out - model doesn't exist
// use App\Models\Membership\Member; // Commented out - model doesn't exist

class E2ETestFixtures
{
    /**
     * Create complete organization setup for testing.
     */
    public static function createOrganizationSetup(): array
    {
        $organization = Organization::factory()->create([
            'name' => 'E2E Test Organization',
        ]);

        // Create users with different roles
        $admin = self::createUser($organization, 'admin', 'admin@e2e.test');
        $manager = self::createUser($organization, 'manager', 'manager@e2e.test');
        $member = self::createUser($organization, 'member', 'member@e2e.test');
        $employee = self::createUser($organization, 'employee', 'employee@e2e.test');

        // Create financial setup
        $chartOfAccounts = self::createChartOfAccounts($organization);
        
        // Create employees
        $employees = self::createEmployees($organization);
        
        // Create inventory setup
        $stores = self::createStores($organization);
        $items = self::createInventoryItems($organization);
        
        // Create membership setup - disabled (models don't exist)
        // $fees = self::createMembershipFees($organization);
        // $members = self::createMembers($organization);

        return [
            'organization' => $organization,
            'users' => compact('admin', 'manager', 'member', 'employee'),
            'chart_of_accounts' => $chartOfAccounts,
            'employees' => $employees,
            'stores' => $stores,
            'items' => $items,
            // 'fees' => $fees, // Disabled - models don't exist
            // 'members' => $members, // Disabled - models don't exist
        ];
    }

    /**
     * Create a user with specific role.
     */
    private static function createUser(Organization $organization, string $role, string $email): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'email_verified_at' => now(),
            'name' => ucwords($role) . ' User',
        ]);

        $organization->users()->attach($user->id, [
            'roles' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    /**
     * Create chart of accounts for organization.
     */
    private static function createChartOfAccounts(Organization $organization): array
    {
        return [
            'cash' => ChartOfAccount::factory()->create([
                'organization_id' => $organization->id,
                'type' => 'asset',
                'name' => 'Cash Account',
                'code' => '1001',
            ]),
            'bank' => ChartOfAccount::factory()->create([
                'organization_id' => $organization->id,
                'type' => 'asset',
                'name' => 'Bank Account',
                'code' => '1002',
            ]),
            'accounts_receivable' => ChartOfAccount::factory()->create([
                'organization_id' => $organization->id,
                'type' => 'asset',
                'name' => 'Accounts Receivable',
                'code' => '1003',
            ]),
            'revenue' => ChartOfAccount::factory()->create([
                'organization_id' => $organization->id,
                'type' => 'revenue',
                'name' => 'Sales Revenue',
                'code' => '4001',
            ]),
            'expenses' => ChartOfAccount::factory()->create([
                'organization_id' => $organization->id,
                'type' => 'expense',
                'name' => 'Operating Expenses',
                'code' => '5001',
            ]),
            'salary_expense' => ChartOfAccount::factory()->create([
                'organization_id' => $organization->id,
                'type' => 'expense',
                'name' => 'Salary Expense',
                'code' => '5002',
            ]),
        ];
    }

    /**
     * Create employees for organization.
     */
    private static function createEmployees(Organization $organization): array
    {
        return [
            'john_doe' => Employee::factory()->create([
                'organization_id' => $organization->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@company.com',
                'employee_id' => 'EMP001',
                'employment_type' => 'full_time',
                'salary' => 60000.00,
                'department' => 'Engineering',
                'position' => 'Senior Developer',
                'hire_date' => now()->subMonths(12),
            ]),
            'jane_smith' => Employee::factory()->create([
                'organization_id' => $organization->id,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@company.com',
                'employee_id' => 'EMP002',
                'employment_type' => 'full_time',
                'salary' => 55000.00,
                'department' => 'Marketing',
                'position' => 'Marketing Manager',
                'hire_date' => now()->subMonths(8),
            ]),
            'bob_wilson' => Employee::factory()->create([
                'organization_id' => $organization->id,
                'first_name' => 'Bob',
                'last_name' => 'Wilson',
                'email' => 'bob.wilson@company.com',
                'employee_id' => 'EMP003',
                'employment_type' => 'part_time',
                'salary' => 30000.00,
                'department' => 'Sales',
                'position' => 'Sales Representative',
                'hire_date' => now()->subMonths(6),
            ]),
        ];
    }

    /**
     * Create inventory stores for organization.
     */
    private static function createStores(Organization $organization): array
    {
        return [
            'main_store' => Store::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'Main Store',
                'location' => 'Building A, Floor 1',
                'store_type' => 'retail',
                'is_active' => true,
            ]),
            'warehouse' => Store::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'Main Warehouse',
                'location' => 'Building B, Warehouse Area',
                'store_type' => 'warehouse',
                'is_active' => true,
            ]),
            'secondary_store' => Store::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'Secondary Store',
                'location' => 'Building C, Ground Floor',
                'store_type' => 'retail',
                'is_active' => true,
            ]),
        ];
    }

    /**
     * Create inventory items for organization.
     */
    private static function createInventoryItems(Organization $organization): array
    {
        return [
            'laptop' => Item::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'Laptop Computer',
                'sku' => 'LAPTOP-001',
                'description' => 'Business laptop computer',
                'unit_price' => 1200.00,
                'cost_price' => 800.00,
                'reorder_level' => 10,
                'category' => 'Electronics',
            ]),
            'office_chair' => Item::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'Office Chair',
                'sku' => 'CHAIR-001',
                'description' => 'Ergonomic office chair',
                'unit_price' => 300.00,
                'cost_price' => 200.00,
                'reorder_level' => 15,
                'category' => 'Furniture',
            ]),
            'desk' => Item::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'Office Desk',
                'sku' => 'DESK-001',
                'description' => 'Standard office desk',
                'unit_price' => 500.00,
                'cost_price' => 350.00,
                'reorder_level' => 5,
                'category' => 'Furniture',
            ]),
            'printer_paper' => Item::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'Printer Paper',
                'sku' => 'PAPER-001',
                'description' => 'A4 printer paper ream',
                'unit_price' => 15.00,
                'cost_price' => 10.00,
                'reorder_level' => 50,
                'category' => 'Office Supplies',
            ]),
        ];
    }

    /*
     * Membership methods disabled - models don't exist
     *
    private static function createMembershipFees(Organization $organization): array
    {
        return [
            'annual_membership' => Fee::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'Annual Membership Fee',
                'description' => 'Annual membership subscription',
                'amount' => 1200.00,
                'frequency' => 'annual',
                'is_active' => true,
            ]),
            'registration_fee' => Fee::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'One-Time Registration Fee',
                'description' => 'Initial registration fee',
                'amount' => 500.00,
                'frequency' => 'one_time',
                'is_active' => true,
            ]),
            'monthly_dues' => Fee::factory()->create([
                'organization_id' => $organization->id,
                'name' => 'Monthly Dues',
                'description' => 'Monthly membership dues',
                'amount' => 100.00,
                'frequency' => 'monthly',
                'is_active' => true,
            ]),
        ];
    }

    private static function createMembers(Organization $organization): array
    {
        return [
            'alice_johnson' => Member::factory()->create([
                'organization_id' => $organization->id,
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'email' => 'alice.johnson@email.com',
                'member_number' => 'MEM001',
                'membership_type' => 'premium',
                'join_date' => now()->subMonths(6),
                'status' => 'active',
            ]),
            'charlie_brown' => Member::factory()->create([
                'organization_id' => $organization->id,
                'first_name' => 'Charlie',
                'last_name' => 'Brown',
                'email' => 'charlie.brown@email.com',
                'member_number' => 'MEM002',
                'membership_type' => 'standard',
                'join_date' => now()->subMonths(3),
                'status' => 'active',
            ]),
            'diana_prince' => Member::factory()->create([
                'organization_id' => $organization->id,
                'first_name' => 'Diana',
                'last_name' => 'Prince',
                'email' => 'diana.prince@email.com',
                'member_number' => 'MEM003',
                'membership_type' => 'premium',
                'join_date' => now()->subMonths(12),
                'status' => 'active',
            ]),
        ];
    }
    */

    /**
     * Create sample journal entries for testing.
     */
    public static function createJournalEntries(Organization $organization, array $chartOfAccounts): array
    {
        $entries = [];

        // Sales transaction
        $salesEntry = JournalEntry::factory()->create([
            'organization_id' => $organization->id,
            'entry_number' => 'JE001',
            'entry_date' => now()->subDays(5),
            'description' => 'Sales to Customer ABC',
            'total_amount' => 5000.00,
        ]);

        // Add ledger entries for sales
        $salesEntry->ledgerEntries()->createMany([
            [
                'chart_of_account_id' => $chartOfAccounts['accounts_receivable']->id,
                'debit' => 5000.00,
                'credit' => 0.00,
                'description' => 'Accounts Receivable - Customer ABC',
            ],
            [
                'chart_of_account_id' => $chartOfAccounts['revenue']->id,
                'debit' => 0.00,
                'credit' => 5000.00,
                'description' => 'Sales Revenue',
            ],
        ]);

        $entries['sales'] = $salesEntry;

        // Expense transaction
        $expenseEntry = JournalEntry::factory()->create([
            'organization_id' => $organization->id,
            'entry_number' => 'JE002',
            'entry_date' => now()->subDays(3),
            'description' => 'Office Supplies Purchase',
            'total_amount' => 1500.00,
        ]);

        // Add ledger entries for expense
        $expenseEntry->ledgerEntries()->createMany([
            [
                'chart_of_account_id' => $chartOfAccounts['expenses']->id,
                'debit' => 1500.00,
                'credit' => 0.00,
                'description' => 'Office Supplies Expense',
            ],
            [
                'chart_of_account_id' => $chartOfAccounts['cash']->id,
                'debit' => 0.00,
                'credit' => 1500.00,
                'description' => 'Cash Payment',
            ],
        ]);

        $entries['expense'] = $expenseEntry;

        return $entries;
    }

    /**
     * Create sample inventory transactions.
     */
    public static function createInventoryTransactions(Organization $organization, array $items, array $stores): array
    {
        $transactions = [];

        // Stock IN transaction
        $stockIn = \App\Models\Inventory\Transaction::factory()->create([
            'organization_id' => $organization->id,
            'transaction_number' => 'TXN001',
            'transaction_type' => 'IN',
            'store_id' => $stores['main_store']->id,
            'transaction_date' => now()->subDays(2),
            'reference_number' => 'PO001',
            'notes' => 'Purchase from Vendor XYZ',
        ]);

        // Add transaction details
        $stockIn->transactionDetails()->createMany([
            [
                'item_id' => $items['laptop']->id,
                'quantity' => 20,
                'unit_cost' => 800.00,
                'total_cost' => 16000.00,
            ],
            [
                'item_id' => $items['office_chair']->id,
                'quantity' => 25,
                'unit_cost' => 200.00,
                'total_cost' => 5000.00,
            ],
        ]);

        $transactions['stock_in'] = $stockIn;

        // Stock OUT transaction
        $stockOut = \App\Models\Inventory\Transaction::factory()->create([
            'organization_id' => $organization->id,
            'transaction_number' => 'TXN002',
            'transaction_type' => 'OUT',
            'store_id' => $stores['main_store']->id,
            'transaction_date' => now()->subDays(1),
            'reference_number' => 'SALE001',
            'notes' => 'Sale to Customer ABC',
        ]);

        // Add transaction details
        $stockOut->transactionDetails()->createMany([
            [
                'item_id' => $items['laptop']->id,
                'quantity' => 5,
                'unit_cost' => 1200.00,
                'total_cost' => 6000.00,
            ],
            [
                'item_id' => $items['office_chair']->id,
                'quantity' => 10,
                'unit_cost' => 300.00,
                'total_cost' => 3000.00,
            ],
        ]);

        $transactions['stock_out'] = $stockOut;

        return $transactions;
    }

    /**
     * Clean up all test fixtures.
     */
    public static function cleanup(array $fixtures): void
    {
        if (isset($fixtures['organization'])) {
            $fixtures['organization']->delete();
        }
    }
}