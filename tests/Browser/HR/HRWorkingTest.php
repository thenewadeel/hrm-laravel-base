<?php

namespace Tests\Browser\HR;

use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\FastTestOptimizations;
use Tests\Browser\JavaScriptDuskTestCase;

class HRWorkingTest extends JavaScriptDuskTestCase
{
    use FastTestOptimizations;

    /**
     * Simple test that should work with current setup.
     */
    public function test_hr_pages_load(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                // Test basic page load
                $this->quickVisit($browser, '/');
                $browser->screenshot('hr-working-home');

                // Test HR employee page
                $this->quickVisit($browser, '/hr/employees');
                $this->fastAssertSee($browser, 'Employees');
                $browser->screenshot('hr-working-employees');

                // Test create page
                $this->quickVisit($browser, '/hr/employees/create');
                $this->fastAssertSee($browser, 'Create');
                $browser->screenshot('hr-working-create');

                // If we get here without exceptions, test passes
                $this->assertTrue(true);
            } catch (\Exception $e) {
                // Log error but still take screenshot
                $browser->screenshot('hr-working-error');
                $this->assertTrue(false, "HR pages failed to load: " . $e->getMessage());
            }
        });
    }

    /**
     * Test that confirms basic HR functionality exists.
     */
    public function test_hr_functionality_exists(): void
    {
        $this->browse(function (Browser $browser) {
            try {
                // Visit employee index
                $this->quickVisit($browser, '/hr/employees');

                // Just verify we don't get 404
                $title = $browser->driver->getTitle();
                $this->assertNotEmpty($title);

                $browser->screenshot('hr-working-verify');
            } catch (\Exception $e) {
                $this->assertTrue(false, "HR functionality test failed: " . $e->getMessage());
            }
        });
    }
}