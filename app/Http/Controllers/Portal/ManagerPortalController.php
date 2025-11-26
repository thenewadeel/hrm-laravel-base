<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\OrganizationUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerPortalController extends Controller
{
    private function checkManagerRole()
    {
        $user = Auth::user();
        $orgUser = OrganizationUser::where('user_id', $user->id)->first();

        if (! $orgUser || ! $orgUser->hasRole('manager')) {
            abort(403, 'Access denied. Manager role required.');
        }
    }

    public function dashboard()
    {
        $this->checkManagerRole();
        $teamMembers = $this->getTeamMembers();
        $pendingApprovals = $this->getPendingApprovals();
        $teamMetrics = $this->getTeamMetrics();
        $teamAttendance = $this->getTeamMembers();

        return view('portal.manager.dashboard', compact(
            'teamMembers',
            'pendingApprovals',
            'teamMetrics',
            'teamAttendance'
        ));
    }

    public function teamAttendance(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));

        $teamMembers = $this->getTeamMembers();

        // Load attendance for each team member for the selected date
        $teamMembers->each(function ($member) use ($date) {
            $member->attendance = AttendanceRecord::where('user_id', $member->id)
                ->whereDate('record_date', $date)
                ->first();
        });

        $presentCount = $teamMembers->filter(function ($member) {
            return $member->attendance && in_array($member->attendance->status, ['present', 'late']);
        })->count();

        $leaveCount = $teamMembers->filter(function ($member) use ($date) {
            return LeaveRequest::where('user_id', $member->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->exists();
        })->count();

        $absentCount = $teamMembers->count() - $presentCount - $leaveCount;

        return view('portal.manager.team-attendance', compact(
            'teamMembers',
            'date',
            'presentCount',
            'leaveCount',
            'absentCount'
        ));
    }

    public function reports()
    {
        $teamMembers = $this->getTeamMembers();
        $metrics = $this->getTeamReportsMetrics();

        return view('portal.manager.reports', compact('teamMembers', 'metrics'));
    }

    public function approveLeave(LeaveRequest $leaveRequest)
    {
        // Verify the leave request belongs to manager's team
        if (! $this->isInTeam($leaveRequest->user_id)) {
            abort(403, 'Not authorized to approve this leave request');
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Leave request approved successfully');
    }

    public function rejectLeave(Request $request, LeaveRequest $leaveRequest)
    {
        if (! $this->isInTeam($leaveRequest->user_id)) {
            abort(403, 'Not authorized to reject this leave request');
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $request->input('reason'),
        ]);

        return back()->with('success', 'Leave request rejected');
    }

    private function getTeamMembers()
    {
        // This should be based on your organization structure
        // For now, return all users except the current manager
        return User::where('id', '!=', auth()->id())
            ->where('current_organization_id', auth()->user()->current_organization_id)
            ->get();
    }

    private function getPendingApprovals()
    {
        $teamUserIds = $this->getTeamMembers()->pluck('id');

        return [
            'leave_requests' => LeaveRequest::whereIn('user_id', $teamUserIds)
                ->where('status', 'pending')
                ->with('user')
                ->get(),
            'attendance_regularizations' => AttendanceRecord::whereIn('user_id', $teamUserIds)
                ->where('status', 'pending_regularization')
                ->with('user')
                ->get(),
        ];
    }

    private function getTeamMetrics()
    {
        $teamUserIds = $this->getTeamMembers()->pluck('id');
        $currentMonth = now()->format('Y-m');

        return [
            'team_size' => $teamUserIds->count(),
            'attendance_rate' => $this->calculateTeamAttendanceRate($teamUserIds, $currentMonth),
            'on_leave_today' => $this->getOnLeaveTodayCount($teamUserIds),
            'pending_approvals' => LeaveRequest::whereIn('user_id', $teamUserIds)
                ->where('status', 'pending')
                ->count(),
        ];
    }

    private function getTeamReportsMetrics()
    {
        $teamUserIds = $this->getTeamMembers()->pluck('id');
        $currentMonth = now()->format('Y-m');

        return [
            'team_attendance_rate' => $this->calculateTeamAttendanceRate($teamUserIds, $currentMonth),
            'average_daily_hours' => $this->calculateAverageDailyHours($teamUserIds, $currentMonth),
            'overtime_hours' => $this->calculateOvertimeHours($teamUserIds, $currentMonth),
            'leave_days_taken' => $this->calculateLeaveDaysTaken($teamUserIds, $currentMonth),
        ];
    }

    private function calculateTeamAttendanceRate($teamUserIds, $month)
    {
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $presentDays = AttendanceRecord::whereIn('user_id', $teamUserIds)
            ->whereIn('status', ['present', 'late'])
            ->whereBetween('record_date', [$startDate, $endDate])
            ->count();

        $workingDays = $this->getWorkingDaysCount($startDate, $endDate) * $teamUserIds->count();

        return $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 1) : 0;
    }

    private function calculateAverageDailyHours($teamUserIds, $month)
    {
        $totalHours = AttendanceRecord::whereIn('user_id', $teamUserIds)
            ->whereYear('record_date', Carbon::parse($month)->year)
            ->whereMonth('record_date', Carbon::parse($month)->month)
            ->sum('total_hours');

        $workingDays = $this->getWorkingDaysCount(
            Carbon::parse($month)->startOfMonth(),
            Carbon::parse($month)->endOfMonth()
        );

        return $workingDays > 0 ? round($totalHours / ($workingDays * $teamUserIds->count()), 1) : 0;
    }

    private function calculateOvertimeHours($teamUserIds, $month)
    {
        $totalHours = AttendanceRecord::whereIn('user_id', $teamUserIds)
            ->whereYear('record_date', Carbon::parse($month)->year)
            ->whereMonth('record_date', Carbon::parse($month)->month)
            ->sum('total_hours');

        $expectedHours = $this->getWorkingDaysCount(
            Carbon::parse($month)->startOfMonth(),
            Carbon::parse($month)->endOfMonth()
        ) * $teamUserIds->count() * 8; // 8 hours per day

        return max(0, $totalHours - $expectedHours);
    }

    private function calculateLeaveDaysTaken($teamUserIds, $month)
    {
        return LeaveRequest::whereIn('user_id', $teamUserIds)
            ->where('status', 'approved')
            ->whereYear('start_date', Carbon::parse($month)->year)
            ->whereMonth('start_date', Carbon::parse($month)->month)
            ->sum('total_days');
    }

    private function getOnLeaveTodayCount($teamUserIds)
    {
        return LeaveRequest::whereIn('user_id', $teamUserIds)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->count();
    }

    private function getWorkingDaysCount($startDate, $endDate)
    {
        return $startDate->diffInDaysFiltered(function ($date) {
            return ! $date->isWeekend();
        }, $endDate);
    }

    private function isInTeam($userId)
    {
        $teamUserIds = $this->getTeamMembers()->pluck('id');

        return $teamUserIds->contains($userId);
    }
}
