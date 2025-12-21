<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

class BasicDuskTest extends JavaScriptDuskTestCase
{
    /**
     * Test that application homepage loads correctly.
     */
    public function test_homepage_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/simple-test')
                ->assertTitle('Simple Test')
                ->assertSee('Simple Test Page');
        });
    }

    /**
     * Test that page elements are present.
     */
    public function test_page_elements_present(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/simple-test')
                ->pause(1000)
                ->assertPresent('h1')
                ->assertSee('Simple Test Page');
        });
    }

    /**
     * Test navigation works.
     */
    public function test_navigation_works(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/simple-test')
                ->pause(1000)
                ->assertSee('Simple Test Page')
                ->assertSee('This is a simple test');
        });
    }
}
