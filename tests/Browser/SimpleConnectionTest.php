<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

class SimpleConnectionTest extends JavaScriptDuskTestCase
{
    /**
     * Test basic connection to application.
     */
    public function test_simple_connection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(1000)
                ->assertTitle('HRM-Base');
        });
    }
}
