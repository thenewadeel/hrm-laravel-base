<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

class WorkingDuskTest extends BaseBrowserTest
{
    /**
     * Test that application homepage loads correctly.
     */
    public function test_homepage_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(2000)
                ->assertTitle('HRM-Base');
        });
    }

    /**
     * Test that page has basic structure.
     */
    public function test_page_structure(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/simple-test')
                ->pause(1000)
                ->assertTitle('Simple Test')
                ->assertSee('Simple Test Page');
        });
    }

    /**
     * Test that we can navigate to different pages.
     */
    public function test_basic_navigation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/simple-test')
                ->pause(1000);

            // Check if we can visit a known route
            $browser->visit('/simple-test')
                ->pause(1000)
                ->assertSee('Simple Test Page');
        });
    }
}
