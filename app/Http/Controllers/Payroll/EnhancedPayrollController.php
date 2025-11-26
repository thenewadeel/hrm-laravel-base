<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeIncrement;
use App\Models\EmployeeLoan;
use App\Models\SalaryAdvance;
use App\Models\TaxBracket;
use App\Services\PayrollCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EnhancedPayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollCalculationService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Display payroll dashboard
     */
    public function dashboard(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));
        $organizationId = auth()->user()->currentOrganization->id;

        $summary = $this->payrollService->generatePayrollSummary($organizationId, $period);

        $pendingIncrements = EmployeeIncrement::where('organization_id', $organizationId)
            ->pending()
            ->with('employee')
            ->count();

        $pendingLoans = EmployeeLoan::where('organization_id', $organizationId)
            ->pending()
            ->with('employee')
            ->count();

        $pendingAdvances = SalaryAdvance::where('organization_id', $organizationId)
            ->pending()
            ->with('employee')
            ->count();

        return view('payroll.dashboard', compact('summary', 'period', 'pendingIncrements', 'pendingLoans', 'pendingAdvances'));
    }

    /**
     * Display payroll processing page
     */
    public function processing(Request $request)
    {
        $period = $request->get('period', now()->format('Y-m'));
        $employeeId = $request->get('employee_id');

        // Convert period string to Carbon object for view
        $period = \Carbon\Carbon::createFromFormat('Y-m', $period);

        // Get organization ID from authenticated user
        $organizationId = auth()->user()->current_organization_id ??
                        auth()->user()->organizations()->first()->id;

        // If specific employee requested, filter for that employee only
        if ($employeeId) {
            $employees = Employee::where('organization_id', $organizationId)
                ->where('id', $employeeId)
                ->where('is_active', true)
                ->with(['allowances.allowanceType', 'deductions.deductionType', 'loans', 'salaryAdvances'])
                ->get();
            $employee = $employees->first();
        } else {
            $employees = Employee::where('organization_id', $organizationId)
                ->where('is_active', true)
                ->with(['allowances.allowanceType', 'deductions.deductionType', 'loans', 'salaryAdvances'])
                ->get();
            $employee = null;
        }

        $payrollData = [];
        $totalHours = 0;
        $regularHours = 0;
        $overtimeHours = 0;
        $attendanceData = collect();

        foreach ($employees as $employee) {
            $payrollData[$employee->id] = $this->payrollService->calculateEmployeePayroll($employee, $period);

            // Calculate attendance totals for period
            $startDate = $period->copy()->startOfMonth();
            $endDate = $period->copy()->endOfMonth();

            $attendanceRecords = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
                ->where('record_date', '>=', $startDate)
                ->where('record_date', '<=', $endDate)
                ->get();

            $attendanceData = $attendanceData->merge($attendanceRecords);

            $employeeTotalHours = $attendanceRecords->sum('total_hours');
            $employeeRegularHours = $attendanceRecords->sum(function ($record) {
                return min($record->total_hours, 8); // Regular hours capped at 8 per day
            });
            $employeeOvertimeHours = $attendanceRecords->sum(function ($record) {
                return max(0, $record->total_hours - 8); // Overtime is hours beyond 8 per day
            });

            $totalHours += $employeeTotalHours;
            $regularHours += $employeeRegularHours;
            $overtimeHours += $employeeOvertimeHours;
        }

        return view('payroll.processing', compact(
            'employees',
            'employee',
            'payrollData',
            'attendanceData',
            'period',
            'totalHours',
            'regularHours',
            'overtimeHours'
        ));
    }

    /**
     * Process payroll for all employees
     */
    public function processPayroll(Request $request)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
        ]);

        $period = $request->get('period');
        $organizationId = auth()->user()->currentOrganization->id;

        $employees = Employee::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $results = $this->payrollService->processBatchPayroll($employees, $period);

        return redirect()->back()->with('success', 'Payroll processed for '.count($results).' employees');
    }

    /**
     * Display employee payroll details
     */
    public function employeePayroll(Request $request, Employee $employee)
    {
        $period = $request->get('period', now()->format('Y-m'));

        $payrollData = $this->payrollService->calculateEmployeePayroll($employee, $period);

        return view('payroll.employee-details', compact('employee', 'payrollData', 'period'));
    }

    /**
     * Display increments management page
     */
    public function increments()
    {
        $organizationId = auth()->user()->currentOrganization->id;

        $increments = EmployeeIncrement::with(['employee', 'approver'])
            ->where('organization_id', $organizationId)
            ->latest()
            ->paginate(15);

        $employees = Employee::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('payroll.increments', compact('increments', 'employees'));
    }

    /**
     * Store new increment
     */
    public function storeIncrement(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'increment_type' => 'required|in:percentage,fixed_amount',
            'increment_value' => 'required|numeric|min:0',
            'effective_date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:500',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $currentSalary = $employee->basic_salary;

        $newSalary = $request->increment_type === 'percentage'
            ? $currentSalary + ($currentSalary * ($request->increment_value / 100))
            : $currentSalary + $request->increment_value;

        EmployeeIncrement::create([
            'employee_id' => $request->employee_id,
            'organization_id' => auth()->user()->currentOrganization->id,
            'increment_type' => $request->increment_type,
            'increment_value' => $request->increment_value,
            'previous_salary' => $currentSalary,
            'new_salary' => $newSalary,
            'effective_date' => $request->effective_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Increment created successfully');
    }

    /**
     * Approve increment
     */
    public function approveIncrement(Request $request, EmployeeIncrement $increment)
    {
        $increment->approve(auth()->user(), $request->get('approval_notes'));

        return redirect()->back()->with('success', 'Increment approved successfully');
    }

    /**
     * Implement increment
     */
    public function implementIncrement(EmployeeIncrement $increment)
    {
        $increment->implement();

        return redirect()->back()->with('success', 'Increment implemented successfully');
    }

    /**
     * Display loans management page
     */
    public function loans()
    {
        $organizationId = auth()->user()->currentOrganization->id;

        $loans = EmployeeLoan::with(['employee', 'approver'])
            ->where('organization_id', $organizationId)
            ->latest()
            ->paginate(15);

        $employees = Employee::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('payroll.loans', compact('loans', 'employees'));
    }

    /**
     * Store new loan application
     */
    public function storeLoan(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'loan_type' => 'required|string|max:255',
            'principal_amount' => 'required|numeric|min:0',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'repayment_period_months' => 'required|integer|min:1|max:360',
            'disbursement_date' => 'required|date|after_or_equal:today',
            'purpose' => 'nullable|string|max:500',
        ]);

        $monthlyInstallment = EmployeeLoan::calculateMonthlyInstallment(
            $request->principal_amount,
            $request->interest_rate,
            $request->repayment_period_months
        );

        $totalInterest = EmployeeLoan::calculateTotalInterest(
            $request->principal_amount,
            $request->interest_rate,
            $request->repayment_period_months
        );

        $totalRepayment = $request->principal_amount + $totalInterest;

        EmployeeLoan::create([
            'employee_id' => $request->employee_id,
            'organization_id' => auth()->user()->currentOrganization->id,
            'loan_reference' => 'LOAN-'.date('Y').'-'.str_pad(EmployeeLoan::count() + 1, 4, '0', STR_PAD_LEFT),
            'loan_type' => $request->loan_type,
            'principal_amount' => $request->principal_amount,
            'interest_rate' => $request->interest_rate,
            'repayment_period_months' => $request->repayment_period_months,
            'monthly_installment' => $monthlyInstallment,
            'total_interest' => $totalInterest,
            'total_repayment' => $totalRepayment,
            'balance_amount' => $totalRepayment,
            'disbursement_date' => $request->disbursement_date,
            'first_payment_date' => Carbon::parse($request->disbursement_date)->addMonth(),
            'maturity_date' => Carbon::parse($request->disbursement_date)->addMonths($request->repayment_period_months),
            'purpose' => $request->purpose,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Loan application created successfully');
    }

    /**
     * Approve loan
     */
    public function approveLoan(Request $request, EmployeeLoan $loan)
    {
        $loan->approve(auth()->user(), $request->get('approval_notes'));

        return redirect()->back()->with('success', 'Loan approved successfully');
    }

    /**
     * Disburse loan
     */
    public function disburseLoan(EmployeeLoan $loan)
    {
        $loan->disburse();

        return redirect()->back()->with('success', 'Loan disbursed successfully');
    }

    /**
     * Display salary advances management page
     */
    public function advances()
    {
        $organizationId = auth()->user()->currentOrganization->id;

        $advances = SalaryAdvance::with(['employee', 'approver'])
            ->where('organization_id', $organizationId)
            ->latest()
            ->paginate(15);

        $employees = Employee::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('payroll.advances', compact('advances', 'employees'));
    }

    /**
     * Store new salary advance
     */
    public function storeAdvance(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'repayment_months' => 'required|integer|min:1|max:12',
            'reason' => 'required|string|max:500',
        ]);

        $monthlyDeduction = $request->amount / $request->repayment_months;

        SalaryAdvance::create([
            'employee_id' => $request->employee_id,
            'organization_id' => auth()->user()->currentOrganization->id,
            'advance_reference' => 'ADV-'.date('Y').'-'.str_pad(SalaryAdvance::count() + 1, 4, '0', STR_PAD_LEFT),
            'amount' => $request->amount,
            'repayment_months' => $request->repayment_months,
            'monthly_deduction' => $monthlyDeduction,
            'request_date' => now(),
            'first_deduction_month' => now()->addMonth()->format('Y-m'),
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Salary advance created successfully');
    }

    /**
     * Approve salary advance
     */
    public function approveAdvance(Request $request, SalaryAdvance $advance)
    {
        $advance->approve(auth()->user(), $request->get('approval_notes'));
        $advance->activate();

        return redirect()->back()->with('success', 'Salary advance approved and activated');
    }

    /**
     * Display tax configuration page
     */
    public function taxConfiguration()
    {
        $organizationId = auth()->user()->currentOrganization->id;

        $taxBrackets = TaxBracket::where('organization_id', $organizationId)
            ->active()
            ->orderBy('min_income')
            ->get();

        return view('payroll.tax-configuration', compact('taxBrackets'));
    }

    /**
     * Store tax bracket
     */
    public function storeTaxBracket(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'min_income' => 'required|numeric|min:0',
            'max_income' => 'nullable|numeric|min:0',
            'rate' => 'required|numeric|min:0|max:100',
            'base_tax' => 'required|numeric|min:0',
            'exemption_amount' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
        ]);

        TaxBracket::create([
            'organization_id' => auth()->user()->currentOrganization->id,
            'name' => $request->name,
            'min_income' => $request->min_income,
            'max_income' => $request->max_income,
            'rate' => $request->rate,
            'base_tax' => $request->base_tax,
            'exemption_amount' => $request->exemption_amount,
            'effective_date' => $request->effective_date,
        ]);

        return redirect()->back()->with('success', 'Tax bracket created successfully');
    }

    /**
     * Generate payroll report
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
            'format' => 'required|in:pdf,excel',
        ]);

        $period = $request->get('period');
        $organizationId = auth()->user()->currentOrganization->id;

        $summary = $this->payrollService->generatePayrollSummary($organizationId, $period);

        if ($request->get('format') === 'pdf') {
            // Generate PDF report
            return $this->generatePdfReport($summary, $period);
        } else {
            // Generate Excel report
            return $this->generateExcelReport($summary, $period);
        }
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport($summary, $period)
    {
        // Implementation for PDF generation
        return response()->json(['message' => 'PDF report generation not implemented yet']);
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($summary, $period)
    {
        // Implementation for Excel generation
        return response()->json(['message' => 'Excel report generation not implemented yet']);
    }
}
