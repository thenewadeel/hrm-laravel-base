<?php

use App\Models\Accounting\JournalEntry;
use App\Models\Customer;
use App\Models\Vendor;
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

test('can generate comprehensive receivables statement with aging', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    // Use a unique organization name to avoid conflicts
    $organization->update(['name' => 'Test Receivables '.uniqid()]);

    $customer = Customer::factory()->create(['organization_id' => $organization->id]);

    // Create entries with different aging
    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
        'voucher_type' => 'SALES',
        'status' => 'posted',
        'due_date' => now()->addDays(5), // Current (future date)
        'total_amount' => 1000,
    ]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
        'voucher_type' => 'SALES',
        'status' => 'posted',
        'due_date' => now()->subDays(15), // Current (15 days ago)
        'total_amount' => 2000,
    ]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
        'voucher_type' => 'SALES',
        'status' => 'posted',
        'due_date' => now()->subDays(45), // 30 days bucket (45 days ago)
        'total_amount' => 1500,
    ]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
        'voucher_type' => 'SALES',
        'status' => 'posted',
        'due_date' => now()->subDays(95), // 90+ days bucket
        'total_amount' => 500,
    ]);

    $statement = app(OutstandingStatementsService::class)->generateReceivablesStatement();

    expect($statement['type'])->toBe('receivables');
    expect($statement['summary']['total_customers'])->toBe(1);
    expect($statement['summary']['total_outstanding'])->toBe(5000);
    expect($statement['summary']['aging']['current'])->toBe(3000); // 1000 + 2000
    expect($statement['summary']['aging']['30_days'])->toBe(1500); // 45 days = 30 days bucket
    expect($statement['summary']['aging']['60_days'])->toBe(0); // No entries in 60 days bucket
    expect($statement['summary']['aging']['90_days'])->toBe(500);
});

test('can generate comprehensive payables statement with aging', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    // Use a unique organization name to avoid conflicts
    $organization->update(['name' => 'Test Payables '.uniqid()]);

    $vendor = Vendor::factory()->create(['organization_id' => $organization->id]);

    // Create entries with different aging
    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'vendor_id' => $vendor->id,
        'voucher_type' => 'PURCHASE',
        'status' => 'posted',
        'due_date' => now()->addDays(5), // Current
        'total_amount' => 1000,
    ]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'vendor_id' => $vendor->id,
        'voucher_type' => 'PURCHASE',
        'status' => 'posted',
        'due_date' => now()->subDays(25), // 30 days bucket
        'total_amount' => 2000,
    ]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'vendor_id' => $vendor->id,
        'voucher_type' => 'PURCHASE',
        'status' => 'posted',
        'due_date' => now()->subDays(50), // 60 days bucket
        'total_amount' => 1500,
    ]);

    $statement = app(OutstandingStatementsService::class)->generatePayablesStatement();

    expect($statement['type'])->toBe('payables');
    expect($statement['summary']['total_vendors'])->toBe(1);
    expect($statement['summary']['total_outstanding'])->toBe(4500);
    expect($statement['summary']['aging']['current'])->toBe(1000);
    expect($statement['summary']['aging']['30_days'])->toBe(2000);
    expect($statement['summary']['aging']['60_days'])->toBe(1500);
});

test('can filter receivables statement by customer', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    // Use a unique organization name to avoid conflicts
    $organization->update(['name' => 'Test Filter Receivables '.uniqid()]);

    $customer1 = Customer::factory()->create(['organization_id' => $organization->id]);
    $customer2 = Customer::factory()->create(['organization_id' => $organization->id]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer1->id,
        'voucher_type' => 'SALES',
        'status' => 'posted',
        'total_amount' => 1000,
    ]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer2->id,
        'voucher_type' => 'SALES',
        'status' => 'posted',
        'total_amount' => 2000,
    ]);

    $statement = app(OutstandingStatementsService::class)->generateReceivablesStatement(
        customerId: $customer1->id
    );

    expect($statement['summary']['total_customers'])->toBe(1);
    expect($statement['summary']['total_outstanding'])->toBe(1000);
    expect($statement['customer_statements']->first()['customer']['id'])->toBe($customer1->id);
});

