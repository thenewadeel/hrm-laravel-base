<?php

namespace Tests\Feature\Portal;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\SetupEmployee;

class AttendanceSyncTest extends TestCase
{
    use RefreshDatabase, SetupEmployee;

    protected $today;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupEmployeeManagement();
        $this->today = Carbon::today();
    }

    #[Test]
    public function test_can_view_attendance_dashboard()
    {
        $today = $this->today;

        // Create a mix of attendance records for the current week with proper dates
        $dates = [
            $today->copy(),
            $today->copy()->addDay(),
            $today->copy()->addDays(2),
            $today->copy()->subDay(),
            $today->copy()->subDays(2),
        ];

        // Create present records
        AttendanceRecord::factory()->present()->create([
            'employee_id' => $this->employee->id,
            'organization_id' => $this->organization->id,
            'record_date' => $dates[0],
            'late_minutes' => 0,
            'overtime_minutes' => 0,
        ]);

        AttendanceRecord::factory()->present()->create([
            'employee_id' => $this->employee->id,
            'organization_id' => $this->organization->id,
            'record_date' => $dates[1],
            'late_minutes' => 0,
            'overtime_minutes' => 0,
        ]);

        // Create late record
        AttendanceRecord::factory()->late()->create([
            'employee_id' => $this->employee->id,
            'organization_id' => $this->organization->id,
            'record_date' => $dates[2],
            'late_minutes' => 45,
            'overtime_minutes' => 0,
        ]);

        // Create absent record
        AttendanceRecord::factory()->absent()->create([
            'employee_id' => $this->employee->id,
            'organization_id' => $this->organization->id,
            'record_date' => $dates[3],
            'late_minutes' => 0,
            'overtime_minutes' => 0,
        ]);

        $response = $this
            ->get(route('attendance.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Attendance Dashboard');
        $response->assertSee('Present Today');
        $response->assertSee('Absent Today');
        $response->assertSee('Late Arrivals');
    }

    #[Test]
    public function test_can_filter_attendance_by_date_range()
    {
        // Create records for different dates
        $startDate = $this->today->copy()->subDays(7);
        $endDate = $this->today->copy()->subDays(1);

        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'organization_id' => $this->organization->id,
            'record_date' => $startDate,
            'punch_in' => $startDate->copy()->setTime(9, 0),
            'punch_out' => $startDate->copy()->setTime(17, 0),
            'late_minutes' => 0,
            'overtime_minutes' => 0,
            'total_hours' => 8.0,
        ]);

        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'organization_id' => $this->organization->id,
            'record_date' => $endDate,
            'punch_in' => $endDate->copy()->setTime(8, 30),
            'punch_out' => $endDate->copy()->setTime(16, 30),
            'late_minutes' => 0,
            'overtime_minutes' => 0,
            'total_hours' => 8.0,
        ]);

        $response = $this
            ->get(route('attendance.dashboard', [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertSee($startDate->format('M j, Y'));
        $response->assertSee($endDate->format('M j, Y'));
    }

    #[Test]
    public function test_can_identify_attendance_exceptions()
    {
        // Create records with exceptions
        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'organization_id' => $this->organization->id,
            'record_date' => $this->today,
            'punch_in' => $this->today->copy()->setTime(10, 30),
            'punch_out' => null,
            'late_minutes' => 90,
            'overtime_minutes' => 0,
            'total_hours' => 0,
            'status' => 'late',
        ]);

        $response = $this
            ->get(route('attendance.dashboard', ['show_exceptions' => true]));

        $response->assertStatus(200);
        $response->assertSee('Attendance Exceptions');
        $response->assertSee('Late Arrivals');
        $response->assertSee('Missed Punches');
        $response->assertSee('90'); // Late minutes
    }

    #[Test]
    public function test_can_regularize_missed_punch()
    {
        $attendanceRecord = AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'organization_id' => $this->organization->id,
            'record_date' => $this->today,
            'punch_in' => $this->today->copy()->setTime(9, 0),
            'punch_out' => null, // Missed punch out
            'total_hours' => 0,
            'late_minutes' => 0,
            'overtime_minutes' => 0,
        ]);

        $regularizedTime = $this->today->copy()->setTime(17, 30);

        // Debug: Check what we're sending
        \Log::debug('Test regularization data', [
            'punch_out' => $regularizedTime->format('Y-m-d H:i:s'),
            'record_date' => $this->today->format('Y-m-d'),
            'punch_in' => $attendanceRecord->punch_in?->format('Y-m-d H:i:s'),
        ]);

        $response = $this
            ->post(route('attendance.regularize', $attendanceRecord->id), [
                'punch_out' => $regularizedTime->format('Y-m-d H:i:s'),
                'reason' => 'Forgot to punch out',
            ]);

        $response->assertStatus(200);

        // Debug: Check what was saved
        $updatedRecord = AttendanceRecord::find($attendanceRecord->id);
        \Log::debug('After regularization', [
            'saved_punch_out' => $updatedRecord->punch_out?->toDateTimeString(),
            'saved_total_hours' => $updatedRecord->total_hours,
            'expected_total_hours' => 8.5,
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendanceRecord->id,
            'punch_out' => $regularizedTime,
            'total_hours' => 8.5, // 9:00 to 17:30 = 8.5 hours
        ]);
    }

    #[Test]
    public function test_attendance_data_integrates_with_payroll()
    {
        $startDate = $this->today->copy()->startOfMonth();
        $endDate = $this->today->copy()->endOfMonth();

        $workDays = $startDate->copy();
        while ($workDays <= $endDate) {
            if ($workDays->isWeekday()) {
                AttendanceRecord::factory()->create([
                    'employee_id' => $this->employee->id,
                    'organization_id' => $this->organization->id,
                    'record_date' => $workDays,
                    'punch_in' => $workDays->copy()->setTime(9, 0),
                    'punch_out' => $workDays->copy()->setTime(17, 0),
                    'total_hours' => 8.0,
                    'late_minutes' => 0,
                    'overtime_minutes' => 0,
                    'status' => 'present',
                ]);
            }
            $workDays->addDay();
        }

        // Update the specific day with overtime
        AttendanceRecord::updateOrCreate(
            [
                'employee_id' => $this->employee->id,
                'record_date' => $this->today,
            ],
            [
                'organization_id' => $this->organization->id,
                'punch_in' => $this->today->copy()->setTime(9, 0),
                'punch_out' => $this->today->copy()->setTime(19, 0),
                'total_hours' => 10.0,
                'overtime_minutes' => 120,
                'late_minutes' => 0,
                'status' => 'present',
            ]
        );

        $response = $this
            ->get(route('payroll.processing', [
                'period' => $this->today->format('Y-m'),
                'employee_id' => $this->employee->id,
            ]));

        $response->assertStatus(200);
        $response->assertSee('Total Hours');
        $response->assertSee('Overtime Hours');
        $response->assertSee('Regular Hours');
    }

    #[Test]
    public function test_employee_can_clock_in_and_out()
    {
        $this->actingAs($this->employee->user);
        // Clock in
        $clockInResponse = $this->post(route('portal.employee.clock-in'));
        $clockInResponse->assertStatus(200);

        // Check that a record was created with the right date
        // Use whereDate to compare only the date part, ignoring time
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'status' => 'present',
        ]);

        // Additional check to verify the date part matches
        $record = AttendanceRecord::where('employee_id', $this->employee->id)
            ->whereDate('record_date', $this->today)
            ->first();

        $this->assertNotNull($record, 'No attendance record found for today');
        $this->assertNotNull($record->punch_in, 'Punch-in time was not set');

        // dd([
        //     'cp'
        // ]);
        // Try to clock in again - should fail
        $secondClockInResponse = $this->post(route('portal.employee.clock-in'));
        $secondClockInResponse->assertStatus(422);

        // Clock out
        $clockOutResponse = $this->post(route('portal.employee.clock-out'));
        $clockOutResponse->assertStatus(200);

        // Verify clock out was recorded
        $recordAfterClockOut = AttendanceRecord::where('employee_id', $this->employee->id)
            ->whereDate('record_date', $this->today)
            ->first();

        $this->assertNotNull($recordAfterClockOut->punch_out, 'Punch-out time was not set');

        // Try to clock out again - should fail
        $secondClockOutResponse = $this->post(route('portal.employee.clock-out'));
        $secondClockOutResponse->assertStatus(422);
    }

    #[Test]
    public function test_can_calculate_attendance_summary()
    {
        // Create various attendance records
        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'organization_id' => $this->organization->id,
            'record_date' => $this->today->copy()->subDays(1),
            'status' => 'present',
            'total_hours' => 8.0,
            'late_minutes' => 0,
            'overtime_minutes' => 0,
        ]);

        AttendanceRecord::factory()->create([
            'employee_id' => $this->employee->id,
            'organization_id' => $this->organization->id,
            'record_date' => $this->today->copy()->subDays(2),
            'status' => 'absent',
            'late_minutes' => 0,
            'overtime_minutes' => 0,
        ]);

        $response = $this
            ->get(route('attendance.dashboard', [
                'employee_id' => $this->employee->id,
                'start_date' => $this->today->copy()->subDays(7)->format('Y-m-d'),
                'end_date' => $this->today->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertSee('Attendance Summary');
        $response->assertSee('Present Days');
        $response->assertSee('Absent Days');
        $response->assertSee('Late Days');
        $response->assertSee('Total Hours');
    }

    #[Test]
    public function test_biometric_attendance_sync()
    {
        // Ensure employee has a biometric_id
        $this->employee->update(['biometric_id' => 'BIO123']);

        $biometricData = [
            'biometric_id' => 'BIO123',
            'punch_time' => now()->format('Y-m-d H:i:s'),
            'device_serial_no' => 'BIO001',
            'punch_type' => 'in',
        ];

        $response = $this
            ->post(route('attendance.biometric-sync'), $biometricData);

        $response->assertStatus(302); // Redirect back

        // Check that no errors were added to session
        $this->assertEmpty(session('errors'));

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
            'biometric_id' => 'BIO123',
            'device_serial_no' => 'BIO001',
        ]);
    }
}
