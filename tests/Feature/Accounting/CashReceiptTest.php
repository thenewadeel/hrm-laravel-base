<?php

use App\Models\Accounting\CashReceipt;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cash receipt can be created with required fields', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
    ]);
    $creditAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue',
    ]);

    $cashReceipt = CashReceipt::create([
        'organization_id' => $organization->id,
        'receipt_number' => 'RCPT-000001',
        'date' => now(),
        'received_from' => 'John Doe',
        'amount' => 1000.00,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
        'description' => 'Payment for services',
        'notes' => 'Cash payment received',
    ]);

    expect($cashReceipt)->toBeInstanceOf(CashReceipt::class);
    expect($cashReceipt->receipt_number)->toBe('RCPT-000001');
    expect($cashReceipt->received_from)->toBe('John Doe');
    expect($cashReceipt->amount)->toBe('1000.00');
    expect($cashReceipt->description)->toBe('Payment for services');
    expect($cashReceipt->notes)->toBe('Cash payment received');
});

test('cash receipt belongs to organization', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $creditAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $cashReceipt = CashReceipt::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
    ]);

    expect($cashReceipt->organization)->toBeInstanceOf(Organization::class);
    expect($cashReceipt->organization->id)->toBe($organization->id);
});

test('cash receipt belongs to cash account', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $creditAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $cashReceipt = CashReceipt::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
    ]);

    expect($cashReceipt->cashAccount)->toBeInstanceOf(ChartOfAccount::class);
    expect($cashReceipt->cashAccount->id)->toBe($cashAccount->id);
});

test('cash receipt belongs to credit account', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $creditAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $cashReceipt = CashReceipt::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
    ]);

    expect($cashReceipt->creditAccount)->toBeInstanceOf(ChartOfAccount::class);
    expect($cashReceipt->creditAccount->id)->toBe($creditAccount->id);
});

test('cash receipt uses soft deletes', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $creditAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $cashReceipt = CashReceipt::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
    ]);

    $cashReceipt->delete();

    expect(CashReceipt::find($cashReceipt->id))->toBeNull();
    expect(CashReceipt::withTrashed()->find($cashReceipt->id))->not->toBeNull();
});

test('cash receipt casts amount as decimal', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $creditAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $cashReceipt = CashReceipt::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
        'amount' => 1234.56,
    ]);

    expect($cashReceipt->amount)->toBeString();
    expect($cashReceipt->amount)->toBe('1234.56');
});

test('cash receipt casts date properly', function () {
    $organization = Organization::factory()->create();
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $creditAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);

    $date = now();
    $cashReceipt = CashReceipt::factory()->create([
        'organization_id' => $organization->id,
        'cash_account_id' => $cashAccount->id,
        'credit_account_id' => $creditAccount->id,
        'date' => $date,
    ]);

    expect($cashReceipt->date)->toBeInstanceOf(Carbon\Carbon::class);
    expect($cashReceipt->date->format('Y-m-d'))->toBe($date->format('Y-m-d'));
});
