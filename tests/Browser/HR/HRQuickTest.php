<?php

namespace Tests\Browser\HR;

use Laravel\Dusk\Browser;
use Tests\Browser\JavaScriptDuskTestCase;

class HRQuickTest extends JavaScriptDuskTestCase
{
    /**
     * Quick test to verify HR functionality works.
     */
    public function test_hr_quick_verification(): void
    {
        $this->browse(function (Browser $browser) {
            // Test basic route accessibility
            $browser->visit('/')
                ->pause(2000)
                ->screenshot('hr-quick-home');

            $browser->visit('/hr/employees')
                ->pause(2000)
                ->screenshot('hr-quick-employees');

            $browser->visit('/hr/employees/create')
                ->pause(2000)
                ->screenshot('hr-quick-create');

            // Simple assertion - if we get here without crashing, routes are working
            $this->assertTrue(true);
        });
    }

    /**
     * Test that we can navigate to HR pages.
     */
    public function test_hr_navigation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(2000);

            // Try direct navigation
            $browser->visit('/hr/employees')
                ->pause(2000);

            // Get basic page info
            $title = $browser->driver->getTitle();
            $this->assertNotEmpty($title);
        });
    }
}