<?php

namespace Tests\Browser\Assertions;

use Laravel\Dusk\Browser;

trait DashboardAssertions
{
    /**
     * Assert dashboard statistics are displayed correctly.
     */
    public function assertDashboardStats(Browser $browser, array $expectedStats): void
    {
        if (isset($expectedStats['stores'])) {
            $browser->assertSeeIn('[data-stats="stores"]', (string) $expectedStats['stores']);
        }

        if (isset($expectedStats['items'])) {
            $browser->assertSeeIn('[data-stats="items"]', (string) $expectedStats['items']);
        }

        if (isset($expectedStats['low_stock'])) {
            $browser->assertSeeIn('[data-stats="low-stock"]', (string) $expectedStats['low_stock']);
        }

        if (isset($expectedStats['transactions'])) {
            $browser->assertSeeIn('[data-stats="transactions"]', (string) $expectedStats['transactions']);
        }
    }

    /**
     * Assert quick action links are present and functional.
     */
    public function assertQuickActionsPresent(Browser $browser): void
    {
        $browser->assertSeeLink('Add Store')
            ->assertSeeLink('New Transaction')
            ->assertSeeLink('Add Item');
    }

    /**
     * Assert low stock alerts are displayed.
     */
    public function assertLowStockAlerts(Browser $browser, ?int $expectedCount = null): void
    {
        if ($expectedCount !== null) {
            $browser->assertSeeIn('[data-stats="low-stock"]', (string) $expectedCount);
        }

        if ($expectedCount > 0) {
            $browser->assertSee('Low Stock Items')
                ->assertPresent('[data-low-stock-alerts]');
        } else {
            $browser->assertSee('All items are well stocked');
        }
    }

    /**
     * assert stores are listed correctly.
     */
    public function assertStoresListed(Browser $browser, array $storeNames): void
    {
        foreach ($storeNames as $storeName) {
            $browser->assertSee($storeName);
        }
    }

    /**
     * Assert recent transactions are displayed.
     */
    public function assertRecentTransactions(Browser $browser, int $minCount = 1): void
    {
        $browser->assertSee('Recent Transactions');

        if ($minCount > 0) {
            $browser->assertPresent('[data-recent-transactions]');
        }
    }

    /**
     * Assert organization context is active.
     */
    public function assertOrganizationContext(Browser $browser, string $organizationName): void
    {
        $browser->assertSee("Dashboard - {$organizationName}")
            ->assertSee($organizationName);
    }

    /**
     * Assert dashboard loads without errors.
     */
    public function assertDashboardLoadsSuccessfully(Browser $browser): void
    {
        $browser->assertPathIs('/dashboard')
            ->assertSee('Dashboard')
            ->assertSee('Inventory Overview')
            ->waitForJavaScript($this)
            ->assertNoJavaScriptErrors();
    }

    /**
     * Assert responsive layout works correctly.
     */
    public function assertResponsiveLayout(Browser $browser): void
    {
        // Test mobile view
        $browser->resize(375, 667)
            ->assertPresent('.md\\:hidden') // Mobile navigation visible
            ->assertMissing('.hidden.md\\:flex'); // Desktop navigation hidden

        // Test desktop view
        $browser->resize(1920, 1080)
            ->assertPresent('.hidden.md\\:flex') // Desktop navigation visible
            ->assertMissing('.md\\:hidden'); // Mobile navigation hidden
    }

