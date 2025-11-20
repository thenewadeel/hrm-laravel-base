<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmployeePortalController extends Controller
{
    public function dashboard()
    {
        $employee = $this->getCurrentEmployee();

        if (!$employee) {
            return redirect()->route('portal.employee.setup')->with('error', 'No employee profile found for your account.');
        }

        $todayAttendance = $this->getTodayAttendance($employee);
        $leaveBalance = $this->getLeaveBalance($employee);
        $recentPayslips = $this->getRecentPayslips($employee);

        return view('portal.employee.dashboard', compact(
            'employee',
            'todayAttendance',
            'leaveBalance',
            'recentPayslips'
        ));
    }

    public function attendance(Request $request)
    {
        $employee = $this->getCurrentEmployee();

        if (!$employee) {
            return redirect()->route('portal.employee.setup')->with('error', 'No employee profile found for your account.');
        }

        $month = $request->get('month', now()->format('Y-m'));

        $records = AttendanceRecord::where('employee_id', $employee->id)
            ->whereYear('record_date', Carbon::parse($month)->year)
            ->whereMonth('record_date', Carbon::parse($month)->month)
            ->orderBy('record_date', 'desc')
            ->get();

        $summary = $this->calculateAttendanceSummary($records, $month);

        return view('portal.employee.attendance', compact('employee', 'records', 'summary', 'month'));
    }

    public function leave()
    {
        $employee = $this->getCurrentEmployee();

        if (!$employee) {
            return redirect()->route('portal.employee.setup')->with('error', 'No employee profile found for your account.');
        }

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $leaveBalance = $this->getLeaveBalance($employee);

        return view('portal.employee.leave', compact('employee', 'leaveRequests', 'leaveBalance'));
    }

    public function createLeave()
    {
        $employee = $this->getCurrentEmployee();

        if (!$employee) {
            return redirect()->route('portal.employee.setup')->with('error', 'No employee profile found for your account.');
        }

        $leaveBalance = $this->getLeaveBalance($employee);
        return view('portal.employee.leave-create', compact('employee', 'leaveBalance'));
    }

    public function storeLeave(Request $request)
    {
        $employee = $this->getCurrentEmployee();

        if (!$employee) {
            return redirect()->route('portal.employee.setup')->with('error', 'No employee profile found for your account.');
        }

        $validated = $request->validate([
            'leave_type' => 'required|in:sick,vacation,personal,emergency,maternity,paternity',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500'
        ]);

        $totalDays = Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date'])) + 1;
        $availableBalance = $this->getLeaveBalance($employee);

        if ($totalDays > $availableBalance) {
            return back()->withErrors(['leave_days' => "Insufficient leave balance. Available: {$availableBalance} days, Requested: {$totalDays} days"]);
        }
        // dd([
        //     'employee' => $employee->toArray(),
        //     'user' => $employee->user->toArray(),
        //     $validated,
        //     'days' => $totalDays
        // ]);
        LeaveRequest::create([
            'employee_id' => $employee->id,
            'organization_id' => $employee->organization_id,
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
            'applied_at' => now()
        ]);

        return redirect()->route('portal.employee.leave')->with('success', 'Leave request submitted successfully!');
    }

    public function payslips()
    {
        $employee = $this->getCurrentEmployee();

        if (!$employee) {
            return redirect()->route('portal.employee.setup')->with('error', 'No employee profile found for your account.');
        }

        $payslips = PayrollEntry::where('employee_id', $employee->id)
            ->orderBy('period', 'desc')
            ->get();

        return view('portal.employee.payslips', compact('employee', 'payslips'));
    }

    public function showPayslip(PayrollEntry $payslip)
    {
        $employee = $this->getCurrentEmployee();

        if (!$employee || $payslip->employee_id !== $employee->id) {
            abort(403);
        }

        return view('portal.employee.payslip-show', compact('employee', 'payslip'));
    }

    public function downloadPayslip(PayrollEntry $payslip)
    {
        $employee = $this->getCurrentEmployee();

        if (!$employee || $payslip->employee_id !== $employee->id) {
            abort(403);
        }

        // Generate PDF
        $pdf = new \Dompdf\Dompdf();
        $pdf->setPaper('A4', 'portrait');
        
        // Load payslip user relationship if not loaded
        if (!$payslip->relationLoaded('user')) {
            $payslip->load('user');
        }
        
        $html = view('portal.employee.payslip-download', compact('employee', 'payslip'))->render();
        $pdf->loadHtml($html);
        $pdf->render();
        
        $filename = $payslip->payslip_filename;
        
        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=0, must-revalidate')
            ->header('Pragma', 'public');
    }

    public function clockIn()
    {
        $employee = $this->getCurrentEmployee();

        if (!$employee) {
            return redirect()->route('portal.employee.setup')->with('error', 'No employee profile found for your account.');
        }

        $existingRecord = AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('record_date', today())
            ->first();

        if ($existingRecord) {
            return back()->with('error', 'Already clocked in today');
        }

        $punchInTime = now();
        $scheduledTime = today()->setTime(9, 0);
        $status = $punchInTime->gt($scheduledTime->addMinutes(15)) ? 'late' : 'present';

        AttendanceRecord::create([
            'employee_id' => $employee->id,
            'organization_id' => $employee->organization_id,
            'record_date' => today(),
            'punch_in' => $punchInTime,
            'status' => $status
        ]);

        return back()->with('success', 'Successfully clocked in at ' . $punchInTime->format('h:i A'));
    }

    public function clockOut()
    {
        $employee = $this->getCurrentEmployee();

        if (!$employee) {
            return redirect()->route('portal.employee.setup')->with('error', 'No employee profile found for your account.');
        }

        $attendance = AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('record_date', today())
            ->first();

        if (!$attendance) {
            return back()->with('error', 'No clock-in record found for today');
        }

        if ($attendance->punch_out) {
            return back()->with('error', 'Already clocked out today');
        }

        $punchOutTime = now();
        $totalHours = $punchOutTime->diffInHours($attendance->punch_in);

        $attendance->update([
            'punch_out' => $punchOutTime,
            'total_hours' => $totalHours
        ]);

        return back()->with('success', 'Successfully clocked out at ' . $punchOutTime->format('h:i A'));
    }

    /**
     * Get the current authenticated user's employee record
     */
    private function getCurrentEmployee()
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        // Get employee record for current organization
        return Employee::where('user_id', $user->id)
            ->where('organization_id', $user->operatingOrganizationId)
            ->where('is_active', true)
            ->first();
    }

    private function getTodayAttendance(Employee $employee)
    {
        return AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('record_date', today())
            ->first();
    }

    private function getLeaveBalance(Employee $employee)
    {
        $currentYear = now()->year;
        $totalAllowed = 18; // Annual leave days

        $usedLeave = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        return max(0, $totalAllowed - $usedLeave);
    }

    private function getRecentPayslips(Employee $employee)
    {
        return PayrollEntry::where('employee_id', $employee->id)
            ->where('status', 'paid')
            ->orderBy('period', 'desc')
            ->take(3)
            ->get();
    }

    private function calculateAttendanceSummary($records, $month)
    {
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $workingDays = $this->getWorkingDaysCount($startDate, $endDate);
        $presentDays = $records->whereIn('status', ['present', 'late'])->count();
        $attendanceRate = $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 1) : 0;

        return [
            'attendance_rate' => $attendanceRate,
            'present' => $records->where('status', 'present')->count(),
            'late' => $records->where('status', 'late')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'leave' => $records->where('status', 'leave')->count(),
            'total_hours' => $records->sum('total_hours')
        ];
    }

    private function getWorkingDaysCount($startDate, $endDate)
    {
        return $startDate->diffInDaysFiltered(function ($date) {
            return !$date->isWeekend();
        }, $endDate);
    }

    /**
     * Setup page for users without employee profiles
     */
    public function setup()
    {
        $user = auth()->user();

        // Check if user already has an employee profile
        $employee = $this->getCurrentEmployee();
        if ($employee) {
            return redirect()->route('portal.employee.dashboard');
        }

        return view('portal.employee.setup', compact('user'));
    }

    /**
     * Handle employee profile setup
     */
    public function completeSetup(Request $request)
    {
        $user = auth()->user();

        // Check if user already has an employee profile
        $existingEmployee = $this->getCurrentEmployee();
        if ($existingEmployee) {
            return redirect()->route('portal.employee.dashboard');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        // Create employee profile
        $employee = Employee::create([
            'user_id' => $user->id,
            'organization_id' => $user->current_organization_id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $user->email,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('portal.employee.dashboard')
            ->with('success', 'Employee profile setup completed successfully!');
    }
}
