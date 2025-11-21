<?php

use App\Livewire\Accounting\Vouchers\Create;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\AccountingPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('voucher create component renders successfully', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_VOUCHERS, $organization);

    $this->actingAs($user);

    Livewire::test(Create::class)
        ->assertStatus(200);
});

test('voucher create component has required fields', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_VOUCHERS, $organization);

    $this->actingAs($user);

    Livewire::test(Create::class)
        ->assertSet('date', now()->format('Y-m-d'))
        ->assertSet('type', '')
        ->assertSet('amount', '')
        ->assertSet('description', '')
        ->assertSet('notes', '');
});

test('voucher create component loads chart of accounts', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_VOUCHERS, $organization);

    // Create test accounts
    $accounts = ChartOfAccount::factory()->count(3)->create([
        'organization_id' => $organization->id,
    ]);

    $this->actingAs($user);

    Livewire::test(Create::class)
        ->assertViewHas('accounts')
        ->assertSeeInOrder([$accounts[0]->name, $accounts[1]->name, $accounts[2]->name]);
});

test('voucher create component validates required fields', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_VOUCHERS, $organization);

    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('date', '')
        ->set('type', '')
        ->set('amount', '')
        ->set('description', '')
        ->call('createVoucher')
        ->assertHasErrors([
            'date' => 'required',
            'type' => 'required',
            'amount' => 'required',
            'description' => 'required',
        ]);
});

test('voucher create component validates amount is positive', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_VOUCHERS, $organization);

    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('date', now()->format('Y-m-d'))
        ->set('type', 'sales')
        ->set('amount', -100)
        ->set('description', 'Test voucher')
        ->call('createVoucher')
        ->assertHasErrors(['amount' => 'min']);
});

test('voucher create component creates voucher successfully', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_VOUCHERS, $organization);

    $this->actingAs($user);

    Livewire::test(Create::class)
        ->set('date', now()->format('Y-m-d'))
        ->set('type', 'sales')
        ->set('amount', 1000)
        ->set('description', 'Test sales voucher')
        ->set('notes', 'Test notes')
        ->call('createVoucher')
        ->assertDispatched('voucher-created')
        ->assertDispatched('show-message', message: 'Voucher created successfully!', type: 'success');
});

test('voucher create component handles creation errors', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Grant permission to user
    $user->organizations()->attach($organization->id, ['roles' => ['accounting_admin']]);
    $user->givePermissionTo(AccountingPermissions::CREATE_VOUCHERS, $organization);

    $this->actingAs($user);

    // Mock the service to throw an exception
    $this->mock(\App\Services\GeneralVoucherService::class)
        ->shouldReceive('createVoucher')
        ->andThrow(new \Exception('Service error'));

    Livewire::test(Create::class)
        ->set('date', now()->format('Y-m-d'))
        ->set('type', 'sales')
        ->set('amount', 1000)
        ->set('description', 'Test voucher')
        ->call('createVoucher')
        ->assertDispatched('show-message', message: 'Error creating voucher: Service error', type: 'error');
});
