<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;

class AuthenticatedDuskTest extends JavaScriptDuskTestCase
{
    /**
     * Test user authentication flow.
     */
    public function test_user_authentication(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $organization->users()->attach($user->id, [
            'roles' => json_encode(['admin']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->pause(2000)
                ->assertPathIs('/dashboard')
                ->screenshot('authenticated-dashboard');
        });
    }

    /**
     * Test organization context switching.
     */
    public function test_organization_context(): void
    {
        $organization = Organization::factory()->create(['name' => 'Test Organization']);
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $organization->users()->attach($user->id, [
            'roles' => json_encode(['admin']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->pause(2000)
                ->assertPathIs('/dashboard')
                ->screenshot('organization-dashboard');
        });
    }
}