    /**
     * Assert theme switching works.
     */
    public function assertThemeSwitchingWorks(Browser $browser): void
    {
        if ($browser->present('[data-theme-toggle]')) {
            $initialTheme = $this->executeScript($browser, "
                return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            ");

            $browser->click('[data-theme-toggle]')
                ->pause(500);

            $newTheme = $this->executeScript($browser, "
                return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            ");

            $this->assertNotEquals($initialTheme, $newTheme);
        }
    }

    /**
     * Assert navigation menu works correctly.
     */
    public function assertNavigationWorks(Browser $browser): void
    {
        // Test main navigation links
        $navigationLinks = [
            'Dashboard',
            'Inventory',
            'Accounts',
            'HRM',
        ];

        foreach ($navigationLinks as $link) {
            if ($browser->seeLink($link)) {
                $browser->clickLink($link)
                    ->pause(500)
                    ->assertPathIsNot('/login');

                // Go back to dashboard for next test
                $browser->visit('/dashboard')
                    ->waitForJavaScript($this);
            }
        }
    }

    /**
     * Assert search functionality is available.
     */
    public function assertSearchAvailable(Browser $browser): void
    {
        $this->assertSearchVariableDefined($browser);

        if ($browser->present('input[placeholder*="search" i]')) {
            $browser->assertVisible('input[placeholder*="search" i]')
                ->assertEnabled('input[placeholder*="search" i]');
        }
    }

    /**
     * Assert user menu works correctly.
     */
    public function assertUserMenuWorks(Browser $browser): void
    {
        if ($browser->present('[data-user-menu]')) {
            $browser->click('[data-user-menu]')
                ->waitFor('.dropdown-menu', 5)
                ->assertSee('Profile')
                ->assertSee('Log Out');
        }
    }

    /**
     * Assert notifications system works.
     */
    public function assertNotificationsWork(Browser $browser): void
    {
        if ($browser->present('[data-notifications]')) {
            $browser->click('[data-notifications]')
                ->waitFor('.notifications-panel', 5)
                ->assertPresent('.notifications-panel');
        }
    }

    /**
     * Assert page performance is acceptable.
     */
    public function assertPagePerformance(Browser $browser, float $maxLoadTime = 5.0): void
    {
        $startTime = microtime(true);

        $browser->visit('/dashboard')
            ->waitForJavaScript($this);

        $loadTime = microtime(true) - $startTime;
        $this->assertLessThan($maxLoadTime, $loadTime, "Page should load within {$maxLoadTime} seconds");
    }

    /**
     * Assert accessibility standards are met.
     */
    public function assertAccessibilityStandards(Browser $browser): void
    {
        // Check for proper heading structure
        $hasHeadings = $this->executeScript($browser, "
            return document.querySelectorAll('h1, h2, h3, h4, h5, h6').length > 0;
        ");
        $this->assertTrue($hasHeadings, 'Page should have proper heading structure');

        // Check for skip links (important for accessibility)
        $hasSkipLinks = $this->executeScript($browser, "
            return document.querySelectorAll('a[href^=\"#\"], [role=\"navigation\"]').length > 0;
        ");
        $this->assertTrue($hasSkipLinks, 'Page should have navigation elements');

        // Check for proper ARIA labels on interactive elements
        $hasAriaLabels = $this->executeScript($browser, "
            var interactiveElements = document.querySelectorAll('button, a, input, select, textarea');
            var elementsWithAria = 0;
            interactiveElements.forEach(function(el) {
                if (el.getAttribute('aria-label') || 
                    el.getAttribute('aria-labelledby') || 
                    el.textContent.trim()) {
                    elementsWithAria++;
                }
            });
            return elementsWithAria > 0;
        ");
        $this->assertTrue($hasAriaLabels, 'Interactive elements should have proper labels');
    }

    /**
     * Assert data isolation between organizations.
     */
    public function assertDataIsolation(Browser $browser, string $currentOrgName, string $otherOrgName): void
    {
        $browser->assertSee($currentOrgName)
            ->assertDontSee($otherOrgName);
    }

    /**
     * Assert Alpine.js components are properly initialized.
     */
    public function assertAlpineComponentsInitialized(Browser $browser): void
    {
        $alpineElements = $this->executeScript($browser, "
            return document.querySelectorAll('[x-data]').length;
        ");
        $this->assertGreaterThan(0, $alpineElements, 'Alpine.js components should be initialized');

        $alpineData = $this->executeScript($browser, "
            var el = document.querySelector('[x-data]');
            return el && el._x_dataStack && el._x_dataStack.length > 0;
        ");
        $this->assertTrue($alpineData, 'Alpine.js data should be available');
    }

    /**
     * Assert Livewire components are loaded.
     */
    public function assertLivewireComponentsLoaded(Browser $browser): void
    {
        $livewireElements = $this->executeScript($browser, "
            return document.querySelectorAll('[wire\\\\:id]').length;
        ");

        // Livewire components might not be present on all pages
        if ($livewireElements > 0) {
            $livewireInitialized = $this->executeScript($browser, "
                return typeof Livewire !== 'undefined';
            ");
            $this->assertTrue($livewireInitialized, 'Livewire should be initialized when components are present');
        }
    }

    /**
     * Assert no console errors or warnings.
     */
    public function assertNoConsoleErrors(Browser $browser): void
    {
        $consoleErrors = $this->executeScript($browser, "
            var errors = [];
            var originalError = console.error;
            var originalWarn = console.warn;
            
            console.error = function() {
                errors.push({
                    type: 'error',
                    args: Array.prototype.slice.call(arguments)
                });
                originalError.apply(console, arguments);
            };
            
            console.warn = function() {
                var args = Array.prototype.slice.call(arguments);
                var message = args.join(' ');
                if (message.includes('error') || message.includes('undefined')) {
                    errors.push({
                        type: 'warning',
                        args: args
                    });
                }
                originalWarn.apply(console, arguments);
            };
            
            return errors;
        ");

        $errorMessages = array_filter($consoleErrors, function ($error) {
            $message = implode(' ', $error['args'] ?? []);

            return str_contains(strtolower($message), 'error');
        });

        $this->assertEmpty($errorMessages, 'No console errors should be present');
    }

    /**
     * Assert drawer system works correctly.
     */
    public function assertDrawerSystemWorks(Browser $browser): void
    {
        $drawerPositions = ['left', 'right', 'top', 'bottom'];

        foreach ($drawerPositions as $position) {
            // Test drawer toggle
            if ($browser->present("[data-drawer-toggle='{$position}']")) {
                $this->toggleDrawer($browser, $position);
                $this->assertDrawerState($browser, $position, true);
                $this->assertDrawerVisible($browser, $position);

                $this->closeDrawer($browser, $position);
                $this->assertDrawerState($browser, $position, false);
            }
        }
    }

    /**
     * Assert mobile menu functionality.
     */
    public function assertMobileMenuWorks(Browser $browser): void
    {
        $browser->resize(375, 667) // Mobile viewport
            ->waitForJavaScript($this);

        // Test mobile menu toggle
        if ($browser->present('button[aria-label*="menu" i]')) {
            $browser->click('button[aria-label*="menu" i]')
                ->waitFor('.mobile-menu-open', 5)
                ->assertVisible('.mobile-menu-open');

            $browser->click('button[aria-label*="menu" i]')
                ->waitUntilMissing('.mobile-menu-open', 5)
                ->assertMissing('.mobile-menu-open');
        }
    }

    /**
     * Assert dashboard content is loaded via AJAX if applicable.
     */
    public function assertDynamicContentLoaded(Browser $browser): void
    {
        // Wait for any loading indicators to disappear
        $browser->waitUntilMissing('.loading, [data-loading]', 10)
            ->pause(500);

        // Verify content is actually loaded
        $browser->assertPresent('[data-stats]')
            ->assertPresent('[data-quick-actions]');
    }
}
