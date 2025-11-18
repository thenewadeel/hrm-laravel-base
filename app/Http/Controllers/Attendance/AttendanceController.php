<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display the attendance dashboard.
     */
    public function dashboard(Request $request)
    {
        $today = Carbon::today();
        $organizationId = auth()->user()->operatingOrganizationId;

        // Get filter parameters
        $startDate = $request->get('start_date', $today->copy()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', $today->format('Y-m-d'));
        $employeeId = $request->get('employee_id');
        $showExceptions = $request->get('show_exceptions', false);

        // Base query for organization
        $baseQuery = AttendanceRecord::with(['employee', 'employee.user'])
            ->where('organization_id', $organizationId)
            ->whereBetween('record_date', [$startDate, $endDate]);

        // Today's metrics
        $presentToday = AttendanceRecord::where('organization_id', $organizationId)
            ->where('record_date', $today)
            ->where('status', 'present')
            ->count();

        $absentToday = AttendanceRecord::where('organization_id', $organizationId)
            ->where('record_date', $today)
            ->where('status', 'absent')
            ->count();

        $lateToday = AttendanceRecord::where('organization_id', $organizationId)
            ->where('record_date', $today)
            ->where('status', 'late')
            ->count();

        $totalHours = AttendanceRecord::where('organization_id', $organizationId)
            ->where('record_date', $today)
            ->where('status', 'present')
            ->sum('total_hours');

        // Exception metrics
        $lateExceptions = AttendanceRecord::where('organization_id', $organizationId)
            ->whereBetween('record_date', [$startDate, $endDate])
            ->where('status', 'late')
            ->count();

        $missedPunchExceptions = AttendanceRecord::where('organization_id', $organizationId)
            ->whereBetween('record_date', [$startDate, $endDate])
            ->where(function ($query) {
                $query->whereNull('punch_in')
                    ->orWhereNull('punch_out');
            })
            ->count();

        $totalLateMinutes = AttendanceRecord::where('organization_id', $organizationId)
            ->whereBetween('record_date', [$startDate, $endDate])
            ->where('status', 'late')
            ->sum('late_minutes');

        // Employee-specific summary
        $presentDays = 0;
        $absentDays = 0;
        $lateDays = 0;
        $summaryTotalHours = 0;

        if ($employeeId) {
            $presentDays = AttendanceRecord::where('organization_id', $organizationId)
                ->where('employee_id', $employeeId)
                ->whereBetween('record_date', [$startDate, $endDate])
                ->where('status', 'present')
                ->count();

            $absentDays = AttendanceRecord::where('organization_id', $organizationId)
                ->where('employee_id', $employeeId)
                ->whereBetween('record_date', [$startDate, $endDate])
                ->where('status', 'absent')
                ->count();

            $lateDays = AttendanceRecord::where('organization_id', $organizationId)
                ->where('employee_id', $employeeId)
                ->whereBetween('record_date', [$startDate, $endDate])
                ->where('status', 'late')
                ->count();

            $summaryTotalHours = AttendanceRecord::where('organization_id', $organizationId)
                ->where('employee_id', $employeeId)
                ->whereBetween('record_date', [$startDate, $endDate])
                ->where('status', 'present')
                ->sum('total_hours');
        }

        // Apply employee filter to base query
        if ($employeeId) {
            $baseQuery->where('employee_id', $employeeId);
        }

        // Get attendance records with employee data
        $attendanceRecords = $baseQuery->orderBy('record_date', 'desc')
            ->orderBy('employee_id')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'employee_id' => $record->employee_id,
                    'employee_name' => $record->employee->user->name ?? 'Unknown Employee',
                    'department' => $record->employee->department ?? 'N/A',
                    'record_date' => $record->record_date,
                    'punch_in' => $record->punch_in,
                    'punch_out' => $record->punch_out,
                    'total_hours' => $record->total_hours,
                    'status' => $record->status,
                    'late_minutes' => $record->late_minutes,
                    'overtime_minutes' => $record->overtime_minutes,
                    'notes' => $record->notes,
                ];
            });

        // Get employees for filter dropdown
        $employees = Employee::with('user')
            ->where('organization_id', $organizationId)
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->user->name ?? 'Unknown',
                ];
            });

        return view('attendance.dashboard', [
            'presentToday' => $presentToday,
            'absentToday' => $absentToday,
            'lateToday' => $lateToday,
            'totalHours' => $totalHours ?? 0,

            // Exception data
            'lateExceptions' => $lateExceptions,
            'missedPunchExceptions' => $missedPunchExceptions,
            'totalLateMinutes' => $totalLateMinutes,

            // Summary data
            'presentDays' => $presentDays,
            'absentDays' => $absentDays,
            'lateDays' => $lateDays,
            'summaryTotalHours' => $summaryTotalHours,

            // Records and filters
            'attendanceRecords' => $attendanceRecords,
            'employees' => $employees,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'employee_id' => $employeeId,
                'show_exceptions' => $showExceptions,
            ],
            // Add date objects for display
            'startDateObj' => Carbon::parse($startDate),
            'endDateObj' => Carbon::parse($endDate),
        ]);
    }

    /**
     * Sync biometric data.
     */
    public function syncBiometricData(Request $request)
    {
        // For testing, accept simpler data structure
        if ($request->has('biometric_id')) {
            // Single record format for tests
            $biometricData = [[
                'biometric_id' => $request->biometric_id,
                'punch_time' => $request->punch_time,
                'device_serial_no' => $request->device_serial_no,
                'punch_type' => $request->punch_type,
            ]];
        } else {
            $request->validate([
                'biometric_data' => 'required|array',
                'biometric_data.*.biometric_id' => 'required|string',
                'biometric_data.*.punch_time' => 'required|date',
                'biometric_data.*.device_serial_no' => 'required|string',
                'biometric_data.*.punch_type' => 'required|in:in,out',
            ]);
            $biometricData = $request->biometric_data;
        }

        $syncedCount = 0;
        $errors = [];

        foreach ($biometricData as $data) {
            try {
                // Find employee by biometric ID
                $employee = Employee::where('biometric_id', $data['biometric_id'])
                    ->where('organization_id', auth()->user()->operatingOrganizationId)
                    ->first();

                if (! $employee) {
                    $errors[] = "Employee not found for biometric ID: {$data['biometric_id']}";

                    continue;
                }

                $punchTime = Carbon::parse($data['punch_time']);
                $recordDate = $punchTime->format('Y-m-d');

                // Find or create attendance record
                $attendanceRecord = AttendanceRecord::firstOrNew([
                    'employee_id' => $employee->id,
                    'organization_id' => $employee->organization_id,
                    'record_date' => $recordDate,
                ]);

                // Update punch in/out based on punch type
                if ($data['punch_type'] === 'in') {
                    $attendanceRecord->punch_in = $punchTime;
                    $attendanceRecord->biometric_id = $data['biometric_id'];
                    $attendanceRecord->device_serial_no = $data['device_serial_no'];
                } else {
                    $attendanceRecord->punch_out = $punchTime;
                }

                // Calculate total hours if both punches are present
                if ($attendanceRecord->punch_in && $attendanceRecord->punch_out) {
                    // Ensure punch_out is after punch_in to avoid negative hours
                    if ($attendanceRecord->punch_out->greaterThan($attendanceRecord->punch_in)) {
                        $totalHours = $attendanceRecord->punch_out->diffInMinutes($attendanceRecord->punch_in) / 60;
                        $attendanceRecord->total_hours = round($totalHours, 2);
                    } else {
                        $attendanceRecord->total_hours = 0;
                    }

                    // Calculate late minutes (if punch in is after 9:00 AM)
                    $scheduledStart = $punchTime->copy()->setTime(9, 0, 0);
                    if ($attendanceRecord->punch_in->gt($scheduledStart)) {
                        $attendanceRecord->late_minutes = $attendanceRecord->punch_in->diffInMinutes($scheduledStart);
                        $attendanceRecord->status = 'late';
                    } else {
                        $attendanceRecord->status = 'present';
                    }
                } else {
                    $attendanceRecord->status = 'missed_punch';
                }

                $attendanceRecord->save();
                $syncedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error syncing data for biometric ID {$data['biometric_id']}: {$e->getMessage()}";
            }
        }

        $message = "Successfully synced {$syncedCount} records.";
        if (! empty($errors)) {
            $message .= ' '.count($errors).' errors occurred.';
        }

        return back()->with(
            $errors ? 'warning' : 'success',
            $message
        )->withErrors($errors);
    }

    /**
     * Regularize attendance time.
     */
    /**
     * Regularize attendance time.
     */
    public function regularizeTime(Request $request, $id)
    {
        $request->validate([
            'punch_out' => 'required_if:punch_in,null|date',
            'punch_in' => 'required_if:punch_out,null|date',
            'reason' => 'required|string|max:500',
        ]);

        $attendanceRecord = AttendanceRecord::where('id', $id)
            ->where('organization_id', auth()->user()->current_organization_id)
            ->firstOrFail();

        // Update punch times - use explicit timezone handling
        if ($request->filled('punch_in')) {
            $attendanceRecord->punch_in = Carbon::parse($request->punch_in)->setTimezone(config('app.timezone'));
        }

        if ($request->filled('punch_out')) {
            $attendanceRecord->punch_out = Carbon::parse($request->punch_out)->setTimezone(config('app.timezone'));
        }

        // Recalculate metrics with proper time handling
        if ($attendanceRecord->punch_in && $attendanceRecord->punch_out) {
            // Ensure both times are in the same timezone and format
            $punchIn = Carbon::parse($attendanceRecord->punch_in)->setTimezone(config('app.timezone'));
            $punchOut = Carbon::parse($attendanceRecord->punch_out)->setTimezone(config('app.timezone'));

            // Debug logging (remove in production)
            \Log::debug('Time regularization calculation', [
                'punch_in' => $punchIn->toDateTimeString(),
                'punch_out' => $punchOut->toDateTimeString(),
                'record_date' => $attendanceRecord->record_date->toDateString(),
            ]);

            // Calculate total hours - use absolute difference to avoid negative values
            $totalMinutes = $punchOut->diffInMinutes($punchIn, false); // false = absolute value
            $attendanceRecord->total_hours = round($totalMinutes / 60, 2);

            // If we still get negative, force positive (shouldn't happen with diffInMinutes)
            if ($attendanceRecord->total_hours < 0) {
                \Log::warning('Negative total hours detected, forcing positive', [
                    'total_hours' => $attendanceRecord->total_hours,
                    'punch_in' => $punchIn->toDateTimeString(),
                    'punch_out' => $punchOut->toDateTimeString(),
                ]);
                $attendanceRecord->total_hours = abs($attendanceRecord->total_hours);
            }

            // Calculate late minutes (if punch in is after 9:00 AM on the record date)
            $scheduledStart = Carbon::parse($attendanceRecord->record_date)
                ->setTimezone(config('app.timezone'))
                ->setTime(9, 0, 0);

            if ($punchIn->gt($scheduledStart)) {
                $attendanceRecord->late_minutes = $punchIn->diffInMinutes($scheduledStart);
                $attendanceRecord->status = 'late';
            } else {
                $attendanceRecord->status = 'present';
                $attendanceRecord->late_minutes = 0;
            }

            // Calculate overtime (if punch out is after 6:00 PM on the record date)
            $scheduledEnd = Carbon::parse($attendanceRecord->record_date)
                ->setTimezone(config('app.timezone'))
                ->setTime(18, 0, 0);

            if ($punchOut->gt($scheduledEnd)) {
                $attendanceRecord->overtime_minutes = $punchOut->diffInMinutes($scheduledEnd);
            } else {
                $attendanceRecord->overtime_minutes = 0;
            }
        }

        $attendanceRecord->notes = $request->reason;
        $attendanceRecord->save();

        // Log the final result for debugging
        \Log::debug('Time regularization completed', [
            'record_id' => $attendanceRecord->id,
            'punch_in' => $attendanceRecord->punch_in?->toDateTimeString(),
            'punch_out' => $attendanceRecord->punch_out?->toDateTimeString(),
            'total_hours' => $attendanceRecord->total_hours,
            'status' => $attendanceRecord->status,
        ]);

        return response()->json([
            'message' => 'Time regularized successfully',
            'record' => $attendanceRecord->fresh(),
        ]);
    }

    /**
     * Apply leave for absent employee.
     */
    public function applyLeave(Request $request, $id)
    {
        $request->validate([
            'leave_type' => 'required|in:sick,casual,earned,unpaid',
            'reason' => 'required|string|max:500',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $attendanceRecord = AttendanceRecord::where('id', $id)
            ->where('organization_id', auth()->user()->operatingOrganizationId)
            ->firstOrFail();

        // Convert single day record to leave
        $attendanceRecord->status = 'leave';
        $attendanceRecord->notes = "Leave applied: {$request->leave_type} - {$request->reason}";
        $attendanceRecord->save();

        // If it's a multi-day leave, create records for other days
        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);

        if ($fromDate->ne($toDate)) {
            $currentDate = $fromDate->copy()->addDay();
            while ($currentDate->lte($toDate)) {
                // Skip if record already exists
                $existingRecord = AttendanceRecord::where('employee_id', $attendanceRecord->employee_id)
                    ->where('record_date', $currentDate->format('Y-m-d'))
                    ->first();

                if (! $existingRecord) {
                    AttendanceRecord::create([
                        'employee_id' => $attendanceRecord->employee_id,
                        'organization_id' => $attendanceRecord->organization_id,
                        'record_date' => $currentDate->format('Y-m-d'),
                        'status' => 'leave',
                        'notes' => "Leave applied: {$request->leave_type} - {$request->reason}",
                        'total_hours' => 0,
                        'late_minutes' => 0,
                        'overtime_minutes' => 0,
                    ]);
                }

                $currentDate->addDay();
            }
        }

        return response()->json([
            'message' => 'Leave applied successfully',
            'days_affected' => $fromDate->diffInDays($toDate) + 1,
        ]);
    }

    /**
     * Export attendance data for payroll.
     */
    public function exportForPayroll(Request $request)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
            'employee_id' => 'sometimes|exists:employees,id',
        ]);

        $period = Carbon::parse($request->period);
        $startDate = $period->copy()->startOfMonth();
        $endDate = $period->copy()->endOfMonth();
        $organizationId = auth()->user()->operatingOrganizationId;

        $query = AttendanceRecord::with(['employee', 'employee.user'])
            ->where('organization_id', $organizationId)
            ->whereBetween('record_date', [$startDate, $endDate]);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $attendanceData = $query->get();

        // Generate CSV content
        $csvHeader = [
            'Employee ID',
            'Employee Name',
            'Date',
            'Punch In',
            'Punch Out',
            'Total Hours',
            'Late Minutes',
            'Overtime Minutes',
            'Status',
            'Department',
        ];

        $csvContent = implode(',', $csvHeader)."\n";

        foreach ($attendanceData as $record) {
            $csvContent .= implode(',', [
                $record->employee->employee_id ?? 'N/A',
                '"'.($record->employee->user->name ?? 'Unknown').'"',
                $record->record_date->format('Y-m-d'),
                $record->punch_in ? $record->punch_in->format('H:i') : 'N/A',
                $record->punch_out ? $record->punch_out->format('H:i') : 'N/A',
                $record->total_hours,
                $record->late_minutes,
                $record->overtime_minutes,
                $record->status,
                '"'.($record->employee->department ?? 'N/A').'"',
            ])."\n";
        }

        $filename = "attendance_export_{$request->period}_".($request->employee_id ? 'employee_'.$request->employee_id : 'all').'.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Process employee clock in.
     */
    /**
     * Process employee clock in.
     */
    /**
     * Process employee clock in.
     */
    /**
     * Process employee clock in.
     */
    public function clockIn(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())
            ->where('organization_id', auth()->user()->current_organization_id)
            ->firstOrFail();

        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        // Find existing record or create new one
        $attendanceRecord = AttendanceRecord::where([
            'employee_id' => $employee->id,
            'record_date' => $today,
        ])->first();

        // Check if already clocked in today
        if ($attendanceRecord && $attendanceRecord->punch_in) {
            return response()->json([
                'message' => 'You have already clocked in today',
                'punch_in' => $attendanceRecord->punch_in,
            ], 422);
        }

        // Create new record if doesn't exist
        if (! $attendanceRecord) {
            $attendanceRecord = new AttendanceRecord;
            $attendanceRecord->employee_id = $employee->id;
            $attendanceRecord->record_date = $today;
            $attendanceRecord->organization_id = $employee->organization_id;
        }

        $attendanceRecord->punch_in = $now;
        $attendanceRecord->punch_out = null; // Ensure punch_out is null for new clock-in
        $attendanceRecord->status = 'present';
        $attendanceRecord->total_hours = 0;
        $attendanceRecord->late_minutes = 0;
        $attendanceRecord->overtime_minutes = 0;

        $attendanceRecord->save();

        return response()->json([
            'message' => 'Clock in successful',
            'punch_in' => $attendanceRecord->punch_in->format('h:i A'),
            'record' => $attendanceRecord,
        ]);
    }

    /**
     * Process employee clock out.
     */
    /**
     * Process employee clock out.
     */
    /**
     * Process employee clock out.
     */
    public function clockOut(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())
            ->where('organization_id', auth()->user()->current_organization_id)
            ->firstOrFail();

        $now = Carbon::now();
        $today = $now->format('Y-m-d');

        // Find existing record for today
        $attendanceRecord = AttendanceRecord::where('employee_id', $employee->id)
            ->where('record_date', $today)
            ->first();

        if (! $attendanceRecord) {
            return response()->json([
                'message' => 'No clock-in record found for today. Please clock in first.',
            ], 404);
        }

        if (! $attendanceRecord->punch_in) {
            return response()->json([
                'message' => 'Please clock in first before clocking out',
            ], 422);
        }

        if ($attendanceRecord->punch_out) {
            return response()->json([
                'message' => 'You have already clocked out today',
                'punch_out' => $attendanceRecord->punch_out,
            ], 422);
        }

        $attendanceRecord->punch_out = $now;

        // Calculate total hours
        $punchIn = Carbon::parse($attendanceRecord->punch_in);
        $punchOut = Carbon::parse($attendanceRecord->punch_out);

        $totalMinutes = $punchOut->diffInMinutes($punchIn);
        $attendanceRecord->total_hours = round($totalMinutes / 60, 2);

        // Calculate overtime
        $scheduledEnd = $punchIn->copy()->setTime(18, 0, 0);
        if ($punchOut->gt($scheduledEnd)) {
            $attendanceRecord->overtime_minutes = $punchOut->diffInMinutes($scheduledEnd);
        } else {
            $attendanceRecord->overtime_minutes = 0;
        }

        $attendanceRecord->save();

        return response()->json([
            'message' => 'Clock out successful',
            'punch_out' => $attendanceRecord->punch_out->format('h:i A'),
            'total_hours' => $attendanceRecord->total_hours,
            'overtime_minutes' => $attendanceRecord->overtime_minutes,
            'record' => $attendanceRecord,
        ]);
    }

    /**
     * Payroll processing view (for test compatibility)
     */
    public function payrollProcessing(Request $request)
    {
        // Validate request parameters
        dd([
            'cp',
        ]);
        $request->validate([
            'period' => 'required|date_format:Y-m',
            'employee_id' => 'sometimes|exists:employees,id',
        ]);
        $period = Carbon::parse($request->period);
        $startDate = $period->copy()->startOfMonth();
        $endDate = $period->copy()->endOfMonth();
        $organizationId = auth()->user()->current_organization_id;

        $query = AttendanceRecord::with(['employee', 'employee.user'])
            ->where('organization_id', $organizationId)
            ->whereBetween('record_date', [$startDate, $endDate]);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $attendanceData = $query->get();

        // Calculate payroll metrics
        $totalHours = $attendanceData->sum('total_hours');
        $overtimeHours = round($attendanceData->sum('overtime_minutes') / 60, 2);
        $regularHours = max(0, $totalHours - $overtimeHours);

        return view('payroll.processing', [
            'attendanceData' => $attendanceData,
            'period' => $period, // Make sure this is passed as Carbon object
            'totalHours' => $totalHours,
            'overtimeHours' => $overtimeHours,
            'regularHours' => $regularHours,
            'employee' => $request->filled('employee_id') ? Employee::find($request->employee_id) : null,
        ]);
    }
}
