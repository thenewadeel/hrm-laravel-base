<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

class DebugDuskTest extends BaseBrowserTest
{
    /**
     * Debug test to see what's actually on the page.
     */
    public function test_debug_page_content(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(3000)
                ->screenshot('debug-page')
                ->storeSource('debug-source');

            // Try to get page source
            $source = $browser->driver->getPageSource();
            file_put_contents(storage_path('app/debug-page-source.html'), $source);

            // Check if we can find any text
            $browser->assertTitle('hrm-laravel-base');
        });
    }
}
