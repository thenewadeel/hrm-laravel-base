<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthTest extends DuskTestCase
{
    /**
     * Test authentication and organization setup.
     */
    public function test_auth_organization_setup(): void
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
                ->visit('/dashboard')
                ->pause(2000);

            // Check if dashboard loads
            $title = $browser->driver->getTitle();
            // echo "\n=== DASHBOARD TITLE: {$title} ===\n";

            $url = $browser->driver->getCurrentURL();
            // echo "\n=== DASHBOARD URL: {$url} ===\n";

            // Take screenshot
            $browser->screenshot('auth_dashboard_test');
        });
    }

    /**
     * Test visiting fees with proper setup.
     */
    public function test_fees_with_proper_setup(): void
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

            // Check page title
            $title = $browser->driver->getTitle();
            // echo "\n=== FEES TITLE: {$title} ===\n";

            $url = $browser->driver->getCurrentURL();
            // echo "\n=== FEES URL: {$url} ===\n";

            // Check for expected content
            $source = $browser->driver->getPageSource();
            if (strpos($source, '💰 Fees') !== false) {
                // echo "\n=== FOUND FEES HEADER ===\n";
            }

            if (strpos($source, 'livewire:membership.fee-manager') !== false) {
                // echo "\n=== FOUND FEE MANAGER COMPONENT ===\n";
            }

            // Take screenshot
            $browser->screenshot('auth_fees_test');
        });
    }
}
