<?php

namespace App\Http\Controllers;

use App\Models\Organization;
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
     */
    private function getOrganizationMetrics(Organization $organization): array
    {
        $totalEmployees = $organization->users()->count();
        $totalDepartments = $organization->units()->count();

        return [
            'total_employees' => $totalEmployees,
            'total_departments' => $totalDepartments,
            'attendance_rate' => 94.2,
            'monthly_payroll' => 1200000,
            'employee_growth' => 12, // percentage
            'payroll_growth' => 5.3, // percentage
            'attendance_improvement' => 2.1, // percentage
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
     */
    private function getRecentActivities(Organization $organization)
    {
        // Mock data - replace with actual activity log
        return [
            [
                'type' => 'employee_added',
                'description' => 'John Smith joined Engineering department',
                'time' => '2 hours ago',
                'icon' => 'user-add',
            ],
            [
                'type' => 'department_created',
                'description' => 'New "Research & Development" department created',
                'time' => '1 day ago',
                'icon' => 'folder-add',
            ],
            [
                'type' => 'attendance_regularized',
                'description' => '15 attendance records regularized',
                'time' => '2 days ago',
                'icon' => 'clock',
            ],
            [
                'type' => 'payroll_processed',
                'description' => 'October payroll processed successfully',
                'time' => '3 days ago',
                'icon' => 'currency-dollar',
            ],
        ];
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
    private function getHeadcountTrend(Organization $organization, $startDate, $endDate): array
    {
        // Mock implementation - replace with actual trend data
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [120, 125, 130, 128, 135, 140],
        ];
    }

    private function getAttendanceTrend(Organization $organization, $startDate, $endDate): array
    {
        // Mock implementation - replace with actual attendance trend data
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [94.2, 93.8, 95.1, 94.7, 95.3, 94.9],
        ];
    }

    private function getDepartmentPerformance(Organization $organization, $startDate, $endDate): array
    {
        // Mock implementation - replace with actual performance data
        return [
            'labels' => ['Engineering', 'Sales', 'Marketing', 'HR', 'Finance'],
            'productivity' => [87, 92, 78, 85, 90],
            'efficiency' => [82, 88, 75, 83, 87],
        ];
    }

    private function getCostAnalysis(Organization $organization, $startDate, $endDate): array
    {
        // Mock implementation - replace with actual cost analysis
        return [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'payroll_costs' => [1200000, 1250000, 1300000, 1280000, 1350000, 1400000],
            'operational_costs' => [300000, 320000, 310000, 330000, 340000, 350000],
        ];
    }
}
