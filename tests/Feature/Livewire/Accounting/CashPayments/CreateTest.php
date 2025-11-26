<?php

use App\Livewire\Accounting\CashPayments\Create;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\AccountingPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('cash payment create component renders successfully', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_PAYMENTS, $organization);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->assertStatus(200);
});

test('cash payment create component has required fields', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_PAYMENTS, $organization);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->assertSet('date', now()->format('Y-m-d'))
        ->assertSet('paid_to', '')
        ->assertSet('amount', '')
        ->assertSet('cash_account_id', '')
        ->assertSet('debit_account_id', '')
        ->assertSet('purpose', '')
        ->assertSet('notes', '');
});

test('cash payment create component loads cash and expense accounts', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_PAYMENTS, $organization);
    
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset',
        'name' => 'Cash Account'
    ]);
    $expenseAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense',
        'name' => 'Office Expense'
    ]);
    $otherAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'liability',
        'name' => 'Other Liability'
    ]);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->assertViewHas('cashAccounts')
        ->assertViewHas('debitAccounts')
        ->assertSee('Cash Account')
        ->assertSee('Office Expense');
});

test('cash payment create component validates required fields', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_PAYMENTS, $organization);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->set('date', '')
        ->set('paid_to', '')
        ->set('amount', '')
        ->set('cash_account_id', '')
        ->set('debit_account_id', '')
        ->call('createPayment')
        ->assertHasErrors([
            'date' => 'required',
            'paid_to' => 'required',
            'amount' => 'required',
            'cash_account_id' => 'required',
            'debit_account_id' => 'required',
        ]);
});

test('cash payment create component validates amount is positive', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_PAYMENTS, $organization);
    
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $debitAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->set('date', now()->format('Y-m-d'))
        ->set('paid_to', 'Jane Smith')
        ->set('amount', -100)
        ->set('cash_account_id', $cashAccount->id)
        ->set('debit_account_id', $debitAccount->id)
        ->call('createPayment')
        ->assertHasErrors(['amount' => 'min']);
});

test('cash payment create component creates payment successfully', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_PAYMENTS, $organization);
    
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset'
    ]);
    $debitAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'expense'
    ]);

    // Set up initial cash balance
    \App\Models\Accounting\LedgerEntry::create([
        'entry_date' => now(),
        'chart_of_account_id' => $cashAccount->id,
        'type' => 'debit',
        'amount' => 10000.00,
        'description' => 'Initial cash balance',
    ]);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->set('date', now()->format('Y-m-d'))
        ->set('paid_to', 'Jane Smith')
        ->set('amount', 500)
        ->set('cash_account_id', $cashAccount->id)
        ->set('debit_account_id', $debitAccount->id)
        ->set('purpose', 'Office supplies')
        ->set('notes', 'Payment for stationery')
        ->call('createPayment')
        ->assertDispatched('cash-payment-created')
        ->assertDispatched('show-message', message: 'Cash payment created successfully!', type: 'success')
        ->assertSet('date', now()->format('Y-m-d')) // Form should reset
        ->assertSet('paid_to', '')
        ->assertSet('amount', '')
        ->assertSet('purpose', '')
        ->assertSet('notes', '');
});

test('cash payment create component handles creation errors', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_PAYMENTS, $organization);
    
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $debitAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    
    $this->actingAs($user);
    
    // Mock service to throw an exception
    $this->mock(\App\Services\CashPaymentService::class)
        ->shouldReceive('createPayment')
        ->andThrow(new \Exception('Service error'));
    
    Livewire::test(Create::class)
        ->set('date', now()->format('Y-m-d'))
        ->set('paid_to', 'Jane Smith')
        ->set('amount', 500)
        ->set('cash_account_id', $cashAccount->id)
        ->set('debit_account_id', $debitAccount->id)
        ->call('createPayment')
        ->assertDispatched('show-message', message: 'Error creating cash payment: Service error', type: 'error');
});