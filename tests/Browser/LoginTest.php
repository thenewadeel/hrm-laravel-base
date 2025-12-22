<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    /**
     * Test login page content.
     */
    public function test_login_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->pause(3000);

            $title = $browser->driver->getTitle();
            $source = $browser->driver->getPageSource();

            // echo "\n=== LOGIN PAGE DEBUG ===\n";
            // echo "Title: " . $title . "\n";
            // echo "URL: " . $browser->driver->getCurrentURL() . "\n";
            // echo "First 1000 chars:\n" . substr($source, 0, 1000) . "\n";
            // echo "====================\n";

            $browser->screenshot('login_page_content');
        });
    }

    /**
     * Test login flow works properly.
     */
    public function test_login_flow(): void
    {
        $this->browse(function (Browser $browser) {
            // Create a user and organization
            $user = \App\Models\User::factory()->create([
                'email' => 'test@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]);

            $organization = \App\Models\Organization::factory()->create();

            // Attach user to organization
            $organization->users()->attach($user->id, [
                'roles' => json_encode(['admin']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Set current organization for the user
            $user->current_organization_id = $organization->id;
            $user->save();

            // Test using loginAs helper (simulates authenticated state)
            $browser->loginAs($user)
                ->visit('/')
                ->pause(2000);

            // Verify we're not redirected to login
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertStringNotContainsString('/login', $currentUrl);

            // Verify dashboard content loads
            $browser->assertSee('Dashboard');

            $browser->screenshot('login_flow_success');
        });
    }
}
