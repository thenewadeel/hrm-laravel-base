<?php

use App\Models\Customer;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('invoice can be created with required fields', function () {
    $organization = Organization::factory()->create();
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);
    $vendor = Vendor::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    
    $invoice = \App\Models\Invoice::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
        'vendor_id' => $vendor->id,
        'invoice_number' => 'INV-2025-0001',
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'total_amount' => 5000.00,
        'tax_amount' => 400.00,
        'status' => 'draft',
        'created_by' => $user->id,
    ]);

    expect($invoice)->toBeInstanceOf(\App\Models\Invoice::class);
    expect($invoice->organization_id)->toBe($organization->id);
    expect($invoice->customer_id)->toBe($customer->id);
    expect($invoice->vendor_id)->toBe($vendor->id);
    expect($invoice->invoice_number)->toBe('INV-2025-0001');
    expect($invoice->total_amount)->toBe(5000.0);
    expect($invoice->tax_amount)->toBe(400.0);
    expect($invoice->status)->toBe('draft');
});

test('invoice belongs to customer', function () {
    $organization = Organization::factory()->create();
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);
    $invoice = \App\Models\Invoice::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
    ]);

    expect($invoice->customer)->toBeInstanceOf(Customer::class);
    expect($invoice->customer->id)->toBe($customer->id);
});

test('invoice belongs to vendor', function () {
    $organization = Organization::factory()->create();
    $vendor = Vendor::factory()->create(['organization_id' => $organization->id]);
    $invoice = \App\Models\Invoice::factory()->create([
        'organization_id' => $organization->id,
        'vendor_id' => $vendor->id,
    ]);

    expect($invoice->vendor)->toBeInstanceOf(Vendor::class);
    expect($invoice->vendor->id)->toBe($vendor->id);
});

test('invoice uses soft deletes', function () {
    $invoice = \App\Models\Invoice::factory()->create();
    
    $invoice->delete();
    
    expect($invoice->trashed())->toBeTrue();
    expect(\App\Models\Invoice::find($invoice->id))->toBeNull();
    expect(\App\Models\Invoice::withTrashed()->find($invoice->id))->not->toBeNull();
});

test('invoice casts amounts correctly', function () {
    $invoice = \App\Models\Invoice::factory()->create([
        'total_amount' => 5000.50,
        'tax_amount' => 400.25,
    ]);

    expect($invoice->total_amount)->toBeFloat();
    expect($invoice->total_amount)->toBe(5000.50);
    expect($invoice->tax_amount)->toBeFloat();
    expect($invoice->tax_amount)->toBe(400.25);
});

test('invoice casts dates correctly', function () {
    $invoiceDate = now();
    $dueDate = now()->addDays(30);
    
    $invoice = \App\Models\Invoice::factory()->create([
        'invoice_date' => $invoiceDate,
        'due_date' => $dueDate,
    ]);

    expect($invoice->invoice_date)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($invoice->invoice_date->format('Y-m-d'))->toBe($invoiceDate->format('Y-m-d'));
    expect($invoice->due_date)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($invoice->due_date->format('Y-m-d'))->toBe($dueDate->format('Y-m-d'));
});

test('invoice status scope works', function () {
    $organization = Organization::factory()->create();
    
    $draftInvoice = \App\Models\Invoice::factory()->create([
        'organization_id' => $organization->id,
        'status' => 'draft',
    ]);
    
    $sentInvoice = \App\Models\Invoice::factory()->create([
        'organization_id' => $organization->id,
        'status' => 'sent',
    ]);
    
    $paidInvoice = \App\Models\Invoice::factory()->create([
        'organization_id' => $organization->id,
        'status' => 'paid',
    ]);
    
    expect(\App\Models\Invoice::draft()->count())->toBe(1);
    expect(\App\Models\Invoice::sent()->count())->toBe(1);
    expect(\App\Models\Invoice::paid()->count())->toBe(1);
    expect(\App\Models\Invoice::draft()->first()->id)->toBe($draftInvoice->id);
});