<?php

use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Accounting\JournalEntry;
use App\Services\OutstandingStatementsService;
use Tests\Traits\SetupOrganization;

uses(SetupOrganization::class);

test('can generate receivables aging report', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];
    
    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);
    
    // Create test data
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);
    
    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'voucher_type' => 'SALES',
        'customer_id' => $customer->id,
        'total_amount' => 1000,
        'entry_date' => now()->subDays(45),
        'due_date' => now()->subDays(15),
        'status' => 'posted',
    ]);

    $aging = app(OutstandingStatementsService::class)->generateReceivablesAging();
    $summary = app(OutstandingStatementsService::class)->getReceivablesAgingSummary();

    expect($aging)->toHaveCount(1);
    expect($aging->first()['aging_bucket'])->toBe('Current');
    expect($summary['Total'])->toBe(1000.0);
});

test('can generate payables aging report', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];
    
    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);
    
    // Create test data
    $vendor = Vendor::factory()->create(['organization_id' => $organization->id]);
    
    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'voucher_type' => 'PURCHASE',
        'vendor_id' => $vendor->id,
        'total_amount' => 2000,
        'entry_date' => now()->subDays(30),
        'due_date' => now()->addDays(15),
        'status' => 'posted',
    ]);

    $aging = app(OutstandingStatementsService::class)->generatePayablesAging();
    $summary = app(OutstandingStatementsService::class)->getPayablesAgingSummary();

    expect($aging)->toHaveCount(1);
    expect($aging->first()['aging_bucket'])->toBe('Current');
    expect($summary['Total'])->toBe(2000.0);
});

test('can get customer outstanding summary', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];
    
    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);
    
    // Create test data
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);
    
    JournalEntry::factory()->count(3)->create([
        'organization_id' => $organization->id,
        'voucher_type' => 'SALES',
        'customer_id' => $customer->id,
        'total_amount' => 500,
        'status' => 'posted',
    ]);

    $summary = app(OutstandingStatementsService::class)->getCustomerOutstandingSummary();

    expect($summary)->toHaveCount(1);
    expect((float) $summary->first()['total_outstanding'])->toBe(1500.0);
    expect($summary->first()['total_invoices'])->toBe(3);
});

test('can get vendor outstanding summary', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];
    
    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);
    
    // Create test data
    $vendor = Vendor::factory()->create(['organization_id' => $organization->id]);
    
    JournalEntry::factory()->count(2)->create([
        'organization_id' => $organization->id,
        'voucher_type' => 'PURCHASE',
        'vendor_id' => $vendor->id,
        'total_amount' => 750,
        'status' => 'posted',
    ]);

    $summary = app(OutstandingStatementsService::class)->getVendorOutstandingSummary();

    expect($summary)->toHaveCount(1);
    expect((float) $summary->first()['total_outstanding'])->toBe(1500.0);
    expect($summary->first()['total_bills'])->toBe(2);
});
