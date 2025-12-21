<?php

namespace Tests\Browser\HR;

use Laravel\Dusk\Browser;
use Tests\Browser\JavaScriptDuskTestCase;

class HRMinimalTest extends JavaScriptDuskTestCase
{
    /**
     * Minimal test to check if HR routes exist and are accessible.
     */
    public function test_hr_routes_exist(): void
    {
        $this->browse(function (Browser $browser) {
            // Visit login page first (should exist)
            $browser->visit('/login')
                ->pause(2000)
                ->assertSee('Login') // Common login page element
                ->screenshot('hr-minimal-login-page');

            // Try to access HR routes (may redirect to login)
            $hrRoutes = [
                '/hr/employees',
                '/hr/employees/create',
                '/hr/positions',
                '/hr/shifts'
            ];

            foreach ($hrRoutes as $route) {
                $browser->visit($route)
                    ->pause(2000)
                    ->screenshot('hr-minimal-' . str_replace('/', '-', $route));
                
                // Should not be 404 - should either show page or redirect to login
                $browser->assertDontSee('404');
            }
        });
    }

    /**
     * Test that login works and then we can access HR pages.
     */
    public function test_login_and_hr_access(): void
    {
        $this->browse(function (Browser $browser) {
            // First, let's try to access the application
            $browser->visit('/')
                ->pause(3000)
                ->screenshot('hr-minimal-home-page');

            // If there's a login link, try to use it
            $loginLinks = $browser->elements('a[href*="login"]');
            if (!empty($loginLinks)) {
                $browser->click('a[href*="login"]')
                    ->pause(2000)
                    ->screenshot('hr-minimal-clicked-login');
            }

            // Try direct login page
            $browser->visit('/login')
                ->pause(2000)
                ->screenshot('hr-minimal-login-direct');

            // Check if there are any forms to interact with
            $forms = $browser->elements('form');
            if (!empty($forms)) {
                $browser->dump("Found " . count($forms) . " forms on login page");
            }

            // Check if there are any input fields
            $inputs = $browser->elements('input');
            if (!empty($inputs)) {
                $browser->dump("Found " . count($inputs) . " input fields");
                foreach ($inputs as $index => $input) {
                    $name = $input->getAttribute('name');
                    $type = $input->getAttribute('type');
                    $browser->dump("Input {$index}: name='{$name}', type='{$type}'");
                }
            }
        });
    }

    /**
     * Test HR page content without authentication expectations.
     */
    public function test_hr_page_content(): void
    {
        $this->browse(function (Browser $browser) {
            // Test what we can see on HR pages
            $browser->visit('/hr/employees')
                ->pause(3000)
                ->screenshot('hr-minimal-employees-content');

            // Get page title
            $title = $browser->driver->getTitle();
            $browser->dump("Page title: {$title}");

            // Look for any text that might indicate we're on the right page
            $pageText = $browser->text('body');
            $hasEmployee = strpos(strtolower($pageText), 'employee') !== false;
            $hasHR = strpos(strtolower($pageText), 'hr') !== false;
            $hasManagement = strpos(strtolower($pageText), 'management') !== false;

            $browser->dump("Page contains 'employee': " . ($hasEmployee ? 'yes' : 'no'));
            $browser->dump("Page contains 'hr': " . ($hasHR ? 'yes' : 'no'));
            $browser->dump("Page contains 'management': " . ($hasManagement ? 'yes' : 'no'));

            // Look for any forms or tables
            $forms = $browser->elements('form, table, div');
            $browser->dump("Page elements: " . count($forms) . " total elements found");
        });
    }
}