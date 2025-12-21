<?php

namespace Tests\Browser\HR;

use App\Models\Employee;
use Laravel\Dusk\Browser;
use Tests\Browser\JavaScriptDuskTestCase;

class HREmployeeWorkflowTest extends JavaScriptDuskTestCase
{
    /**
     * Test complete employee creation and viewing workflow.
     */
    public function test_complete_employee_workflow(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            // Step 1: Start at employee index
            $browser->visit('/hr/employees')
                ->pause(2000)
                ->waitForText('Employee Management', 10)
                ->assertSee('No employees found') // Should be empty initially
                ->screenshot('hr-workflow-step-1-index');

            // Step 2: Go to create page
            $browser->clickLink('Add Employee')
                ->pause(2000)
                ->assertPathIs('/hr/employees/create')
                ->assertSee('Add Employee')
                ->screenshot('hr-workflow-step-2-create-page');

            // Step 3: Fill the form
            $timestamp = time();
            $email = "test.employee.{$timestamp}@example.com";
            
            $browser->type('input[name="first_name"]', 'Jane')
                ->type('input[name="last_name"]', 'Smith')
                ->type('input[name="email"]', $email)
                ->type('input[name="password"]', 'password123')
                ->type('input[name="password_confirmation"]', 'password123')
                ->pause(1000)
                ->screenshot('hr-workflow-step-3-form-filled');

            // Step 4: Submit form
            $browser->press('Create Employee')
                ->pause(3000)
                ->assertPathIs('/hr/employees') // Should redirect to index
                ->screenshot('hr-workflow-step-4-after-creation');

            // Step 5: Verify employee appears in list
            $browser->assertSee($email)
                ->assertSee('Jane Smith')
                ->assertSee('Active')
                ->screenshot('hr-workflow-step-5-employee-in-list');
        });
    }

    /**
     * Test employee search and filter functionality.
     */
    public function test_employee_search_and_filter(): void
    {
        // First create an employee to test with
        $this->createBrowserWithOrganization(function ($browser) {
            $organization = $this->getCurrentOrganization();
            
            // Create a test employee using factory
            $employee = Employee::factory()->create([
                'organization_id' => $organization->id,
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.johnson@example.com',
            ]);

            // Visit employee index
            $browser->visit('/hr/employees')
                ->pause(2000)
                ->waitForText('Employee Management', 10)
                ->assertSee('Michael Johnson')
                ->screenshot('hr-search-step-1-initial');

            // Test search by name
            $browser->type('input[name="search"]', 'Michael')
                ->pause(2000)
                ->assertSee('Michael Johnson')
                ->assertDontSee('Jane') // Should not show other employees
                ->screenshot('hr-search-step-2-search-michael');

            // Clear search
            $browser->clear('input[name="search"]')
                ->pause(1000)
                ->press('Filter')
                ->pause(2000)
                ->assertSee('Michael Johnson')
                ->screenshot('hr-search-step-3-cleared-search');
        });
    }

    /**
     * Test employee view and edit functionality.
     */
    public function test_employee_view_and_edit(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            $organization = $this->getCurrentOrganization();
            
            // Create a test employee
            $employee = Employee::factory()->create([
                'organization_id' => $organization->id,
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'email' => 'sarah.williams@example.com',
            ]);

            // Visit employee index and click on employee
            $browser->visit('/hr/employees')
                ->pause(2000)
                ->waitForText('Employee Management', 10)
                ->clickLink('Sarah Williams')
                ->pause(2000)
                ->assertPathBeginsWith('/hr/employees/') // Should be on employee show page
                ->assertSee('Sarah Williams')
                ->screenshot('hr-view-step-1-employee-details');

            // Navigate to edit page
            $browser->clickLink('Edit')
                ->pause(2000)
                ->assertPathBeginsWith('/hr/employees/') // Should be on edit page
                ->assertSee('Edit Employee')
                ->screenshot('hr-view-step-2-edit-page');

            // Make a small change
            $browser->type('input[name="phone"]', '555-0123')
                ->pause(1000)
                ->press('Update Employee')
                ->pause(3000)
                ->assertSee('555-0123') // Should see the updated phone number
                ->screenshot('hr-view-step-3-updated-employee');
        });
    }

    /**
     * Test that all HR pages load without major JavaScript errors.
     */
    public function test_hr_pages_load_cleanly(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            // Test employee index
            $browser->visit('/hr/employees')
                ->pause(2000)
                ->assertSee('Employee Management')
                ->screenshot('hr-clean-employee-index');

            // Test employee create
            $browser->visit('/hr/employees/create')
                ->pause(2000)
                ->assertSee('Add Employee')
                ->screenshot('hr-clean-employee-create');

            // Test positions index if it exists
            $browser->visit('/hr/positions')
                ->pause(2000)
                ->waitForText('Job Positions', 10)
                ->screenshot('hr-clean-positions-index');

            // Test shifts index if it exists
            $browser->visit('/hr/shifts')
                ->pause(2000)
                ->waitForText('Shift Management', 10)
                ->screenshot('hr-clean-shifts-index');
        });
    }
}