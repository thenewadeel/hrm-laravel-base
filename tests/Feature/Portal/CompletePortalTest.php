<?php

namespace Tests\Feature\Portal;

use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\LeaveRequest;
use App\Models\PayrollEntry;
use App\Models\Organization;
use App\Models\OrganizationUser;
use Tests\Traits\SetupEmployee;

class CompletePortalTest extends TestCase
{
    use RefreshDatabase, SetupEmployee;

    // protected $employee;
    // protected $manager;
    // protected $organization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEmployeeManagement();

        // $this->organization = Organization::factory()->create();

        // $this->employee = User::factory()->create([
        //     'current_organization_id' => $this->organization->id
        // ]);

        // $this->manager = User::factory()->create([
        //     'current_organization_id' => $this->organization->id
        // ]);

        // // Setup organization user relationships
        // OrganizationUser::create([
        //     'user_id' => $this->employee->id,
        //     'organization_id' => $this->organization->id,
        //     'roles' => ['employee'],
        //     'position' => 'Software Developer'
        // ]);

        // OrganizationUser::create([
        //     'user_id' => $this->manager->id,
        //     'organization_id' => $this->organization->id,
        //     'roles' => ['manager', 'employee'],
        //     'position' => 'Engineering Manager'
        // ]);
    }

    #[Test]
    public function employee_can_access_all_portal_features()
    {
        $this->actingAsRegularEmployee();

        // Dashboard
        $response = $this->get(route('portal.employee.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Employee Portal');

        // Attendance
        $response = $this->get(route('portal.employee.attendance'));
        $response->assertStatus(200);
        $response->assertSee('My Attendance');

        // Leave
        $response = $this->get(route('portal.employee.leave'));
        $response->assertStatus(200);
        $response->assertSee('Leave Management');

        // Payslips
        $response = $this->get(route('portal.employee.payslips'));
        $response->assertStatus(200);
        $response->assertSee('My Payslips');
    }

    #[Test]
    public function employee_can_clock_in_and_out()
    {
        $this->actingAsRegularEmployee();

        // Clock in
        $response = $this->post(route('portal.employee.clock-in'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'record_date' => today()
        ]);

        // Clock out
        $response = $this->post(route('portal.employee.clock-out'));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $attendance = AttendanceRecord::where('employee_id', $this->employee->id)
            ->whereDate('record_date', today())
            ->first();

        $this->assertNotNull($attendance->punch_out);
    }

    #[Test]
    public function employee_can_apply_for_leave()
    {
        $this->actingAsRegularEmployee();

        $response = $this->get(route('portal.employee.leave.create'));
        $response->assertStatus(200);
        $response->assertSee('Apply for Leave');

        $leaveData = [
            'leave_type' => 'vacation',
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(9)->format('Y-m-d'),
            'reason' => 'Family vacation'
        ];

        $response = $this->post(route('portal.employee.leave.store'), $leaveData);
        $response->assertRedirect(route('portal.employee.leave'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $this->employee->id,
            'leave_type' => 'vacation',
            'status' => 'pending'
        ]);
    }

    #[Test]
    public function manager_can_access_manager_portal()
    {
        $this->actingAsManager();

        $response = $this->get(route('portal.manager.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Manager Portal');

        $response = $this->get(route('portal.manager.team-attendance'));
        $response->assertStatus(200);
        $response->assertSee('Team Attendance');

        $response = $this->get(route('portal.manager.reports'));
        $response->assertStatus(200);
        $response->assertSee('Team Reports');
    }

    #[Test]
    public function employee_can_view_payslip_details()
    {
        $this->actingAsRegularEmployee();
        $payslip = PayrollEntry::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $response = $this->get(route('portal.employee.payslips.show', $payslip));
        $response->assertStatus(200);
        $response->assertSee('Payslip Details');
    }

    #[Test]
    public function employee_cannot_view_other_employee_payslips()
    {
        $otherEmployee = User::factory()->create();
        $payslip = PayrollEntry::factory()->create([
            'employee_id' => $otherEmployee->id
        ]);

        $this->actingAsRegularEmployee();

        $response = $this->get(route('portal.employee.payslips.show', $payslip));
        // dd(['cp' => $response]);
        $response->assertStatus(403);
    }

    #[Test]
    public function attendance_records_show_correct_summary()
    {
        $this->actingAsRegularEmployee();

        // Create some attendance records
        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'record_date' => now()->subDays(1),
            'status' => 'present',
            'total_hours' => 8
        ]);

        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'record_date' => now()->subDays(2),
            'status' => 'late',
            'total_hours' => 7.5
        ]);

        $response = $this->get(route('portal.employee.attendance'));
        $response->assertStatus(200);
        $response->assertSee('Present Days');
        $response->assertSee('Late Days');
    }
}
