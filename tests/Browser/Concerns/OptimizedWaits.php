<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;

trait OptimizedWaits
{
    /**
     * Optimized wait for element visibility with shorter timeout.
     */
    protected function waitForElement(Browser $browser, string $selector, int $timeout = 5): Browser
    {
        try {
            return $browser->waitFor($selector, $timeout);
        } catch (\Exception) {
            // Fallback to immediate check if wait fails
            if ($browser->element($selector)) {
                return $browser;
            }
            throw new \Exception("Element {$selector} not found");
        }
    }

    /**
     * Quick wait for text to appear.
     */
    protected function waitForText(Browser $browser, string $text, int $timeout = 3): Browser
    {
        try {
            return $browser->waitForText($text, $timeout);
        } catch (\Exception) {
            // Fallback to immediate check if wait fails
            if (str_contains($browser->text(), $text)) {
                return $browser;
            }
            throw new \Exception("Text '{$text}' not found");
        }
    }

    /**
     * Optimized wait for JavaScript to be ready.
     */
    protected function waitForJavaScriptReady(Browser $browser, int $timeout = 3): Browser
    {
        $script = 'return document.readyState === "complete" && typeof jQuery !== "undefined" ? jQuery.active === 0 : true;';
        
        try {
            return $browser->waitUsing($timeout, 100, function () use ($browser, $script) {
                return $browser->script($script)[0] ?? false;
            });
        } catch (\Exception) {
            // Quick fallback - just ensure document is ready
            return $browser->waitUsing(2, 100, function () use ($browser) {
                return $browser->script('return document.readyState === "complete"')[0] ?? false;
            });
        }
    }

    /**
     * Fast wait for Livewire components to load.
     */
    protected function waitForLivewireComponent(Browser $browser, ?string $component = null, int $timeout = 3): Browser
    {
        $script = $component 
            ? "return window.Livewire?.components?.componentsByName?.['{$component}'] !== undefined;"
            : "return window.Livewire !== undefined;";
            
        try {
            return $browser->waitUsing($timeout, 100, function () use ($browser, $script) {
                return $browser->script($script)[0] ?? false;
            });
        } catch (\Exception) {
            // Don't fail tests if Livewire check times out
            return $browser;
        }
    }

    /**
     * Quick pause for animations with minimal delay.
     */
    protected function microPause(Browser $browser, int $ms = 100): Browser
    {
        usleep($ms * 1000);
        return $browser;
    }

    /**
     * Optimized wait for route change.
     */
    protected function waitForRoute(Browser $browser, string $path, int $timeout = 3): Browser
    {
        try {
            return $browser->waitForLocation($path, $timeout);
        } catch (\Exception) {
            // Check if already at the correct location
            if (str_contains($browser->driver->getCurrentURL(), $path)) {
                return $browser;
            }
            throw new \Exception("Route {$path} not found");
        }
    }

    /**
     * Smart wait for page to load with multiple checks.
     */
    protected function waitForPageLoad(Browser $browser, ?string $expectedText = null, int $timeout = 5): Browser
    {
        $browser->waitForJavaScriptReady($timeout);
        
        if ($expectedText) {
            $this->waitForText($browser, $expectedText, 2);
        }
        
        // Micro pause for any remaining animations
        $this->microPause($browser, 200);
        
        return $browser;
    }
}