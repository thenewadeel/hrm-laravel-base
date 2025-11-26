<?php

use App\Services\OutstandingStatementsService;
use Tests\Traits\SetupOrganization;

uses(SetupOrganization::class);

test('outstanding statements service basic functionality', function () {
    $setup = $this->createOrganizationWithUser();
    $user = $setup['user'];
    $organization = $setup['organization'];

    $user->update(['current_organization_id' => $organization->id]);
    $this->actingAs($user);

    // Test basic receivables statement generation
    $statement = app(OutstandingStatementsService::class)->generateReceivablesStatement();

    expect($statement)->toHaveKey('type');
    expect($statement['type'])->toBe('receivables');
    expect($statement)->toHaveKey('summary');
    expect($statement)->toHaveKey('customer_statements');
    expect($statement)->toHaveKey('as_of_date');
    expect($statement)->toHaveKey('generated_at');

    // Test basic payables statement generation
    $payablesStatement = app(OutstandingStatementsService::class)->generatePayablesStatement();

    expect($payablesStatement)->toHaveKey('type');
    expect($payablesStatement['type'])->toBe('payables');
    expect($payablesStatement)->toHaveKey('summary');
    expect($payablesStatement)->toHaveKey('vendor_statements');

    // Test aging summary
    $agingSummary = app(OutstandingStatementsService::class)->getAgingSummary();

    expect($agingSummary)->toHaveKey('receivables');
    expect($agingSummary)->toHaveKey('payables');
});
