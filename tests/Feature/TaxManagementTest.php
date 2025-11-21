<?php

use App\Models\Accounting\TaxCalculation;
use App\Models\Accounting\TaxExemption;
use App\Models\Accounting\TaxFiling;
use App\Models\Accounting\TaxJurisdiction;
use App\Models\Accounting\TaxRate;
use App\Models\Customer;
use App\Models\Organization;
use App\Models\User;
use App\Services\TaxCalculationService;
use App\Services\TaxComplianceService;
use App\Services\TaxReportingService;

// Tax Rate Management Tests
test('user can create tax rate', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    $jurisdiction = TaxJurisdiction::factory()->create(['organization_id' => $organization->id]);

    $taxRateData = [
        'name' => 'Test Sales Tax',
        'code' => 'TST',
        'type' => 'sales',
        'rate' => 7.5,
        'tax_jurisdiction_id' => $jurisdiction->id,
        'effective_date' => now()->toDateString(),
        'is_active' => true,
    ];

    $taxRate = TaxRate::create(array_merge($taxRateData, [
        'organization_id' => $organization->id,
    ]));

    expect($taxRate)->toBeInstanceOf(TaxRate::class);
    expect($taxRate->name)->toBe('Test Sales Tax');
    expect($taxRate->rate)->toBe(7.5);
    expect($taxRate->getTypeDisplayName())->toBe('Sales Tax');
});

test('user can update tax rate', function () {
    $taxRate = TaxRate::factory()->create();

    $taxRate->update([
        'name' => 'Updated Tax Rate',
        'rate' => 8.25,
    ]);

    expect($taxRate->fresh()->name)->toBe('Updated Tax Rate');
    expect($taxRate->fresh()->rate)->toBe(8.25);
});

test('tax rate scope active works correctly', function () {
    $organization = Organization::factory()->create();

    TaxRate::factory()->create([
        'organization_id' => $organization->id,
        'is_active' => true,
        'effective_date' => now()->subDays(10),
    ]);

    TaxRate::factory()->create([
        'organization_id' => $organization->id,
        'is_active' => false,
    ]);

    TaxRate::factory()->create([
        'organization_id' => $organization->id,
        'is_active' => true,
        'effective_date' => now()->addDays(10),
    ]);

    $activeRates = TaxRate::active()->get();
    expect($activeRates)->toHaveCount(1);
});

// Tax Jurisdiction Tests
test('user can create tax jurisdiction', function () {
    $organization = Organization::factory()->create();

    $jurisdiction = TaxJurisdiction::create([
        'organization_id' => $organization->id,
        'name' => 'Test Tax Authority',
        'code' => 'TEST',
        'type' => 'state',
        'tax_id_number' => 'TEST-123456',
        'is_active' => true,
        'filing_requirements' => [
            'frequency' => 'quarterly',
            'due_days' => 30,
        ],
    ]);

    expect($jurisdiction)->toBeInstanceOf(TaxJurisdiction::class);
    expect($jurisdiction->name)->toBe('Test Tax Authority');
    expect($jurisdiction->getTypeDisplayName())->toBe('State/Province');
    expect($jurisdiction->getFilingFrequency())->toBe('quarterly');
    expect($jurisdiction->getDueDays())->toBe(30);
});

// Tax Exemption Tests
test('user can create tax exemption', function () {
    $organization = Organization::factory()->create();
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);
    $taxRate = TaxRate::factory()->create(['organization_id' => $organization->id]);

    $exemption = TaxExemption::create([
        'organization_id' => $organization->id,
        'exemptible_type' => Customer::class,
        'exemptible_id' => $customer->id,
        'tax_rate_id' => $taxRate->id,
        'certificate_number' => 'EXEMPT-001',
        'exemption_type' => 'resale',
        'exemption_percentage' => 100,
        'issue_date' => now()->toDateString(),
        'is_active' => true,
    ]);

    expect($exemption)->toBeInstanceOf(TaxExemption::class);
    expect($exemption->certificate_number)->toBe('EXEMPT-001');
    expect($exemption->getExemptionTypeDisplayName())->toBe('Resale Certificate');
    expect($exemption->isValid())->toBeTrue();
});

