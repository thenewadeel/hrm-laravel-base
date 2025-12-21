<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Concerns\OptimizedWaits;

class SimpleConnectionWorkingTest extends DuskTestCase
{
    use OptimizedWaits;

    /**
     * Test basic browser connectivity without JavaScript.
     */
    public function test_basic_page_load(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(2000)
                ->waitForText('HRM', 5)
                ->assertSourceHas('<html')
                ->assertTitleContains('HRM-Base');
        });
    }

    /**
     * Test that we can even just connect to the browser.
     */
    public function test_browser_connection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(500)  // Reduced from 1000ms to 500ms
                ->assertSourceHas('<body');
        });
    }

    /**
     * Test login page loads.
     */
    public function test_login_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->pause(2000)
                ->waitForText('Email', 5)
                ->assertPathIs('/login')
                ->assertSee('Email')
                ->assertSee('Password');
        });
    }
}
