<?php

namespace App\Services;

use App\Models\Organization;
use Dompdf\Dompdf;

class AdvancePdfService
{
    private AdvanceReportService $reportService;

    public function __construct(AdvanceReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate employee statement PDF
     */
    public function generateEmployeeStatementPdf(Organization $organization, ?int $employeeId = null, ?\Carbon\Carbon $startDate = null, ?\Carbon\Carbon $endDate = null): string
    {
        $data = $this->reportService->generateEmployeeStatement($organization, $employeeId, $startDate, $endDate);

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getEmployeeStatementHtml($data);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate aging analysis PDF
     */
    public function generateAgingAnalysisPdf(Organization $organization): string
    {
        $data = $this->reportService->generateAgingAnalysis($organization);

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getAgingAnalysisHtml($data);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate monthly summary PDF
     */
    public function generateMonthlySummaryPdf(Organization $organization, int $months = 12): string
    {
        $data = $this->reportService->generateMonthlySummary($organization, $months);

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'landscape');

        $html = $this->getMonthlySummaryHtml($data);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate department report PDF
     */
    public function generateDepartmentReportPdf(Organization $organization): string
    {
        $data = $this->reportService->generateDepartmentReport($organization);

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'landscape');

        $html = $this->getDepartmentReportHtml($data);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate advance vs salary analysis PDF
     */
    public function generateAdvanceVsSalaryPdf(Organization $organization, ?\Carbon\Carbon $startDate = null, ?\Carbon\Carbon $endDate = null): string
    {
        $data = $this->reportService->generateAdvanceVsSalaryAnalysis($organization, $startDate, $endDate);

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'landscape');

        $html = $this->getAdvanceVsSalaryHtml($data);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate outstanding advances PDF
     */
    public function generateOutstandingAdvancesPdf(Organization $organization): string
    {
        $data = $this->reportService->generateOutstandingAdvances($organization);

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getOutstandingAdvancesHtml($data);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Generate comprehensive analytics PDF
     */
    public function generateAnalyticsPdf(Organization $organization): string
    {
        $data = $this->reportService->getAdvanceAnalytics($organization);

        $pdf = new Dompdf;
        $pdf->setPaper('A4', 'portrait');

        $html = $this->getAnalyticsHtml($data);
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * HTML template for employee statement
     */
    private function getEmployeeStatementHtml(array $data): string
    {
        $title = $data['employee'] ?
            "Advance Statement - {$data['employee']->first_name} {$data['employee']->last_name}" :
            'All Employees Advance Statement';

        $period = '';
        if ($data['period']['start'] && $data['period']['end']) {
            $period = "Period: {$data['period']['start']->format('M d, Y')} - {$data['period']['end']->format('M d, Y')}";
        }

        $html = "
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .summary { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
            .summary-item { display: inline-block; margin: 10px 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .status-pending { color: #f59e0b; }
            .status-active { color: #10b981; }
            .status-completed { color: #6b7280; }
        </style>
        
        <div class='header'>
            <h1>{$title}</h1>
            <p>{$period}</p>
        </div>

        <div class='summary'>
            <div class='summary-item'><strong>Total Advances:</strong> {$data['summary']['total_advances']}</div>
            <div class='summary-item'><strong>Total Amount:</strong> $".number_format($data['summary']['total_amount'], 2)."</div>
            <div class='summary-item'><strong>Total Repaid:</strong> $".number_format($data['summary']['total_repaid'], 2)."</div>
            <div class='summary-item'><strong>Outstanding:</strong> $".number_format($data['summary']['total_balance'], 2).'</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Employee</th>
                    <th>Amount</th>
                    <th>Balance</th>
                    <th>Monthly Deduction</th>
                    <th>Months Repaid</th>
                    <th>Status</th>
                    <th>Request Date</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($data['advances'] as $advance) {
            $statusClass = "status-{$advance->status}";
            $html .= "
                <tr>
                    <td>{$advance->advance_reference}</td>
                    <td>{$advance->employee->first_name} {$advance->employee->last_name}</td>
                    <td>$".number_format($advance->amount, 2).'</td>
                    <td>$'.number_format($advance->balance_amount, 2).'</td>
                    <td>$'.number_format($advance->monthly_deduction, 2)."</td>
                    <td>{$advance->months_repaid}/{$advance->repayment_months}</td>
                    <td class='{$statusClass}'>".ucfirst($advance->status)."</td>
                    <td>{$advance->request_date->format('M d, Y')}</td>
                </tr>";
        }

        $html .= '
            </tbody>
        </table>';

        return $html;
    }

    /**
     * HTML template for aging analysis
     */
    private function getAgingAnalysisHtml(array $data): string
    {
        $html = "
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .summary { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
            .bucket { margin: 20px 0; }
            .bucket-header { background: #3b82f6; color: white; padding: 10px; font-weight: bold; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
        </style>
        
        <div class='header'>
            <h1>Advance Aging Analysis</h1>
            <p>As of ".now()->format('M d, Y')."</p>
        </div>

        <div class='summary'>
            <div><strong>Total Outstanding Advances:</strong> $".number_format($data['total_outstanding'], 2)."</div>
            <div><strong>Total Active Advances:</strong> {$data['total_active_advances']}</div>
        </div>";

        foreach ($data['aging_buckets'] as $bucketName => $bucket) {
            if ($bucket['advances']->count() > 0) {
                $html .= "
                <div class='bucket'>
                    <div class='bucket-header'>{$bucketName} Days ({$bucket['advances']->count()} advances - $".number_format($bucket['total'], 2).')</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Days Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>';

                foreach ($bucket['advances'] as $advance) {
                    $daysOutstanding = \Carbon\Carbon::parse($advance->first_deduction_month)->diffInDays(now());
                    $html .= "
                        <tr>
                            <td>{$advance->employee->first_name} {$advance->employee->last_name}</td>
                            <td>{$advance->advance_reference}</td>
                            <td>$".number_format($advance->amount, 2).'</td>
                            <td>$'.number_format($advance->balance_amount, 2)."</td>
                            <td>{$daysOutstanding}</td>
                        </tr>";
                }

                $html .= '
                        </tbody>
                    </table>
                </div>';
            }
        }

        return $html;
    }

    /**
     * HTML template for monthly summary
     */
    private function getMonthlySummaryHtml(array $data): string
    {
        $html = "
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .summary { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .text-right { text-align: right; }
        </style>
        
        <div class='header'>
            <h1>Monthly Advance Summary</h1>
            <p>Last 12 Months</p>
        </div>

        <div class='summary'>
            <div><strong>Total Requested:</strong> {$data['summary']['total_requested']} advances ($".number_format($data['summary']['total_amount_requested'], 2).")</div>
            <div><strong>Total Approved:</strong> {$data['summary']['total_approved']} advances ($".number_format($data['summary']['total_amount_approved'], 2).')</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Requested</th>
                    <th>Approved</th>
                    <th>Amount Requested</th>
                    <th>Amount Approved</th>
                    <th>Pending Approval</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($data['monthly_data'] as $month) {
            $html .= "
                <tr>
                    <td>{$month['month_name']}</td>
                    <td class='text-right'>{$month['advances_requested']}</td>
                    <td class='text-right'>{$month['advances_approved']}</td>
                    <td class='text-right'>$".number_format($month['amount_requested'], 2)."</td>
                    <td class='text-right'>$".number_format($month['amount_approved'], 2)."</td>
                    <td class='text-right'>{$month['pending_approval']}</td>
                </tr>";
        }

        $html .= '
            </tbody>
        </table>';

        return $html;
    }

    /**
     * HTML template for department report
     */
    private function getDepartmentReportHtml(array $data): string
    {
        $html = "
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .summary { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .text-right { text-align: right; }
        </style>
        
        <div class='header'>
            <h1>Department-wise Advance Report</h1>
            <p>As of ".now()->format('M d, Y')."</p>
        </div>

        <div class='summary'>
            <div><strong>Total Departments:</strong> {$data['summary']['total_departments']}</div>
            <div><strong>Total Advances:</strong> {$data['summary']['total_advances']}</div>
            <div><strong>Total Amount:</strong> $".number_format($data['summary']['total_amount'], 2).'</div>
            <div><strong>Total Outstanding:</strong> $'.number_format($data['summary']['total_balance'], 2).'</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Employees</th>
                    <th>Total Advances</th>
                    <th>Total Amount</th>
                    <th>Outstanding</th>
                    <th>Average Advance</th>
                    <th>Active</th>
                    <th>Pending</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($data['departments'] as $dept) {
            $html .= "
                <tr>
                    <td>{$dept['department']}</td>
                    <td class='text-right'>{$dept['employee_count']}</td>
                    <td class='text-right'>{$dept['total_advances']}</td>
                    <td class='text-right'>$".number_format($dept['total_amount'], 2)."</td>
                    <td class='text-right'>$".number_format($dept['total_balance'], 2)."</td>
                    <td class='text-right'>$".number_format($dept['avg_advance_amount'], 2)."</td>
                    <td class='text-right'>{$dept['active_advances']}</td>
                    <td class='text-right'>{$dept['pending_advances']}</td>
                    <td class='text-right'>{$dept['completed_advances']}</td>
                </tr>";
        }

        $html .= '
            </tbody>
        </table>';

        return $html;
    }

    /**
     * HTML template for advance vs salary analysis
     */
    private function getAdvanceVsSalaryHtml(array $data): string
    {
        $html = "
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .summary { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .text-right { text-align: right; }
        </style>
        
        <div class='header'>
            <h1>Advance vs Salary Analysis</h1>
            <p>Period: {$data['period']['start']->format('M d, Y')} - {$data['period']['end']->format('M d, Y')}</p>
        </div>

        <div class='summary'>
            <div><strong>Total Employees:</strong> {$data['summary']['total_employees']}</div>
            <div><strong>Employees with Advances:</strong> {$data['summary']['employees_with_advances']}</div>
            <div><strong>Total Monthly Salary:</strong> $".number_format($data['summary']['total_monthly_salary'], 2).'</div>
            <div><strong>Total Advances:</strong> $'.number_format($data['summary']['total_advances'], 2).'</div>
            <div><strong>Total Outstanding:</strong> $'.number_format($data['summary']['total_balance'], 2).'</div>
            <div><strong>Avg Advance/Salary Ratio:</strong> '.number_format($data['summary']['avg_advance_to_salary_ratio'], 2).'</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Monthly Salary</th>
                    <th>Total Advances</th>
                    <th>Outstanding</th>
                    <th>Advance Count</th>
                    <th>Advance/Salary Ratio</th>
                    <th>Balance/Salary Ratio</th>
                    <th>Avg Advance</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($data['employees'] as $emp) {
            $html .= "
                <tr>
                    <td>{$emp['employee']->first_name} {$emp['employee']->last_name}</td>
                    <td class='text-right'>$".number_format($emp['monthly_salary'], 2)."</td>
                    <td class='text-right'>$".number_format($emp['total_advances'], 2)."</td>
                    <td class='text-right'>$".number_format($emp['total_balance'], 2)."</td>
                    <td class='text-right'>{$emp['advance_count']}</td>
                    <td class='text-right'>".number_format($emp['advance_to_salary_ratio'], 2)."</td>
                    <td class='text-right'>".number_format($emp['balance_to_salary_ratio'], 2)."</td>
                    <td class='text-right'>$".number_format($emp['avg_advance_amount'], 2).'</td>
                </tr>';
        }

        $html .= '
            </tbody>
        </table>';

        return $html;
    }

    /**
     * HTML template for outstanding advances
     */
    private function getOutstandingAdvancesHtml(array $data): string
    {
        $html = "
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .summary { background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
            .risk-section { margin: 20px 0; }
            .risk-low { border-left: 4px solid #10b981; padding-left: 10px; }
            .risk-medium { border-left: 4px solid #f59e0b; padding-left: 10px; }
            .risk-high { border-left: 4px solid #ef4444; padding-left: 10px; }
            .risk-critical { border-left: 4px solid #991b1b; padding-left: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .text-right { text-align: right; }
        </style>
        
        <div class='header'>
            <h1>Outstanding Advances Report</h1>
            <p>As of ".now()->format('M d, Y')."</p>
        </div>

        <div class='summary'>
            <div><strong>Total Outstanding:</strong> $".number_format($data['summary']['total_outstanding'], 2)."</div>
            <div><strong>Total Count:</strong> {$data['summary']['total_count']}</div>
            <div><strong>Low Risk:</strong> {$data['summary']['low_risk_count']}</div>
            <div><strong>Medium Risk:</strong> {$data['summary']['medium_risk_count']}</div>
            <div><strong>High Risk:</strong> {$data['summary']['high_risk_count']}</div>
            <div><strong>Critical Risk:</strong> {$data['summary']['critical_risk_count']}</div>
        </div>";

        $riskCategories = [
            'low' => ['name' => 'Low Risk (< 1 month salary)', 'class' => 'risk-low'],
            'medium' => ['name' => 'Medium Risk (1-2 months salary)', 'class' => 'risk-medium'],
            'high' => ['name' => 'High Risk (2-3 months salary)', 'class' => 'risk-high'],
            'critical' => ['name' => 'Critical Risk (> 3 months salary)', 'class' => 'risk-critical'],
        ];

        foreach ($riskCategories as $key => $category) {
            $advances = $data['risk_categories'][$key];
            if ($advances->count() > 0) {
                $total = $advances->sum('balance_amount');
                $html .= "
                <div class='risk-section {$category['class']}'>
                    <h3>{$category['name']} ({$advances->count()} advances - $".number_format($total, 2).')</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Balance</th>
                                <th>Monthly Salary</th>
                                <th>Balance/Salary Ratio</th>
                                <th>Months Remaining</th>
                            </tr>
                        </thead>
                        <tbody>';

                foreach ($advances as $advance) {
                    $monthlySalary = $advance->employee->salaryStructure?->basic_salary ?? 0;
                    $ratio = $monthlySalary > 0 ? $advance->balance_amount / $monthlySalary : 0;
                    $monthsRemaining = $advance->remaining_months;

                    $html .= "
                        <tr>
                            <td>{$advance->employee->first_name} {$advance->employee->last_name}</td>
                            <td>{$advance->advance_reference}</td>
                            <td class='text-right'>$".number_format($advance->amount, 2)."</td>
                            <td class='text-right'>$".number_format($advance->balance_amount, 2)."</td>
                            <td class='text-right'>$".number_format($monthlySalary, 2)."</td>
                            <td class='text-right'>".number_format($ratio, 2)."</td>
                            <td class='text-right'>{$monthsRemaining}</td>
                        </tr>";
                }

                $html .= '
                        </tbody>
                    </table>
                </div>';
            }
        }

        return $html;
    }

    /**
     * HTML template for analytics
     */
    private function getAnalyticsHtml(array $data): string
    {
        $html = "
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .section { margin: 30px 0; }
            .section h2 { color: #3b82f6; border-bottom: 2px solid #3b82f6; padding-bottom: 5px; }
            .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0; }
            .metric { background: #f8fafc; padding: 15px; border-radius: 8px; border-left: 4px solid #3b82f6; }
            .metric-value { font-size: 24px; font-weight: bold; color: #1e40af; }
            .metric-label { color: #64748b; margin-top: 5px; }
        </style>
        
        <div class='header'>
            <h1>Advance Analytics Dashboard</h1>
            <p>As of ".now()->format('M d, Y')."</p>
        </div>

        <div class='section'>
            <h2>Overview</h2>
            <div class='metrics'>
                <div class='metric'>
                    <div class='metric-value'>{$data['overview']['total_advances']}</div>
                    <div class='metric-label'>Total Advances</div>
                </div>
                <div class='metric'>
                    <div class='metric-value'>$".number_format($data['overview']['total_amount_disbursed'], 0)."</div>
                    <div class='metric-label'>Total Amount Disbursed</div>
                </div>
                <div class='metric'>
                    <div class='metric-value'>$".number_format($data['overview']['total_outstanding'], 0)."</div>
                    <div class='metric-label'>Total Outstanding</div>
                </div>
                <div class='metric'>
                    <div class='metric-value'>{$data['overview']['active_advances']}</div>
                    <div class='metric-label'>Active Advances</div>
                </div>
                <div class='metric'>
                    <div class='metric-value'>{$data['overview']['completed_advances']}</div>
                    <div class='metric-label'>Completed Advances</div>
                </div>
                <div class='metric'>
                    <div class='metric-value'>{$data['overview']['pending_advances']}</div>
                    <div class='metric-label'>Pending Approval</div>
                </div>
            </div>
        </div>

        <div class='section'>
            <h2>Performance Metrics</h2>
            <div class='metrics'>
                <div class='metric'>
                    <div class='metric-value'>$".number_format($data['performance_metrics']['avg_advance_amount'], 0)."</div>
                    <div class='metric-label'>Average Advance Amount</div>
                </div>
                <div class='metric'>
                    <div class='metric-value'>".number_format($data['performance_metrics']['avg_repayment_period'], 1)."</div>
                    <div class='metric-label'>Average Repayment Period (months)</div>
                </div>
                <div class='metric'>
                    <div class='metric-value'>".number_format($data['performance_metrics']['completion_rate'], 1)."%</div>
                    <div class='metric-label'>Completion Rate</div>
                </div>
                <div class='metric'>
                    <div class='metric-value'>$".number_format($data['performance_metrics']['avg_monthly_deduction'], 0)."</div>
                    <div class='metric-label'>Average Monthly Deduction</div>
                </div>
            </div>
        </div>";

        return $html;
    }
}
