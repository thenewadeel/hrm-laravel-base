<?php

use App\Models\Accounting\CashReceipt;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\LedgerEntry;
use App\Models\Organization;
use App\Services\CashReceiptService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cash receipt service creates receipt with journal entry', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'code' => '1001',
        'name' => 'Cash Account',
    ]);
    $creditAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
        'code' => '4001',
        'name' => 'Sales Revenue',
    ]);

    $service = new CashReceiptService;

    $receiptData = [
        'date' => now()->toDateString(),
        'received_from' => 'John Doe',
        'amount' => 1000.00,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
        'description' => 'Payment for services',
        'notes' => 'Cash payment received',
    ];

    $cashReceipt = $service->createReceipt($receiptData, $organization->id);

    expect($cashReceipt)->toBeInstanceOf(CashReceipt::class);
    expect($cashReceipt->receipt_number)->toStartWith('RCPT-');
    expect($cashReceipt->received_from)->toBe('John Doe');
    expect($cashReceipt->amount)->toBe('1000.00');
    expect($cashReceipt->organization_id)->toBe($organization->id);

    // Check that journal entry was created
    $journalEntry = JournalEntry::where('reference_number', $cashReceipt->receipt_number)->first();
    expect($journalEntry)->not->toBeNull();
    expect($journalEntry->description)->toBe('Payment for services');
    expect($journalEntry->status)->toBe('posted');

    // Check that ledger entries were created
    $ledgerEntries = LedgerEntry::where('transactionable_type', CashReceipt::class)
        ->where('transactionable_id', $cashReceipt->id)
        ->get();
    expect($ledgerEntries)->toHaveCount(2);

    // Check debit entry (cash account increased)
    $debitEntry = $ledgerEntries->where('chart_of_account_id', $cashAccount->id)
        ->where('type', 'debit')
        ->first();
    expect($debitEntry)->not->toBeNull();
    expect($debitEntry->amount)->toBe('1000.00');

    // Check credit entry (revenue account increased)
    $creditEntry = $ledgerEntries->where('chart_of_account_id', $creditAccount->id)
        ->where('type', 'credit')
        ->first();
    expect($creditEntry)->not->toBeNull();
    expect($creditEntry->amount)->toBe('1000.00');
});

test('cash receipt service validates amount is positive', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $creditAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $service = new CashReceiptService;

    $receiptData = [
        'date' => now()->toDateString(),
        'received_from' => 'John Doe',
        'amount' => -100.00,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
        'description' => 'Invalid negative amount',
    ];

    expect(fn () => $service->createReceipt($receiptData, $organization->id))
        ->toThrow(\InvalidArgumentException::class, 'Amount must be positive');
});

test('cash receipt service generates sequential receipt numbers', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
    ]);
    $creditAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
    ]);

    $service = new CashReceiptService;

    $receiptData = [
        'date' => now()->toDateString(),
        'received_from' => 'Customer 1',
        'amount' => 100.00,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
        'description' => 'First receipt',
    ];

    $firstReceipt = $service->createReceipt($receiptData, $organization->id);
    $secondReceipt = $service->createReceipt($receiptData + ['received_from' => 'Customer 2'], $organization->id);

    expect($firstReceipt->receipt_number)->toBe('RCPT-000001');
    expect($secondReceipt->receipt_number)->toBe('RCPT-000002');
});

test('cash receipt service validates account ownership', function () {
    $organization1 = Organization::factory()->create();
    $organization2 = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization1->id]);
    $creditAccount = ChartOfAccount::factory()->create(['organization_id' => $organization1->id]);

    $service = new CashReceiptService;

    $receiptData = [
        'date' => now()->toDateString(),
        'received_from' => 'John Doe',
        'amount' => 100.00,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
        'description' => 'Cross-org receipt',
    ];

    expect(fn () => $service->createReceipt($receiptData, $organization2->id))
        ->toThrow(\InvalidArgumentException::class, 'Accounts must belong to the same organization');
});
