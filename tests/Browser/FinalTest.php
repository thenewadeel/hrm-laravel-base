<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FinalTest extends DuskTestCase
{
    /**
     * Test what's actually on the homepage.
     */
    public function test_homepage_content(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(2000);
            
            $title = $browser->driver->getTitle();
            $currentUrl = $browser->driver->getCurrentURL();
            
            // Verify basic page loads
            $this->assertNotEmpty($title);
            $this->assertStringContainsString('8000', $currentUrl); // Check we're on dev server
            
            $browser->screenshot('homepage_content');
        });
    }
}