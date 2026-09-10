<?php

use App\Models\Employee;
use App\Models\JobPosition;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\OrganizationUser;
use App\Models\Scopes\OrganizationScope;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * MVP vertical slice — Org Management → Employee Enrollment.
 *
 * Validates the Blue/Green Day-3 exclusion criteria:
 *   - a new org must onboard + enroll without seeing tenant A's data
 *   - cross-org position_id / shift_id / organization_unit_id must be rejected
 *   - role/permission propagation from position default_roles onto the pivot
 */
beforeEach(function () {
    $this->tenantA = Organization::factory()->create(['name' => 'Tenant Alpha']);
    $this->tenantB = Organization::factory()->create(['name' => 'Tenant Beta']);

    $this->adminA = User::factory()->create(['current_organization_id' => $this->tenantA->id]);
    $this->adminB = User::factory()->create(['current_organization_id' => $this->tenantB->id]);

    $this->adminA->organizations()->attach($this->tenantA->id, [
        'roles' => json_encode(['admin']),
    ]);
    $this->adminB->organizations()->attach($this->tenantB->id, [
        'roles' => json_encode(['admin']),
    ]);
});

it('provisions each tenant with an isolated root unit and admin', function () {
    $this->actingAs($this->adminA)->post(route('setup.organization.store'), ['name' => 'Alpha Org']);
    $this->actingAs($this->adminB)->post(route('setup.organization.store'), ['name' => 'Beta Org']);

    $orgA = Organization::where('name', 'Alpha Org')->firstOrFail();
    $orgB = Organization::where('name', 'Beta Org')->firstOrFail();

    $rootUnitsA = OrganizationUnit::withoutGlobalScope(OrganizationScope::class)
        ->where('organization_id', $orgA->id)
        ->where('type', 'head_office')
        ->get();
    $rootUnitsB = OrganizationUnit::withoutGlobalScope(OrganizationScope::class)
        ->where('organization_id', $orgB->id)
        ->where('type', 'head_office')
        ->get();

    expect($rootUnitsA)->toHaveCount(1)
        ->and($rootUnitsB)->toHaveCount(1)
        ->and($rootUnitsA->first()->id)->not->toBe($rootUnitsB->first()->id);

    // Both provisioners were attached as admin to their own org only
    expect($this->adminA->organizations()->pluck('organizations.id'))
        ->toContain($orgA->id)->not->toContain($orgB->id)
        ->and($this->adminB->organizations()->pluck('organizations.id'))
        ->toContain($orgB->id)->not->toContain($orgA->id);
});

it('does not leak employees across tenants', function () {
    $unitA = OrganizationUnit::factory()->create(['organization_id' => $this->tenantA->id]);
    $unitB = OrganizationUnit::factory()->create(['organization_id' => $this->tenantB->id]);

    Employee::factory()->forOrganization($this->tenantA)->forUnit($unitA)->create([
        'email' => 'alice@alpha.test',
    ]);
    Employee::factory()->forOrganization($this->tenantB)->forUnit($unitB)->create([
        'email' => 'bob@beta.test',
    ]);

    $visibleToA = Employee::where('organization_id', $this->tenantA->id)->get();
    $visibleToB = Employee::where('organization_id', $this->tenantB->id)->get();

    // Scoped queries see only their own tenant's employees
    expect($visibleToA)->toHaveCount(1)
        ->and($visibleToA->first()->email)->toBe('alice@alpha.test')
        ->and($visibleToB)->toHaveCount(1)
        ->and($visibleToB->first()->email)->toBe('bob@beta.test');

    // A user acting inside tenant A must not enumerate tenant B's employees
    $asAdminA = $this->actingAs($this->adminA)->get(route('hr.employees.index'));
    $asAdminA->assertStatus(200)->assertDontSee('bob@beta.test');
});

it('does not leak departments, positions or shifts across tenants', function () {
    $unitA = OrganizationUnit::factory()->create(['organization_id' => $this->tenantA->id, 'name' => 'Alpha Dept']);
    $unitB = OrganizationUnit::factory()->create(['organization_id' => $this->tenantB->id, 'name' => 'Beta Dept']);

    JobPosition::factory()->create(['organization_id' => $this->tenantA->id, 'organization_unit_id' => $unitA->id, 'title' => 'Alpha Role']);
    JobPosition::factory()->create(['organization_id' => $this->tenantB->id, 'organization_unit_id' => $unitB->id, 'title' => 'Beta Role']);

    Shift::factory()->create(['organization_id' => $this->tenantA->id, 'name' => 'Alpha Shift']);
    Shift::factory()->create(['organization_id' => $this->tenantB->id, 'name' => 'Beta Shift']);

    expect(OrganizationUnit::where('organization_id', $this->tenantA->id)->pluck('name'))
        ->toContain('Alpha Dept')->not->toContain('Beta Dept');

    expect(JobPosition::where('organization_id', $this->tenantA->id)->pluck('title'))
        ->toContain('Alpha Role')->not->toContain('Beta Role');

    expect(Shift::where('organization_id', $this->tenantA->id)->pluck('name'))
        ->toContain('Alpha Shift')->not->toContain('Beta Shift');
});

