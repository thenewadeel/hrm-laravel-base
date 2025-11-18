<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationDashboardController extends Controller
{
    public function dashboard()
    {
        $organization = Organization::first(); // Or get current organization

        $metrics = $this->getOrganizationMetrics($organization);
        $departmentStats = $this->getDepartmentStatistics($organization);
        $recentActivities = $this->getRecentActivities($organization);

        return view('organizations.dashboard', compact('metrics', 'departmentStats', 'recentActivities'));
    }

    /**
     * Get organization-wide metrics.
     */
    private function getOrganizationMetrics(Organization $organization)
    {
        // Mock data - replace with actual queries
        return [
            'total_employees' => 247,
            'total_departments' => 14,
            'attendance_rate' => 94.2,
            'monthly_payroll' => 1200000,
            'employee_growth' => 12, // percentage
            'payroll_growth' => 5.3, // percentage
            'attendance_improvement' => 2.1 // percentage
        ];
    }

    /**
     * Get department-level statistics.
     */
    private function getDepartmentStatistics(Organization $organization)
    {
        // Mock data - replace with actual department aggregation
        return [
            ['name' => 'Engineering', 'count' => 45, 'percentage' => 18],
            ['name' => 'Sales', 'count' => 32, 'percentage' => 13],
            ['name' => 'Marketing', 'count' => 28, 'percentage' => 11],
            ['name' => 'Human Resources', 'count' => 18, 'percentage' => 7],
            ['name' => 'Finance', 'count' => 22, 'percentage' => 9],
            ['name' => 'Operations', 'count' => 35, 'percentage' => 14],
            ['name' => 'Customer Support', 'count' => 42, 'percentage' => 17],
            ['name' => 'Other', 'count' => 25, 'percentage' => 10],
        ];
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
                'icon' => 'user-add'
            ],
            [
                'type' => 'department_created',
                'description' => 'New "Research & Development" department created',
                'time' => '1 day ago',
                'icon' => 'folder-add'
            ],
            [
                'type' => 'attendance_regularized',
                'description' => '15 attendance records regularized',
                'time' => '2 days ago',
                'icon' => 'clock'
            ],
            [
                'type' => 'payroll_processed',
                'description' => 'October payroll processed successfully',
                'time' => '3 days ago',
                'icon' => 'currency-dollar'
            ]
        ];
    }

    /**
     * Get organization structure tree.
     */
    public function structure()
    {
        $organization = Organization::first();
        $tree = OrganizationUnit::with(['children', 'users'])->whereNull('parent_id')->get();

        return view('organizations.structure', compact('organization', 'tree'));
    }

    /**
     * Get organization analytics report.
     */
    public function analytics(Request $request)
    {
        $startDate = $request->get('start_date', now()->subMonth());
        $endDate = $request->get('end_date', now());

        $analytics = [
            'headcount_trend' => $this->getHeadcountTrend($startDate, $endDate),
            'attendance_trend' => $this->getAttendanceTrend($startDate, $endDate),
            'department_performance' => $this->getDepartmentPerformance($startDate, $endDate),
            'cost_analysis' => $this->getCostAnalysis($startDate, $endDate)
        ];

        return view('organizations.analytics', compact('analytics'));
    }

    // Additional methods for data aggregation...
    private function getHeadcountTrend($startDate, $endDate)
    { /* Implementation */
    }
    private function getAttendanceTrend($startDate, $endDate)
    { /* Implementation */
    }
    private function getDepartmentPerformance($startDate, $endDate)
    { /* Implementation */
    }
    private function getCostAnalysis($startDate, $endDate)
    { /* Implementation */
    }
}
