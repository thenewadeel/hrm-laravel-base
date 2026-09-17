<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Organization;
use App\Models\PayrollEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationDashboardController extends Controller
{
    public function index(): View
    {
        $organization = auth()->user()->currentOrganization;

        if (! $organization) {
            abort(403, 'No organization assigned');
        }

        $metrics = $this->getOrganizationMetrics($organization);
        $departmentStats = $this->getDepartmentStatistics($organization);
        $recentActivities = $this->getRecentActivities($organization);

        return view('organizations.dashboard', compact('organization', 'metrics', 'departmentStats', 'recentActivities'));
    }

    /**
     * Get organization-wide metrics.
     *
     * @return array{
     *     total_employees: int,
     *     total_departments: int,
     *     attendance_rate: float,
     *     monthly_payroll: float,
     *     employee_growth: float,
     *     payroll_growth: float,
     *     attendance_improvement: float,
     * }
     */
    private function getOrganizationMetrics(Organization $organization): array
    {
        $totalEmployees = $organization->users()->count();
        $totalDepartments = $organization->units()->count();

        $attendanceToday = AttendanceRecord::query()
            ->forOrganization($organization->id)
            ->forDate(today()->toDateString())
            ->get(['status']);

        $attendanceTotal = $attendanceToday->count();
        $attendanceRate = $attendanceTotal > 0
            ? round(($attendanceToday->whereIn('status', ['present', 'late'])->count() / $attendanceTotal) * 100, 1)
            : 0;

        $monthlyPayroll = (float) PayrollEntry::query()
            ->ofOrganization($organization->id)
            ->forPeriod(now()->format('Y-m'))
            ->sum('gross_pay');

        $totalEmployeesCount = Employee::query()->ofOrganization($organization->id)->count();
        $newEmployeesThisMonth = Employee::query()
            ->ofOrganization($organization->id)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $employeeGrowth = $totalEmployeesCount > 0
            ? round(($newEmployeesThisMonth / $totalEmployeesCount) * 100, 1)
            : 0;

        return [
            'total_employees' => $totalEmployees,
            'total_departments' => $totalDepartments,
            'attendance_rate' => $attendanceRate,
            'monthly_payroll' => $monthlyPayroll,
            'employee_growth' => $employeeGrowth,
            'payroll_growth' => 0.0,
            'attendance_improvement' => 0.0,
        ];
    }

    /**
     * Get department-level statistics.
     */
    private function getDepartmentStatistics(Organization $organization): array
    {
        $units = $organization->units()->withCount('users')->get();
        $totalEmployees = $units->sum('users_count');

        return $units->map(function ($unit) use ($totalEmployees) {
            return [
                'name' => $unit->name,
                'count' => $unit->users_count,
                'percentage' => $totalEmployees > 0 ? round(($unit->users_count / $totalEmployees) * 100, 1) : 0,
            ];
        })->toArray();
    }

    /**
     * Get recent organizational activities.
     *
     * Derived from real data: newly added employees, approved leave requests,
     * and today's attendance records.
     *
     * @return list<array{type: string, description: string, time: string, icon: string}>
     */
    private function getRecentActivities(Organization $organization): array
    {
        $newEmployees = Employee::query()
            ->ofOrganization($organization->id)
            ->with(['organizationUnit:id,name'])
            ->orderByDesc('created_at')
            ->limit(2)
            ->get();

        $approvedLeaves = LeaveRequest::query()
            ->ofOrganization($organization->id)
            ->approved()
            ->with(['employee:id,first_name,last_name'])
            ->orderByDesc('updated_at')
            ->limit(2)
            ->get();

        $attendanceToday = AttendanceRecord::query()
            ->forOrganization($organization->id)
            ->forDate(today()->toDateString())
            ->count();

        $activities = collect();

        foreach ($newEmployees as $employee) {
            $name = trim($employee->first_name.' '.$employee->last_name);
            $department = $employee->organizationUnit?->name;

            $activities->push([
                'type' => 'employee_added',
                'description' => $department
                    ? "{$name} joined {$department} department"
                    : "New employee {$name} added",
                'timestamp' => $employee->created_at,
                'icon' => 'user-group',
            ]);
        }

        foreach ($approvedLeaves as $leave) {
            $name = $leave->employee ? trim($leave->employee->first_name.' '.$leave->employee->last_name) : 'Employee';

            $activities->push([
                'type' => 'leave_approved',
                'description' => "Leave request approved for {$name}",
                'timestamp' => $leave->approved_at ?? $leave->updated_at,
                'icon' => 'calendar',
            ]);
        }

        if ($attendanceToday > 0) {
            $activities->push([
                'type' => 'attendance_recorded',
                'description' => "{$attendanceToday} attendance records recorded today",
                'timestamp' => now(),
                'icon' => 'clock',
            ]);
        }

        return $activities
            ->sortByDesc('timestamp')
            ->map(fn (array $activity) => [
                'type' => $activity['type'],
                'description' => $activity['description'],
                'time' => $activity['timestamp']->diffForHumans(),
                'icon' => $activity['icon'],
            ])
            ->values()
            ->toArray();
    }

    /**
     * Get organization structure tree.
     */
    public function structure(): View
    {
        $organization = auth()->user()->currentOrganization;

        if (! $organization) {
            abort(403, 'No organization assigned');
        }

        $tree = $organization->units()
            ->with(['children', 'users'])
            ->whereNull('parent_id')
            ->get();

        return view('organizations.structure', compact('organization', 'tree'));
    }

    /**
     * Get organization analytics report.
     */
    public function analytics(Request $request): View
    {
        $organization = auth()->user()->currentOrganization;

        if (! $organization) {
            abort(403, 'No organization assigned');
        }

        $startDate = $request->get('start_date', now()->subMonth());
        $endDate = $request->get('end_date', now());

        $analytics = [
            'headcount_trend' => $this->getHeadcountTrend($organization, $startDate, $endDate),
            'attendance_trend' => $this->getAttendanceTrend($organization, $startDate, $endDate),
            'department_performance' => $this->getDepartmentPerformance($organization, $startDate, $endDate),
            'cost_analysis' => $this->getCostAnalysis($organization, $startDate, $endDate),
        ];

        return view('organizations.analytics', compact('analytics'));
    }

    // Additional methods for data aggregation...
    /**
     * Safe default placeholder - no reliable headcount trend data source yet.
     * Values are illustrative only and will be replaced when a real source exists.
     */
    private function getHeadcountTrend(Organization $organization, $startDate, $endDate): array
    {
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [120, 125, 130, 128, 135, 140],
        ];
    }

    /**
     * Safe default placeholder - no reliable attendance trend data source yet.
     * Values are illustrative only and will be replaced when a real source exists.
     */
    private function getAttendanceTrend(Organization $organization, $startDate, $endDate): array
    {
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [94.2, 93.8, 95.1, 94.7, 95.3, 94.9],
        ];
    }

    /**
     * Safe default placeholder - no reliable department performance data source yet.
     * Values are illustrative only and will be replaced when a real source exists.
     */
    private function getDepartmentPerformance(Organization $organization, $startDate, $endDate): array
    {
        return [
            'labels' => ['Engineering', 'Sales', 'Marketing', 'HR', 'Finance'],
            'productivity' => [87, 92, 78, 85, 90],
            'efficiency' => [82, 88, 75, 83, 87],
        ];
    }

    /**
     * Safe default placeholder - no reliable cost analysis data source yet.
     * Values are illustrative only and will be replaced when a real source exists.
     */
    private function getCostAnalysis(Organization $organization, $startDate, $endDate): array
    {
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'payroll_costs' => [1200000, 1250000, 1300000, 1280000, 1350000, 1400000],
            'operational_costs' => [300000, 320000, 310000, 330000, 340000, 350000],
        ];
    }
}
