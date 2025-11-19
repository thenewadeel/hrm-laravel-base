<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Vendor;
use App\Services\ExpenseVoucherService;
use App\Services\PurchaseVoucherService;
use App\Services\SalaryVoucherService;
use App\Services\SalesVoucherService;
use Tests\Traits\SetupOrganization;

uses(SetupOrganization::class);

test('can create sales voucher', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    // Refresh user to get updated organization_id
    $user->refresh();

    $customer = Customer::factory()->create(['organization_id' => $organization->id]);

    // Create required accounts
    ChartOfAccount::factory()->create(['code' => '4000', 'type' => 'revenue', 'organization_id' => $organization->id]); // Sales Revenue
    ChartOfAccount::factory()->create(['code' => '1200', 'type' => 'asset', 'organization_id' => $organization->id]); // Receivables
    ChartOfAccount::factory()->create(['code' => '2000', 'type' => 'liability', 'organization_id' => $organization->id]); // Tax Payable

    // Debug: Check if models exist
    expect(Customer::find($customer->id))->not->toBeNull();
    expect(auth()->user()->current_organization_id)->toBe($organization->id);

    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Test Sales Voucher',
        'customer_id' => $customer->id,
        'line_items' => [
            [
                'description' => 'Test Product',
                'quantity' => 2,
                'unit_price' => 100.00,
            ],
        ],
        'tax_amount' => 20.00,
    ];

    $voucher = app(SalesVoucherService::class)->createSalesVoucher($data);

    expect($voucher)->toBeInstanceOf(JournalEntry::class);
    expect($voucher->voucher_type)->toBe('SALES');
    expect($voucher->customer_id)->toBe($customer->id);
    expect((float) $voucher->total_amount)->toBe(220.0);
    expect($voucher->status)->toBe('posted');
});

test('can create purchase voucher', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    $vendor = Vendor::factory()->create(['organization_id' => $organization->id]);

    // Create required accounts
    ChartOfAccount::factory()->create(['code' => '5000', 'type' => 'expense', 'organization_id' => $organization->id]); // Purchase Expense
    ChartOfAccount::factory()->create(['code' => '2000', 'type' => 'liability', 'organization_id' => $organization->id]); // Payables
    ChartOfAccount::factory()->create(['code' => '1200', 'type' => 'asset', 'organization_id' => $organization->id]); // Tax Receivable

    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Test Purchase Voucher',
        'vendor_id' => $vendor->id,
        'line_items' => [
            [
                'description' => 'Test Purchase',
                'quantity' => 1,
                'unit_price' => 500.00,
            ],
        ],
        'tax_amount' => 50.00,
    ];

    $voucher = app(PurchaseVoucherService::class)->createPurchaseVoucher($data);

    expect($voucher)->toBeInstanceOf(JournalEntry::class);
    expect($voucher->voucher_type)->toBe('PURCHASE');
    expect($voucher->vendor_id)->toBe($vendor->id);
    expect((float) $voucher->total_amount)->toBe(550.0);
    expect($voucher->status)->toBe('posted');
});

test('can create salary voucher', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    $employee = Employee::factory()->create(['organization_id' => $organization->id]);

    // Create required accounts
    ChartOfAccount::factory()->create(['code' => '5100', 'type' => 'expense', 'organization_id' => $organization->id]); // Salary Expense
    ChartOfAccount::factory()->create(['code' => '1000', 'type' => 'asset', 'organization_id' => $organization->id]); // Cash
    ChartOfAccount::factory()->create(['code' => '2100', 'type' => 'liability', 'organization_id' => $organization->id]); // Tax Payable
    ChartOfAccount::factory()->create(['code' => '2101', 'type' => 'liability', 'organization_id' => $organization->id]); // Other Deductions

    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Test Salary Voucher',
        'employee_id' => $employee->id,
        'salary_amount' => 3000.00,
        'tax_deduction' => 300.00,
        'other_deductions' => 200.00,
    ];

    $voucher = app(SalaryVoucherService::class)->createSalaryVoucher($data);

    expect($voucher)->toBeInstanceOf(JournalEntry::class);
    expect($voucher->voucher_type)->toBe('SALARY');
    expect((float) $voucher->total_amount)->toBe(3000.0);
    expect((float) $voucher->tax_amount)->toBe(300.0);
    expect($voucher->status)->toBe('posted');
});

test('can create expense voucher', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    // Create required accounts
    ChartOfAccount::factory()->create(['code' => '5200', 'type' => 'expense', 'organization_id' => $organization->id]); // Rent Expense
    ChartOfAccount::factory()->create(['code' => '1000', 'type' => 'asset', 'organization_id' => $organization->id]); // Cash

    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Test Rent Payment',
        'expense_account_code' => '5200',
        'amount' => 1500.00,
    ];

    $voucher = app(ExpenseVoucherService::class)->createExpenseVoucher($data);

    expect($voucher)->toBeInstanceOf(JournalEntry::class);
    expect($voucher->voucher_type)->toBe('EXPENSE');
    expect((float) $voucher->total_amount)->toBe(1500.0);
    expect($voucher->status)->toBe('posted');
});
