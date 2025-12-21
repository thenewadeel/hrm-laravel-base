<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

class SimpleDuskTest extends JavaScriptDuskTestCase
{
    /**
     * Test basic page load without database dependencies.
     */
    public function test_simple_page_load(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(2000)
                ->assertTitle('hrm-laravel-base');
        });
    }

    /**
     * Test that we can access the login page.
     */
    public function test_login_page_accessible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->pause(1000)
                ->screenshot('login-page-debug')
                ->assertPathIs('/login'); // Check path instead
        });
    }
}
