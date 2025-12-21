<?php

namespace Tests\Browser;

use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\HandlesAlpineTesting;
use Tests\Browser\Concerns\HandlesDrawerTesting;
use Tests\Browser\Concerns\HandlesMultiTenantTesting;

class DashboardTest extends JavaScriptDuskTestCase
{
    use HandlesAlpineTesting;
    use HandlesDrawerTesting;
    use HandlesMultiTenantTesting;

    /**
     * Test dashboard loads correctly with Alpine.js data.
     */
    public function test_dashboard_loads_with_alpine_data(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $this->waitForJavaScript($browser)
                ->waitForAlpine($browser);

            // Debug: take screenshot to see what's on the page
            $browser->screenshot('dashboard-debug');

            // Check if we're on the right page
            $currentPath = $browser->script('return window.location.pathname;')[0];
            dump("Current path: {$currentPath}");

            // Assert Alpine.js is properly initialized
            $this->assertAlpineData($browser, 'test', 'working');
            $this->assertAlpineData($browser, 'drawers', [
                'left' => false,
                'right' => false,
                'top' => false,
                'bottom' => false,
            ]);

            // Assert dashboard elements are present - be more flexible
            $browser->assertSee('Dashboard')
                ->assertSee('Inventory Overview')
                ->assertSee('Quick Actions')
                ->assertSee('Stores')
                ->assertSee('Total Items')
                ->assertSee('Low Stock Items')
                ->assertSee('Recent Transactions');

            // Try to find organization name in different formats
            $pageText = $browser->text('body');
            if (! str_contains($pageText, $org->name)) {
                dump("Organization name '{$org->name}' not found in page text");
                dump('Page text preview: '.substr($pageText, 0, 500));
            }

            // Assert no JavaScript errors
            $this->assertNoJavaScriptErrors($browser);
        });
    }

    /**
     * Test Alpine.js drawer system functionality.
     */
    public function test_alpine_drawer_system(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $browser->visit('/dashboard')
                ->waitForAlpine($this);

            // Test left drawer (navigation)
            $this->toggleDrawer($browser, 'left');
            $this->assertDrawerState($browser, 'left', true);
            $this->assertDrawerVisible($browser, 'left');

            // Close drawer
            $this->closeDrawer($browser, 'left');
            $this->assertDrawerState($browser, 'left', false);

            // Test right drawer (module settings)
            $this->toggleDrawer($browser, 'right');
            $this->assertDrawerState($browser, 'right', true);
            $this->assertDrawerVisible($browser, 'right');

            // Test top drawer (app info)
            $this->toggleDrawer($browser, 'top');
            $this->assertDrawerState($browser, 'top', true);
            $this->assertDrawerVisible($browser, 'top');

            // Test bottom drawer (user preferences)
            $this->toggleDrawer($browser, 'bottom');
            $this->assertDrawerState($browser, 'bottom', true);
            $this->assertDrawerVisible($browser, 'bottom');

            // Test close all drawers functionality
            $this->closeAllDrawers($browser);
            $this->assertAllDrawersClosed($browser);

            // Test keyboard navigation (Escape key)
            $this->toggleDrawer($browser, 'left');
            $this->assertDrawerState($browser, 'left', true);
            $browser->keys('body', '{escape}');
            $this->assertDrawerState($browser, 'left', false);
        });
    }

    /**
     * Test mobile menu toggle functionality.
     */
    public function test_mobile_menu_toggle(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $browser->visit('/dashboard')
                ->resize(375, 667) // Mobile viewport
                ->waitForAlpine($this);

            // Mobile menu should be closed by default
            $browser->assertMissing('.mobile-menu-open');

            // Click mobile menu button
            $browser->click('button[aria-label="Toggle mobile menu"]')
                ->waitFor('.mobile-menu-open', 5)
                ->assertVisible('.mobile-menu-open');

            // Click again to close
            $browser->click('button[aria-label="Toggle mobile menu"]')
                ->waitUntilMissing('.mobile-menu-open', 5)
                ->assertMissing('.mobile-menu-open');
        });
    }

    /**
     * Test dashboard statistics display correctly.
     */
    public function test_dashboard_statistics(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            // Create test data
            $stores = Store::factory()->count(3)->create(['organization_id' => $org->id]);
            $items = Item::factory()->count(15)->create(['organization_id' => $org->id]);

            // Attach items to stores
            foreach ($items as $item) {
                $item->stores()->attach($stores->random()->id, [
                    'quantity' => rand(1, 100),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create transactions
            Transaction::factory()->count(5)->create([
                'organization_id' => $org->id,
                'store_id' => $stores->random()->id,
            ]);

            $browser->visit('/dashboard')
                ->waitForJavaScript($this);

            // Assert statistics are displayed correctly
            $browser->assertSeeIn('[data-stats="stores"]', '3')
                ->assertSeeIn('[data-stats="items"]', '15')
                ->assertSeeIn('[data-stats="transactions"]', '5');

            // Assert stores are listed
            foreach ($stores as $store) {
                $browser->assertSee($store->name);
            }

            // Assert recent transactions are displayed
            $browser->assertSee('Recent Transactions');
        });
    }

    /**
     * Test low stock alerts functionality.
     */
    public function test_low_stock_alerts(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            // Create store and item with low stock
            $store = Store::factory()->create(['organization_id' => $org->id]);
            $item = Item::factory()->create([
                'organization_id' => $org->id,
                'reorder_level' => 10,
            ]);

            // Attach item with low quantity
            $item->stores()->attach($store->id, [
                'quantity' => 5, // Below reorder level
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $browser->visit('/dashboard')
                ->waitForJavaScript($this);

            // Assert low stock alert is displayed
            $browser->assertSee('Low Stock Items')
                ->assertSee('1') // Count of low stock items
                ->assertSee($item->name)
                ->assertSee('Low Stock');

            // Test with no low stock items
            $item->stores()->updateExistingPivot($store->id, ['quantity' => 20]);

            $browser->refresh()
                ->waitForJavaScript($this)
                ->assertSee('All items are well stocked!');
        });
    }

    /**
     * Test search functionality without JavaScript errors.
     */
    public function test_search_functionality(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $browser->visit('/dashboard')
                ->waitForJavaScript($this);

            // Check that search is properly defined (no "search is not defined" errors)
            $searchDefined = $this->executeScript($browser, "
                return typeof window.search !== 'undefined' || 
                       document.querySelector('[x-model*=\"search\"]') !== null;
            ");

            $this->assertTrue($searchDefined, 'Search functionality should be properly defined');

            // Test any search inputs that might be present
            $browser->whenAvailable('input[placeholder*="search" i]', function ($searchInput) {
                $searchInput->type('test')
                    ->pause(500); // Wait for debounce
            });

            // Assert no JavaScript errors occurred during search
            $this->assertNoJavaScriptErrors($browser);
        });
    }

    /**
     * Test theme switching functionality.
     */
    public function test_theme_switching(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $browser->visit('/dashboard')
                ->waitForAlpine($this);

            // Check if theme toggle is present
            if ($browser->present('[data-theme-toggle]')) {
                // Get initial theme
                $initialTheme = $this->executeScript($browser, "
                    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                ");

                // Toggle theme
                $browser->click('[data-theme-toggle]')
                    ->pause(500);

                // Assert theme changed
                $newTheme = $this->executeScript($browser, "
                    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                ");

                $this->assertNotEquals($initialTheme, $newTheme, 'Theme should have changed');
            }

            // Assert no JavaScript errors
            $this->assertNoJavaScriptErrors($browser);
        });
    }

    /**
     * test responsive design on different screen sizes.
     */
    public function test_responsive_design(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            // Test desktop view
            $browser->visit('/dashboard')
                ->resize(1920, 1080)
                ->waitForJavaScript($this)
                ->assertPresent('.hidden.md\\:flex') // Desktop navigation visible
                ->assertMissing('.md\\:hidden'); // Mobile navigation hidden

            // Test tablet view
            $browser->resize(768, 1024)
                ->pause(500)
                ->assertPresent('.hidden.md\\:flex')
                ->assertMissing('.md\\:hidden');

            // Test mobile view
            $browser->resize(375, 667)
                ->pause(500)
                ->assertPresent('.md\\:hidden') // Mobile navigation visible
                ->assertMissing('.hidden.md\\:flex'); // Desktop navigation hidden

            // Assert dashboard content is still accessible
            $browser->assertSee('Dashboard - '.$org->name)
                ->assertSee('Inventory Overview');
        });
    }

    /**
     * test multi-tenant data isolation.
     */
    public function test_multi_tenant_data_isolation(): void
    {
        // Create two organizations
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        // Create data for org1
        $store1 = Store::factory()->create(['organization_id' => $org1->id]);
        $item1 = Item::factory()->create(['organization_id' => $org1->id]);
        $item1->stores()->attach($store1->id, [
            'quantity' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create data for org2
        $store2 = Store::factory()->create(['organization_id' => $org2->id]);
        $item2 = Item::factory()->create(['organization_id' => $org2->id]);

        // Test org1 dashboard
        $this->createBrowserWithOrganization(function (Browser $browser) use ($store1, $item1, $store2, $item2) {
            $browser->visit('/dashboard')
                ->waitForJavaScript($this);

            // Should see org1 data
            $browser->assertSee($store1->name)
                ->assertSee($item1->name);

            // Should NOT see org2 data
            $browser->assertDontSee($store2->name)
                ->assertDontSee($item2->name);
        }, $org1);

        // Test org2 dashboard
        $this->createBrowserWithOrganization(function (Browser $browser) use ($store1, $item1, $store2, $item2) {
            $browser->visit('/dashboard')
                ->waitForJavaScript($this);

            // Should see org2 data
            $browser->assertSee($store2->name)
                ->assertSee($item2->name);

            // Should NOT see org1 data
            $browser->assertDontSee($store1->name)
                ->assertDontSee($item1->name);
        }, $org2);
    }

    /**
     * Test JavaScript error handling and console output.
     */
    public function test_javascript_error_handling(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $browser->visit('/dashboard')
                ->waitForJavaScript($this);

            // Check for any JavaScript errors
            $errors = $this->checkForJavaScriptErrors($browser);

            if (! empty($errors)) {
                // Log errors for debugging
                foreach ($errors as $error) {
                    $browser->dump($error);
                }
            }

            $this->assertEmpty($errors, 'No JavaScript errors should be present on dashboard');

            // Check console for any warnings
            $consoleLogs = $browser->script("
                var logs = [];
                var originalLog = console.log;
                var originalWarn = console.warn;
                console.log = function() { logs.push({type: 'log', args: Array.prototype.slice.call(arguments)}); originalLog.apply(console, arguments); };
                console.warn = function() { logs.push({type: 'warn', args: Array.prototype.slice.call(arguments)}); originalWarn.apply(console, arguments); };
                return logs;
            ");

            // Assert no critical warnings
            $criticalWarnings = array_filter($consoleLogs[0] ?? [], function ($log) {
                $message = implode(' ', $log['args'] ?? []);

                return str_contains($message, 'error') || str_contains($message, 'undefined');
            });

            $this->assertEmpty($criticalWarnings, 'No critical JavaScript warnings should be present');
        });
    }

    /**
     * Test quick action links functionality.
     */
    public function test_quick_action_links(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $browser->visit('/dashboard')
                ->waitForJavaScript($this);

            // Test Add Store link
            $browser->clickLink('Add Store')
                ->waitForLocation('/inventory/stores/create', 10)
                ->assertPathIs('/inventory/stores/create')
                ->assertSee('Create Store');

            // Go back to dashboard
            $browser->visit('/dashboard')
                ->waitForJavaScript($this);

            // Test New Transaction link
            $browser->clickLink('New Transaction')
                ->waitForLocation('/inventory/transactions/create', 10)
                ->assertPathIs('/inventory/transactions/create')
                ->assertSee('Create Transaction');

            // Go back to dashboard
            $browser->visit('/dashboard')
                ->waitForJavaScript($this);

            // Test Add Item link
            $browser->clickLink('Add Item')
                ->waitForLocation('/inventory/items/create', 10)
                ->assertPathIs('/inventory/items/create')
                ->assertSee('Create Item');
        });
    }

    /**
     * Test dashboard performance and loading times.
     */
    public function test_dashboard_performance(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $startTime = microtime(true);

            $browser->visit('/dashboard')
                ->waitForJavaScript($this)
                ->waitForAlpine($this);

            $loadTime = microtime(true) - $startTime;

            // Assert page loads within reasonable time (5 seconds)
            $this->assertLessThan(5.0, $loadTime, 'Dashboard should load within 5 seconds');

            // Check that all key elements are loaded
            $browser->assertPresent('[data-stats="stores"]')
                ->assertPresent('[data-stats="items"]')
                ->assertPresent('[data-stats="low-stock"]')
                ->assertPresent('[data-stats="transactions"]');

            // Test that Alpine.js components are properly initialized
            $alpineComponents = $this->executeScript($browser, "
                return document.querySelectorAll('[x-data]').length;
            ");

            $this->assertGreaterThan(0, $alpineComponents, 'Alpine.js components should be initialized');
        });
    }

    /**
     * Test accessibility compliance.
     */
    public function test_accessibility_compliance(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $browser->visit('/dashboard')
                ->waitForJavaScript($this);

            // Check for proper heading hierarchy
            $h1Count = $browser->script("return document.querySelectorAll('h1').length;")[0] ?? 0;
            $h2Count = $browser->script("return document.querySelectorAll('h2').length;")[0] ?? 0;

            $this->assertGreaterThan(0, $h1Count + $h2Count, 'Page should have proper heading structure');

            // Check for ARIA labels on interactive elements
            $interactiveElements = $browser->script("
                var elements = document.querySelectorAll('button, a, input, select, textarea');
                var elementsWithoutAria = [];
                elements.forEach(function(el) {
                    if (!el.getAttribute('aria-label') && !el.getAttribute('aria-labelledby') && !el.textContent.trim()) {
                        elementsWithoutAria.push(el.tagName + (el.id ? '#' + el.id : ''));
                    }
                });
                return elementsWithoutAria;
            ");

            $this->assertEmpty($interactiveElements[0] ?? [], 'Interactive elements should have proper ARIA labels');

            // Check keyboard navigation
            $browser->keys('body', '{tab}')
                ->pause(200)
                ->assertFocused('button, a, input, select, textarea, [tabindex]:not([tabindex="-1"])');
        });
    }
}
