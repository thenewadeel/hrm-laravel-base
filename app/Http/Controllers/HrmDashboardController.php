<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class HrmDashboardController extends Controller
{
    public function index(): View
    {
        $organization = auth()->user()->organizations()->first();

        if (! $organization) {
            abort(403, 'No organization assigned');
        }

        return view('hrm.dashboard', [
            'organization' => $organization,
            'employeeSummary' => $this->getEmployeeSummary($organization),
            'attendanceOverview' => $this->getAttendanceOverview($organization),
            'leaveManagement' => $this->getLeaveManagement($organization),
            'performanceKpis' => $this->getPerformanceKpis(),
            'recentActivities' => $this->getRecentActivities($organization),
            'upcomingEvents' => $this->getUpcomingEvents($organization),
            'organizationUnitStats' => $this->getorganizationUnitStats($organization),
            'trainingDevelopment' => $this->getTrainingDevelopment(),
        ]);
    }

    /**
     * Employee summary derived from real employee records in the organization.
     *
     * @return array{
     *     total_employees: int,
     *     active_employees: int,
     *     new_hires: int,
     *     turnover_rate: float,
     *     organizationUnit_distribution: array<string, int>,
     * }
     */
    private function getEmployeeSummary(Organization $organization): array
    {
        $employees = Employee::query()
            ->ofOrganization($organization->id)
            ->get(['id', 'organization_unit_id', 'is_active', 'created_at']);

        $totalEmployees = $employees->count();
        $activeEmployees = $employees->filter(fn (Employee $employee) => $employee->is_active)->count();
        $newHires = $employees->filter(
            fn (Employee $employee) => $employee->created_at->greaterThanOrEqualTo(now()->startOfMonth())
        )->count();

        $unitNames = OrganizationUnit::query()
            ->ofOrganization($organization->id)
            ->pluck('name', 'id');

        $organizationUnitDistribution = $employees
            ->groupBy('organization_unit_id')
            ->mapWithKeys(function ($group, $unitId) use ($unitNames) {
                return [$unitNames->get($unitId, 'Unassigned') => $group->count()];
            })
            ->sortDesc()
            ->toArray();

        return [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'new_hires' => $newHires,
            'turnover_rate' => 0.0,
            'organizationUnit_distribution' => $organizationUnitDistribution,
        ];
    }

    /**
     * Attendance overview for today and the current week (Monday to Friday).
     *
     * @return array{
     *     present_today: int,
     *     absent_today: int,
     *     late_today: int,
     *     attendance_rate: float,
     *     weekly_trend: array{
     *         labels: list<string>,
     *         present: list<int>,
     *         absent: list<int>,
     *         late: list<int>,
     *     },
     * }
     */
    private function getAttendanceOverview(Organization $organization): array
    {
        $today = today()->toDateString();

        $todayRecords = AttendanceRecord::query()
            ->forOrganization($organization->id)
            ->forDate($today)
            ->get(['status']);

        $presentToday = $todayRecords->where('status', 'present')->count();
        $absentToday = $todayRecords->where('status', 'absent')->count();
        $lateToday = $todayRecords->where('status', 'late')->count();
        $totalToday = $todayRecords->count();

        $attendanceRate = $totalToday > 0
            ? round((($presentToday + $lateToday) / $totalToday) * 100, 1)
            : 0;

        $weekStart = now()->startOfWeek();
        $weekEnd = now()->startOfWeek()->addDays(4);

        $weekRecords = AttendanceRecord::query()
            ->forOrganization($organization->id)
            ->dateRange($weekStart, $weekEnd)
            ->whereIn('status', ['present', 'late', 'absent'])
            ->get(['record_date', 'status']);

        $presentTrend = [0, 0, 0, 0, 0];
        $absentTrend = [0, 0, 0, 0, 0];
        $lateTrend = [0, 0, 0, 0, 0];

        foreach ($weekRecords as $record) {
            $index = ((int) $record->record_date->format('N')) - 1;

            if ($index < 0 || $index > 4) {
                continue;
            }

            match ($record->status) {
                'present' => $presentTrend[$index]++,
                'absent' => $absentTrend[$index]++,
                'late' => $lateTrend[$index]++,
                default => null,
            };
        }

        return [
            'present_today' => $presentToday,
            'absent_today' => $absentToday,
            'late_today' => $lateToday,
            'attendance_rate' => $attendanceRate,
            'weekly_trend' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'present' => $presentTrend,
                'absent' => $absentTrend,
                'late' => $lateTrend,
            ],
        ];
    }

    /**
     * Leave management overview derived from real leave requests.
     *
     * @return array{
     *     pending_requests: int,
     *     approved_this_month: int,
     *     leave_balance_summary: array{annual: int, sick: int, personal: int},
     *     upcoming_leaves: list<array{
     *         employee_name: string,
     *         leave_type: string,
     *         start_date: Carbon,
     *         end_date: Carbon,
     *         status: string,
     *     }>,
     * }
     */
    private function getLeaveManagement(Organization $organization): array
    {
        $pendingRequests = LeaveRequest::query()
            ->ofOrganization($organization->id)
            ->pending()
            ->count();

        $approvedThisMonth = LeaveRequest::query()
            ->ofOrganization($organization->id)
            ->approved()
            ->whereBetween('start_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->count();

        $yearBalances = LeaveRequest::query()
            ->ofOrganization($organization->id)
            ->approved()
            ->whereBetween('start_date', [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()])
            ->get(['leave_type', 'total_days'])
            ->groupBy('leave_type')
            ->map(fn ($group) => (int) $group->sum('total_days'));

        $upcomingLeaves = LeaveRequest::query()
            ->ofOrganization($organization->id)
            ->approved()
            ->whereDate('start_date', '>=', today()->toDateString())
            ->with(['employee:id,first_name,last_name'])
            ->orderBy('start_date')
            ->limit(5)
            ->get()
            ->map(function (LeaveRequest $leave) {
                return [
                    'employee_name' => $this->employeeName($leave),
                    'leave_type' => $leave->leave_type,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'status' => $leave->status,
                ];
            })
            ->values()
            ->toArray();

        return [
            'pending_requests' => $pendingRequests,
            'approved_this_month' => $approvedThisMonth,
            'leave_balance_summary' => [
                'annual' => $yearBalances->get('annual', 0),
                'sick' => $yearBalances->get('sick', 0),
                'personal' => $yearBalances->get('personal', 0),
            ],
            'upcoming_leaves' => $upcomingLeaves,
        ];
    }

    /**
     * Performance KPIs.
     *
     * No performance model exists yet, so all values are safe defaults and no
     * names are fabricated.
     *
     * @return array{
     *     average_productivity: float,
     *     goal_completion_rate: float,
     *     employee_engagement: float,
     *     top_performers: array<int, mixed>,
     *     improvement_areas: array<int, mixed>,
     * }
     */
    private function getPerformanceKpis(): array
    {
        return [
            'average_productivity' => 0.0,
            'goal_completion_rate' => 0.0,
            'employee_engagement' => 0.0,
            'top_performers' => [],
            'improvement_areas' => [],
        ];
    }

    /**
     * Recent activities derived from newly created employees and approved leaves.
     *
     * @return list<array{
     *     type: string,
     *     description: string,
     *     employee_name: string,
     *     timestamp: Carbon,
     *     icon: string,
     * }>
     */
    private function getRecentActivities(Organization $organization): array
    {
        $newHires = Employee::query()
            ->ofOrganization($organization->id)
            ->orderByDesc('created_at')
            ->limit(2)
            ->get(['id', 'first_name', 'last_name', 'created_at']);

        $approvedLeaves = LeaveRequest::query()
            ->ofOrganization($organization->id)
            ->approved()
            ->with(['employee:id,first_name,last_name'])
            ->orderByDesc('updated_at')
            ->limit(2)
            ->get();

        $activities = collect()
            ->merge($newHires->map(fn (Employee $employee) => [
                'type' => 'new_hire',
                'description' => 'New employee onboarded',
                'employee_name' => trim($employee->first_name.' '.$employee->last_name),
                'timestamp' => $employee->created_at,
                'icon' => '👤',
            ]))
            ->merge($approvedLeaves->map(fn (LeaveRequest $leave) => [
                'type' => 'leave_approved',
                'description' => 'Leave request approved',
                'employee_name' => $this->employeeName($leave),
                'timestamp' => $leave->approved_at ?? $leave->updated_at,
                'icon' => '🏖️',
            ]))
            ->sortByDesc('timestamp')
            ->values()
            ->toArray();

        return $activities;
    }

    /**
     * Upcoming events derived from approved leaves starting today or later.
     *
     * @return list<array{
     *     title: string,
     *     date: Carbon,
     *     type: string,
     *     attendees_count: int,
     * }>
     */
    private function getUpcomingEvents(Organization $organization): array
    {
        return LeaveRequest::query()
            ->ofOrganization($organization->id)
            ->approved()
            ->whereDate('start_date', '>=', today()->toDateString())
            ->with(['employee:id,first_name,last_name'])
            ->orderBy('start_date')
            ->limit(3)
            ->get()
            ->map(function (LeaveRequest $leave) {
                return [
                    'title' => sprintf('Leave: %s (%s)', $this->employeeName($leave), $leave->leave_type),
                    'date' => $leave->start_date,
                    'type' => 'leave',
                    'attendees_count' => 1,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Organization unit level statistics derived from real employee and attendance data.
     *
     * @return list<array{
     *     name: string,
     *     employee_count: int,
     *     attendance_rate: float,
     *     vacancy_count: int,
     * }>
     */
    private function getorganizationUnitStats(Organization $organization): array
    {
        $today = today()->toDateString();

        $units = OrganizationUnit::query()
            ->ofOrganization($organization->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $unitIds = $units->pluck('id');

        $employeeCounts = Employee::query()
            ->ofOrganization($organization->id)
            ->whereIn('organization_unit_id', $unitIds)
            ->get(['organization_unit_id'])
            ->groupBy('organization_unit_id')
            ->map->count();

        $attendanceToday = AttendanceRecord::query()
            ->forOrganization($organization->id)
            ->forDate($today)
            ->get(['employee_id', 'status']);

        $employeeUnitMap = Employee::query()
            ->ofOrganization($organization->id)
            ->whereIn('id', $attendanceToday->pluck('employee_id')->filter()->unique())
            ->get(['id', 'organization_unit_id'])
            ->pluck('organization_unit_id', 'id');

        $unitAttendance = [];

        foreach ($attendanceToday as $record) {
            $unitId = $employeeUnitMap->get($record->employee_id);

            if ($unitId === null) {
                continue;
            }

            $unitAttendance[$unitId]['total'] = ($unitAttendance[$unitId]['total'] ?? 0) + 1;

            if (in_array($record->status, ['present', 'late'], true)) {
                $unitAttendance[$unitId]['present_late'] = ($unitAttendance[$unitId]['present_late'] ?? 0) + 1;
            }
        }

        return $units
            ->map(function (OrganizationUnit $unit) use ($employeeCounts, $unitAttendance) {
                $total = $unitAttendance[$unit->id]['total'] ?? 0;
                $presentLate = $unitAttendance[$unit->id]['present_late'] ?? 0;

                return [
                    'name' => $unit->name,
                    'employee_count' => (int) ($employeeCounts[$unit->id] ?? 0),
                    'attendance_rate' => $total > 0 ? round(($presentLate / $total) * 100, 1) : 0.0,
                    'vacancy_count' => 0,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Training and development metrics.
     *
     * No training module exists yet, so all values are safe defaults.
     *
     * @return array{
     *     ongoing_trainings: int,
     *     upcoming_sessions: array<int, mixed>,
     *     completion_rate: float,
     *     skill_gaps: array<int, mixed>,
     * }
     */
    private function getTrainingDevelopment(): array
    {
        return [
            'ongoing_trainings' => 0,
            'upcoming_sessions' => [],
            'completion_rate' => 0.0,
            'skill_gaps' => [],
        ];
    }

    /**
     * Resolve a leave request's employee full name.
     */
    private function employeeName(LeaveRequest $leave): string
    {
        if ($leave->employee) {
            return trim($leave->employee->first_name.' '.$leave->employee->last_name);
        }

        return 'Unknown Employee';
    }
}
