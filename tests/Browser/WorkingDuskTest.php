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
                ->assertTitle('hrm-laravel-base');
        });
    }

    /**
     * Test that page has basic structure.
     */
    public function test_page_structure(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('body', 10)
                ->assertPresent('body');
        });
    }

    /**
     * Test that we can navigate to different pages.
     */
    public function test_basic_navigation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('body', 10);

            // Check if we can visit a known route
            $browser->visit('/login')
                ->assertSee('Login');
        });
    }
}
