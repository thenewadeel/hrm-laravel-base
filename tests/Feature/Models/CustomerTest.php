<?php

use App\Models\Customer;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('customer can be created with financial fields', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    $customer = Customer::factory()->create([
        'organization_id' => $organization->id,
        'name' => 'Test Customer',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
        'address' => '123 Test St',
        'city' => 'Test City',
        'state' => 'TS',
        'postal_code' => '12345',
        'country' => 'US',
        'tax_number' => 'TX-123456',
        'customer_type' => 'BUSINESS',
        'credit_limit' => 10000.00,
        'opening_balance' => 5000.00,
        'current_balance' => 2500.00,
        'is_active' => true,
    ]);

    expect($customer)->toBeInstanceOf(Customer::class);
    expect($customer->organization_id)->toBe($organization->id);
    expect($customer->name)->toBe('Test Customer');
    expect($customer->email)->toBe('test@example.com');
    expect($customer->credit_limit)->toBe(10000.0);
    expect($customer->opening_balance)->toBe(5000.0);
    expect($customer->current_balance)->toBe(2500.0);
    expect($customer->is_active)->toBeTrue();
});

test('customer belongs to organization', function () {
    $organization = Organization::factory()->create();
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);

    expect($customer->organization)->toBeInstanceOf(Organization::class);
    expect($customer->organization->id)->toBe($organization->id);
});

test('customer uses soft deletes', function () {
    $customer = Customer::factory()->create();
    
    $customer->delete();
    
    expect($customer->trashed())->toBeTrue();
    expect(Customer::find($customer->id))->toBeNull();
    expect(Customer::withTrashed()->find($customer->id))->not->toBeNull();
});

test('customer casts financial fields correctly', function () {
    $customer = Customer::factory()->create([
        'credit_limit' => 10000.50,
        'opening_balance' => 5000.75,
        'current_balance' => 2500.25,
        'customer_type' => 'BUSINESS',
    ]);

    expect($customer->credit_limit)->toBeFloat();
    expect($customer->credit_limit)->toBe(10000.50);
    expect($customer->opening_balance)->toBeFloat();
    expect($customer->opening_balance)->toBe(5000.75);
    expect($customer->current_balance)->toBeFloat();
    expect($customer->current_balance)->toBe(2500.25);
});

test('customer scope active works correctly', function () {
    $organization = Organization::factory()->create();
    
    $activeCustomer = Customer::factory()->create([
        'organization_id' => $organization->id,
        'is_active' => true,
    ]);
    
    $inactiveCustomer = Customer::factory()->create([
        'organization_id' => $organization->id,
        'is_active' => false,
    ]);

    $activeCustomers = Customer::active()->get();
    
    expect($activeCustomers)->toHaveCount(1);
    expect($activeCustomers->first()->id)->toBe($activeCustomer->id);
});

test('customer scope with balance works correctly', function () {
    $organization = Organization::factory()->create();
    
    $highBalanceCustomer = Customer::factory()->create([
        'organization_id' => $organization->id,
        'current_balance' => 5000.00,
    ]);
    
    $lowBalanceCustomer = Customer::factory()->create([
        'organization_id' => $organization->id,
        'current_balance' => 1000.00,
    ]);
    
    $highBalanceCustomers = Customer::withBalance('>', 3000.00)->get();
    
    expect($highBalanceCustomers)->toHaveCount(1);
    expect($highBalanceCustomers->first()->id)->toBe($highBalanceCustomer->id);
});

test('customer full address concatenates correctly', function () {
    $customer = Customer::factory()->create([
        'address' => '123 Main St',
        'city' => 'Test City',
        'state' => 'TX',
        'postal_code' => '12345',
        'country' => 'US',
    ]);

    $expectedAddress = '123 Main St, Test City, TX, 12345, US';
    
    expect($customer->full_address)->toBe($expectedAddress);
});

test('customer display name includes balance', function () {
    $customer = Customer::factory()->create([
        'name' => 'Test Customer',
        'current_balance' => 2500.00,
        'customer_type' => 'BUSINESS',
    ]);

    expect($customer->display_name)->toBe('Test Customer (Balance: 2500.00)');
});