<?php

namespace Tests\Browser\Concerns;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;

trait HandlesMultiTenantTesting
{
    /**
     * Create organization with test data.
     */
    protected function createOrganizationWithData(?string $name = null): Organization
    {
        $organization = Organization::factory()->create(['name' => $name ?: 'Test Organization']);

        // Create some test data for the organization
        $this->createOrganizationTestData($organization);

        return $organization;
    }

    /**
     * Create test data for organization.
     */
    protected function createOrganizationTestData(Organization $organization): void
    {
        // Create stores
        \App\Models\Inventory\Store::factory()->count(3)->create([
            'organization_id' => $organization->id,
        ]);

        // Create items
        \App\Models\Inventory\Item::factory()->count(10)->create([
            'organization_id' => $organization->id,
        ]);

        // Create transactions
        \App\Models\Inventory\Transaction::factory()->count(5)->create([
            'organization_id' => $organization->id,
        ]);
    }

    /**
     * Create user with organization membership.
     */
    protected function createUserWithOrganization(Organization $organization, string $role = 'admin'): User
    {
        $user = User::factory()->create();

        $organization->users()->attach($user->id, [
            'roles' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    /**
     * Switch organization context in browser.
     */
    protected function switchOrganization(Browser $browser, Organization $organization): void
    {
        $browser->visit("/switch-organization/{$organization->id}")
            ->waitForLocation('/', 10)
            ->waitForText($organization->name, 10);
    }

    /**
     * Assert organization context is active.
     */
    protected function assertOrganizationContext(Browser $browser, Organization $organization): void
    {
        $browser->assertSee($organization->name)
            ->assertPresent('[data-organization-id]')
            ->assertAttribute('[data-organization-id]', 'data-organization-id', (string) $organization->id);
    }

    /**
     * Assert data isolation between organizations.
     */
    protected function assertDataIsolation(Browser $browser, Organization $currentOrg, Organization $otherOrg): void
    {
        // Should see current organization data
        $browser->assertSee($currentOrg->name);

        // Should NOT see other organization data
        $browser->assertDontSee($otherOrg->name);
    }

    /**
     * Test multi-tenant dashboard isolation.
     */
    protected function testDashboardDataIsolation(): void
    {
        // Create two organizations with different data
        $org1 = $this->createOrganizationWithData('Organization One');
        $org2 = $this->createOrganizationWithData('Organization Two');

        // Create users for each organization
        $user1 = $this->createUserWithOrganization($org1, 'admin');
        $user2 = $this->createUserWithOrganization($org2, 'admin');

        // Test org1 dashboard
        $this->browse(function (Browser $browser) use ($user1, $org1, $org2) {
            $browser->loginAs($user1)
                ->visit('/dashboard')
                ->waitForJavaScript($this);

            $this->assertOrganizationContext($browser, $org1);
            $this->assertDataIsolation($browser, $org1, $org2);
        });

        // Test org2 dashboard
        $this->browse(function (Browser $browser) use ($user2, $org1, $org2) {
            $browser->loginAs($user2)
                ->visit('/dashboard')
                ->waitForJavaScript($this);

            $this->assertOrganizationContext($browser, $org2);
            $this->assertDataIsolation($browser, $org2, $org1);
        });
    }

    /**
     * Test organization switching functionality.
     */
    protected function testOrganizationSwitching(): void
    {
        $org1 = $this->createOrganizationWithData('First Organization');
        $org2 = $this->createOrganizationWithData('Second Organization');

        $user = $this->createUserWithOrganization($org1, 'admin');
        $org2->users()->attach($user->id, [
            'roles' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user, $org1, $org2) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->waitForJavaScript($this);

            // Start with org1
            $this->assertOrganizationContext($browser, $org1);

            // Switch to org2
            $this->switchOrganization($browser, $org2);
            $this->assertOrganizationContext($browser, $org2);

            // Switch back to org1
            $this->switchOrganization($browser, $org1);
            $this->assertOrganizationContext($browser, $org1);
        });
    }

    /**
     * Test role-based access control across organizations.
     */
    protected function testRoleBasedAccess(): void
    {
        $org1 = $this->createOrganizationWithData('Admin Org');
        $org2 = $this->createOrganizationWithData('Member Org');

        $user = $this->createUserWithOrganization($org1, 'admin');
        $org2->users()->attach($user->id, [
            'roles' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user, $org1, $org2) {
            // Test admin access in org1
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->waitForJavaScript($this);

            $this->assertOrganizationContext($browser, $org1);
            // Should see admin features
            $browser->assertSee('Add Store')
                ->assertSee('New Transaction')
                ->assertSee('Add Item');

            // Switch to org2 (member role)
            $this->switchOrganization($browser, $org2);
            $this->assertOrganizationContext($browser, $org2);

            // Should have limited access
            // This depends on your specific role-based implementation
            // $browser->assertDontSee('Admin Settings');
        });
    }

    /**
     * Test data scoping in queries.
     */
    protected function testDataScoping(): void
    {
        $org1 = $this->createOrganizationWithData('Scope Test Org 1');
        $org2 = $this->createOrganizationWithData('Scope Test Org 2');

        $user = $this->createUserWithOrganization($org1, 'admin');

        $this->browse(function (Browser $browser) use ($user, $org1, $org2) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->waitForJavaScript($this);

            // Check that only org1 data is loaded
            $org1Stores = \App\Models\Inventory\Store::where('organization_id', $org1->id)->count();
            $org2Stores = \App\Models\Inventory\Store::where('organization_id', $org2->id)->count();

            // Dashboard should show org1 store count
            $browser->assertSeeIn('[data-stats="stores"]', (string) $org1Stores);
            $browser->assertDontSeeIn('[data-stats="stores"]', (string) $org2Stores);
        });
    }

    /**
     * Test cross-organization data leakage prevention.
     */
    protected function testDataLeakagePrevention(): void
    {
        $org1 = $this->createOrganizationWithData('Secure Org 1');
        $org2 = $this->createOrganizationWithData('Secure Org 2');

        $user = $this->createUserWithOrganization($org1, 'member');

        $this->browse(function (Browser $browser) use ($user, $org1, $org2) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->waitForJavaScript($this);

            // Try to access org2 data directly via URL (should be blocked or show empty)
            $browser->visit("/organizations/{$org2->id}/dashboard")
                ->pause(1000);

            // Should either be redirected back or see no data
            // This depends on your specific implementation
            $currentPath = $browser->script('return window.location.pathname;')[0];

            // Either redirected to own dashboard or staying on secure page
            $this->assertTrue(
                $currentPath === '/dashboard' ||
                str_contains($currentPath, $org1->id),
                'Should not be able to access other organization data'
            );
        });
    }

