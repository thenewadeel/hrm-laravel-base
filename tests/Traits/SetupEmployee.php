<?php

namespace Tests\Traits;

use App\Models\Employee;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\OrganizationUser;
use App\Models\User;

trait SetupEmployee
{
    use SetupOrganization;

    // protected Organization $organization;
    protected OrganizationUnit $engineeringUnit;
    protected OrganizationUnit $marketingUnit;
    protected Employee $hrUser;
    protected Employee $manager;
    protected Employee $employee;
    protected $additionalEmployees;
    protected $marketingEmployee;

    /**
     * Setup employee management test environment
     */
    protected function setupEmployeeManagement(): void
    {
        // First setup organization (from SetupOrganization trait)
        $this->setupOrganization();

        // Now setup employee-specific data
        $this->createEmployeeTestData();
    }

    /**
     * Create all employee test data
     */
    protected function createEmployeeTestData(): void
    {
        // Use existing organization from SetupOrganization or create new one
        $this->organization = $this->organization ?? Organization::factory()->create([
            'name' => 'Test Organization',
            'is_active' => true
        ]);

        // Create organization units if they don't exist
        $this->engineeringUnit = $this->engineeringUnit ?? OrganizationUnit::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Engineering',
            'type' => 'department'
        ]);

        $this->marketingUnit = $this->marketingUnit ?? OrganizationUnit::factory()->create([
            'organization_id' => $this->organization->id,
            'name' => 'Marketing',
            'type' => 'department'
        ]);

        // Create users with their employee records
        $this->hrUser = $this->createEmployeeWithUser(
            'HR Manager',
            'hr@test.com',
            ['hr', 'manager'],
            'HR Manager',
            true
        );

        $this->manager = $this->createEmployeeWithUser(
            'Engineering Manager',
            'manager@test.com',
            ['manager'],
            'Engineering Manager'
        );

        $this->employee = $this->createEmployeeWithUser(
            'John Doe',
            'john.doe@test.com',
            ['employee'],
            'Software Developer'
        );

        // Create additional employees without user accounts (HR records only)
        $this->additionalEmployees = Employee::factory()
            ->count(3)
            ->create([
                'organization_id' => $this->organization->id,
                'organization_unit_id' => $this->engineeringUnit->id,
                'is_active' => true,
            ]);

        // Create marketing employee
        $this->marketingEmployee = $this->createEmployeeWithUser(
            'Marketing Person',
            'marketing@test.com',
            ['employee'],
            'Marketing Specialist',
            false,
            $this->marketingUnit->id
        );
    }

    /**
     * Helper method to create employee with user account and organization membership
     */
    protected function createEmployeeWithUser($name, $email, $roles, $position, $isAdmin = false, $unitId = null)
    {
        $unitId = $unitId ?? $this->engineeringUnit->id;

        // Create user account
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'current_organization_id' => $this->organization->id,
        ]);

        // Create employee record
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'organization_unit_id' => $unitId,
            'first_name' => explode(' ', $name)[0],
            'last_name' => explode(' ', $name)[1] ?? 'User',
            'email' => $email,
            'is_admin' => $isAdmin,
            'is_active' => true,
        ]);

        // Create organization user membership
        OrganizationUser::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'organization_unit_id' => $unitId,
            'roles' => $roles,
            'position' => $position
        ]);

        return $employee;
    }

    /**
     * Create employee without user account (HR record only)
     */
    protected function createEmployeeWithoutUser(array $attributes = []): Employee
    {
        return Employee::factory()->create(array_merge([
            'user_id' => null,
            'organization_id' => $this->organization->id,
            'organization_unit_id' => $this->engineeringUnit->id,
            'is_active' => true,
        ], $attributes));
    }

    /**
     * Grant system access to existing employee
     */
    protected function grantSystemAccessToEmployee(Employee $employee, array $roles = ['employee'], string $position = 'Employee'): array
    {
        if ($employee->user_id) {
            throw new \Exception('Employee already has system access!');
        }

        // Create user account
        $user = User::factory()->create([
            'name' => $employee->first_name . ' ' . $employee->last_name,
            'email' => $employee->email,
            'current_organization_id' => $this->organization->id,
        ]);

        // Update employee with user_id
        $employee->update(['user_id' => $user->id]);

        // Create organization user relationship
        OrganizationUser::create([
            'user_id' => $user->id,
            'organization_id' => $this->organization->id,
            'organization_unit_id' => $employee->organization_unit_id,
            'roles' => $roles,
            'position' => $position
        ]);

        return [
            'user' => $user,
            'employee' => $employee->fresh(),
            'org_user' => OrganizationUser::where('user_id', $user->id)->first()
        ];
    }

    /**
     * Get HR user's associated User account for authentication
     */
    protected function getHrUserAccount(): User
    {
        return User::where('email', 'hr@test.com')->first();
    }

    /**
     * Get employee's associated User account for authentication
     */
    protected function getEmployeeUserAccount(): User
    {
        return User::where('email', 'john.doe@test.com')->first();
    }

    /**
     * Get manager's associated User account for authentication
     */
    protected function getManagerUserAccount(): User
    {
        return User::where('email', 'manager@test.com')->first();
    }

    /**
     * Create test data for employee details page
     */
    protected function createEmployeeDetailsTestData(Employee $employee): array
    {
        $attendanceRecords = \App\Models\AttendanceRecord::factory()
            ->count(5)
            ->create(['employee_id' => $employee->id, 'status' => 'present']);

        $leaveRequests = \App\Models\LeaveRequest::factory()
            ->create(['employee_id' => $employee->id, 'status' => 'approved']);

        $payrollEntries = \App\Models\PayrollEntry::factory()
            ->create(['employee_id' => $employee->id, 'status' => 'paid']);

        return [
            'attendance_records' => $attendanceRecords,
            'leave_requests' => $leaveRequests,
            'payroll_entries' => $payrollEntries,
        ];
    }

    /**
     * Quick setup for HR authentication
     */
    protected function actingAsHrUser(): void
    {
        $hrUserAccount = $this->getHrUserAccount();
        $this->actingAs($hrUserAccount);
    }

    /**
     * Quick setup for regular employee authentication
     */
    protected function actingAsRegularEmployee(): void
    {
        $employeeUserAccount = $this->getEmployeeUserAccount();
        $this->actingAs($employeeUserAccount);
    }

    /**
     * Quick setup for manager authentication
     */
    protected function actingAsManager(): void
    {
        $managerUserAccount = $this->getManagerUserAccount();
        $this->actingAs($managerUserAccount);
    }
}
