<?php

use App\Models\AllowanceType;
use App\Models\DeductionType;
use App\Models\Employee;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeDeduction;
use App\Models\TaxBracket;
use App\Services\PayrollCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('payroll calculation service calculates basic payroll correctly', function () {
    $user = createUserWithOrganization();
    $organization = $user->organizations()->first();

    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'basic_salary' => 5000,
    ]);

    $service = new PayrollCalculationService;
    $payroll = $service->calculateEmployeePayroll($employee, '2025-11');

    expect((float) $payroll['basic_salary'])->toBe(5000.0);
    expect((float) $payroll['gross_pay'])->toBe(5000.0);
    expect((float) $payroll['net_pay'])->toBe(5000.0);
});

test('payroll calculation includes allowances correctly', function () {
    $user = createUserWithOrganization();
    $organization = $user->organizations()->first();

    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'basic_salary' => 5000,
    ]);

    $allowanceType = AllowanceType::factory()->create([
        'organization_id' => $organization->id,
        'calculation_type' => 'fixed_amount',
        'default_value' => 500,
        'is_taxable' => true,
    ]);

    EmployeeAllowance::factory()->create([
        'employee_id' => $employee->id,
        'organization_id' => $organization->id,
        'allowance_type_id' => $allowanceType->id,
        'amount' => 500,
        'effective_date' => now()->subMonth(), // Make it effective before the period
    ]);

    $service = new PayrollCalculationService;
    $payroll = $service->calculateEmployeePayroll($employee, '2025-11');

    expect((float) $payroll['allowances']['total'])->toBe(500.0);
    expect((float) $payroll['gross_pay'])->toBe(5500.0);
});

test('payroll calculation includes deductions correctly', function () {
    $user = createUserWithOrganization();
    $organization = $user->organizations()->first();

    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'basic_salary' => 5000,
    ]);

    $deductionType = DeductionType::factory()->create([
        'organization_id' => $organization->id,
        'calculation_type' => 'fixed_amount',
        'default_value' => 200,
        'is_tax_exempt' => false,
    ]);

    EmployeeDeduction::factory()->create([
        'employee_id' => $employee->id,
        'organization_id' => $organization->id,
        'deduction_type_id' => $deductionType->id,
        'amount' => 200,
        'effective_date' => now()->subMonth(), // Make it effective before the period
    ]);

    $service = new PayrollCalculationService;
    $payroll = $service->calculateEmployeePayroll($employee, '2025-11');

    expect((float) $payroll['deductions']['total'])->toBe(200.0);
    expect((float) $payroll['total_deductions'])->toBe(200.0);
    expect((float) $payroll['net_pay'])->toBe(4800.0);
});

test('tax calculation works correctly', function () {
    $user = createUserWithOrganization();
    $organization = $user->organizations()->first();

    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'basic_salary' => 5000,
    ]);

    TaxBracket::factory()->create([
        'organization_id' => $organization->id,
        'min_income' => 0,
        'max_income' => 6000,
        'rate' => 15,
        'base_tax' => 0,
        'exemption_amount' => 500,
        'effective_date' => now(),
    ]);

    $service = new PayrollCalculationService;
    $payroll = $service->calculateEmployeePayroll($employee, '2025-11');

    expect((float) $payroll['taxable_income'])->toBe(5000.0);
    expect((float) $payroll['tax'])->toBe(675.0); // (5000 - 500) * 15%
});

test('effective basic salary considers increments', function () {
    $user = createUserWithOrganization();
    $organization = $user->organizations()->first();

    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'basic_salary' => 5000,
    ]);

    // Create an increment that should be effective
    $increment = $employee->increments()->create([
        'organization_id' => $organization->id,
        'increment_type' => 'percentage',
        'increment_value' => 10,
        'previous_salary' => 5000,
        'new_salary' => 5500,
        'effective_date' => now()->subMonth(), // Effective before period
        'status' => 'approved',
    ]);

    // Debug: Check if increment was created
    expect($increment)->toBeInstanceOf(\App\Models\EmployeeIncrement::class);
    expect($increment->status)->toBe('approved');
    expect((float) $increment->new_salary)->toBe(5500.0);

    // Implement the increment as well
    $increment->implement();

    $service = new PayrollCalculationService;
    $effectiveSalary = $service->getEffectiveBasicSalary($employee, '2025-11');

    expect((float) $effectiveSalary)->toBe(5500.0);
});

test('batch payroll processing works correctly', function () {
    $user = createUserWithOrganization();
    $organization = $user->organizations()->first();

    $employees = Employee::factory()->count(3)->create([
        'organization_id' => $organization->id,
        'basic_salary' => 5000,
    ]);

    $service = new PayrollCalculationService;
    $results = $service->processBatchPayroll($employees->pluck('id')->toArray(), '2025-11');

    expect($results)->toHaveCount(3);
    foreach ($results as $result) {
        expect((float) $result['basic_salary'])->toBe(5000.0);
        expect((float) $result['net_pay'])->toBe(5000.0);
    }
});

test('payroll summary generation works correctly', function () {
    $user = createUserWithOrganization();
    $organization = $user->organizations()->first();

    $employees = Employee::factory()->count(2)->create([
        'organization_id' => $organization->id,
        'basic_salary' => 5000,
    ]);

    $service = new PayrollCalculationService;
    $summary = $service->generatePayrollSummary($organization->id, '2025-11');

    expect($summary['total_employees'])->toBe(2);
    expect((float) $summary['total_basic_salary'])->toBe(10000.0);
    expect((float) $summary['total_net_pay'])->toBe(10000.0);
    expect($summary['employee_breakdown'])->toHaveCount(2);
});

// Helper function to create user with organization
function createUserWithOrganization()
{
    $user = \App\Models\User::factory()->create();
    $organization = \App\Models\Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode(['admin'])]);

    return $user;
}
