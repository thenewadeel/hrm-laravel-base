<?php

use App\Models\Accounting\CashPayment;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cash payment can be created with required fields', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
    ]);
    $debitAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
    ]);

    $cashPayment = CashPayment::create([
        'organization_id' => $organization->id,
        'voucher_number' => 'VCH-000001',
        'date' => now(),
        'paid_to' => 'Jane Smith',
        'amount' => 500.00,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
        'purpose' => 'Office supplies',
        'notes' => 'Payment for stationery',
    ]);

    expect($cashPayment)->toBeInstanceOf(CashPayment::class);
    expect($cashPayment->voucher_number)->toBe('VCH-000001');
    expect($cashPayment->paid_to)->toBe('Jane Smith');
    expect($cashPayment->amount)->toBe('500.00');
    expect($cashPayment->purpose)->toBe('Office supplies');
    expect($cashPayment->notes)->toBe('Payment for stationery');
});

test('cash payment belongs to organization', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $debitAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $cashPayment = CashPayment::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
    ]);

    expect($cashPayment->organization)->toBeInstanceOf(Organization::class);
    expect($cashPayment->organization->id)->toBe($organization->id);
});

test('cash payment belongs to cash account', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $debitAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $cashPayment = CashPayment::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
    ]);

    expect($cashPayment->cashAccount)->toBeInstanceOf(ChartOfAccount::class);
    expect($cashPayment->cashAccount->id)->toBe($cashAccount->id);
});

test('cash payment belongs to debit account', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $debitAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $cashPayment = CashPayment::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
    ]);

    expect($cashPayment->debitAccount)->toBeInstanceOf(ChartOfAccount::class);
    expect($cashPayment->debitAccount->id)->toBe($debitAccount->id);
});

test('cash payment uses soft deletes', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $debitAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $cashPayment = CashPayment::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
    ]);

    $cashPayment->delete();

    expect(CashPayment::find($cashPayment->id))->toBeNull();
    expect(CashPayment::withTrashed()->find($cashPayment->id))->not->toBeNull();
});

test('cash payment casts amount as decimal', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $debitAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $cashPayment = CashPayment::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
        'amount' => 987.65,
    ]);

    expect($cashPayment->amount)->toBeString();
    expect($cashPayment->amount)->toBe('987.65');
});

test('cash payment casts date properly', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $debitAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $date = now();
    $cashPayment = CashPayment::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'debit_account_id' => $debitAccount->id,
        'date' => $date,
    ]);

    expect($cashPayment->date)->toBeInstanceOf(Carbon\Carbon::class);
    expect($cashPayment->date->format('Y-m-d'))->toBe($date->format('Y-m-d'));
});
