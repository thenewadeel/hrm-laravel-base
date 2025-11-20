<?php

use App\Models\Accounting\CashPayment;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\LedgerEntry;
use App\Models\Organization;
use App\Services\CashPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cash payment service creates payment with journal entry', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'code' => '1001',
        'name' => 'Cash Account',
    ]);
    $debitAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
        'code' => '5001',
        'name' => 'Office Expenses',
    ]);

    // Set up initial cash balance by creating a ledger entry
    \App\Models\Accounting\LedgerEntry::create([
        'entry_date' => now(),
        'chart_of_account_id' => $cashAccount->id,
        'type' => 'debit',
        'amount' => 10000.00,
        'description' => 'Initial cash balance',
    ]);

    $service = new CashPaymentService;

    $paymentData = [
        'date' => now()->toDateString(),
        'paid_to' => 'Jane Smith',
        'amount' => 500.00,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
        'purpose' => 'Office supplies',
        'notes' => 'Payment for stationery',
    ];

    $cashPayment = $service->createPayment($paymentData, $organization->id);

    expect($cashPayment)->toBeInstanceOf(CashPayment::class);
    expect($cashPayment->voucher_number)->toStartWith('VCH-');
    expect($cashPayment->paid_to)->toBe('Jane Smith');
    expect($cashPayment->amount)->toBe('500.00');
    expect($cashPayment->organization_id)->toBe($organization->id);

    // Check that journal entry was created
    $journalEntry = JournalEntry::where('reference_number', $cashPayment->voucher_number)->first();
    expect($journalEntry)->not->toBeNull();
    expect($journalEntry->description)->toBe('Office supplies');
    expect($journalEntry->status)->toBe('posted');

    // Check that ledger entries were created
    $ledgerEntries = LedgerEntry::where('transactionable_type', CashPayment::class)
        ->where('transactionable_id', $cashPayment->id)
        ->get();
    expect($ledgerEntries)->toHaveCount(2);

    // Check debit entry (expense account increased)
    $debitEntry = $ledgerEntries->where('chart_of_account_id', $debitAccount->id)
        ->where('type', 'debit')
        ->first();
    expect($debitEntry)->not->toBeNull();
    expect($debitEntry->amount)->toBe('500.00');

    // Check credit entry (cash account decreased)
    $creditEntry = $ledgerEntries->where('chart_of_account_id', $cashAccount->id)
        ->where('type', 'credit')
        ->first();
    expect($creditEntry)->not->toBeNull();
    expect($creditEntry->amount)->toBe('500.00');
});

test('cash payment service validates amount is positive', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $debitAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $service = new CashPaymentService;

    $paymentData = [
        'date' => now()->toDateString(),
        'paid_to' => 'Jane Smith',
        'amount' => -100.00,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
        'purpose' => 'Invalid negative amount',
    ];

    expect(fn () => $service->createPayment($paymentData, $organization->id))
        ->toThrow(\InvalidArgumentException::class, 'Amount must be positive');
});

test('cash payment service generates sequential voucher numbers', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
    ]);
    $debitAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
    ]);

    // Set up initial cash balance
    \App\Models\Accounting\LedgerEntry::create([
        'entry_date' => now(),
        'chart_of_account_id' => $cashAccount->id,
        'type' => 'debit',
        'amount' => 10000.00,
        'description' => 'Initial cash balance',
    ]);

    $service = new CashPaymentService;

    $paymentData = [
        'date' => now()->toDateString(),
        'paid_to' => 'Vendor 1',
        'amount' => 100.00,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
        'purpose' => 'First payment',
    ];

    $firstPayment = $service->createPayment($paymentData, $organization->id);
    $secondPayment = $service->createPayment($paymentData + ['paid_to' => 'Vendor 2'], $organization->id);

    expect($firstPayment->voucher_number)->toBe('VCH-000001');
    expect($secondPayment->voucher_number)->toBe('VCH-000002');
});

test('cash payment service validates account ownership', function () {
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization1->id]);
    $debitAccount = ChartOfAccount::factory()->create(['organization_id' => $organization1->id]);

    $service = new CashPaymentService;

    $paymentData = [
        'date' => now()->toDateString(),
        'paid_to' => 'Jane Smith',
        'amount' => 100.00,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
        'purpose' => 'Cross-org payment',
    ];

    expect(fn () => $service->createPayment($paymentData, $organization2->id))
        ->toThrow(\InvalidArgumentException::class, 'Accounts must belong to the same organization');
});

test('cash payment service validates sufficient cash balance', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
    ]);
    $debitAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
    ]);

    $service = new CashPaymentService;

    $paymentData = [
        'date' => now()->toDateString(),
        'paid_to' => 'Jane Smith',
        'amount' => 10000.00, // Very large amount
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
        'purpose' => 'Insufficient funds test',
    ];

    expect(fn () => $service->createPayment($paymentData, $organization->id))
        ->toThrow(\InvalidArgumentException::class, 'Insufficient cash balance');
});
