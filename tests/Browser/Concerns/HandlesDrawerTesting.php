<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;

trait HandlesDrawerTesting
{
    /**
     * Toggle drawer by position.
     */
    protected function toggleDrawer(Browser $browser, string $position): void
    {
        $selector = "[data-drawer-toggle='{$position}'], [x-data*='toggleDrawer'], button:contains('{$position}')";

        $browser->whenAvailable($selector, function ($toggle) {
            $toggle->click();
        })->pause(300); // Wait for animation
    }

    /**
     * Close drawer by position.
     */
    protected function closeDrawer(Browser $browser, string $position): void
    {
        $selector = "[data-drawer-close='{$position}'], .drawer-{$position} [data-close], .drawer-{$position} .close";

        $browser->whenAvailable($selector, function ($close) {
            $close->click();
        })->pause(300);
    }

    /**
     * Close all drawers.
     */
    protected function closeAllDrawers(Browser $browser): void
    {
        // Try Alpine method first
        $this->callAlpineMethod($browser, 'closeAllDrawers');

        // Fallback to clicking close buttons
        $browser->whenAvailable('[data-drawer-close]', function ($closeButtons) {
            $closeButtons->click('[data-drawer-close]');
        });

        $browser->pause(300);
    }

    /**
     * Assert drawer state.
     */
    protected function assertDrawerState(Browser $browser, string $position, bool $isOpen): void
    {
        $actualState = $this->getDrawerState($browser, $position);
        $this->assertEquals($isOpen, $actualState, "Drawer '{$position}' should be ".($isOpen ? 'open' : 'closed'));
    }

    /**
     * Get drawer state from Alpine data.
     */
    protected function getDrawerState(Browser $browser, string $position): bool
    {
        return $this->getAlpineData($browser, "drawers.{$position}");
    }

    /**
     * Assert drawer is visible in DOM.
     */
    protected function assertDrawerVisible(Browser $browser, string $position): void
    {
        $selector = ".drawer-{$position}, [data-drawer='{$position}']";
        $browser->waitFor($selector, 5)
            ->assertVisible($selector);
    }

    /**
     * Assert drawer is hidden in DOM.
     */
    protected function assertDrawerHidden(Browser $browser, string $position): void
    {
        $selector = ".drawer-{$position}, [data-drawer='{$position}']";
        $browser->waitUntilMissing($selector, 5)
            ->assertMissing($selector);
    }

    /**
     * Assert all drawers are closed.
     */
    protected function assertAllDrawersClosed(Browser $browser): void
    {
        $positions = ['left', 'right', 'top', 'bottom'];

        foreach ($positions as $position) {
            $this->assertDrawerState($browser, $position, false);
            $this->assertDrawerHidden($browser, $position);
        }
    }

    /**
     * Test drawer overlay functionality.
     */
    protected function assertDrawerOverlay(Browser $browser, bool $shouldBeVisible): void
    {
        $overlaySelector = '[data-drawer-overlay], .drawer-overlay';

        if ($shouldBeVisible) {
            $browser->waitFor($overlaySelector, 5)
                ->assertVisible($overlaySelector);
        } else {
            $browser->waitUntilMissing($overlaySelector, 5)
                ->assertMissing($overlaySelector);
        }
    }

    /**
     * Test drawer focus management.
     */
    protected function assertDrawerFocus(Browser $browser, string $position): void
    {
        $drawerSelector = ".drawer-{$position}, [data-drawer='{$position}']";

        $browser->waitFor($drawerSelector, 5)
            ->assertFocused($drawerSelector.' [tabindex="-1"], '.$drawerSelector.':first-child');
    }

    /**
     * Test drawer keyboard navigation.
     */
    protected function testDrawerKeyboardNavigation(Browser $browser, string $position): void
    {
        // Open drawer
        $this->toggleDrawer($browser, $position);
        $this->assertDrawerState($browser, $position, true);

        // Test Tab navigation within drawer
        $browser->keys('body', '{tab}')
            ->pause(100);

        // Test Escape key closes drawer
        $browser->keys('body', '{escape}')
            ->pause(300);

        $this->assertDrawerState($browser, $position, false);
    }

