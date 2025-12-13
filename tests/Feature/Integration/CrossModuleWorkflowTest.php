<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\Voucher;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use App\Models\User;
use App\Services\AccountingService;
use App\Services\InventoryService;
use App\Services\PayrollCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Cross-Module Integration Tests
test('payroll to accounting integration creates salary vouchers', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Setup chart of accounts for payroll
    $salaryAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
        'name' => 'Salary Expense',
        'code' => '5000',
    ]);
    
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account',
        'code' => '1000',
    ]);
    
    // Create employee for payroll
    $employee = \App\Models\Employee::factory()->create([
        'organization_id' => $organization->id,
    ]);
    
    // Process payroll which should create accounting voucher
    $payrollData = [
        'employee_id' => $employee->id,
        'basic_salary' => 50000,
        'allowances' => [],
        'deductions' => [],
        'pay_period' => now()->format('Y-m'),
    ];
    
    $payrollService = app(PayrollService::class);
    $payroll = $payrollService->processPayroll($organization->id, [$payrollData]);
    
    // Verify payroll was processed
    expect($payroll)->toBeTruthy();
    
    // Verify salary voucher was created in accounting
    $salaryVoucher = Voucher::where('type', 'salary')
        ->where('organization_id', $organization->id)
        ->first();
    
    expect($salaryVoucher)->toBeTruthy();
    expect($salaryVoucher->amount)->toBe(50000);
    
    // Verify journal entries were created
    $journalEntries = JournalEntry::where('voucher_id', $salaryVoucher->id)->get();
    expect($journalEntries)->toHaveCount(2); // Debit and Credit entries
    
    // Verify proper double-entry bookkeeping
    $totalDebits = $journalEntries->where('type', 'debit')->sum('amount');
    $totalCredits = $journalEntries->where('type', 'credit')->sum('amount');
    expect($totalDebits)->toBe($totalCredits);
});

test('inventory to accounting integration posts stock movements', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Setup chart of accounts for inventory
    $inventoryAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Inventory',
        'code' => '1200',
    ]);
    
    $cogsAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
        'name' => 'Cost of Goods Sold',
        'code' => '5001',
    ]);
    
    // Create store and items
    $store = Store::factory()->create(['organization_id' => $organization->id]);
    $item = Item::factory()->create(['organization_id' => $organization->id]);
    
    // Add item to store with initial stock
    $inventoryService = app(InventoryService::class);
    $inventoryService->updateStoreInventory($store->id, [
        ['item_id' => $item->id, 'quantity' => 100, 'cost' => 50]
    ]);
    
    // Process inventory transaction (stock out)
    $transaction = Transaction::factory()->create([
        'organization_id' => $organization->id,
        'store_id' => $store->id,
        'type' => 'OUT',
        'status' => 'finalized',
    ]);
    
    $transaction->items()->attach($item->id, ['quantity' => 10, 'unit_cost' => 50]);
    
    // Finalize transaction which should create accounting entries
    $accountingService = app(AccountingService::class);
    $accountingService->postInventoryTransaction($transaction);
    
    // Verify accounting entries were created
    $journalEntries = JournalEntry::where('reference_type', 'inventory_transaction')
        ->where('reference_id', $transaction->id)
        ->get();
    
    expect($journalEntries)->toHaveCount(2); // Should have debit and credit entries
    
    // Verify proper accounts were used
    $usedAccounts = $journalEntries->pluck('chart_of_account_id')->unique();
    expect($usedAccounts)->toContain($inventoryAccount->id);
    expect($usedAccounts)->toContain($cogsAccount->id);
});

test('membership fee to accounting integration creates cash receipts', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Setup chart of accounts
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash',
        'code' => '1000',
    ]);
    
    $membershipRevenueAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
        'name' => 'Membership Revenue',
        'code' => '4000',
    ]);
    
    // Create member and fee
    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $memberFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'amount' => 1000,
        'fee_type' => 'subscription',
        'status' => 'paid',
        'paid_amount' => 1000,
        'paid_date' => now(),
    ]);
    
    // Process fee payment which should create cash receipt
    $accountingService = app(AccountingService::class);
    $cashReceipt = $accountingService->processMembershipPayment($memberFee);
    
    // Verify cash receipt was created
    expect($cashReceipt)->toBeTruthy();
    expect($cashReceipt->amount)->toBe(1000);
    
    // Verify journal entries were created
    $journalEntries = JournalEntry::where('reference_type', 'member_fee')
        ->where('reference_id', $memberFee->id)
        ->get();
    
    expect($journalEntries)->toHaveCount(2); // Debit cash, credit revenue
    
    // Verify proper accounts were used
    $usedAccounts = $journalEntries->pluck('chart_of_account_id')->unique();
    expect($usedAccounts)->toContain($cashAccount->id);
    expect($usedAccounts)->toContain($membershipRevenueAccount->id);
});

