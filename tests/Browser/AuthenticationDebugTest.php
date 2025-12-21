<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;

class AuthenticationDebugTest extends JavaScriptDuskTestCase
{
    /**
     * Debug authentication flow.
     */
    public function test_authentication_debug(): void
    {
        $this->browse(function (Browser $browser) {
            // Create organization and user
            $organization = Organization::factory()->create();
            $user = User::factory()->create();

            // Attach user to organization
            $organization->users()->attach($user->id, [
                'roles' => json_encode(['admin']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Debug info
            dump("Organization: " . $organization->name);
            dump("User: " . $user->email);

            // Login and visit
            $browser->loginAs($user)
                ->visit('/')
                ->pause(5000)
                ->screenshot('auth-debug-after-login')
                ->dump('Current URL: ' . $browser->driver->getCurrentURL())
                ->dump('Page title: ' . $browser->driver->getTitle());

            // Try to find any text on the page
            $pageSource = $browser->driver->getPageSource();
            dump('Page source length: ' . strlen($pageSource));
            dump('Page contains org name: ' . (strpos($pageSource, $organization->name) !== false ? 'YES' : 'NO'));
            dump('Page contains Dashboard: ' . (strpos($pageSource, 'Dashboard') !== false ? 'YES' : 'NO'));
        });
    }

    /**
     * Test unauthenticated page access.
     */
    public function test_unauthenticated_access(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(3000)
                ->screenshot('unauthenticated-access')
                ->dump('Unauthenticated page title: ' . $browser->driver->getTitle());
        });
    }
}