test('tax exemption validity check works', function () {
    $organization = Organization::factory()->create();
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);

    // Valid exemption
    $validExemption = TaxExemption::factory()->create([
        'organization_id' => $organization->id,
        'exemptible_type' => Customer::class,
        'exemptible_id' => $customer->id,
        'expiry_date' => now()->addDays(30),
        'is_active' => true,
    ]);

    // Expired exemption
    $expiredExemption = TaxExemption::factory()->create([
        'organization_id' => $organization->id,
        'exemptible_type' => Customer::class,
        'exemptible_id' => $customer->id,
        'expiry_date' => now()->subDays(30),
        'is_active' => true,
    ]);

    // Inactive exemption
    $inactiveExemption = TaxExemption::factory()->create([
        'organization_id' => $organization->id,
        'exemptible_type' => Customer::class,
        'exemptible_id' => $customer->id,
        'is_active' => false,
    ]);

    expect($validExemption->isValid())->toBeTrue();
    expect($expiredExemption->isValid())->toBeFalse();
    expect($inactiveExemption->isValid())->toBeFalse();
});

// Tax Calculation Service Tests
test('tax calculation service calculates taxes correctly', function () {
    $organization = Organization::factory()->create();
    $taxRate = TaxRate::factory()->create([
        'organization_id' => $organization->id,
        'rate' => 10.0,
        'type' => 'sales',
    ]);

    $voucher = \App\Models\Accounting\Voucher::factory()->create([
        'organization_id' => $organization->id,
        'amount' => 1000,
        'type' => 'sales',
    ]);

    $taxService = new TaxCalculationService;
    $calculations = $taxService->calculateTaxes($voucher, 1000, 'sales');

    expect($calculations)->toHaveCount(1);
    expect($calculations->first()->tax_amount)->toBe(100.0);
    expect($calculations->first()->base_amount)->toBe(1000.0);
});

test('tax calculation service applies exemptions correctly', function () {
    $organization = Organization::factory()->create();
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);
    $taxRate = TaxRate::factory()->create([
        'organization_id' => $organization->id,
        'rate' => 10.0,
        'type' => 'sales',
    ]);

    $exemption = TaxExemption::factory()->create([
        'organization_id' => $organization->id,
        'exemptible_type' => Customer::class,
        'exemptible_id' => $customer->id,
        'tax_rate_id' => $taxRate->id,
        'exemption_percentage' => 50,
    ]);

    $voucher = \App\Models\Accounting\Voucher::factory()->create([
        'organization_id' => $organization->id,
        'amount' => 1000,
        'type' => 'sales',
    ]);

    $taxService = new TaxCalculationService;
    $calculations = $taxService->calculateTaxes($voucher, 1000, 'sales', $customer);

    expect($calculations)->toHaveCount(1);
    expect($calculations->first()->tax_amount)->toBe(50.0); // 10% of 50% exempted amount
    expect($calculations->first()->taxable_amount)->toBe(500.0);
});

test('tax calculation service handles compound taxes', function () {
    $organization = Organization::factory()->create();

    $baseTax = TaxRate::factory()->create([
        'organization_id' => $organization->id,
        'rate' => 10.0,
        'type' => 'sales',
        'is_compound' => false,
    ]);

    $compoundTax = TaxRate::factory()->create([
        'organization_id' => $organization->id,
        'rate' => 5.0,
        'type' => 'sales',
        'is_compound' => true,
    ]);

    $voucher = \App\Models\Accounting\Voucher::factory()->create([
        'organization_id' => $organization->id,
        'amount' => 1000,
        'type' => 'sales',
    ]);

    $taxService = new TaxCalculationService;
    $calculations = $taxService->calculateTaxes($voucher, 1000, 'sales');

    expect($calculations)->toHaveCount(2);

    $baseTaxCalc = $calculations->firstWhere('tax_rate_id', $baseTax->id);
    $compoundTaxCalc = $calculations->firstWhere('tax_rate_id', $compoundTax->id);

    expect($baseTaxCalc->tax_amount)->toBe(100.0); // 10% of 1000
    expect($compoundTaxCalc->tax_amount)->toBe(55.0); // 5% of (1000 + 100)
});

// Tax Reporting Service Tests
test('tax reporting service generates correct reports', function () {
    $organization = Organization::factory()->create();
    $taxRate = TaxRate::factory()->create(['organization_id' => $organization->id]);

    TaxCalculation::factory()->count(5)->create([
        'organization_id' => $organization->id,
        'tax_rate_id' => $taxRate->id,
        'base_amount' => 1000,
        'tax_amount' => 100,
        'calculation_date' => now(),
    ]);

    $reportingService = new TaxReportingService;
    $report = $reportingService->generateTaxReport(
        $organization->id,
        now()->startOfMonth()->toDateString(),
        now()->endOfMonth()->toDateString()
    );

    expect($report['summary']['total_tax_collected'])->toBe(500.0);
    expect($report['summary']['total_base_amount'])->toBe(5000.0);
    expect($report['summary']['total_transactions'])->toBe(5);
});

