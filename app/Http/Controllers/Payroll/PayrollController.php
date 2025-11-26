<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PayrollController extends Controller
{
    /**
     * Mock payroll data
     */
    private function getMockPayrollData()
    {
        return [
            'period' => 'October 2025',
            'start_date' => '2025-10-01',
            'end_date' => '2025-10-31',
            'status' => 'calculated',
            'total_gross' => 500000.00,
            'total_deductions' => 120000.00,
            'total_net' => 380000.00,
            'employee_count' => 105,
            'accounts' => [
                'salary_expense' => [
                    'code' => '5001',
                    'name' => 'Salary Expense',
                    'type' => 'expense',
                ],
                'payroll_payable' => [
                    'code' => '2001',
                    'name' => 'Payroll Payable',
                    'type' => 'liability',
                ],
            ],
            'employees' => [
                [
                    'id' => 1,
                    'employee_code' => 'EMP-1001',
                    'name' => 'John Smith',
                    'initials' => 'JS',
                    'department' => 'Engineering',
                    'attendance' => [
                        'days_present' => 22,
                        'lop_days' => 0,
                    ],
                    'gross_pay' => 5000.00,
                    'deductions' => [
                        'tax' => 800.00,
                        'insurance' => 400.00,
                        'total' => 1200.00,
                    ],
                    'net_pay' => 3800.00,
                ],
                [
                    'id' => 2,
                    'employee_code' => 'EMP-1002',
                    'name' => 'Maria Johnson',
                    'initials' => 'MJ',
                    'department' => 'Sales',
                    'attendance' => [
                        'days_present' => 20,
                        'lop_days' => 2,
                    ],
                    'gross_pay' => 4545.45,
                    'deductions' => [
                        'tax' => 727.27,
                        'insurance' => 363.64,
                        'total' => 1090.91,
                    ],
                    'net_pay' => 3454.54,
                ],
                [
                    'id' => 3,
                    'employee_code' => 'EMP-1003',
                    'name' => 'Robert Smith',
                    'initials' => 'RS',
                    'department' => 'Marketing',
                    'attendance' => [
                        'days_present' => 21,
                        'lop_days' => 1,
                    ],
                    'gross_pay' => 4772.73,
                    'deductions' => [
                        'tax' => 763.64,
                        'insurance' => 381.82,
                        'total' => 1145.46,
                    ],
                    'net_pay' => 3627.27,
                ],
            ],
        ];
    }

    /**
     * Mock journal entry data
     */
    private function getMockJournalEntry()
    {
        return [
            'id' => 'JE-2025-10-PAY',
            'reference_number' => 'PAY-2025-10-001',
            'entry_date' => Carbon::now()->format('Y-m-d'),
            'description' => 'Payroll for October 2025',
            'status' => 'draft',
            'total_amount' => 500000.00,
            'line_items' => [
                [
                    'account_code' => '5001',
                    'account_name' => 'Salary Expense',
                    'debit' => 500000.00,
                    'credit' => 0.00,
                ],
                [
                    'account_code' => '2001',
                    'account_name' => 'Payroll Payable',
                    'debit' => 0.00,
                    'credit' => 380000.00,
                ],
            ],
        ];
    }

    /**
     * Display payroll processing dashboard.
     */
    public function processing(Request $request)
    {
        // Handle period parameter
        $period = $request->get('period')
            ? \Carbon\Carbon::createFromFormat('Y-m', $request->get('period'))
            : \Carbon\Carbon::now();

        // Get attendance data for the period
        $employeeId = $request->get('employee_id');
        $query = \App\Models\AttendanceRecord::whereMonth('record_date', $period->month)
            ->whereYear('record_date', $period->year);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $attendanceData = $query->get();

        // Calculate payroll metrics
        $totalHours = $attendanceData->sum('total_hours');
        $overtimeHours = round($attendanceData->sum('overtime_minutes') / 60, 2);
        $regularHours = max(0, $totalHours - $overtimeHours);

        return view('payroll.processing', [
            'attendanceData' => $attendanceData,
            'period' => $period,
            'totalHours' => $totalHours,
            'overtimeHours' => $overtimeHours,
            'regularHours' => $regularHours,
            'employee' => $employeeId ? \App\Models\Employee::find($employeeId) : null,
        ]);
    }

    /**
     * Calculate payroll for the period.
     */
    public function calculatePayroll(Request $request)
    {
        // Mock calculation process
        $payrollData = $this->getMockPayrollData();

        return back()->with([
            'success' => 'Payroll calculated successfully!',
            'calculation_summary' => [
                'gross_total' => number_format($payrollData['total_gross'], 2),
                'employee_count' => $payrollData['employee_count'],
                'period' => $payrollData['period'],
            ],
        ]);
    }

    /**
     * Generate accounting journal entry for payroll.
     */
    public function generateAccountingEntry(Request $request)
    {
        // Mock journal entry creation
        $journalEntry = $this->getMockJournalEntry();

        return response()->json([
            'success' => true,
            'message' => 'Accounting journal entry generated successfully!',
            'data' => [
                'journal_entry' => $journalEntry,
                'payroll_period' => 'October 2025',
                'generated_at' => Carbon::now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * View individual employee payslip.
     */
    public function viewPayslip($employeeId, $period)
    {
        $payrollData = $this->getMockPayrollData();
        $employee = collect($payrollData['employees'])->firstWhere('id', $employeeId);

        if (! $employee) {
            abort(404, 'Employee not found');
        }

        $payslip = [
            'employee' => $employee,
            'period' => $period,
            'issue_date' => Carbon::now()->format('M d, Y'),
            'pay_date' => Carbon::parse('last day of '.$period)->format('M d, Y'),
            'earnings' => [
                'basic_salary' => $employee['gross_pay'] * 0.6,
                'housing_allowance' => $employee['gross_pay'] * 0.25,
                'transport_allowance' => $employee['gross_pay'] * 0.15,
                'overtime' => 0.00,
                'bonus' => 0.00,
            ],
            'deductions' => $employee['deductions'],
        ];

        return view('payroll.payslip', compact('payslip'));
    }

    /**
     * Edit payroll calculation for employee.
     */
    public function editCalculation(Request $request, $employeeId)
    {
        // Mock update process
        $validated = $request->validate([
            'gross_pay' => 'required|numeric',
            'tax_amount' => 'required|numeric',
            'insurance_amount' => 'required|numeric',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payroll calculation updated successfully',
            'updated_data' => [
                'employee_id' => $employeeId,
                'gross_pay' => $validated['gross_pay'],
                'net_pay' => $validated['gross_pay'] - $validated['tax_amount'] - $validated['insurance_amount'],
                'updated_at' => Carbon::now()->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Export payroll report.
     */
    public function exportPayroll(Request $request)
    {
        $payrollData = $this->getMockPayrollData();

        // Mock export file generation
        $filename = "payroll-export-{$payrollData['period']}.csv";

        return response()->json([
            'success' => true,
            'message' => 'Payroll export generated successfully',
            'download_url' => '#', // Mock URL
            'file_name' => $filename,
            'file_size' => '245 KB',
        ]);
    }
}
