<?php

use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\OrganizationUnit;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('assigns position and shift to employee during creation', function () {
    $user = User::factory()->create();
    $orgUnit = OrganizationUnit::factory()->create();
    $position = JobPosition::factory()->create(['organization_unit_id' => $orgUnit->id]);
    $shift = Shift::factory()->create();

    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'organization_unit_id' => $orgUnit->id,
        'position_id' => $position->id,
        'shift_id' => $shift->id,
    ];

    $response = $this->actingAs($user)->post(route('hr.employees.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('employees', [
        'position_id' => $position->id,
        'shift_id' => $shift->id,
    ]);
});

it('loads employee with position and shift relationships', function () {
    $employee = Employee::factory()->create();
    $position = JobPosition::factory()->create();
    $shift = Shift::factory()->create();

    $employee->update([
        'position_id' => $position->id,
        'shift_id' => $shift->id,
    ]);

    $loadedEmployee = Employee::with(['position', 'shift'])->find($employee->id);

    expect($loadedEmployee->position)->toBeInstanceOf(JobPosition::class)
        ->and($loadedEmployee->shift)->toBeInstanceOf(Shift::class);
});

it('prevents assigning inactive position to employee', function () {
    $user = User::factory()->create();
    $inactivePosition = JobPosition::factory()->create(['is_active' => false]);

    $data = [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'position_id' => $inactivePosition->id,
    ];

    $response = $this->actingAs($user)->post(route('hr.employees.store'), $data);

    $response->assertSessionHasErrors('position_id');
});

it('filters employees by position', function () {
    $position1 = JobPosition::factory()->create();
    $position2 = JobPosition::factory()->create();
    Employee::factory()->count(2)->create(['position_id' => $position1->id]);
    Employee::factory()->count(1)->create(['position_id' => $position2->id]);

    $employees = Employee::where('position_id', $position1->id)->get();

    expect($employees)->toHaveCount(2);
});

it('filters employees by shift', function () {
    $shift1 = Shift::factory()->create();
    $shift2 = Shift::factory()->create();
    Employee::factory()->count(3)->create(['shift_id' => $shift1->id]);
    Employee::factory()->count(2)->create(['shift_id' => $shift2->id]);

    $employees = Employee::where('shift_id', $shift1->id)->get();

    expect($employees)->toHaveCount(3);
});