// Tax Compliance Service Tests
test('tax compliance service creates filings correctly', function () {
    $organization = Organization::factory()->create();
    $taxRate = TaxRate::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create();

    TaxCalculation::factory()->count(3)->create([
        'organization_id' => $organization->id,
        'tax_rate_id' => $taxRate->id,
        'tax_amount' => 100,
        'calculation_date' => now(),
    ]);

    $complianceService = new TaxComplianceService;
    $filing = $complianceService->createTaxFiling(
        $organization->id,
        $taxRate->id,
        'quarterly',
        now()->startOfQuarter()->toDateString(),
        now()->endOfQuarter()->toDateString(),
        $user
    );

    expect($filing)->toBeInstanceOf(TaxFiling::class);
    expect($filing->total_tax_collected)->toBe(300.0);
    expect($filing->status)->toBe('draft');
});

test('tax compliance service checks expiry dates', function () {
    $organization = Organization::factory()->create();
    $customer = Customer::factory()->create(['organization_id' => $organization->id]);

    // Expiring soon
    TaxExemption::factory()->create([
        'organization_id' => $organization->id,
        'exemptible_type' => Customer::class,
        'exemptible_id' => $customer->id,
        'expiry_date' => now()->addDays(30),
        'is_active' => true,
    ]);

    // Not expiring soon
    TaxExemption::factory()->create([
        'organization_id' => $organization->id,
        'exemptible_type' => Customer::class,
        'exemptible_id' => $customer->id,
        'expiry_date' => now()->addDays(120),
        'is_active' => true,
    ]);

    $complianceService = new TaxComplianceService;
    $expiringExemptions = $complianceService->checkExpiryDates();

    expect($expiringExemptions)->toHaveCount(1);
});

// Integration Tests
test('voucher tax integration works end-to-end', function () {
    $organization = Organization::factory()->create();
    $taxRate = TaxRate::factory()->create([
        'organization_id' => $organization->id,
        'rate' => 8.0,
        'type' => 'sales',
    ]);

    $voucher = \App\Models\Accounting\Voucher::factory()->create([
        'organization_id' => $organization->id,
        'amount' => 500,
        'type' => 'sales',
        'status' => 'draft',
    ]);

    // Calculate taxes
    $voucher->calculateTaxes();

    // Verify calculations
    expect($voucher->taxCalculations)->toHaveCount(1);
    expect($voucher->total_tax)->toBe(40.0);
    expect($voucher->total_with_tax)->toBe(540.0);

    // Test recalculation
    $voucher->amount = 600;
    $voucher->save();
    $voucher->recalculateTaxes();

    expect($voucher->fresh()->total_tax)->toBe(48.0);
    expect($voucher->fresh()->total_with_tax)->toBe(648.0);
});

test('tax calculations are properly scoped to organization', function () {
    $org1 = Organization::factory()->create();
    $org2 = Organization::factory()->create();

    $taxRate1 = TaxRate::factory()->create(['organization_id' => $org1->id]);
    $taxRate2 = TaxRate::factory()->create(['organization_id' => $org2->id]);

    $voucher1 = \App\Models\Accounting\Voucher::factory()->create([
        'organization_id' => $org1->id,
        'amount' => 1000,
        'type' => 'sales',
    ]);

    $voucher2 = \App\Models\Accounting\Voucher::factory()->create([
        'organization_id' => $org2->id,
        'amount' => 1000,
        'type' => 'sales',
    ]);

    $taxService = new TaxCalculationService;

    // Calculate taxes for org1
    $calculations1 = $taxService->calculateTaxes($voucher1, 1000, 'sales');

    // Calculate taxes for org2
    $calculations2 = $taxService->calculateTaxes($voucher2, 1000, 'sales');

    // Verify calculations are separate
    expect($calculations1)->toHaveCount(1);
    expect($calculations2)->toHaveCount(1);
    expect($calculations1->first()->tax_rate_id)->toBe($taxRate1->id);
    expect($calculations2->first()->tax_rate_id)->toBe($taxRate2->id);
});
