<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;

class FeeManagementSetupTest extends JavaScriptDuskTestCase
{
    /**
     * Test that the fee management test setup is working correctly.
     */
    public function test_fee_management_setup_works(): void
    {
        $this->browse(function (Browser $browser) {
            // Skip authentication for now - test page loading
            $browser->visit('/fees')
                ->pause(2000); // Wait for page load

            // Debug: check what tables exist before test
            $tables = \DB::select("SELECT name FROM sqlite_master WHERE type='table'");
            echo 'Tables before test: '.json_encode(array_column($tables, 'name'))."\n";

            // Test page loading without authentication
            $browser->visit('/fees')
                ->pause(2000); // Wait for page load

            // Debug: check what's actually on the page
            $pageTitle = $browser->script('return document.title;')[0];
            $bodyContent = $browser->script('return document.body.innerText;')[0];

            echo "Page title: $pageTitle\n";
            echo 'Body content preview: '.substr($bodyContent, 0, 300)."...\n";

            // Check for error indicators
            if (strpos($bodyContent, '500') !== false) {
                echo "Found 500 error in page content\n";
            }
            if (strpos($bodyContent, '404') !== false) {
                echo "Found 404 error in page content\n";
            }
            if (strpos($bodyContent, 'Unauthorized') !== false) {
                echo "Found Unauthorized in page content\n";
            }

            $browser->assertSee('Fees');

            // Test JavaScript is enabled
            $jsEnabled = $browser->script("return typeof Livewire !== 'undefined';")[0];
            $this->assertTrue($jsEnabled, 'Livewire should be loaded');

            // Test Alpine.js is loaded
            $alpineLoaded = $browser->script("return typeof Alpine !== 'undefined';")[0];
            $this->assertTrue($alpineLoaded, 'Alpine.js should be loaded');

            // Check for console errors
            $this->assertNoJavaScriptErrors($browser);
        });
    }

    /**
     * Test that fee management page loads with proper organization context.
     */
    public function test_fee_management_loads_with_organization_context(): void
    {
        $this->browse(function (Browser $browser) {
            $organization = $this->createAuthenticatedUser();

            $browser->visit('/fees')
                ->waitForText('💰 Fees', 10)
                ->assertSee('Manage member fees and payments');

            // Verify organization context is properly set (if such attribute exists)
            if ($browser->element('[data-organization-id]')) {
                $orgId = $browser->attribute('[data-organization-id]', 'data-organization-id');
                $this->assertEquals($organization->id, $orgId);
            }
        });
    }

    /**
     * Test that fee management page has proper Livewire components.
     */
    public function test_fee_management_has_livewire_components(): void
    {
        $this->browse(function (Browser $browser) {
            $this->createAuthenticatedUser();

            $browser->visit('/fees')
                ->waitForText('💰 Fees', 10)
                ->waitFor('[wire\\:id]', 10)
                ->assertPresent('[wire\\:id]');

            // Check for specific Livewire component
            $this->waitForLivewireComponent($browser, 'membership.fee-manager', 5);
        });
    }
}