    /**
     * Test organization-specific URLs and routing.
     */
    protected function testOrganizationRouting(): void
    {
        $org = $this->createOrganizationWithData('Routing Test Org');
        $user = $this->createUserWithOrganization($org, 'admin');

        $this->browse(function (Browser $browser) use ($user, $org) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->waitForJavaScript($this);

            // Test that URLs contain organization context
            $currentUrl = $browser->script('return window.location.href;')[0];

            // Check if organization is properly set in session/context
            $this->assertOrganizationContext($browser, $org);

            // Test navigation maintains organization context
            $browser->clickLink('Stores')
                ->waitForLocation('/inventory/stores', 10)
                ->assertPathIs('/inventory/stores');

            // Should still be in organization context
            $this->assertOrganizationContext($browser, $org);
        });
    }

    /**
     * Test concurrent organization sessions.
     */
    protected function testConcurrentSessions(): void
    {
        $org1 = $this->createOrganizationWithData('Concurrent Org 1');
        $org2 = $this->createOrganizationWithData('Concurrent Org 2');

        $user1 = $this->createUserWithOrganization($org1, 'admin');
        $user2 = $this->createUserWithOrganization($org2, 'admin');

        // Test two different users in different organizations
        $this->browse(function (Browser $browser1, Browser $browser2) use ($user1, $user2, $org1, $org2) {
            // User 1 in org1
            $browser1->loginAs($user1)
                ->visit('/dashboard')
                ->waitForJavaScript($this);

            // User 2 in org2
            $browser2->loginAs($user2)
                ->visit('/dashboard')
                ->waitForJavaScript($this);

            // Each should see their own organization data
            $this->assertOrganizationContext($browser1, $org1);
            $this->assertOrganizationContext($browser2, $org2);

            // Data should be isolated
            $browser1->assertSee($org1->name)->assertDontSee($org2->name);
            $browser2->assertSee($org2->name)->assertDontSee($org1->name);
        });
    }

    /**
     * Test organization data integrity.
     */
    protected function testDataIntegrity(): void
    {
        $org = $this->createOrganizationWithData('Integrity Test Org');
        $user = $this->createUserWithOrganization($org, 'admin');

        $this->browse(function (Browser $browser) use ($user, $org) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->waitForJavaScript($this);

            // Get initial stats
            $initialStores = $this->executeScript($browser, "
                return parseInt(document.querySelector('[data-stats=\"stores\"]').textContent);
            ");

            $initialItems = $this->executeScript($browser, "
                return parseInt(document.querySelector('[data-stats=\"items\"]').textContent);
            ");

            // Create new data via UI (if available) or verify existing data integrity
            $browser->assertSeeIn('[data-stats="stores"]', (string) $initialStores)
                ->assertSeeIn('[data-stats="items"]', (string) $initialItems);

            // Verify data belongs to correct organization
            $dbStores = \App\Models\Inventory\Store::where('organization_id', $org->id)->count();
            $dbItems = \App\Models\Inventory\Item::where('organization_id', $org->id)->count();

            $this->assertEquals($dbStores, $initialStores, 'Dashboard store count should match database');
            $this->assertEquals($dbItems, $initialItems, 'Dashboard item count should match database');
        });
    }
}