it('rejects cross-tenant position_id on employee enrollment', function () {
    $unitA = OrganizationUnit::factory()->create(['organization_id' => $this->tenantA->id]);
    $positionB = JobPosition::factory()->create(['organization_id' => $this->tenantB->id, 'organization_unit_id' => $unitA->id, 'is_active' => true]);

    $this->actingAs($this->adminA)->post(route('hr.employees.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@alpha.test',
        'password' => 'password',
        'password_confirmation' => 'password',
        'organization_unit_id' => $unitA->id,
        'position_id' => $positionB->id, // belongs to tenant B
        'roles' => ['employee'],
    ])->assertSessionHasErrors('position_id');

    expect(Employee::where('organization_id', $this->tenantA->id)->count())->toBe(0);
});

it('rejects cross-tenant shift_id on employee enrollment', function () {
    $unitA = OrganizationUnit::factory()->create(['organization_id' => $this->tenantA->id]);
    $shiftB = Shift::factory()->create(['organization_id' => $this->tenantB->id, 'is_active' => true]);

    $this->actingAs($this->adminA)->post(route('hr.employees.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@alpha.test',
        'password' => 'password',
        'password_confirmation' => 'password',
        'organization_unit_id' => $unitA->id,
        'shift_id' => $shiftB->id, // belongs to tenant B
        'roles' => ['employee'],
    ])->assertSessionHasErrors('shift_id');

    expect(Employee::where('organization_id', $this->tenantA->id)->count())->toBe(0);
});

it('rejects cross-tenant organization_unit_id on employee enrollment', function () {
    $unitB = OrganizationUnit::factory()->create(['organization_id' => $this->tenantB->id]);

    $this->actingAs($this->adminA)->post(route('hr.employees.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane@alpha.test',
        'password' => 'password',
        'password_confirmation' => 'password',
        'organization_unit_id' => $unitB->id, // belongs to tenant B
        'roles' => ['employee'],
    ])->assertSessionHasErrors('organization_unit_id');

    expect(Employee::where('organization_id', $this->tenantA->id)->count())->toBe(0);
});

it('propagates position default_roles onto the organization_user pivot', function () {
    $unitA = OrganizationUnit::factory()->create(['organization_id' => $this->tenantA->id]);
    $position = JobPosition::factory()->create([
        'organization_id' => $this->tenantA->id,
        'organization_unit_id' => $unitA->id,
        'is_active' => true,
        'default_roles' => ['manager', 'viewer'],
    ]);

    $this->actingAs($this->adminA)->post(route('hr.employees.store'), [
        'first_name' => 'John',
        'last_name' => 'Roe',
        'email' => 'john@alpha.test',
        'password' => 'password',
        'password_confirmation' => 'password',
        'organization_unit_id' => $unitA->id,
        'position_id' => $position->id,
        'roles' => ['employee'],
    ])->assertRedirect();

    $employee = Employee::where('organization_id', $this->tenantA->id)->where('email', 'john@alpha.test')->first();
    $pivot = OrganizationUser::where('organization_id', $this->tenantA->id)
        ->where('user_id', $employee->user_id)
        ->first();

    expect($pivot)->not->toBeNull()
        ->and($pivot->roles)->toBe(['manager', 'viewer', 'employee']);
});

it('enrolls an employee with login, without login, and via grantSystemAccess', function () {
    $unitA = OrganizationUnit::factory()->create(['organization_id' => $this->tenantA->id]);

    // 1. With login
    $this->actingAs($this->adminA)->post(route('hr.employees.store'), [
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
        'email' => 'ada@alpha.test',
        'password' => 'password',
        'password_confirmation' => 'password',
        'organization_unit_id' => $unitA->id,
        'roles' => ['employee'],
    ])->assertRedirect();

    $withLogin = Employee::where('organization_id', $this->tenantA->id)->where('email', 'ada@alpha.test')->first();
    expect($withLogin->user_id)->not->toBeNull();

    // 2. Without login (HR record only)
    $this->actingAs($this->adminA)->post(route('hr.employees.store-without-user'), [
        'first_name' => 'Grace',
        'last_name' => 'Hopper',
        'email' => 'grace@alpha.test',
        'position' => 'Developer',
        'organization_unit_id' => $unitA->id,
        'roles' => ['employee'],
    ])->assertRedirect();

    $noLogin = Employee::where('organization_id', $this->tenantA->id)->where('email', 'grace@alpha.test')->first();
    expect($noLogin->user_id)->toBeNull();

    // 3. Grant system access to the record-only employee
    $this->actingAs($this->adminA)->post(route('hr.employees.grant-access', $noLogin), [
        'roles' => ['employee'],
        'position' => 'Developer',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect();

    expect($noLogin->refresh()->user_id)->not->toBeNull();
});
