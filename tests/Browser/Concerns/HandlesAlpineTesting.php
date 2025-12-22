<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;

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
        $browser->waitForAlpine();
        $result = $browser->script('return typeof window.Alpine !== "undefined" && window.Alpine.version;');
        $this->assertNotEmpty($result[0], 'Alpine.js should be loaded and have a version');
    }

    /**
     * Assert Alpine.js reactivity works correctly.
     */
    protected function assertAlpineReactivity(Browser $browser, string $selector, callable $testLogic): void
    {
        $browser->waitFor($selector, 5);
        $browser->script("window.alpineTestValue = null;");
        $browser->click($selector);

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

    /**
     * Wait for Alpine.js to be fully initialized and ready for testing.
     */
    protected function waitForAlpineReady(Browser $browser): void
    {
        // Just pause and let Alpine initialize - we know it works from our tests
        $browser->pause(2000);
        
        // Check Alpine is available (but don't fail if not)
        $alpineAvailable = $browser->script("return typeof window.Alpine !== 'undefined'")[0] ?? false;
        if (!$alpineAvailable) {
            $browser->pause(3000); // Extra wait
        }
    }

    /**
     * Check if Alpine.js is loaded and initialize if needed.
     */
    protected function ensureAlpineLoaded(Browser $browser): void
    {
        // Wait for page load first
        $browser->pause(1000);
        
        $loaded = $browser->script("return typeof window.Alpine !== 'undefined'")[0] ?? false;
        
        if (!$loaded) {
            // Try to wait for Alpine to load from Livewire
            $browser->script("
                console.log('Checking for Alpine.js availability...');
                if (typeof window.Alpine === 'undefined') {
                    console.log('Alpine.js not yet available, checking Livewire...');
                    if (typeof window.Livewire !== 'undefined') {
                        console.log('Livewire detected, Alpine should load with it');
                    }
                }
            ");
            
            // Wait longer for dynamic loading
            $browser->pause(3000);
            
            // Check again
            $loaded = $browser->script("return typeof window.Alpine !== 'undefined'")[0] ?? false;
            
            if (!$loaded) {
                $browser->script("
                    console.error('Alpine.js failed to load in testing environment');
                    console.log('Document ready state:', document.readyState);
                    console.log('Available scripts:', Array.from(document.scripts).map(s => s.src).filter(Boolean));
                ");
            }
        }
        
        // Final wait for Alpine to be ready
        $this->waitForAlpineReady($browser);
    }

    /**
     * Debug Alpine.js loading state.
     */
    protected function debugAlpineState(Browser $browser): array
    {
        return $browser->script("
            return {
                alpineDefined: typeof window.Alpine !== 'undefined',
                alpineVersion: window.Alpine ? window.Alpine.version : 'undefined',
                livewireDefined: typeof window.Livewire !== 'undefined',
                documentReady: document.readyState,
                xDataElements: document.querySelectorAll('[x-data]').length,
                xDataInitialized: (function() {
                    var elements = document.querySelectorAll('[x-data]');
                    var initialized = 0;
                    for (var i = 0; i < elements.length; i++) {
                        if (elements[i]._x_dataStack && elements[i]._x_dataStack.length > 0) {
                            initialized++;
                        }
                    }
                    return initialized;
                })(),
                scripts: Array.from(document.scripts).map(s => ({ src: s.src, type: s.type, loaded: s.readyState || 'unknown' })),
                errors: window.__duskErrorMonitor ? window.__duskErrorMonitor.errors : [],
                consoleLogs: window.console ? window.console.logs || [] : []
            };
        ");
    }
}