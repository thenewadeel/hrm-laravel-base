<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\PayrollEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmployeePortalController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $todayAttendance = $this->getTodayAttendance();
        $leaveBalance = $this->getLeaveBalance();
        $recentPayslips = $this->getRecentPayslips();

        return view('portal.employee.dashboard', compact(
            'user',
            'todayAttendance',
            'leaveBalance',
            'recentPayslips'
        ));
    }

    public function attendance(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        $records = AttendanceRecord::where('user_id', auth()->id())
            ->whereYear('record_date', Carbon::parse($month)->year)
            ->whereMonth('record_date', Carbon::parse($month)->month)
            ->orderBy('record_date', 'desc')
            ->get();

        $summary = $this->calculateAttendanceSummary($records, $month);

        return view('portal.employee.attendance', compact('records', 'summary', 'month'));
    }

    public function leave()
    {
        $leaveRequests = LeaveRequest::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        $leaveBalance = $this->getLeaveBalance();

        return view('portal.employee.leave', compact('leaveRequests', 'leaveBalance'));
    }

    public function createLeave()
    {
        $leaveBalance = $this->getLeaveBalance();
        return view('portal.employee.leave-create', compact('leaveBalance'));
    }

    public function storeLeave(Request $request)
    {
        $validated = $request->validate([
            'leave_type' => 'required|in:sick,vacation,personal,emergency,maternity,paternity',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500'
        ]);

        $totalDays = Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date'])) + 1;
        $availableBalance = $this->getLeaveBalance();

        if ($totalDays > $availableBalance) {
            return back()->withErrors(['leave_days' => "Insufficient leave balance. Available: {$availableBalance} days, Requested: {$totalDays} days"]);
        }

        LeaveRequest::create([
            'user_id' => auth()->id(),
            'organization_id' => auth()->user()->current_organization_id,
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
        $payslips = PayrollEntry::where('user_id', auth()->id())
            ->orderBy('period', 'desc')
            ->get();

        return view('portal.employee.payslips', compact('payslips'));
    }

    public function showPayslip(PayrollEntry $payslip)
    {
        // dd(['cp' => auth()->id()]);
        if ($payslip->user_id !== auth()->id()) {
            abort(403);
        }

        return view('portal.employee.payslip-show', compact('payslip'));
    }

    public function downloadPayslip(PayrollEntry $payslip)
    {
        if ($payslip->user_id !== auth()->id()) {
            abort(403);
        }

        // For now, return view - you can implement PDF generation later
        return view('portal.employee.payslip-download', compact('payslip'));
    }

    public function clockIn()
    {
        $existingRecord = AttendanceRecord::where('user_id', auth()->id())
            ->whereDate('record_date', today())
            ->first();

        if ($existingRecord) {
            return back()->with('error', 'Already clocked in today');
        }

        $punchInTime = now();
        $scheduledTime = today()->setTime(9, 0);
        $status = $punchInTime->gt($scheduledTime->addMinutes(15)) ? 'late' : 'present';

        AttendanceRecord::create([
            'user_id' => auth()->id(),
            'organization_id' => auth()->user()->current_organization_id,
            'record_date' => today(),
            'punch_in' => $punchInTime,
            'status' => $status
        ]);

        return back()->with('success', 'Successfully clocked in at ' . $punchInTime->format('h:i A'));
    }

    public function clockOut()
    {
        $attendance = AttendanceRecord::where('user_id', auth()->id())
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

    private function getTodayAttendance()
    {
        return AttendanceRecord::where('user_id', auth()->id())
            ->whereDate('record_date', today())
            ->first();
    }

    private function getLeaveBalance()
    {
        $currentYear = now()->year;
        $totalAllowed = 18; // Annual leave days

        $usedLeave = LeaveRequest::where('user_id', auth()->id())
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        return max(0, $totalAllowed - $usedLeave);
    }

    private function getRecentPayslips()
    {
        return PayrollEntry::where('user_id', auth()->id())
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
}
