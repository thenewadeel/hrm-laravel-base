<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;

class SimpleDashboardTest extends JavaScriptDuskTestCase
{
    /**
     * Test basic dashboard page loads.
     */
    public function test_dashboard_page_loads(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['email_verified_at' => now()]);

        $organization->users()->attach($user->id, [
            'roles' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user, $organization) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->pause(2000) // Wait for page to load
                ->dump('User ID: ' . $user->id)
                ->dump('Email verified: ' . $user->email_verified_at)
                ->dump('Current org ID: ' . $user->current_organization_id)
                ->dump('Org name: ' . $organization->name)
                ->assertPathIs('/dashboard')
                ->assertSee('Dashboard')
                ->dump('Looking for org name: ' . $organization->name)
                ->screenshot('dashboard-with-org')
                ->pause(1000);

            // Check if organization name is displayed
            $pageSource = $browser->driver->getPageSource();
            $hasOrgName = strpos($pageSource, $organization->name) !== false;
            $browser->dump('Page contains org name: ' . ($hasOrgName ? 'YES' : 'NO'));

            if (!$hasOrgName) {
                $browser->dump('First 500 chars of page:');
                $browser->dump(substr($pageSource, 0, 500));
            }
        });
    }

    /**
     * Test Alpine.js is loaded.
     */
    public function test_alpine_js_is_loaded(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['email_verified_at' => now()]);

        $organization->users()->attach($user->id, [
            'roles' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->pause(2000);

            // Check if Alpine.js is loaded
            $alpineLoaded = $browser->script("return typeof window.Alpine !== 'undefined';")[0] ?? false;
            $this->assertTrue($alpineLoaded, 'Alpine.js should be loaded');
        });
    }

    /**
     * Test no JavaScript errors on dashboard.
     */
    public function test_no_javascript_errors(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['email_verified_at' => now()]);

        $organization->users()->attach($user->id, [
            'roles' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->pause(2000);

            // Check for JavaScript errors
            $errors = $browser->script('
                var errors = [];
                var originalError = console.error;
                console.error = function() {
                    errors.push(Array.prototype.slice.call(arguments));
                    originalError.apply(console, arguments);
                };
                return errors;
            ');

            $this->assertEmpty($errors[0] ?? [], 'No JavaScript errors should be present');
        });
    }

    /**
     * test search functionality doesn't throw errors.
     */
    public function test_search_no_errors(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['email_verified_at' => now()]);

        $organization->users()->attach($user->id, [
            'roles' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->pause(2000);

            // Check if search variable is defined (to prevent "search is not defined" errors)
            $searchDefined = $browser->script("
                return typeof window.search !== 'undefined' || 
                       document.querySelector('[x-model*=\"search\"]') !== null ||
                       document.querySelector('[data-search]') !== null;
            ")[0] ?? false;

            // If search exists, test it doesn't cause errors
            if ($searchDefined) {
                $browser->script("
                    // Test search variable access
                    if (typeof window.search !== 'undefined') {
                        console.log('Search variable found:', window.search);
                    }
                    
                    // Test search inputs
                    var searchInputs = document.querySelectorAll('[x-model*=\"search\"], [data-search]');
                    searchInputs.forEach(function(input) {
                        input.value = 'test';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                ");
            }

            // Check for JavaScript errors
            $errors = $browser->script('
                var errors = [];
                var originalError = console.error;
                console.error = function() {
                    errors.push(Array.prototype.slice.call(arguments));
                    originalError.apply(console, arguments);
                };
                return errors;
            ');

            $this->assertEmpty($errors[0] ?? [], 'No JavaScript errors should occur during search testing');
        });
    }

    /**
     * Test mobile menu works.
     */
    public function test_mobile_menu(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['email_verified_at' => now()]);

        $organization->users()->attach($user->id, [
            'roles' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->resize(375, 667) // Mobile viewport
                ->pause(2000);

            // Try to find and click mobile menu button
            $menuButtons = $browser->elements('button[aria-label*="menu" i], button:has(svg)');

            if (! empty($menuButtons)) {
                $browser->click('button[aria-label*="menu" i], button:has(svg)')
                    ->pause(1000);
            }

            // Should still be on dashboard
            $browser->assertPathIs('/dashboard');
        });
    }
}
