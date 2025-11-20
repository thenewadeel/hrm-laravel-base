<?php

use App\Livewire\Accounting\CashReceipts\Create;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\AccountingPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('cash receipt create component renders successfully', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_RECEIPTS, $organization);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->assertStatus(200);
});

test('cash receipt create component has required fields', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_RECEIPTS, $organization);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->assertSet('date', now()->format('Y-m-d'))
        ->assertSet('received_from', '')
        ->assertSet('amount', '')
        ->assertSet('cash_account_id', '')
        ->assertSet('credit_account_id', '')
        ->assertSet('description', '')
        ->assertSet('notes', '');
});

test('cash receipt create component loads cash accounts', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_RECEIPTS, $organization);
    
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset'
    ]);
    $otherAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'liability'
    ]);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->assertViewHas('cashAccounts')
        ->assertViewHas('creditAccounts')
        ->assertSeeInOrder([$cashAccount->name, $otherAccount->name]);
});

test('cash receipt create component validates required fields', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_RECEIPTS, $organization);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->set('date', '')
        ->set('received_from', '')
        ->set('amount', '')
        ->set('cash_account_id', '')
        ->set('credit_account_id', '')
        ->call('createReceipt')
        ->assertHasErrors([
            'date' => 'required',
            'received_from' => 'required',
            'amount' => 'required',
            'cash_account_id' => 'required',
            'credit_account_id' => 'required',
        ]);
});

test('cash receipt create component validates amount is positive', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_RECEIPTS, $organization);
    
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $creditAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->set('date', now()->format('Y-m-d'))
        ->set('received_from', 'John Doe')
        ->set('amount', -100)
        ->set('cash_account_id', $cashAccount->id)
        ->set('credit_account_id', $creditAccount->id)
        ->call('createReceipt')
        ->assertHasErrors(['amount' => 'min']);
});

test('cash receipt create component creates receipt successfully', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_RECEIPTS, $organization);
    
    $cashAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'asset'
    ]);
    $creditAccount = ChartOfAccount::factory()->create([
        'organization_id' => $organization->id,
        'type' => 'revenue'
    ]);
    
    $this->actingAs($user);
    
    Livewire::test(Create::class)
        ->set('date', now()->format('Y-m-d'))
        ->set('received_from', 'John Doe')
        ->set('amount', 1000)
        ->set('cash_account_id', $cashAccount->id)
        ->set('credit_account_id', $creditAccount->id)
        ->set('description', 'Payment for services')
        ->set('notes', 'Cash payment received')
        ->call('createReceipt')
        ->assertDispatched('cash-receipt-created')
        ->assertDispatched('show-message', message: 'Cash receipt created successfully!', type: 'success')
        ->assertSet('date', now()->format('Y-m-d')) // Form should reset
        ->assertSet('received_from', '')
        ->assertSet('amount', '')
        ->assertSet('description', '')
        ->assertSet('notes', '');
});

test('cash receipt create component handles creation errors', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_CASH_RECEIPTS, $organization);
    
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    $creditAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id]);
    
    $this->actingAs($user);
    
    // Mock the service to throw an exception
    $this->mock(\App\Services\CashReceiptService::class)
        ->shouldReceive('createReceipt')
        ->andThrow(new \Exception('Service error'));
    
    Livewire::test(Create::class)
        ->set('date', now()->format('Y-m-d'))
        ->set('received_from', 'John Doe')
        ->set('amount', 1000)
        ->set('cash_account_id', $cashAccount->id)
        ->set('credit_account_id', $creditAccount->id)
        ->call('createReceipt')
        ->assertDispatched('show-message', message: 'Error creating cash receipt: Service error', type: 'error');
});