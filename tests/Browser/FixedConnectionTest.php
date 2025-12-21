<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\JavaScriptDuskTestCase;

class FixedConnectionTest extends JavaScriptDuskTestCase
{
    /**
     * Test basic browser connection with JavaScript.
     */
    public function test_js_browser_connection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(3000) // Wait for page to fully load
                ->screenshot('debug_homepage');
            
            $title = $browser->driver->getTitle();
            $url = $browser->driver->getCurrentURL();
            
            echo "\n=== DEBUG INFO ===\n";
            echo "Title: " . $title . "\n";
            echo "URL: " . $url . "\n";
            echo "==================\n";
            
            // Basic checks that should always work
            $browser->assertSourceHas('<html')
                ->assertSourceHas('<body')
                ->assertTitleContains('HRM-Base');
                
            // Verify the page loads with proper structure
            $pageSource = $browser->driver->getPageSource();
            assert(str_contains($pageSource, 'HRM-Base'), 'HRM-Base should be in page source');
            assert(str_contains($pageSource, '<title>'), 'Page should have title tag');
        });
    }

    /**
     * Test JavaScript functionality.
     */
    public function test_javascript_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(3000) // Wait for page load
                ->assertScript('typeof document !== "undefined"')
                ->assertScript('document.readyState === "complete"');
        });
    }

    /**
     * Test login page with JavaScript.
     */
    public function test_login_page_with_js(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->pause(3000) // Wait for page load
                ->assertPathIs('/login')
                ->assertSourceHas('name="email"')
                ->assertSourceHas('name="password"')
                ->assertSourceHas('type="email"')
                ->assertSourceHas('type="password"');
                
            // Check for email and password inputs
            $browser->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]')
                ->assertPresent('input[type="email"]')
                ->assertPresent('input[type="password"]');
        });
    }
}