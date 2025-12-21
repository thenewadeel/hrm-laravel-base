<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

class MinimalDuskTest extends JavaScriptDuskTestCase
{
    /**
     * Minimal test without database setup.
     */
    public function test_minimal_page_load(): void
    {
        // Skip database setup for this test
        $this->beforeApplicationDestroyed(function () {
            // No cleanup needed
        });

        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(2000)
                ->assertTitle('HRM-Base');
        });
    }
}
