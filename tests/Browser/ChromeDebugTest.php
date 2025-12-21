<?php

namespace Tests\Browser;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ChromeDebugTest extends DuskTestCase
{
    /**
     * Override driver with minimal Chrome options for debugging.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-extensions',
            '--disable-plugins',
            '--disable-images',
            '--verbose',
            '--window-size=1920,1080',
        ]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

        return RemoteWebDriver::create(
            'http://127.0.0.1:9515',
            $capabilities
        );
    }

    /**
     * Test to see what content we actually get.
     */
    public function test_debug_content(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('about:blank')
                ->pause(2000);
            
            $source = $browser->driver->getPageSource();
            $title = $browser->driver->getTitle();
            $url = $browser->driver->getCurrentURL();
            
            echo "\n=== DEBUG INFO ===\n";
            echo "URL: " . $url . "\n";
            echo "Title: " . $title . "\n";
            echo "Source length: " . strlen($source) . "\n";
            echo "First 500 chars:\n" . substr($source, 0, 500) . "\n";
            echo "==================\n";
            
            // Take screenshot for visual debugging
            $browser->screenshot('chrome_debug_test');
        });
    }
}