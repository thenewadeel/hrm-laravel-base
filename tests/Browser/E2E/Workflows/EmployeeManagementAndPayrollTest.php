<?php

namespace Tests\Browser\E2E\Workflows;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\BaseBrowserTest;
use Tests\Browser\E2E\Concerns\HandlesE2ETestSetup;
use Tests\Browser\E2E\Concerns\HandlesWorkflowAssertions;
use Tests\Browser\E2E\Assertions\E2EAssertions;
use Tests\Browser\E2E\Fixtures\E2ETestFixtures;

class EmployeeManagementAndPayrollTest extends BaseBrowserTest
{
    use HandlesE2ETestSetup, HandlesWorkflowAssertions;

    protected $testData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testData = E2ETestFixtures::createOrganizationSetup();
    }

    protected function tearDown(): void
    {
        E2ETestFixtures::cleanup($this->testData);
        parent::tearDown();
    }

    /**
     * Test complete employee management and payroll processing workflow.
     */
    public function test_complete_employee_management_and_payroll_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];
            $organization = $this->testData['organization'];

            // Step 1: Login as admin
            $browser->loginAs($admin)
                ->visit('/')
                ->waitForText($organization->name, 10);

            E2EAssertions::assertPageTitle($browser, 'Dashboard');
            E2EAssertions::assertDataIsolation($browser, $organization->id);

            // Step 2: Navigate to HR module
            $this->navigateToModule($browser, 'HR');
            $this->waitForPageLoad($browser);

            // Step 3: Create new employee
            $browser->clickLink('Add Employee')
                ->waitFor('.employee-form', 10)
                ->type('first_name', 'New')
                ->type('last_name', 'Employee')
                ->type('email', 'new.employee@company.com')
                ->type('employee_id', 'EMP004')
                ->select('employment_type', 'full_time')
                ->type('salary', 65000)
                ->select('department', 'Engineering')
                ->type('position', 'Software Engineer')
                ->type('hire_date', now()->format('Y-m-d'))
                ->type('phone', '+1234567890')
                ->type('address', '456 New Street')
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Employee created successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'employee_creation');

            // Step 4: Verify employee is created
            $browser->assertSeeIn('.employees-table', 'New Employee')
                ->assertSeeIn('.employees-table', 'new.employee@company.com')
                ->assertSeeIn('.employees-table', 'EMP004')
                ->assertSeeIn('.employees-table', 'Software Engineer');

            // Step 5: Setup employee payroll details
            $browser->clickLink('New Employee')
                ->waitFor('.employee-details', 10)
                ->clickLink('Manage Payroll')
                ->waitFor('.payroll-management', 10);

            // Add salary components
            $browser->clickLink('Add Allowance')
                ->waitFor('.allowance-form', 5)
                ->select('allowance_type', 'housing')
                ->type('amount', 1500.00)
                ->type('effective_date', now()->format('Y-m-d'))
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Allowance added successfully');

            $browser->clickLink('Add Deduction')
                ->waitFor('.deduction-form', 5)
                ->select('deduction_type', 'tax')
                ->type('amount', 800.00)
                ->type('effective_date', now()->format('Y-m-d'))
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Deduction added successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'payroll_setup');

            // Step 6: Process monthly payroll
            $browser->clickLink('Process Payroll')
                ->waitFor('.payroll-processing', 10)
                ->select('payroll_period', 'monthly')
                ->type('period_start', now()->startOfMonth()->format('Y-m-d'))
                ->type('period_end', now()->endOfMonth()->format('Y-m-d'))
                ->check('employee_ids[]', 3) // Select new employee
                ->click('button[data-action="calculate_payroll"]');

            $this->waitForPageLoad($browser);

            // Verify payroll calculation
            $browser->assertSeeIn('[data-gross-salary]', '65,000.00')
                ->assertSeeIn('[data-total-allowances]', '18,000.00') // 1500 * 12 months
                ->assertSeeIn('[data-total-deductions]', '9,600.00') // 800 * 12 months
                ->assertSeeIn('[data-annual-salary]', '73,400.00');

            E2EAssertions::assertPayrollCalculation($browser, [
                'gross_salary' => 65000.00,
                'total_deductions' => 800.00,
                'net_salary' => 64200.00,
                'tax_amount' => 800.00,
            ]);

            // Step 7: Approve and process payroll
            $browser->click('button[data-action="approve_payroll"]')
                ->waitFor('.confirmation-modal', 5)
                ->click('button[data-confirm="true"]');

            E2EAssertions::assertSuccessMessage($browser, 'Payroll processed successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'payroll_processing');

            // Step 8: Verify financial records
            $browser->clickLink('View Financial Records')
                ->waitFor('.financial-records', 10);

            E2EAssertions::assertFinancialTransaction($browser, [
                'id' => 'PAYROLL001',
                'amount' => 64200.00,
                'type' => 'salary_payment',
                'date' => now()->format('Y-m-d'),
            ]);

            // Step 9: Verify double-entry bookkeeping
            E2EAssertions::assertDoubleEntryBalanced($browser, 'PAYROLL001');

            // Step 10: Generate payslip
            $browser->clickLink('Generate Payslip')
                ->waitFor('.payslip-preview', 10)
                ->assertSee('New Employee')
                ->assertSee('65,000.00')
                ->assertSee('6,416.67') // Monthly net salary
                ->assertSee('Software Engineer')
                ->click('button[data-action="download_payslip"]');

            E2EAssertions::assertExportGenerated($browser, 'pdf');

            // Step 11: Setup employee loan
            $browser->visit('/hrm/employees')
                ->waitFor('.employees-table', 10)
                ->clickLink('New Employee')
                ->waitFor('.employee-details', 10)
                ->clickLink('Manage Loans')
                ->waitFor('.loan-management', 10)
                ->clickLink('Add Loan')
                ->waitFor('.loan-form', 5)
                ->type('loan_amount', 10000.00)
                ->select('loan_type', 'personal')
                ->type('interest_rate', 5.00)
                ->type('loan_term_months', 12)
                ->type('monthly_repayment', 856.07)
                ->type('start_date', now()->addMonth()->format('Y-m-d'))
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Loan added successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'loan_setup');

            // Step 12: Process salary increment
            $browser->clickLink('Manage Increments')
                ->waitFor('.increment-management', 10)
                ->clickLink('Add Increment')
                ->waitFor('.increment-form', 5)
                ->type('increment_amount', 5000.00)
                ->select('increment_type', 'annual')
                ->type('effective_date', now()->addMonths(3)->format('Y-m-d'))
                ->type('reason', 'Performance based increment')
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Increment added successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'increment_processing');

            // Step 13: Verify updated salary
            $browser->clickLink('View Details')
                ->waitFor('.employee-details', 10)
                ->assertSeeIn('[data-current-salary]', '65,000.00')
                ->assertSeeIn('[data-future-salary]', '70,000.00');

            // Step 14: Test attendance integration
            $browser->clickLink('Manage Attendance')
                ->waitFor('.attendance-management', 10)
                ->clickLink('Mark Attendance')
                ->waitFor('.attendance-form', 5)
                ->select('employee_id', 3) // New employee
                ->select('attendance_status', 'present')
                ->type('attendance_date', now()->format('Y-m-d'))
                ->type('check_in_time', '09:00')
                ->type('check_out_time', '18:00')
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Attendance marked successfully');

            // Step 15: Generate attendance report
            $browser->clickLink('Generate Report')
                ->waitFor('.attendance-report', 10)
                ->assertSee('New Employee')
                ->assertSeeIn('[data-attendance-days]', '1')
                ->assertSeeIn('[data-attendance-percentage]', '100.00%');

            // Step 16: Test employee performance management
            $browser->clickLink('Manage Performance')
                ->waitFor('.performance-management', 10)
                ->clickLink('Add Review')
                ->waitFor('.performance-review-form', 5)
                ->select('employee_id', 3)
                ->select('review_period', 'quarterly')
                ->type('review_date', now()->format('Y-m-d'))
                ->select('performance_rating', 'exceeds_expectations')
                ->type('review_comments', 'Excellent performance and dedication')
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Performance review added successfully');

            // Step 17: Generate comprehensive employee report
            $browser->clickLink('Employee Reports')
                ->waitFor('.employee-reports', 10)
                ->select('report_type', 'comprehensive')
                ->check('employee_ids[]', 3)
                ->click('button[data-action="generate_report"]')
                ->waitFor('.report-content', 15);

            $browser->assertSee('New Employee')
                ->assertSee('65,000.00')
                ->assertSee('Software Engineer')
                ->assertSee('Engineering')
                ->assertSee('exceeds_expectations');

            E2EAssertions::assertExportGenerated($browser, 'excel');

            // Step 18: Test audit trail
            E2EAssertions::assertAuditTrailEntry($browser, 'employee_created', $admin->name);
            E2EAssertions::assertAuditTrailEntry($browser, 'payroll_processed', $admin->name);
            E2EAssertions::assertAuditTrailEntry($browser, 'loan_added', $admin->name);
            E2EAssertions::assertAuditTrailEntry($browser, 'increment_processed', $admin->name);

            // Step 19: Test responsive design
            $this->assertResponsiveDesign($browser, function (Browser $browser, string $viewport) {
                $browser->visit('/hrm/employees')
                    ->waitFor('.employees-table', 10)
                    ->assertPresent('.employees-table');

                if ($viewport === 'mobile') {
                    $browser->assertPresent('.mobile-employee-card')
                        ->assertMissing('.desktop-employee-table');
                } else {
                    $browser->assertPresent('.desktop-employee-table');
                }
            });

            // Step 20: Test search and filtering
            $browser->visit('/hrm/employees')
                ->waitFor('.employees-table', 10);

            E2EAssertions::assertSearchResults($browser, 'New Employee', ['New Employee', 'new.employee@company.com']);

            $browser->select('filter_department', 'Engineering')
                ->pause(500)
                ->assertSeeIn('.employees-table', 'New Employee')
                ->assertDontSeeIn('.employees-table', 'Jane Smith'); // Marketing

            $browser->select('filter_employment_type', 'full_time')
                ->pause(500)
                ->assertSeeIn('.employees-table', 'New Employee')
                ->assertDontSeeIn('.employees-table', 'Bob Wilson'); // Part-time

            // Step 21: Test error handling
            $this->assertErrorHandling($browser, 'invalid_data', function (Browser $browser) {
                $browser->visit('/hrm/employees/create')
                    ->waitFor('.employee-form', 10)
                    ->type('email', 'invalid-email')
                    ->type('salary', 'invalid-salary')
                    ->click('button[type="submit"]')
                    ->waitFor('.error-message', 5);
            });

            // Step 22: Verify data persistence
            $this->assertDataPersistence($browser, [
                '[data-employee-count]' => '4', // Original 3 + 1 new
                '[data-total-payroll]' => '204,500.00', // Sum of all salaries
                '[data-active-loans]' => '1',
            ]);

            // Step 23: Test performance
            $this->assertPageLoadPerformance($browser, 3000);

            // Step 24: Test accessibility
            E2EAssertions::assertAccessibilityFeatures($browser);

            // Final verification
            E2EAssertions::assertWorkflowCompletion($browser, 'complete_employee_management', true);
        });
    }

    /**
     * Test payroll processing with multiple employees.
     */
    public function test_bulk_payroll_processing(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];

            $browser->loginAs($admin)
                ->visit('/hrm/payroll')
                ->waitFor('.payroll-management', 10);

            // Select all employees for bulk payroll
            $browser->check('select_all_employees')
                ->click('button[data-action="bulk_calculate"]')
                ->waitFor('.bulk-payroll-results', 15);

            // Verify all employees are included
            $browser->assertSeeIn('[data-total-employees]', '3')
                ->assertSeeIn('[data-total-gross-salary]', '145,000.00') // 60000 + 55000 + 30000
                ->assertSeeIn('[data-total-net-salary]', '142,200.00'); // After deductions

            // Process bulk payroll
            $browser->click('button[data-action="bulk_approve"]')
                ->waitFor('.confirmation-modal', 5)
                ->click('button[data-confirm="true"]');

            E2EAssertions::assertSuccessMessage($browser, 'Bulk payroll processed successfully');

            // Verify financial records
            $browser->clickLink('View Financial Records')
                ->waitFor('.financial-records', 10)
                ->assertSeeIn('[data-total-amount]', '142,200.00');
        });
    }

    /**
     * Test employee termination workflow.
     */
    public function test_employee_termination_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];

            $browser->loginAs($admin)
                ->visit('/hrm/employees')
                ->waitFor('.employees-table', 10)
                ->clickLink($this->testData['employees']['bob_wilson']->name)
                ->waitFor('.employee-details', 10)
                ->clickLink('Terminate Employee')
                ->waitFor('.termination-form', 5)
                ->type('termination_date', now()->addMonth()->format('Y-m-d'))
                ->select('termination_reason', 'contract_end')
                ->type('termination_notes', 'Contract period completed')
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Employee termination processed successfully');

            // Verify employee status
            $browser->assertSeeIn('[data-employment-status]', 'Terminated')
                ->assertSeeIn('[data-termination-date]', now()->addMonth()->format('Y-m-d'));

            // Verify final settlement calculation
            $browser->clickLink('Calculate Final Settlement')
                ->waitFor('.settlement-calculation', 10)
                ->assertSeeIn('[data-final-salary]', '2,500.00') // Pro-rated for month
                ->assertSeeIn('[data-total-settlement]', '2,500.00');
        });
    }

    /**
     * Test payroll calculation accuracy with various scenarios.
     */
    public function test_payroll_calculation_accuracy(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];

            $browser->loginAs($admin)
                ->visit('/hrm/payroll/calculator')
                ->waitFor('.payroll-calculator', 10);

            // Test scenario 1: Basic salary
            $browser->type('gross_salary', 60000)
                ->click('button[data-action="calculate"]')
                ->waitFor('.calculation-results', 5);

            $browser->assertSeeIn('[data-monthly-gross]', '5,000.00')
                ->assertSeeIn('[data-annual-gross]', '60,000.00');

            // Test scenario 2: With allowances
            $browser->type('housing_allowance', 1500)
                ->type('transport_allowance', 500)
                ->click('button[data-action="calculate"]')
                ->waitFor('.calculation-results', 5);

            $browser->assertSeeIn('[data-total-allowances]', '2,000.00')
                ->assertSeeIn('[data-new-monthly-gross]', '7,000.00');

            // Test scenario 3: With deductions
            $browser->type('tax_deduction', 800)
                ->type('insurance_deduction', 200)
                ->click('button[data-action="calculate"]')
                ->waitFor('.calculation-results', 5);

            $browser->assertSeeIn('[data-total-deductions]', '1,000.00')
                ->assertSeeIn('[data-net-salary]', '6,000.00');

            // Test scenario 4: With loan repayment
            $browser->type('loan_repayment', 500)
                ->click('button[data-action="calculate"]')
                ->waitFor('.calculation-results', 5);

            $browser->assertSeeIn('[data-final-net-salary]', '5,500.00')
                ->assertSeeIn('[data-take-home]', '5,500.00');
        });
    }

    /**
     * Test employee data privacy and security.
     */
    public function test_employee_data_privacy(): void
    {
        $this->browse(function (Browser $browser) {
            $manager = $this->testData['users']['manager'];
            $member = $this->testData['users']['member'];

            // Test manager access
            $browser->loginAs($manager)
                ->visit('/hrm/employees')
                ->waitFor('.employees-table', 10);

            E2EAssertions::assertUserPermissions($browser, 
                ['view_employees', 'view_attendance'], 
                ['delete_employees', 'manage_payroll']
            );

            // Test member access (should be restricted)
            $browser->loginAs($member)
                ->visit('/hrm/employees')
                ->assertForbidden();

            // Test data masking for sensitive information
            $browser->loginAs($manager)
                ->visit('/hrm/employees/' . $this->testData['employees']['john_doe']->id)
                ->waitFor('.employee-details', 10);

            // Salary should be visible to manager
            $browser->assertSeeIn('[data-salary]', '60,000.00');

            // But SSN and other sensitive data should be masked
            $browser->assertSeeIn('[data-ssn]', '***-**-****');
        });
    }
}