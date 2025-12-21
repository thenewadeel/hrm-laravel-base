<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RouteTest extends DuskTestCase
{
    /**
     * Test if fees route works without authentication.
     */
    public function test_fees_route_without_auth(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/fees')
                ->pause(2000);

            // Get page title
            $title = $browser->driver->getTitle();
            echo "\n=== PAGE TITLE: {$title} ===\n";

            // Check current URL
            $url = $browser->driver->getCurrentURL();
            echo "\n=== CURRENT URL: {$url} ===\n";

            // Get page source
            $source = $browser->driver->getPageSource();
            echo "\n=== SOURCE LENGTH: " . strlen($source) . " ===\n";

            // Take screenshot
            $browser->screenshot('route_test');

            // Should redirect to login
            $this->assertStringContainsString('/login', $url, 'Should redirect to login');
        });
    }

    /**
     * Test if fees route works with authentication.
     */
    public function test_fees_route_with_auth(): void
    {
        $this->browse(function (Browser $browser) {
            $user = \App\Models\User::factory()->create(['email_verified_at' => now()]);
            $org = \App\Models\Organization::factory()->create();
            
            $org->users()->attach($user->id, [
                'roles' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Set current organization for user
            $user->current_organization_id = $org->id;
            $user->save();

            $browser->loginAs($user)
                ->visit('/fees')
                ->pause(3000);

            // Get page title
            $title = $browser->driver->getTitle();
            echo "\n=== PAGE TITLE: {$title} ===\n";

            // Check current URL
            $url = $browser->driver->getCurrentURL();
            echo "\n=== CURRENT URL: {$url} ===\n";

            // Get page source
            $source = $browser->driver->getPageSource();
            echo "\n=== SOURCE LENGTH: " . strlen($source) . " ===\n";

            // Look for expected content
            if (strpos($source, '💰 Fees') !== false) {
                echo "\n=== FOUND FEES HEADER ===\n";
            }

            if (strpos($source, 'livewire:membership.fee-manager') !== false) {
                echo "\n=== FOUND FEE MANAGER COMPONENT ===\n";
            }

            // Take screenshot
            $browser->screenshot('route_test_with_auth');
        });
    }
}