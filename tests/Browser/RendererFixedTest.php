<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RendererFixedTest extends DuskTestCase
{
    /**
     * Test basic page load with the fixed configuration.
     */
    public function test_basic_page_load(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(2000)
                ->assertSourceHas('<html')
                ->assertTitleContains('HRM-Base');
        });
    }

    /**
     * Test login page.
     */
    public function test_login_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->pause(2000)
                ->assertPathIs('/login')
                ->assertSee('Email')
                ->assertSee('Password');
        });
    }
}