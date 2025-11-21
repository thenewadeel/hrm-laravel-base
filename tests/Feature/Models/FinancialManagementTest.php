<?php

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('customer financial fields work correctly', function () {
    $organization = Organization::factory()->create();
    
    $customer = Customer::factory()->create([
        'organization_id' => $organization->id,
        'credit_limit' => 10000.00,
        'opening_balance' => 5000.00,
        'current_balance' => 2500.00,
    ]);

    expect($customer->credit_limit)->toBeFloat();
    expect($customer->opening_balance)->toBeFloat();
    expect($customer->current_balance)->toBeFloat();
    expect($customer->getDisplayNameAttribute())->toContain('Balance: 2500.00');
});

test('customer relationships work correctly', function () {
    $organization = Organization::factory()->create();
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);

    expect($customer->organization)->toBeInstanceOf(Organization::class);
    expect($customer->organization->id)->toBe($organization->id);
});

test('customer soft deletes work', function () {
    $customer = Customer::factory()->create();
    
    $customer->delete();
    
    expect($customer->trashed())->toBeTrue();
    expect(Customer::find($customer->id))->toBeNull();
});

test('customer scopes work correctly', function () {
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

test('invoice can be created with all relationships', function () {
    $organization = Organization::factory()->create();
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);
    $vendor = Vendor::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    
    $invoice = Invoice::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
        'vendor_id' => $vendor->id,
        'total_amount' => 5000.00,
        'tax_amount' => 400.00,
        'created_by' => $user->id,
    ]);

    expect($invoice->organization)->toBeInstanceOf(Organization::class);
    expect($invoice->customer)->toBeInstanceOf(Customer::class);
    expect($invoice->vendor)->toBeInstanceOf(Vendor::class);
    expect($invoice->creator)->toBeInstanceOf(User::class);
    expect($invoice->total_amount)->toBe(5000.0);
    expect($invoice->tax_amount)->toBe(400.0);
});

test('invoice status scopes work correctly', function () {
    $organization = Organization::factory()->create();
    
    $draftInvoice = Invoice::factory()->create([
        'organization_id' => $organization->id,
        'status' => 'draft',
    ]);
    
    $sentInvoice = Invoice::factory()->create([
        'organization_id' => $organization->id,
        'status' => 'sent',
    ]);
    
    $paidInvoice = Invoice::factory()->create([
        'organization_id' => $organization->id,
        'status' => 'paid',
    ]);

    expect(Invoice::draft()->count())->toBe(1);
    expect(Invoice::sent()->count())->toBe(1);
    expect(Invoice::paid()->count())->toBe(1);
    expect(Invoice::draft()->first()->id)->toBe($draftInvoice->id);
});

test('invoice amount due calculation works', function () {
    $organization = Organization::factory()->create();
    
    $invoice = Invoice::factory()->create([
        'organization_id' => $organization->id,
        'total_amount' => 5000.00,
    ]);

    // Create some payments for the invoice
    Payment::factory()->count(3)->create([
        'invoice_id' => $invoice->id,
        'amount' => 2000.00,
        'status' => 'received',
    ]);

    expect($invoice->amount_due)->toBe(3000.00);
});

test('invoice overdue detection works', function () {
    $organization = Organization::factory()->create();
    
    $paidInvoice = Invoice::factory()->create([
        'organization_id' => $organization->id,
        'total_amount' => 5000.00,
        'due_date' => now()->addDays(30),
        'status' => 'paid',
    ]);

    $overdueInvoice = Invoice::factory()->create([
        'organization_id' => $organization->id,
        'total_amount' => 5000.00,
        'due_date' => now()->subDays(10),
        'status' => 'sent',
    ]);

    expect($paidInvoice->isOverdue())->toBeFalse();
    expect($overdueInvoice->isOverdue())->toBeTrue();
});

test('payment can be created with relationships', function () {
    $organization = Organization::factory()->create();
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);
    $invoice = Invoice::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();
    
    $payment = Payment::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
        'invoice_id' => $invoice->id,
        'amount' => 1000.00,
        'payment_date' => now(),
        'status' => 'received',
        'created_by' => $user->id,
    ]);

    expect($payment->organization)->toBeInstanceOf(Organization::class);
    expect($payment->customer)->toBeInstanceOf(Customer::class);
    expect($payment->invoice)->toBeInstanceOf(Invoice::class);
    expect($payment->creator)->toBeInstanceOf(User::class);
    expect($payment->amount)->toBe(1000.00);
    expect($payment->status)->toBe('received');
});

test('payment status scopes work correctly', function () {
    $organization = Organization::factory()->create();
    
    $receivedPayment = Payment::factory()->create([
        'organization_id' => $organization->id,
        'status' => 'received',
    ]);
    
    $pendingPayment = Payment::factory()->create([
        'organization_id' => $organization->id,
        'status' => 'pending',
    ]);

    expect(Payment::received()->count())->toBe(1);
    expect(Payment::pending()->count())->toBe(1);
    expect(Payment::received()->first()->id)->toBe($receivedPayment->id);
});

test('payment fully applied calculation works', function () {
    $organization = Organization::factory()->create();
    
    $invoice = Invoice::factory()->create([
        'organization_id' => $organization->id,
        'total_amount' => 5000.00,
    ]);
    
    // Create payments that fully cover the invoice
    Payment::factory()->count(2)->create([
        'invoice_id' => $invoice->id,
        'amount' => 2500.00,
        'status' => 'received',
    ]);

    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
        'amount' => 1000.00,
        'status' => 'received',
    ]);

    expect($payment->isFullyApplied())->toBeTrue();
});