test('multi-module data consistency across organization boundaries', function () {
    $org1 = Organization::factory()->create();
    $org2 = Organization::factory()->create();
    
    $user1 = User::factory()->create(['current_organization_id' => $org1->id]);
    $user2 = User::factory()->create(['current_organization_id' => $org2->id]);
    
    // Create data in each organization
    $employee1 = \App\Models\Employee::factory()->create(['organization_id' => $org1->id]);
    $employee2 = \App\Models\Employee::factory()->create(['organization_id' => $org2->id]);
    
    $item1 = Item::factory()->create(['organization_id' => $org1->id]);
    $item2 = Item::factory()->create(['organization_id' => $org2->id]);
    
    // Test data isolation - users should only see their organization's data
    expect($user1->can('view-employees'))->toBeTruthy();
    expect($user1->can('view-items'))->toBeTruthy();
    
    // User 1 should not see Organization 2 data
    $this->actingAs($user1)
        ->getJson("/api/employees/{$employee2->id}")
        ->assertStatus(404); // Not found due to organization scope
    
    $this->actingAs($user1)
        ->getJson("/api/inventory/items/{$item2->id}")
        ->assertStatus(404); // Not found due to organization scope
    
    // User 2 should see their own data
    $this->actingAs($user2)
        ->getJson("/api/employees/{$employee2->id}")
        ->assertStatus(200);
    
    $this->actingAs($user2)
        ->getJson("/api/inventory/items/{$item2->id}")
        ->assertStatus(200);
});

test('financial year management affects all modules', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Create financial year
    $financialYear = \App\Models\Accounting\FinancialYear::factory()->create([
        'organization_id' => $organization->id,
        'name' => "Fiscal Year 2024-2025",
        'code' => "FY2024-2025",
        'start_date' => "2024-01-01",
        'end_date' => "2025-12-31",
        'status' => 'active',
    ]);
    
    // Create transactions in active year
    $voucher = Voucher::factory()->create([
        'organization_id' => $organization->id,
        'amount' => 1000,
        'date' => now()->setYear(2024),
    ]);
    
    // Verify transactions are allowed in active year
    $this->actingAs($user)
        ->postJson("/api/vouchers", [
            'date' => now()->setYear(2024),
            'amount' => 500,
        ])
        ->assertStatus(201); // Created successfully
    
    // Try to create transaction in different year (should fail if year is closed)
    $closedYear = \App\Models\Accounting\FinancialYear::factory()->create([
        'organization_id' => $organization->id,
        'year' => 2023,
        'status' => 'locked',
    ]);
    
    $this->actingAs($user)
        ->postJson("/api/vouchers", [
            'date' => now()->setYear(2023),
            'amount' => 500,
        ])
        ->assertStatus(422); // Should fail due to locked year
});

test('audit trail captures cross-module activities', function () {
    Event::fake();
    
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Process payroll (should create audit entries)
    $employee = \App\Models\Employee::factory()->create(['organization_id' => $organization->id]);
    $payrollService = app(PayrollService::class);
    $payroll = $payrollService->processPayroll($organization->id, [[
        'employee_id' => $employee->id,
        'basic_salary' => 50000,
        'pay_period' => now()->format('Y-m'),
    ]]);
    
    // Process inventory transaction (should create audit entries)
    $store = Store::factory()->create(['organization_id' => $organization->id]);
    $item = Item::factory()->create(['organization_id' => $organization->id]);
    $transaction = Transaction::factory()->create([
        'organization_id' => $organization->id,
        'store_id' => $store->id,
        'type' => 'OUT',
        'status' => 'finalized',
    ]);
    
    $inventoryService = app(InventoryService::class);
    $inventoryService->postInventoryTransaction($transaction);
    
    // Process membership fee payment (should create audit entries)
    $member = Member::factory()->create(['organization_id' => $organization->id]);
    $memberFee = MemberFee::factory()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
        'amount' => 1000,
        'status' => 'paid',
        'paid_amount' => 1000,
        'paid_date' => now(),
    ]);
    
    $accountingService = app(AccountingService::class);
    $accountingService->processMembershipPayment($memberFee);
    
    // Verify audit events were dispatched
    Event::assertDispatchedTimes('App\\Events\\Payroll\\PayrollProcessed', 1);
    Event::assertDispatchedTimes('App\\Events\\Inventory\\TransactionPosted', 1);
    Event::assertDispatchedTimes('App\\Events\\Membership\\FeePaymentProcessed', 1);
    
    // Verify audit trail contains user information
    $events = Event::dispatched();
    
    $payrollEvent = $events->first(fn ($event) => $event instanceof \App\Events\Payroll\PayrollProcessed);
    expect($payrollEvent->user_id)->toBe($user->id);
    expect($payrollEvent->organization_id)->toBe($organization->id);
    
    $transactionEvent = $events->first(fn ($event) => $event instanceof \App\Events\Inventory\TransactionPosted);
    expect($transactionEvent->user_id)->toBe($user->id);
    expect($transactionEvent->organization_id)->toBe($organization->id);
    
    $paymentEvent = $events->first(fn ($event) => $event instanceof \App\Events\Membership\FeePaymentProcessed);
    expect($paymentEvent->user_id)->toBe($user->id);
    expect($paymentEvent->organization_id)->toBe($organization->id);
});