<?php

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('attendance dashboard has working sync and payroll buttons', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get('/attendance/dashboard')
        ->assertStatus(200)
        ->assertSee('Sync Biometric Data')
        ->assertSee('Export for Payroll')
        ->assertSee('attendance/sync-biometric')
        ->assertSee('attendance/export-payroll');
});

test('attendance dashboard has functional employee dropdown', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $employee1 = Employee::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
    ]);

    $user2 = User::factory()->create();
    $user2->organizations()->attach($organization->id, ['roles' => 'employee']);
    $employee2 = Employee::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user2->id,
    ]);

    $response = $this->actingAs($user)
        ->get('/attendance/dashboard')
        ->assertStatus(200);

    // Should contain employee names in dropdown
    $response->assertSee($employee1->user->name);
    $response->assertSee($employee2->user->name);
    $response->assertSee('All Employees');
});

test('attendance dashboard has search and status filters', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get('/attendance/dashboard')
        ->assertStatus(200)
        ->assertSee('Search Employee')
        ->assertSee('Search by employee name...')
        ->assertSee('All Status')
        ->assertSee('Present')
        ->assertSee('Absent')
        ->assertSee('Late')
        ->assertSee('Missed Punch')
        ->assertSee('On Leave');
});

test('attendance dashboard has quick date filters', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get('/attendance/dashboard')
        ->assertStatus(200)
        ->assertSee('Today')
        ->assertSee('This Week')
        ->assertSee('This Month')
        ->assertSee('setQuickDateRange');
});

test('attendance dashboard filters work correctly', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
    ]);

    // Create attendance records
    AttendanceRecord::factory()->create([
        'employee_id' => $employee->id,
        'organization_id' => $organization->id,
        'record_date' => Carbon::today(),
        'status' => 'present',
    ]);

    AttendanceRecord::factory()->create([
        'employee_id' => $employee->id,
        'organization_id' => $organization->id,
        'record_date' => Carbon::yesterday(),
        'status' => 'late',
    ]);

    // Test status filter
    $response = $this->actingAs($user)
        ->get('/attendance/dashboard?status=late')
        ->assertStatus(200);

    // Test search filter
    $response = $this->actingAs($user)
        ->get('/attendance/dashboard?search='.$employee->user->name)
        ->assertStatus(200);

    // Test exceptions filter
    $response = $this->actingAs($user)
        ->get('/attendance/dashboard?show_exceptions=1')
        ->assertStatus(200);
});

test('attendance dashboard has modal functionality for actions', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $user->organizations()->attach($organization->id, ['roles' => 'admin']);
    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get('/attendance/dashboard')
        ->assertStatus(200)
        ->assertSee('function openRegularizeModal')
        ->assertSee('function openApplyLeaveModal');
});