test('can filter payables statement by vendor', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    // Use a unique organization name to avoid conflicts
    $organization->update(['name' => 'Test Filter Payables '.uniqid()]);

    $vendor1 = Vendor::factory()->create(['organization_id' => $organization->id]);
    $vendor2 = Vendor::factory()->create(['organization_id' => $organization->id]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'vendor_id' => $vendor1->id,
        'voucher_type' => 'PURCHASE',
        'status' => 'posted',
        'total_amount' => 1000,
    ]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'vendor_id' => $vendor2->id,
        'voucher_type' => 'PURCHASE',
        'status' => 'posted',
        'total_amount' => 2000,
    ]);

    $statement = app(OutstandingStatementsService::class)->generatePayablesStatement(
        vendorId: $vendor1->id
    );

    expect($statement['summary']['total_vendors'])->toBe(1);
    expect($statement['summary']['total_outstanding'])->toBe(1000);
    expect($statement['vendor_statements']->first()['vendor']['id'])->toBe($vendor1->id);
});

test('can filter statements by date range', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    $customer = Customer::factory()->create(['organization_id' => $organization->id]);

    $startDate = now()->subDays(30);
    $endDate = now()->subDays(10);

    // Create entry within date range
    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
        'voucher_type' => 'SALES',
        'status' => 'posted',
        'entry_date' => $startDate->copy()->addDays(5),
        'total_amount' => 1000,
    ]);

    // Create entry outside date range
    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
        'voucher_type' => 'SALES',
        'status' => 'posted',
        'entry_date' => now()->subDays(5),
        'total_amount' => 2000,
    ]);

    $statement = app(OutstandingStatementsService::class)->generateReceivablesStatement(
        startDate: $startDate,
        endDate: $endDate
    );

    expect($statement['summary']['total_outstanding'])->toBe(1000);
});

test('can export statement data', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    $customer = Customer::factory()->create(['organization_id' => $organization->id]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
        'voucher_type' => 'SALES',
        'status' => 'posted',
        'total_amount' => 1000,
    ]);

    $statement = app(OutstandingStatementsService::class)->generateReceivablesStatement();
    $exportData = app(OutstandingStatementsService::class)->exportStatement($statement);

    expect($exportData)->toHaveKey('title');
    expect($exportData['title'])->toBe('Accounts Receivable Outstanding Statement');
    expect($exportData)->toHaveKey('customers');
    expect($exportData['customers'])->toHaveCount(1);
    expect($exportData['customers']->first()['customer_name'])->toBe($customer->name);
});

test('can get aging summary for dashboard', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    $customer = Customer::factory()->create(['organization_id' => $organization->id]);
    $vendor = Vendor::factory()->create(['organization_id' => $organization->id]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'customer_id' => $customer->id,
        'voucher_type' => 'SALES',
        'status' => 'posted',
        'total_amount' => 1000,
    ]);

    JournalEntry::factory()->create([
        'organization_id' => $organization->id,
        'vendor_id' => $vendor->id,
        'voucher_type' => 'PURCHASE',
        'status' => 'posted',
        'total_amount' => 1500,
    ]);

    $summary = app(OutstandingStatementsService::class)->getAgingSummary();

    expect($summary)->toHaveKey('receivables');
    expect($summary)->toHaveKey('payables');
    expect($summary['receivables']['total'])->toBe(1000);
    expect($summary['payables']['total'])->toBe(1500);
});

test('respects organization isolation', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    // Create another organization
    $otherOrg = \App\Models\Organization::factory()->create();

    // Create customer in other organization
    $otherCustomer = Customer::factory()->create(['organization_id' => $otherOrg->id]);

    // Create journal entry for other organization
    JournalEntry::factory()->create([
        'organization_id' => $otherOrg->id,
        'customer_id' => $otherCustomer->id,
        'voucher_type' => 'SALES',
        'status' => 'posted',
        'total_amount' => 5000,
    ]);

    $statement = app(OutstandingStatementsService::class)->generateReceivablesStatement();

    expect($statement['summary']['total_customers'])->toBe(0);
    expect($statement['summary']['total_outstanding'])->toBe(0);
});
