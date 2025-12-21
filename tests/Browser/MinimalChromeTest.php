<?php

namespace Tests\Browser;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MinimalChromeTest extends DuskTestCase
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
            '--disable-javascript', // Start without JS to test basic renderer
            '--disable-web-security',
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
     * Test absolute minimal connection.
     */
    public function test_absolute_minimal(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('about:blank')
                ->pause(1000)
                ->assertSourceHas('<html>');
        });
    }

    /**
     * Test with JavaScript enabled but minimal options.
     */
    public function test_with_minimal_js(): void
    {
        // This will use the parent driver with JS enabled
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(3000) // Longer pause for renderer
                ->assertSourceHas('<html');
        });
    }
}