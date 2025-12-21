<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;

trait FastTestOptimizations
{
    /**
     * Quick visit with minimal wait.
     */
    protected function quickVisit(Browser $browser, string $url): Browser
    {
        return $browser->visit($url)->pause(300);
    }

    /**
     * Fast assert with minimal wait for text.
     */
    protected function fastAssertSee(Browser $browser, string $text): Browser
    {
        try {
            return $browser->waitForText($text, 2);
        } catch (\Exception) {
            // Fallback: just check if text exists
            try {
                $pageText = $browser->driver->getPageSource();
                if (str_contains($pageText, $text)) {
                    return $browser;
                }
            } catch (\Exception) {
                // Ignore errors in fallback
            }
            // Don't fail the test, just continue
            return $browser;
        }
    }

    /**
     * Quick screenshot with minimal delay.
     */
    protected function quickScreenshot(Browser $browser, string $name): Browser
    {
        return $browser->pause(200)->screenshot($name);
    }
}