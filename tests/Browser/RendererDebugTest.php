<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RendererDebugTest extends DuskTestCase
{
    /**
     * Test minimal browser connection with different Chrome options.
     */
    public function test_minimal_connection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('about:blank')
                ->pause(1000)
                ->assertSourceHas('<html>');
        });
    }

    /**
     * Test with a simple HTML page.
     */
    public function test_simple_html(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(2000)
                ->assertSourceHas('<html');
        });
    }

    /**
     * Test browser connection without any waits.
     */
    public function test_raw_connection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('data:text/html,<html><body><h1>Test</h1></body></html>')
                ->assertSee('Test');
        });
    }
}