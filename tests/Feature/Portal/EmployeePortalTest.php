<?php

namespace Tests\Feature\Portal;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\PayrollEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Traits\SetupEmployee;

class EmployeePortalTest extends TestCase
{
    use RefreshDatabase, SetupEmployee;

    // protected $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEmployeeManagement();

        // $this->employee = User::factory()->create();
        // $this->actingAs($this->employee);
        $this->actingAsRegularEmployee();
    }
    #[Test]    public function employee_can_access_their_portal_dashboard()
    {
        $response = $this //->actingAs($this->employee)
            ->get(route('portal.employee.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Employee Portal');
        $response->assertSee('Welcome back');
    }
    #[Test]
    public function employee_can_view_attendance_page()
    {
        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'record_date' => now(),
            'status' => 'present'
        ]);

        $response = $this->get(route('portal.employee.attendance'));

        $response->assertStatus(200);
        $response->assertSee('My Attendance');
        $response->assertSee('Attendance Rate');
    }

    #[Test]
    public function employee_can_filter_attendance_by_month()
    {
        $response = $this->get(route('portal.employee.attendance', ['month' => '2024-01']));

        $response->assertStatus(200);
        $response->assertSee('January 2024');
    }
    #[Test]    public function employee_can_apply_for_leave()
    {
        $response = $this //->actingAs($this->employee)
            ->get(route('portal.employee.leave.create'));

        $response->assertStatus(200);
        $response->assertSee('Apply for Leave');
    }


    #[Test]
    public function employee_can_view_leave_page()
    {
        LeaveRequest::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'pending'
        ]);

        $response = $this->get(route('portal.employee.leave'));

        $response->assertStatus(200);
        $response->assertSee('Leave Management');
        $response->assertSee('Available Balance');
    }

    #[Test]
    public function employee_can_view_payslips_page()
    {
        // dd([
        //     'employee_id' => $this->employee,
        // ]);
        PayrollEntry::factory()->create([
            'employee_id' => $this->employee->id,
            'status' => 'paid'
        ]);

        $response = $this->get(route('portal.employee.payslips'));

        $response->assertStatus(200);
        $response->assertSee('My Payslips');
        $response->assertSee('Avg. Monthly Salary');
    }

    #[Test]
    public function employee_can_view_leave_application_form()
    {
        $response = $this->get(route('portal.employee.leave.create'));

        $response->assertStatus(200);
        $response->assertSee('Apply for Leave');
        $response->assertSee('Leave Type');
    }
    #[Test]    public function employee_can_clock_in_and_out()
    {
        $response = $this //->actingAs($this->employee)
            ->post(route('portal.employee.clock-in'));

        $response->assertStatus(200);
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'type' => 'clock_in'
        ]);
    }
}
