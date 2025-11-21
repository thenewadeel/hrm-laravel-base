<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ExpenseVoucherService;
use App\Services\PurchaseVoucherService;
use App\Services\SalaryVoucherService;
use App\Services\SalesVoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create(['current_organization_id' => $this->organization->id]);

    // Create required chart of accounts
    ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '4000',
        'name' => 'Sales Revenue',
        'type' => 'revenue',
    ]);

    ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '1200',
        'name' => 'Accounts Receivable',
        'type' => 'asset',
    ]);

    ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '2000',
        'name' => 'Accounts Payable',
        'type' => 'liability',
    ]);

    ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '5000',
        'name' => 'Purchase Expense',
        'type' => 'expense',
    ]);

    ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '5100',
        'name' => 'Salary Expense',
        'type' => 'expense',
    ]);

    ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '1000',
        'name' => 'Cash',
        'type' => 'asset',
    ]);

    ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '2100',
        'name' => 'Tax Payable',
        'type' => 'liability',
    ]);

    ChartOfAccount::factory()->create([
        'organization_id' => $this->organization->id,
        'code' => '2101',
        'name' => 'Other Deductions Payable',
        'type' => 'liability',
    ]);

    $this->actingAs($this->user);
});

test('sales voucher can be created with line items', function () {
    $customer = Customer::factory()->create(['organization_id' => $this->organization->id]);

    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Test Sales Invoice',
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-001',
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'tax_amount' => 100,
        'line_items' => [
            [
                'description' => 'Product A',
                'quantity' => 2,
                'unit_price' => 500,
            ],
            [
                'description' => 'Product B',
                'quantity' => 1,
                'unit_price' => 300,
            ],
        ],
    ];

    $service = app(SalesVoucherService::class);
    $voucher = $service->createSalesVoucher($data);

    expect($voucher)->toBeInstanceOf(JournalEntry::class);
    expect($voucher->voucher_type)->toBe('SALES');
    expect($voucher->customer_id)->toBe($customer->id);
    expect((float) $voucher->total_amount)->toBe(1400.0); // 1300 + 100 tax
    expect((float) $voucher->tax_amount)->toBe(100.0);
    expect($voucher->status)->toBe('posted');
    expect($voucher->reference_number)->toMatch('/^SALES-\d{6}$/');

    // Check ledger entries
    expect($voucher->ledgerEntries)->toHaveCount(4); // 2 revenue + 1 tax + 1 receivable
});

test('purchase voucher can be created with line items', function () {
    $vendor = Vendor::factory()->create(['organization_id' => $this->organization->id]);

    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Test Purchase Invoice',
        'vendor_id' => $vendor->id,
        'invoice_number' => 'BILL-001',
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'tax_amount' => 80,
        'line_items' => [
            [
                'description' => 'Raw Material A',
                'quantity' => 10,
                'unit_price' => 50,
            ],
            [
                'description' => 'Raw Material B',
                'quantity' => 5,
                'unit_price' => 20,
            ],
        ],
    ];

    $service = app(PurchaseVoucherService::class);
    $voucher = $service->createPurchaseVoucher($data);

    expect($voucher)->toBeInstanceOf(JournalEntry::class);
    expect($voucher->voucher_type)->toBe('PURCHASE');
    expect($voucher->vendor_id)->toBe($vendor->id);
    expect((float) $voucher->total_amount)->toBe(680.0); // 600 + 80 tax
    expect((float) $voucher->tax_amount)->toBe(80.0);
    expect($voucher->status)->toBe('posted');
    expect($voucher->reference_number)->toMatch('/^PURCHASE-\d{6}$/');

    // Check ledger entries
    expect($voucher->ledgerEntries)->toHaveCount(4); // 2 expense + 1 tax + 1 payable
});

test('salary voucher can be created with deductions', function () {
    $employee = Employee::factory()->create(['organization_id' => $this->organization->id]);

    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Monthly Salary Payment',
        'employee_id' => $employee->id,
        'salary_amount' => 5000,
        'tax_deduction' => 500,
        'other_deductions' => 200,
        'payroll_period' => 'November 2025',
    ];

    $service = app(SalaryVoucherService::class);
    $voucher = $service->createSalaryVoucher($data);

    expect($voucher)->toBeInstanceOf(JournalEntry::class);
    expect($voucher->voucher_type)->toBe('SALARY');
    expect((float) $voucher->total_amount)->toBe(5000.0);
    expect((float) $voucher->tax_amount)->toBe(500.0);
    expect($voucher->status)->toBe('posted');
    expect($voucher->reference_number)->toMatch('/^SALARY-\d{6}$/');

    // Check ledger entries
    expect($voucher->ledgerEntries)->toHaveCount(4); // salary expense + tax payable + deductions payable + cash
});

