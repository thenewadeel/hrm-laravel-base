<?php

namespace Tests\Feature\Portal;

use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\SetupEmployee;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase, SetupEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        // This sets up organization AND employee data
        $this->setupEmployeeManagement();
    }

    #[Test]
    public function test_can_view_employee_list()
    {
        $this->actingAsHrUser();

        $response = $this->get(route('hr.employees.index'));

        $response->assertStatus(200);
        $response->assertSee('Employee Management');
        $response->assertSee('John Doe');
        $response->assertSee('Software Developer');
    }

    #[Test]
    public function test_can_view_employee_details()
    {
        $this->actingAsHrUser();

        // Create test data for this specific test
        $this->createEmployeeDetailsTestData($this->employee);

        $response = $this->get(route('hr.employees.show', $this->employee));

        $response->assertStatus(200);
        $response->assertSee('Employee Profile');
        $response->assertSee('John Doe');
        $response->assertSee('john.doe@test.com');
        $response->assertSee('Software Developer');
    }

    #[Test]
    public function test_can_create_employee()
    {
        $this->actingAsHrUser();

        $employeeData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'position' => 'Senior Developer',
            'organization_unit_id' => $this->engineeringUnit->id,
            'roles' => ['employee'],
            'required_daily_hours' => 8.0,
            'salary_per_month' => 5000.00,
            'pay_frequency' => 'monthly',
        ];

        $response = $this->post(route('hr.employees.store'), $employeeData);

        $response->assertRedirect(route('hr.employees.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@test.com',
        ]);

        // Verify user was created and linked
        $this->assertDatabaseHas('users', [
            'email' => 'jane.smith@test.com',
        ]);
    }

    #[Test]
    public function test_can_update_employee_details()
    {
        $this->actingAsHrUser();

        $updateData = [
            'first_name' => 'John',
            'last_name' => 'Updated',
            'email' => 'john.updated@test.com',
            'position' => 'Lead Developer',
            'organization_unit_id' => $this->engineeringUnit->id,
            'salary_per_month' => 6000.00,
            'required_daily_hours' => 7.5,
        ];

        $response = $this->put(route('hr.employees.update', $this->employee), $updateData);

        $response->assertRedirect(route('hr.employees.show', $this->employee));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'id' => $this->employee->id,
            'first_name' => 'John',
            'last_name' => 'Updated',
            'email' => 'john.updated@test.com',
        ]);
    }

    #[Test]
    public function test_can_update_biometric_id()
    {
        $this->actingAsHrUser();

        $newBiometricId = 'BIO67890';

        $response = $this->put(route('hr.employees.update-biometric', $this->employee), [
            'biometric_id' => $newBiometricId,
        ]);

        $response->assertRedirect(route('hr.employees.show', $this->employee));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'id' => $this->employee->id,
            'biometric_id' => $newBiometricId,
        ]);
    }

    #[Test]
    public function test_employee_belongs_to_organization_unit()
    {
        $this->actingAsHrUser();

        $response = $this->get(route('hr.employees.show', $this->employee));

        $response->assertStatus(200);
        $response->assertSee('Engineering');

        // Verify the relationship
        $this->assertEquals($this->engineeringUnit->id, $this->employee->organization_unit_id);
        $this->assertEquals('Engineering', $this->employee->organizationUnit->name);
    }

    #[Test]
    public function test_employee_creation_requires_authentication()
    {
        $employeeData = [
            'first_name' => 'Unauthorized',
            'last_name' => 'Employee',
            'email' => 'unauthorized@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['employee'],
        ];

        $response = $this->post(route('hr.employees.store'), $employeeData);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_non_hr_users_cannot_create_employees()
    {
        $this->actingAsRegularEmployee();
        $employeeData = [
            'first_name' => 'New',
            'last_name' => 'Employee',
            'email' => 'new@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('hr.employees.store'), $employeeData);

        $response->assertStatus(403);
    }

    #[Test]
    public function test_can_search_employees()
    {
        $this->actingAsHrUser();

        $response = $this->get(route('hr.employees.index', ['search' => 'Marketing']));

        $response->assertStatus(200);
        $response->assertSee('Marketing Person');
        $response->assertDontSee('John Doe');
    }

    #[Test]
    public function test_can_filter_employees_by_department()
    {
        $this->actingAsHrUser();

        $response = $this->get(route('hr.employees.index', ['department' => $this->marketingUnit->id]));

        $response->assertStatus(200);
        $response->assertSee('Marketing Person');
        $response->assertSee('Marketing Specialist');
        $response->assertDontSee('John Doe');
    }

    #[Test]
    public function test_employee_attendance_integration()
    {
        $this->actingAsHrUser();

        // Create attendance records for this specific test
        AttendanceRecord::factory()
            ->count(10)
            ->create(['employee_id' => $this->employee->id, 'status' => 'present']);

        AttendanceRecord::factory()
            ->count(2)
            ->create(['employee_id' => $this->employee->id, 'status' => 'late']);

        $response = $this->get(route('hr.employees.show', $this->employee));

        $response->assertStatus(200);
        // dd(AttendanceRecord::count());
        // Verify attendance records are loaded
        $attendanceCount = AttendanceRecord::where('employee_id', $this->employee->id)->count();
        $this->assertEquals(12, $attendanceCount);
    }

    #[Test]
    public function test_employee_leave_balance_calculation()
    {
        $this->actingAsHrUser();
        $currentYear = now()->year;
        // Create leave requests for this specific test with explicit current year dates
        LeaveRequest::factory()
            ->create([
                'employee_id' => $this->employee->id,
                'status' => 'approved',
                // 'total_days' => 3, is forced set on model boot
                'start_date' => now()->setYear($currentYear)->subDays(30),
                'end_date' => now()->setYear($currentYear)->subDays(25),
            ]);

        // dd(LeaveRequest::all()->toArray());
        LeaveRequest::factory()
            ->create([
                'employee_id' => $this->employee->id,
                'status' => 'approved',
                // 'total_days' => 3, is forced set on model boot
                'start_date' => now()->setYear($currentYear)->subDays(10),
                'end_date' => now()->setYear($currentYear)->subDays(7),
            ]);

        $response = $this->get(route('hr.employees.show', $this->employee));

        $response->assertStatus(200);

        // Verify calculation
        $usedLeave = LeaveRequest::where('employee_id', $this->employee->id)
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->sum('total_days');

        $this->assertEquals(10, $usedLeave);
    }

    #[Test]
    public function test_can_create_employee_without_user_account()
    {
        $this->actingAsHrUser();

        $employeeData = [
            'first_name' => 'HR',
            'last_name' => 'Record Only',
            'email' => 'hr.record@test.com',
            'position' => 'Contract Worker',
            'organization_unit_id' => $this->engineeringUnit->id,
            'required_daily_hours' => 8.0,
            'salary_per_month' => 3000.00,
            'pay_frequency' => 'monthly',
        ];

        $response = $this->post(route('hr.employees.store-without-user'), $employeeData);

        $response->assertRedirect(route('hr.employees.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employees', [
            'first_name' => 'HR',
            'last_name' => 'Record Only',
            'email' => 'hr.record@test.com',
            'user_id' => null,
        ]);
    }

    #[Test]
    public function test_can_grant_system_access_to_employee()
    {
        // Create an employee without user account first
        $employeeWithoutUser = $this->createEmployeeWithoutUser([
            'first_name' => 'No',
            'last_name' => 'Access',
            'email' => 'no.access@test.com',
        ]);

        $this->actingAsHrUser();

        $accessData = [
            'roles' => ['employee'],
            'position' => 'Junior Developer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('hr.employees.grant-access', $employeeWithoutUser), $accessData);

        $response->assertRedirect(route('hr.employees.show', $employeeWithoutUser));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'no.access@test.com',
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employeeWithoutUser->id,
            'user_id' => User::where('email', 'no.access@test.com')->first()->id,
        ]);
    }
}
