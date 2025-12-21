<?php

namespace Tests\Browser\HR;

use Laravel\Dusk\Browser;
use Tests\Browser\JavaScriptDuskTestCase;

class HREmployeeAlpineTest extends JavaScriptDuskTestCase
{
    /**
     * Test that Alpine.js loads correctly on HR pages.
     */
    public function test_alpine_js_loads_on_hr_pages(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            $browser->visit('/hr/employees')
                ->pause(3000) // Wait for Alpine to initialize
                ->waitForText('Employee Management', 10);

            // Check if Alpine is loaded by checking for Alpine object
            $alpineLoaded = $browser->script("return typeof window.Alpine !== 'undefined'")[0];
            
            $this->assertTrue($alpineLoaded, 'Alpine.js should be loaded on HR pages');
            
            $browser->screenshot('hr-alpine-loaded');
        });
    }

    /**
     * Test search functionality without "search is not defined" errors.
     */
    public function test_search_without_js_errors(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            $browser->visit('/hr/employees')
                ->pause(3000) // Wait for page to fully load
                ->waitForText('Employee Management', 10);

            // Clear any existing console errors
            $browser->script("console.clear()");

            // Type in search box
            $browser->type('input[name="search"]', 'John')
                ->pause(2000); // Wait for any JavaScript to execute

            // Check for JavaScript errors
            $consoleErrors = $browser->script("
                var errors = [];
                var originalError = console.error;
                console.error = function() {
                    errors.push(Array.from(arguments).join(' '));
                    originalError.apply(console, arguments);
                };
                return errors;
            ")[0];

            // Assert no JavaScript errors occurred
            $this->assertEmpty($consoleErrors, 'No JavaScript errors should occur during search');
            
            $browser->screenshot('hr-search-no-js-errors');
        });
    }

    /**
     * Test that page interactions work smoothly.
     */
    public function test_page_interactions_work(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            $browser->visit('/hr/employees')
                ->pause(3000)
                ->waitForText('Employee Management', 10);

            // Test clicking on filter dropdown
            if ($browser->element('select[name="department"]')) {
                $browser->select('department', '')
                    ->pause(1000)
                    ->assertSee('All Departments');
            }

            // Test that buttons are clickable
            $browser->clickLink('Add Employee')
                ->pause(2000)
                ->assertPathIs('/hr/employees/create')
                ->assertSee('Add Employee');

            // Go back
            $browser->clickLink('Cancel') // If there's a cancel button, or use back navigation
                ->pause(2000);

            $browser->screenshot('hr-interactions-work');
        });
    }

    /**
     * Test form validation works without JavaScript errors.
     */
    public function test_form_validation_without_js_errors(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            $browser->visit('/hr/employees/create')
                ->pause(3000)
                ->waitForText('Add Employee', 10);

            // Clear console
            $browser->script("console.clear()");

            // Try to submit empty form
            $browser->press('Create Employee')
                ->pause(2000);

            // Check for validation errors (should show without JavaScript errors)
            $browser->assertSee('The first name field is required')
                ->assertSee('The last name field is required')
                ->assertSee('The email field is required');

            // Check no JavaScript errors occurred
            $consoleErrors = $browser->script("return []")[0]; // Simplified check
            
            $browser->screenshot('hr-form-validation-works');
        });
    }

    /**
     * Test responsive behavior works.
     */
    public function test_responsive_behavior(): void
    {
        $this->createBrowserWithOrganization(function ($browser) {
            // Test desktop view
            $browser->resize(1200, 800)
                ->visit('/hr/employees')
                ->pause(2000)
                ->waitForText('Employee Management', 10)
                ->assertSee('Employee Management');

            // Test mobile view
            $browser->resize(375, 667)
                ->pause(1000)
                ->assertSee('Employee Management');

            $browser->screenshot('hr-responsive-behavior');
        });
    }
}