test('expense voucher can be created', function () {
    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Office Rent Payment',
        'expense_account_code' => '5000',
        'amount' => 2000,
        'reference' => 'RENT-001',
        'notes' => 'Monthly office rent for November 2025',
    ];

    $service = app(ExpenseVoucherService::class);
    $voucher = $service->createExpenseVoucher($data);

    expect($voucher)->toBeInstanceOf(JournalEntry::class);
    expect($voucher->voucher_type)->toBe('EXPENSE');
    expect((float) $voucher->total_amount)->toBe(2000.0);
    expect($voucher->status)->toBe('posted');
    expect($voucher->reference_number)->toMatch('/^EXPENSE-\d{6}$/');

    // Check ledger entries
    expect($voucher->ledgerEntries)->toHaveCount(2); // expense debit + cash credit
});

test('sales voucher requires customer', function () {
    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Test Sales Invoice',
        'line_items' => [
            [
                'description' => 'Product A',
                'quantity' => 1,
                'unit_price' => 100,
            ],
        ],
    ];

    $service = app(SalesVoucherService::class);

    expect(fn () => $service->createSalesVoucher($data))
        ->toThrow('Customer is required for sales voucher');
});

test('purchase voucher requires vendor', function () {
    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Test Purchase Invoice',
        'line_items' => [
            [
                'description' => 'Raw Material',
                'quantity' => 1,
                'unit_price' => 100,
            ],
        ],
    ];

    $service = app(PurchaseVoucherService::class);

    expect(fn () => $service->createPurchaseVoucher($data))
        ->toThrow('Vendor is required for purchase voucher');
});

test('salary voucher requires employee and valid amount', function () {
    $employee = Employee::factory()->create(['organization_id' => $this->organization->id]);

    // Test missing employee
    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Salary Payment',
        'salary_amount' => 1000,
    ];

    $service = app(SalaryVoucherService::class);

    expect(fn () => $service->createSalaryVoucher($data))
        ->toThrow('Employee is required for salary voucher');

    // Test invalid amount
    $data['employee_id'] = $employee->id;
    $data['salary_amount'] = 0;

    expect(fn () => $service->createSalaryVoucher($data))
        ->toThrow('Valid salary amount is required');
});

test('expense voucher requires account and valid amount', function () {
    // Test missing account
    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Expense Payment',
        'amount' => 100,
    ];

    $service = app(ExpenseVoucherService::class);

    expect(fn () => $service->createExpenseVoucher($data))
        ->toThrow('Expense account is required');

    // Test invalid amount
    $data['expense_account_code'] = '5000';
    $data['amount'] = 0;

    expect(fn () => $service->createExpenseVoucher($data))
        ->toThrow('Valid amount is required');
});

test('voucher reference numbers are sequential', function () {
    $customer = Customer::factory()->create(['organization_id' => $this->organization->id]);

    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Test Sales Invoice',
        'customer_id' => $customer->id,
        'line_items' => [
            [
                'description' => 'Product A',
                'quantity' => 1,
                'unit_price' => 100,
            ],
        ],
    ];

    $service = app(SalesVoucherService::class);

    $voucher1 = $service->createSalesVoucher($data);
    $voucher2 = $service->createSalesVoucher($data);

    expect($voucher1->reference_number)->toBe('SALES-000001');
    expect($voucher2->reference_number)->toBe('SALES-000002');
});

test('vouchers maintain double entry bookkeeping', function () {
    $customer = Customer::factory()->create(['organization_id' => $this->organization->id]);

    $data = [
        'entry_date' => now()->format('Y-m-d'),
        'description' => 'Test Sales Invoice',
        'customer_id' => $customer->id,
        'line_items' => [
            [
                'description' => 'Product A',
                'quantity' => 1,
                'unit_price' => 100,
            ],
        ],
    ];

    $service = app(SalesVoucherService::class);
    $voucher = $service->createSalesVoucher($data);

    $totalDebits = $voucher->ledgerEntries->where('type', 'debit')->sum('amount');
    $totalCredits = $voucher->ledgerEntries->where('type', 'credit')->sum('amount');

    expect($totalDebits)->toBe($totalCredits);
    expect($totalDebits)->toBe(100.0);
});
