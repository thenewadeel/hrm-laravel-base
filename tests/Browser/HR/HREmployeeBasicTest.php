<?php

namespace Tests\Browser\HR;

use Laravel\Dusk\Browser;
use Tests\Browser\JavaScriptDuskTestCase;

class HREmployeeBasicTest extends JavaScriptDuskTestCase
{
    /**
     * Test that the HR employee index page loads correctly.
     */
    public function test_hr_employee_index_page_loads(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            $browser->visit('/hr/employees')
                ->pause(2000)
                ->waitForText('Employee Management', 10)
                ->assertSee('Employee Management')
                ->assertSee('Add Employee')
                ->assertPathIs('/hr/employees')
                ->screenshot('hr-employee-index-loads');
        });
    }

    /**
     * Test that the employee create page loads and form is present.
     */
    public function test_hr_employee_create_page_loads(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            $browser->visit('/hr/employees/create')
                ->pause(2000)
                ->waitForText('Add Employee', 10)
                ->assertSee('Add Employee')
                ->assertSee('First Name')
                ->assertSee('Last Name')
                ->assertSee('Email')
                ->assertPathIs('/hr/employees/create')
                ->screenshot('hr-employee-create-loads');
        });
    }

    /**
     * Test employee search functionality works without JavaScript errors.
     */
    public function test_hr_employee_search_works(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            // First visit the index page
            $browser->visit('/hr/employees')
                ->pause(2000)
                ->waitForText('Employee Management', 10);

            // Test search input exists and is functional
            $browser->assertPresent('input[name="search"]')
                ->type('input[name="search"]', 'test')
                ->pause(1000)
                ->assertSee('Search') // Should still see search label
                ->screenshot('hr-employee-search-works');
        });
    }

    /**
     * Test employee creation form submission works.
     */
    public function test_hr_employee_creation_works(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            $browser->visit('/hr/employees/create')
                ->pause(2000)
                ->waitForText('Add Employee', 10);

            // Fill in the employee form with valid data
            $timestamp = time();
            $browser->type('input[name="first_name"]', 'John')
                ->type('input[name="last_name"]', 'Doe')
                ->type('input[name="email"]', "john.doe.{$timestamp}@example.com")
                ->type('input[name="password"]', 'password123')
                ->type('input[name="password_confirmation"]', 'password123')
                ->pause(1000);

            // Submit the form - look for submit button
            $browser->press('Create Employee')
                ->pause(3000)
                ->assertPathIs('/hr/employees') // Should redirect back to index
                ->screenshot('hr-employee-created');
        });
    }

    /**
     * Test that the employee index page shows empty state when no employees exist.
     */
    public function test_hr_employee_empty_state(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            $browser->visit('/hr/employees')
                ->pause(2000)
                ->waitForText('Employee Management', 10)
                ->assertSee('No employees found')
                ->assertSee('Get started by adding your first employee')
                ->assertSee('Add Employee')
                ->screenshot('hr-employee-empty-state');
        });
    }

    /**
     * Test navigation between HR pages works correctly.
     */
    public function test_hr_navigation_works(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            // Start at employee index
            $browser->visit('/hr/employees')
                ->pause(2000)
                ->waitForText('Employee Management', 10)
                ->assertSee('Employee Management');

            // Navigate to create page
            $browser->clickLink('Add Employee')
                ->pause(2000)
                ->assertPathIs('/hr/employees/create')
                ->assertSee('Add Employee');

            // Go back to index
            $browser->visit('/hr/employees')
                ->pause(2000)
                ->assertPathIs('/hr/employees')
                ->assertSee('Employee Management')
                ->screenshot('hr-navigation-works');
        });
    }
}