<?php

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view bank accounts index', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $bankAccount = BankAccount::factory()->create(['organization_id' => $organization->id]);

    $response = $this->actingAs($user)
        ->get(route('accounting.bank-accounts.index'));

    $response->assertStatus(200);
    $response->assertSee($bankAccount->account_name);
    $response->assertSee($bankAccount->bank_name);
});

test('user can create bank account', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $chartOfAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
    ]);

    $response = $this->actingAs($user)
        ->post(route('accounting.bank-accounts.store'), [
            'chart_of_account_id' => $chartOfAccount->id,
            'account_number' => '123456789',
            'account_name' => 'Test Bank Account',
            'bank_name' => 'Test Bank',
            'account_type' => 'checking',
            'currency' => 'USD',
            'opening_balance' => 1000.00,
            'status' => 'active',
        ]);

    $response->assertRedirect(route('accounting.bank-accounts.index'));

    $this->assertDatabaseHas('bank_accounts', [
        'organization_id' => $organization->id,
        'account_number' => '123456789',
        'account_name' => 'Test Bank Account',
        'bank_name' => 'Test Bank',
        'account_type' => 'checking',
        'currency' => 'USD',
        'opening_balance' => 1000.00,
        'status' => 'active',
    ]);
});

test('bank account belongs to organization', function () {
    $organization = Organization::factory()->create();
    $bankAccount = BankAccount::factory()->create(['organization_id' => $organization->id]);

    expect($bankAccount->organization_id)->toBe($organization->id);
});

test('bank account has chart of account relationship', function () {
    $chartOfAccount = ChartOfAccount::factory()->create();
    $bankAccount = BankAccount::factory()->create(['chart_of_account_id' => $chartOfAccount->id]);

    expect($bankAccount->chartOfAccount)->toBeInstanceOf(ChartOfAccount::class);
    expect($bankAccount->chartOfAccount->id)->toBe($chartOfAccount->id);
});

test('bank account can have bank transactions', function () {
    $bankAccount = BankAccount::factory()->create();
    $transaction = \App\Models\Accounting\BankTransaction::factory()
        ->create(['bank_account_id' => $bankAccount->id]);

    expect($bankAccount->bankTransactions)->toHaveCount(1);
    expect($bankAccount->bankTransactions->first()->id)->toBe($transaction->id);
});

test('bank account scope active works correctly', function () {
    BankAccount::factory()->create(['status' => 'active']);
    BankAccount::factory()->create(['status' => 'inactive']);
    BankAccount::factory()->create(['status' => 'closed']);

    $activeAccounts = BankAccount::active()->get();

    expect($activeAccounts)->toHaveCount(1);
    expect($activeAccounts->first()->status)->toBe('active');
});

test('bank account formatted balance returns correct format', function () {
    $bankAccount = BankAccount::factory()->create(['current_balance' => 1234.56]);

    expect($bankAccount->formatted_balance)->toBe('1,234.56');
});

test('bank account type label returns correct label', function () {
    $checkingAccount = BankAccount::factory()->create(['account_type' => 'checking']);
    $savingsAccount = BankAccount::factory()->create(['account_type' => 'savings']);

    expect($checkingAccount->account_type_label)->toBe('Checking Account');
    expect($savingsAccount->account_type_label)->toBe('Savings Account');
});

test('bank account status label returns correct label', function () {
    $activeAccount = BankAccount::factory()->create(['status' => 'active']);
    $inactiveAccount = BankAccount::factory()->create(['status' => 'inactive']);

    expect($activeAccount->status_label)->toBe('Active');
    expect($inactiveAccount->status_label)->toBe('Inactive');
});
