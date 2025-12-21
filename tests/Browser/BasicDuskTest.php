<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

class BasicDuskTest extends JavaScriptDuskTestCase
{
    /**
     * Test that the application homepage loads correctly.
     */
    public function test_homepage_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertTitle('hrm-laravel-base')
                ->assertPresent('html')
                ->assertPresent('body');
        });
    }

    /**
     * Test that page elements are present.
     */
    public function test_page_elements_present(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('body', 10)
                ->assertPresent('head')
                ->assertPresent('title')
                ->assertPresent('meta[name="viewport"]');
        });
    }

    /**
     * Test navigation works.
     */
    public function test_navigation_works(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('body', 10)
                ->assertSee('Dashboard');
        });
    }
}
