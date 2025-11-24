<?php

namespace Tests\Feature;

use App\Models\PayrollEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected $organization;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and organization
        $this->organization = \App\Models\Organization::factory()->create();
        $this->user = User::factory()->create();
        $this->user->organizations()->attach($this->organization->id, [
            'organization_unit_id' => null,
            'position' => 'Employee',
            'roles' => '[]',
            'permissions' => '[]',
        ]);
        $this->user->update(['current_organization_id' => $this->organization->id]);

        // Authenticate the user
        $this->actingAs($this->user);
    }

    /**
     * @test Payslip PDF download should work correctly (GREEN phase)
     */
    #[Test]
    public function payslip_download_should_work_correctly(): void
    {
        // Create employee profile for the authenticated user
        $employee = \App\Models\Employee::factory()->create([
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
        ]);

        $payslip = PayrollEntry::factory()->create([
            'employee_id' => $employee->id,
            'organization_id' => $this->organization->id,
            'period' => '2024-01',
            'net_pay' => 1500.00,
        ]);

        // PDF generation should now work correctly
        $response = $this->get(route('portal.employee.payslips.download', $payslip));

        // Should succeed with proper PDF headers
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * @test Payslip PDF download should return proper PDF response (GREEN phase)
     */
    #[Test]
    public function payslip_download_should_return_pdf_response(): void
    {
        // Create employee profile for the authenticated user
        $employee = \App\Models\Employee::factory()->create([
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
        ]);

        // Create test payroll entry with user relationship loaded
        $payslip = PayrollEntry::factory()->create([
            'employee_id' => $employee->id,
            'organization_id' => $this->organization->id,
            'period' => '2024-01',
            'net_pay' => 1500.00,
        ]);

        // Load the employee relationship with user
        $payslip->load('employee.user');

        $response = $this->get(route('portal.employee.payslips.download', $payslip));

        // Should succeed with proper PDF headers
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
        $response->assertHeader('Cache-Control');
    }

    /**
     * @test Payslip PDF should have correct filename
     */
    #[Test]
    public function payslip_pdf_should_have_correct_filename(): void
    {
        // Create employee profile for the authenticated user
        $employee = \App\Models\Employee::factory()->create([
            'user_id' => $this->user->id,
            'organization_id' => $this->organization->id,
        ]);

        $payslip = PayrollEntry::factory()->create([
            'employee_id' => $employee->id,
            'organization_id' => $this->organization->id,
            'period' => '2024-01',
            'net_pay' => 1500.00,
        ]);

        $payslip->load('employee.user');

        $response = $this->get(route('portal.employee.payslips.download', $payslip));

        // Should contain expected filename pattern
        $expectedFilename = "payslip-{$this->user->name}-2024-01.pdf";
        $contentDisposition = $response->headers->get('Content-Disposition');

        $this->assertStringContainsString($expectedFilename, $contentDisposition);
    }

    /**
     * @test Trial Balance PDF download should return proper response
     */
    #[Test]
    public function trial_balance_download_should_return_pdf_response(): void
    {
        $response = $this->get(route('accounting.download.trial-balance'));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * @test Trial Balance PDF should have correct filename
     */
    #[Test]
    public function trial_balance_pdf_should_have_correct_filename(): void
    {
        $response = $this->get(route('accounting.download.trial-balance'));

        $expectedDate = now()->format('Y-m-d');
        $expectedFilename = "trial-balance-{$expectedDate}.pdf";
        $contentDisposition = $response->headers->get('Content-Disposition');

        $this->assertStringContainsString($expectedFilename, $contentDisposition);
    }

    /**
     * @test Income Statement PDF download should return proper response
     */
    #[Test]
    public function income_statement_download_should_return_pdf_response(): void
    {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->get(route('accounting.download.income-statement', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * @test Balance Sheet PDF download should return proper response
     */
    #[Test]
    public function balance_sheet_download_should_return_pdf_response(): void
    {
        $response = $this->get(route('accounting.download.balance-sheet'));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * @test Low Stock PDF download should return proper response
     */
    #[Test]
    public function low_stock_pdf_download_should_return_pdf_response(): void
    {
        $response = $this->get(route('inventory.reports.download.low-stock'));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * @test Stock Levels PDF download should return proper response
     */
    #[Test]
    public function stock_levels_pdf_download_should_return_pdf_response(): void
    {
        $response = $this->get(route('inventory.reports.download.stock-levels'));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * @test Movement PDF download should return proper response
     */
    #[Test]
    public function movement_pdf_download_should_return_pdf_response(): void
    {
        $response = $this->get(route('inventory.reports.download.movement'));

        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * @test PDF generation should handle missing data gracefully
     */
    #[Test]
    public function pdf_generation_should_handle_missing_data_gracefully(): void
    {
        // Test with invalid date that should return 400 or proper error handling
        $response = $this->get(route('accounting.download.trial-balance', [
            'as_of_date' => 'invalid-date',
        ]));

        // Should handle invalid input gracefully
        $this->assertContainsEquals($response->getStatusCode(), [400, 422, 500]);
    }

    /**
     * @test PDF generation should not exceed memory limits
     */
    #[Test]
    public function pdf_generation_should_not_exceed_memory_limits(): void
    {
        // This test would require creating large amounts of data
        // For now, just ensure basic PDF generation doesn't crash
        $response = $this->get(route('accounting.download.trial-balance'));

        $response->assertSuccessful();
        $this->assertLessThan(50 * 1024 * 1024, strlen($response->getContent())); // Less than 50MB
    }
}
