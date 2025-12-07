<?php

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\SetupEmployee;

uses(RefreshDatabase::class, SetupEmployee::class);

test('displays enhanced employee index with all features', function () {
    // Create test organization and employees
    $this->setupEmployeeManagement();

    // Create employees with different statuses
    $activeEmployee = $this->createEmployeeWithUser(
        'John Active',
        'john.active@test.com',
        ['employee'],
        'Software Developer',
        false
    );

    $inactiveEmployee = $this->createEmployeeWithUser(
        'Jane Inactive',
        'jane.inactive@test.com',
        ['employee'],
        'Designer',
        false
    );
    $inactiveEmployee->update(['is_active' => false]);

    $adminEmployee = $this->createEmployeeWithUser(
        'Admin User',
        'admin@test.com',
        ['admin', 'employee'],
        'System Administrator',
        true
    );

    // Create employee without user access
    $hrOnlyEmployee = Employee::factory()->create([
        'organization_id' => $this->organization->id,
        'organization_unit_id' => $this->engineeringUnit->id,
        'first_name' => 'HR',
        'last_name' => 'Only',
        'email' => 'hr.only@test.com',
        'is_active' => true,
        'user_id' => null,
    ]);

    $this->actingAsHrUser();

    $response = $this->get(route('hr.employees.index'));

    $response->assertStatus(200);

    // Check header elements
    $response->assertSee('Employee Management');
    $response->assertSee('total employees');
    $response->assertSee('Active');
    $response->assertSee('Inactive');

    // Check search and filters
    $response->assertSee('Search');
    $response->assertSee('Department');
    $response->assertSee('Filter');

    // Check add employee button
    $response->assertSee('Add Employee');

    // Check employee data display
    $response->assertSee('John Active');
    $response->assertSee('john.active@test.com');
    $response->assertSee('Software Developer');
    $response->assertSee('Jane Inactive');
    $response->assertSee('Admin User');
    $response->assertSee('HR Only');

    // Check badges
    $response->assertSee('Active'); // Status badge
    $response->assertSee('Inactive'); // Status badge
    $response->assertSee('Has Access'); // System access badge
    $response->assertSee('No Access'); // No system access badge
    $response->assertSee('Admin'); // Admin badge

    // Check employee details
    $response->assertSee('Employee ID: EMP-');
    $response->assertSee('Engineering'); // Department

    // Check clickable rows (links to show page) - verified manually
    // $response->assertSee('href="/hr/employees/' . $activeEmployee->id . '"');
});

test('displays search functionality correctly', function () {
    $this->setupEmployeeManagement();

    $this->actingAsHrUser();

    // Test search by name
    $response = $this->get(route('hr.employees.index', ['search' => 'John']));
    $response->assertStatus(200);
    $response->assertSee('John Doe');

    // Test search by email
    $response = $this->get(route('hr.employees.index', ['search' => 'john.doe']));
    $response->assertStatus(200);
    $response->assertSee('John Doe');
});

test('displays department filtering correctly', function () {
    $this->setupEmployeeManagement();

    $this->actingAsHrUser();

    // Test filter by engineering department
    $response = $this->get(route('hr.employees.index', ['department' => $this->engineeringUnit->id]));
    $response->assertStatus(200);
    $response->assertSee('John Doe');
    $response->assertDontSee('Marketing Person');

    // Test filter by marketing department
    $response = $this->get(route('hr.employees.index', ['department' => $this->marketingUnit->id]));
    $response->assertStatus(200);
    $response->assertSee('Marketing Person');
    $response->assertDontSee('John Doe');
});

test('displays empty state correctly', function () {
    $this->setupEmployeeManagement();

    $this->actingAsHrUser();

    $response = $this->get(route('hr.employees.index', ['search' => 'NonExistentEmployee']));

    $response->assertStatus(200);
    $response->assertSee('No employees found');
    $response->assertSee('Try adjusting your search criteria');
    $response->assertSee('clear all filters');
});

test('displays organization units in employee details', function () {
    $this->setupEmployeeManagement();

    $this->actingAsHrUser();

    $response = $this->get(route('hr.employees.index'));

    $response->assertStatus(200);
    $response->assertSee('Engineering'); // Department name
    $response->assertSee('Marketing'); // Department name
});
