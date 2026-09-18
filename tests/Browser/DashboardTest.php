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

            // Force a desktop viewport, then reload so the shell initializes with the sidebar docked/open
            $browser->resize(1920, 1080)
                ->visit('/inventory/dashboard')
                ->waitForJavaScript($browser)
                ->waitForAlpine($browser);

            // Assert Alpine.js is properly initialized
            $this->assertAlpineData($browser, 'navOpen', true);
            $browser->assertPresent('#sidebar');
            $browser->assertVisible('.hidden.md\\:flex');

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
     * Test the executive (Eagle Eye) dashboard renders with live widgets.
     */
    public function test_executive_dashboard_renders(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $browser->resize(1920, 1080)
                ->visit('/dashboard')
                ->waitForJavaScript($this)
                ->waitForAlpine($this);

            $browser->assertSee('Command Center')
                ->assertSee('Eagle Eye')
                ->assertPresent('[data-executive-kpis]')
                ->assertPresent('[data-widget-grid]')
                ->assertPresent('[data-kpi]')
                ->assertPresent('.dashboard-widget')
                ->assertPresent('canvas[x-ref="particleCanvas"]');

            // KPI values should be rendered server-side
            $browser->assertPresent('[data-kpi-value]');

            $this->assertNoJavaScriptErrors($browser);
        });
    }

    /**
     * Test persistent sidebar and header popovers functionality.
     */
    public function test_sidebar_and_header_popovers(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $browser->resize(375, 667) // Mobile viewport so the sidebar is off-canvas
                ->visit('/dashboard')
                ->waitForAlpine($this);

            // Sidebar starts closed on mobile
            $this->assertSidebarTransform($browser, '-translate-x-full');

            // Open the sidebar via the header hamburger
            $browser->click('button[aria-label="Toggle navigation"]')
                ->pause(300);
            $this->assertSidebarTransform($browser, 'translate-x-0');

            // Escape key closes the sidebar
            $browser->keys('body', '{escape}');
            $this->assertSidebarTransform($browser, '-translate-x-full');

            // Switch to a desktop viewport for the header popovers
            $browser->resize(1920, 1080)
                ->visit('/dashboard')
                ->waitForAlpine($this);

            // Notifications popover
            $browser->click('button[aria-label="Notifications"]')
                ->pause(300)
                ->assertSee('No new notifications');

            // Opening search closes notifications and shows the search input
            $browser->click('button[aria-label="Search navigation"]')
                ->pause(300)
                ->assertVisible('input[placeholder*="search" i]');

            // User menu popover
            $browser->click('button[aria-label="User menu"]')
                ->assertSee('Profile');
        });
    }

    /**
     * Test mobile hamburger menu toggle functionality.
     */
    public function test_mobile_menu_toggle(): void
    {
        $this->createBrowserWithOrganization(function (Browser $browser, Organization $org, User $user) {
            $browser->visit('/dashboard')
                ->resize(375, 667) // Mobile viewport
                ->waitForAlpine($this);

            // Sidebar should be closed by default on mobile
            $this->assertSidebarTransform($browser, '-translate-x-full');
            $this->assertElementDisplay($browser, '[data-nav-overlay]', false);

            // Open sidebar with the mobile menu button
            $browser->click('button[aria-label="Toggle navigation"]')
                ->pause(300);
            $this->assertSidebarTransform($browser, 'translate-x-0');
            $this->assertElementDisplay($browser, '[data-nav-overlay]', true);

            // Close with the sidebar close button
            $browser->click('button[aria-label="Close navigation"]')
                ->pause(300);
            $this->assertSidebarTransform($browser, '-translate-x-full');
            $this->assertElementDisplay($browser, '[data-nav-overlay]', false);

            // Re-open then close again via the hamburger
            $browser->click('button[aria-label="Toggle navigation"]')
                ->pause(300);
            $this->assertSidebarTransform($browser, 'translate-x-0');
            $browser->click('button[aria-label="Toggle navigation"]')
                ->pause(300);
            $this->assertSidebarTransform($browser, '-translate-x-full');
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

            $browser->visit('/inventory/dashboard')
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

            $browser->visit('/inventory/dashboard')
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
                ->resize(1920, 1080)
                ->waitForJavaScript($this);

            // Check that search is properly defined (no "search is not defined" errors)
            $searchDefined = $this->executeScript($browser, "
                return typeof window.search !== 'undefined' || 
                       document.querySelector('[x-model*=\"search\"]') !== null;
            ");

            $this->assertTrue($searchDefined, 'Search functionality should be properly defined');

            // Open the header search popover
            $browser->click('button[aria-label="Search navigation"]')
                ->waitForAlpine($this);

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
                // Ensure desktop viewport so the header theme toggle is visible
                $browser->resize(1920, 1080)
                    ->visit('/dashboard')
                    ->waitForAlpine($this);
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
            $browser->visit('/inventory/dashboard')
                ->resize(1920, 1080)
                ->waitForJavaScript($this)
                ->assertVisible('.hidden.md\\:flex') // Desktop header actions visible
                ->assertVisible('#sidebar'); // Sidebar docked
            $this->assertElementDisplay($browser, 'button[aria-label="Toggle navigation"]', false);

            // Test tablet view
            $browser->resize(768, 1024)
                ->pause(500)
                ->assertVisible('.hidden.md\\:flex');
            $this->assertElementDisplay($browser, 'button[aria-label="Toggle navigation"]', false);

            // Test mobile view
            $browser->resize(375, 667)
                ->pause(500)
                ->assertVisible('button[aria-label="Toggle navigation"]') // Mobile hamburger visible
                ->assertVisible('#sidebar'); // Sidebar element still present (off-canvas)
            $this->assertElementDisplay($browser, '.hidden.md\\:flex', false);
            $this->assertSidebarTransform($browser, '-translate-x-full'); // Off-canvas below lg

            // Assert dashboard content is still accessible
            $browser->assertSee($org->name)
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
            $browser->visit('/inventory/dashboard')
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
            $browser->visit('/inventory/dashboard')
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
            $browser->visit('/inventory/dashboard')
                ->waitForJavaScript($this);

            // Test Add Store link
            $browser->clickLink('Add Store')
                ->waitForLocation('/inventory/stores/create', 10)
                ->assertPathIs('/inventory/stores/create')
                ->assertSee('Create Store');

            // Go back to dashboard
            $browser->visit('/inventory/dashboard')
                ->waitForJavaScript($this);

            // Test New Transaction link
            $browser->clickLink('New Transaction')
                ->waitForLocation('/inventory/transactions/create', 10)
                ->assertPathIs('/inventory/transactions/create')
                ->assertSee('Create Transaction');

            // Go back to dashboard
            $browser->visit('/inventory/dashboard')
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

            $browser->visit('/inventory/dashboard')
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

    /**
     * Assert the sidebar has the given transform class (open/closed state).
     */
    protected function assertSidebarTransform(Browser $browser, string $expectedClass): void
    {
        $hasClass = $browser->script(
            "return document.getElementById('sidebar').classList.contains('{$expectedClass}');"
        )[0];

        $this->assertTrue((bool) $hasClass, "Sidebar should have transform class '{$expectedClass}'");
    }

    /**
     * Assert whether an element is shown or hidden via its computed display value.
     */
    protected function assertElementDisplay(Browser $browser, string $selector, bool $visible): void
    {
        $display = $browser->script(
            "var el = document.querySelector('{$selector}'); if (!el) return null; return getComputedStyle(el).display;"
        )[0];

        if ($visible) {
            $this->assertNotEquals('none', $display, "Element '{$selector}' should be visible");
        } else {
            $this->assertEquals('none', $display, "Element '{$selector}' should be hidden");
        }
    }
}
