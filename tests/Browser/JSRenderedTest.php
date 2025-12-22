<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class JSRenderedTest extends DuskTestCase
{
    /**
     * Test with JavaScript content rendering.
     */
    public function test_js_content(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->waitFor('#email', 10) // Wait for email input field
                ->assertPresent('#email')
                ->assertPresent('#password')
                ->assertPathIs('/login');

            // echo "\n=== JS RENDERED SUCCESSFULLY ===\n";
            // echo "Login page rendered with JavaScript content\n";
            // echo "==============================\n";
        });
    }

    /**
     * Test homepage with JS content.
     */
    public function test_homepage_js(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(1000) // Brief pause for page load
                ->assertTitleContains('HRM-Base')
                ->assertSourceHas('<html');

            // echo "\n=== HOMEPAGE JS SUCCESS ===\n";
            // echo "Homepage loaded with JavaScript enabled\n";
            // echo "===============================\n";
        });
    }
}
