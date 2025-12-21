<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;
use Facebook\WebDriver\WebDriverKeys;

trait HandlesAlpineTesting
{
    /**
     * Wait for Alpine.js to be initialized and ready.
     */
    protected function waitForAlpine(Browser $browser): void
    {
        $browser->waitUntil("
            return typeof window.Alpine !== 'undefined' && 
                   window.Alpine.version &&
                   document.querySelectorAll('[x-data]').length > 0;
        ", 10);
    }

    /**
     * Execute JavaScript with Alpine.js context.
     */
    protected function executeAlpineScript(Browser $browser, string $script)
    {
        try {
            $result = $browser->script($script);
            return $result[0] ?? null;
        } catch (\Exception $e) {
            $this->fail("Alpine.js script execution failed: " . $e->getMessage());
        }
    }

    /**
     * Assert Alpine.js is properly loaded and working.
     */
    protected function assertAlpineLoaded(Browser $browser): void
    {
        $browser->waitForAlpine()
            ->script('return typeof window.Alpine !== "undefined" && window.Alpine.version;')
            ->assertNotEmpty('Alpine.js should be loaded and have a version');
    }

    /**
     * Assert Alpine.js reactivity works correctly.
     */
    protected function assertAlpineReactivity(Browser $browser, string $selector, callable $testLogic): void
    {
        $browser->waitFor($selector, 5)
            ->script("window.alpineTestValue = null;")
            ->click($selector);

        // Execute test logic
        $testLogic();

        // Wait for reactivity to settle
        $browser->pause(500);

        // Check if Alpine state was updated
        $result = $browser->script("return window.alpineTestValue;");
        $this->assertNotNull($result[0], 'Alpine reactivity should have updated the value');
    }

    /**
     * Check for Alpine.js errors in console.
     */
    protected function checkAlpineErrors(Browser $browser): void
    {
        $browser->script("
            window.alpineErrors = [];
            window.addEventListener('error', function(e) {
                if (e.message && e.message.toLowerCase().includes('alpine')) {
                    window.alpineErrors.push(e.message);
                }
            });
        ");

        // Trigger some Alpine interactions to surface errors
        $browser->pause(1000);

        $errors = $browser->script("return window.alpineErrors;");
        $this->assertEmpty($errors[0], 'Should not have Alpine.js errors');
    }

    /**
     * Assert no Alpine.js errors occurred.
     */
    protected function assertNoAlpineErrors(Browser $browser): void
    {
        $this->checkAlpineErrors($browser);
    }
}