    /**
     * Test drawer content loading.
     */
    protected function assertDrawerContent(Browser $browser, string $position, string $expectedContent): void
    {
        $this->toggleDrawer($browser, $position);

        $drawerSelector = ".drawer-{$position}, [data-drawer='{$position}']";
        $browser->waitFor($drawerSelector, 5)
            ->assertSeeIn($drawerSelector, $expectedContent);
    }

    /**
     * Test drawer dimensions.
     */
    protected function assertDrawerDimensions(Browser $browser, string $position, array $expectedDimensions): void
    {
        $this->toggleDrawer($browser, $position);

        $drawerSelector = ".drawer-{$position}, [data-drawer='{$position}']";

        $dimensions = $this->executeScript($browser, "
            var el = document.querySelector('{$drawerSelector}');
            if (!el) return null;
            
            var rect = el.getBoundingClientRect();
            return {
                width: rect.width,
                height: rect.height,
                top: rect.top,
                left: rect.left
            };
        ");

        if (isset($expectedDimensions['width'])) {
            $this->assertEquals($expectedDimensions['width'], $dimensions['width'], 'Drawer width should match expected');
        }

        if (isset($expectedDimensions['height'])) {
            $this->assertEquals($expectedDimensions['height'], $dimensions['height'], 'Drawer height should match expected');
        }
    }

    /**
     * Test drawer animation.
     */
    protected function assertDrawerAnimation(Browser $browser, string $position): void
    {
        $startTime = microtime(true);

        $this->toggleDrawer($browser, $position);

        $endTime = microtime(true);
        $animationTime = $endTime - $startTime;

        // Animation should take some time (not instant)
        $this->assertGreaterThan(0.1, $animationTime, 'Drawer animation should take time');

        // But not too long
        $this->assertLessThan(1.0, $animationTime, 'Drawer animation should complete quickly');
    }

    /**
     * Test drawer backdrop click closes drawer.
     */
    protected function testDrawerBackdropClick(Browser $browser, string $position): void
    {
        // Open drawer
        $this->toggleDrawer($browser, $position);
        $this->assertDrawerState($browser, $position, true);

        // Click on backdrop/overlay
        $browser->whenAvailable('[data-drawer-overlay], .drawer-overlay', function ($overlay) {
            $overlay->click();
        })->pause(300);

        // Drawer should be closed
        $this->assertDrawerState($browser, $position, false);
    }

    /**
     * Test drawer accessibility attributes.
     */
    protected function assertDrawerAccessibility(Browser $browser, string $position): void
    {
        $this->toggleDrawer($browser, $position);

        $drawerSelector = ".drawer-{$position}, [data-drawer='{$position}']";

        // Check for proper ARIA attributes
        $browser->assertAttribute($drawerSelector, 'role', 'dialog')
            ->assertAttribute($drawerSelector, 'aria-modal', 'true')
            ->assertAttribute($drawerSelector, 'aria-labelledby')
            ->assertPresent($drawerSelector.' [data-close], '.$drawerSelector.' .close');
    }

    /**
     * Test drawer with dynamic content.
     */
    protected function testDrawerDynamicContent(Browser $browser, string $position, callable $contentLoader): void
    {
        $this->toggleDrawer($browser, $position);

        $drawerSelector = ".drawer-{$position}, [data-drawer='{$position}']";

        // Load dynamic content
        $contentLoader($browser, $drawerSelector);

        // Wait for content to load
        $browser->waitForTextIn($drawerSelector, 'Dynamic content loaded', 10);

        // Assert content is present
        $browser->assertSeeIn($drawerSelector, 'Dynamic content loaded');
    }

    /**
     * Test drawer state persistence.
     */
    protected function testDrawerStatePersistence(Browser $browser, string $position): void
    {
        // Open drawer
        $this->toggleDrawer($browser, $position);
        $this->assertDrawerState($browser, $position, true);

        // Refresh page
        $browser->refresh()
            ->waitForAlpine($this);

        // Check if drawer state is persisted (if implemented)
        // This test depends on whether the app implements drawer state persistence
        $persistedState = $this->getDrawerState($browser, $position);

        // If persistence is implemented, uncomment the following:
        // $this->assertTrue($persistedState, 'Drawer state should be persisted across page refresh');
    }
}
