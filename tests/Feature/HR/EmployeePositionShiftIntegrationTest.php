<?php

use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('assigns position and shift to employee during creation', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $orgUnit = OrganizationUnit::factory()->create(['organization_id' => $organization->id]);
    $position = JobPosition::factory()->create(['organization_id' => $organization->id, 'organization_unit_id' => $orgUnit->id, 'is_active' => true]);
    $shift = Shift::factory()->create(['organization_id' => $organization->id, 'is_active' => true]);

    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'organization_unit_id' => $orgUnit->id,
        'position_id' => $position->id,
        'shift_id' => $shift->id,
        'roles' => ['employee'],
    ];

    $response = $this->actingAs($user)->post(route('hr.employees.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('employees', [
        'position_id' => $position->id,
        'shift_id' => $shift->id,
    ]);
    $this->assertDatabaseHas('organization_user', [
        'position_id' => $position->id,
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

it('rejects an inactive position assigned to employee', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $inactivePosition = JobPosition::factory()->create(['organization_id' => $organization->id, 'is_active' => false]);

    $data = [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'position_id' => $inactivePosition->id,
        'roles' => ['employee'],
    ];

    $response = $this->actingAs($user)->post(route('hr.employees.store'), $data);

    $response->assertSessionHasErrors('position_id');
    $this->assertDatabaseMissing('employees', [
        'email' => 'jane@example.com',
    ]);
});

it('rejects a position from another organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $otherOrganization = Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $crossOrgPosition = JobPosition::factory()->create(['organization_id' => $otherOrganization->id]);

    $data = [
        'first_name' => 'Cross',
        'last_name' => 'Org',
        'email' => 'cross.org@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'position_id' => $crossOrgPosition->id,
        'roles' => ['employee'],
    ];

    $response = $this->actingAs($user)->post(route('hr.employees.store'), $data);

    $response->assertSessionHasErrors('position_id');
});

it('propagates position default roles to the organization user roles', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $position = JobPosition::factory()->create([
        'organization_id' => $organization->id,
        'is_active' => true,
        'default_roles' => ['store_manager', 'inventory_clerk'],
    ]);

    $data = [
        'first_name' => 'Role',
        'last_name' => 'Test',
        'email' => 'roles.test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'position_id' => $position->id,
        'roles' => ['employee'],
    ];

    $response = $this->actingAs($user)->post(route('hr.employees.store'), $data);

    $response->assertRedirect();

    $employee = Employee::where('email', 'roles.test@example.com')->first();
    $orgUser = $employee->organizationUser()->first();

    expect($orgUser->roles)
        ->toContain('store_manager')
        ->toContain('inventory_clerk')
        ->toContain('employee')
        ->toHaveCount(3);
});

it('defaults required daily hours from the assigned shift', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $shift = Shift::factory()->create(['organization_id' => $organization->id, 'is_active' => true, 'working_hours' => 9]);
    $email = 'hours-'.uniqid().'@test.com';

    $data = [
        'first_name' => 'Hours',
        'last_name' => 'Test',
        'email' => $email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'shift_id' => $shift->id,
        'roles' => ['employee'],
    ];

    $response = $this->actingAs($user)->post(route('hr.employees.store'), $data);

    $response->assertRedirect();
    $this->assertDatabaseHas('employees', [
        'email' => $email,
        'required_daily_hours' => '9',
    ]);
});

it('persists required daily hours and pay frequency', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->current_organization_id = $organization->id;
    $user->save();

    $email = 'payroll-'.uniqid().'@test.com';

    $data = [
        'first_name' => 'Payroll',
        'last_name' => 'Test',
        'email' => $email,
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => ['employee'],
        'required_daily_hours' => 7.5,
        'pay_frequency' => 'biweekly',
    ];

    $this->actingAs($user)->post(route('hr.employees.store'), $data);

    $this->assertDatabaseHas('employees', [
        'email' => $email,
        'required_daily_hours' => '7.5',
        'pay_frequency' => 'biweekly',
    ]);
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
