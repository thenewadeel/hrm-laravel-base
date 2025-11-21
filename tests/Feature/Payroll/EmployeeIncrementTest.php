<?php

use App\Models\Employee;
use App\Models\EmployeeIncrement;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('employee increment can be created', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode(['admin'])]);

    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'basic_salary' => 5000,
    ]);

    $increment = EmployeeIncrement::factory()->create([
        'employee_id' => $employee->id,
        'organization_id' => $organization->id,
        'increment_type' => 'percentage',
        'increment_value' => 10,
        'previous_salary' => 5000,
        'new_salary' => 5500,
        'status' => 'pending',
    ]);

    expect($increment)->toBeInstanceOf(EmployeeIncrement::class);
    expect($increment->employee_id)->toBe($employee->id);
    expect($increment->increment_type)->toBe('percentage');
    expect((float) $increment->increment_value)->toBe(10.0);
    expect($increment->status)->toBe('pending');
});

test('employee increment can be approved', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode(['admin'])]);

    $employee = Employee::factory()->create(['organization_id' => $organization->id]);

    $increment = EmployeeIncrement::factory()->create([
        'employee_id' => $employee->id,
        'organization_id' => $organization->id,
        'status' => 'pending',
    ]);

    $increment->approve($user, 'Approved for excellent performance');

    $increment->refresh();
    expect($increment->status)->toBe('approved');
    expect($increment->approved_by)->toBe($user->id);
});

test('employee increment can be implemented', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode(['admin'])]);

    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'basic_salary' => 5000,
    ]);

    $increment = EmployeeIncrement::factory()->create([
        'employee_id' => $employee->id,
        'organization_id' => $organization->id,
        'status' => 'approved',
        'new_salary' => 5500,
    ]);

    $increment->implement();

    $increment->refresh();
    $employee->refresh();

    expect($increment->status)->toBe('implemented');
    expect((float) $employee->basic_salary)->toBe(5500.0);
});

test('increment calculation works correctly for percentage', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $employee = Employee::factory()->create(['organization_id' => $organization->id]);

    $increment = EmployeeIncrement::factory()->create([
        'employee_id' => $employee->id,
        'organization_id' => $organization->id,
        'increment_type' => 'percentage',
        'increment_value' => 15,
        'previous_salary' => 4000,
        'new_salary' => 4600,
    ]);

    expect($increment->increment_amount)->toBe(600.0);
    expect($increment->increment_percentage)->toBe(15.0);
});

test('increment calculation works correctly for fixed amount', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $employee = Employee::factory()->create(['organization_id' => $organization->id]);

    $increment = EmployeeIncrement::factory()->create([
        'employee_id' => $employee->id,
        'organization_id' => $organization->id,
        'increment_type' => 'fixed_amount',
        'increment_value' => 1000,
        'previous_salary' => 4000,
        'new_salary' => 5000,
    ]);

    expect($increment->increment_amount)->toBe(1000.0);
    expect($increment->increment_percentage)->toBe(25.0);
});

test('only approved increments can be implemented', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => json_encode(['admin'])]);

    $employee = Employee::factory()->create(['organization_id' => $organization->id]);

    $increment = EmployeeIncrement::factory()->create([
        'employee_id' => $employee->id,
        'organization_id' => $organization->id,
        'status' => 'pending',
    ]);

    expect(fn () => $increment->implement())->toThrow('Only approved increments can be implemented');
});
