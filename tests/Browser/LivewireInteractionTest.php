<?php

namespace Tests\Browser;

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\HandlesLivewireTesting;
use Tests\DuskTestCase;

class LivewireInteractionTest extends DuskTestCase
{
    use HandlesLivewireTesting;

    protected ?Organization $organization = null;

    protected ?User $adminUser = null;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->adminUser = User::factory()->create(['email_verified_at' => now()]);
        $this->regularUser = User::factory()->create(['email_verified_at' => now()]);

        $this->organization->users()->attach($this->adminUser->id, [
            'roles' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->organization->users()->attach($this->regularUser->id, [
            'roles' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test FeeManager component basic functionality.
     */
    public function test_fee_manager_component_basic_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/fees')
                ->waitForLivewireToLoad($browser)
                ->assertLivewireComponentPresent($browser, 'fee-manager')
                ->assertNoLivewireErrors($browser);

            // Test component initialization
            $browser->assertLivewirePropertyExists($browser, 'fee-manager', 'search')
                ->assertLivewirePropertyEquals($browser, 'fee-manager', 'search', '')
                ->assertLivewirePropertyEquals($browser, 'fee-manager', 'status', 'all')
                ->assertLivewirePropertyEquals($browser, 'fee-manager', 'feeType', 'all')
                ->assertLivewirePropertyExists($browser, 'fee-manager', 'showCreateForm')
                ->assertLivewirePropertyEquals($browser, 'fee-manager', 'showCreateForm', false);
        });
    }

    /**
     * Test FeeManager search functionality.
     */
    public function test_fee_manager_search_functionality(): void
    {
        $member = Member::factory()->create(['organization_id' => $this->organization->id]);
        MemberFee::factory()->create([
            'member_id' => $member->id,
            'organization_id' => $this->organization->id,
            'description' => 'Test Fee Description',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/fees')
                ->waitForLivewireToLoad($browser)
                ->assertLivewireComponentPresent($browser, 'fee-manager');

            // Test search functionality
            $browser->type('[wire\\:model.live="search"]', 'Test Fee')
                ->pause(1000) // Wait for debouncing
                ->assertSee('Test Fee Description');

            // Test search clearing
            $browser->clear('[wire\\:model.live="search"]')
                ->pause(1000)
                ->assertDontSee('Test Fee Description');
        });
    }

    /**
     * Test FeeManager form interactions.
     */
    public function test_fee_manager_form_interactions(): void
    {
        $member = Member::factory()->create(['organization_id' => $this->organization->id]);

        $this->browse(function (Browser $browser) use ($member) {
            $browser->loginAs($this->adminUser)
                ->visit('/fees')
                ->waitForLivewireToLoad($browser)
                ->assertLivewireComponentPresent($browser, 'fee-manager');

            // Test opening create form
            $browser->click('[wire\\:click="showCreateFeeForm"]')
                ->pause(1000) // Wait for Livewire update
                ->assertSee('Create New Fee');

            // Verify form opened
            $this->assertLivewirePropertyEquals($browser, 'fee-manager', 'showCreateForm', true);

            // Test form field interactions
            $browser->select('[wire\\:model="fee_type"]', 'subscription')
                ->pause(200);
            
            $this->assertLivewirePropertyEquals($browser, 'fee-manager', 'fee_type', 'subscription');

            $browser->type('[wire\\:model="description"]', 'Test Subscription Fee')
                ->pause(200);
            
            $this->assertLivewirePropertyEquals($browser, 'fee-manager', 'description', 'Test Subscription Fee');

            // Test form closing
            $browser->click('[wire\\:click="hideCreateFeeForm"]')
                ->pause(1000);

            $this->assertLivewirePropertyEquals($browser, 'fee-manager', 'showCreateForm', false);
        });
    }

    /**
     * Test Alpine.js and Livewire integration.
     */
    public function test_alpine_livewire_integration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/fees')
                ->waitForLivewireToLoad($browser);

            // Test if Alpine and Livewire are both loaded
            $script = '
                return {
                    alpineLoaded: typeof window.Alpine !== "undefined",
                    livewireLoaded: typeof window.Livewire !== "undefined",
                    livewireComponents: window.Livewire ? window.Livewire.components.componentsArray.length : 0,
                    alpineComponents: document.querySelectorAll("[x-data]").length
                };
            ';

            $result = $browser->script($script)[0] ?? [];
            $this->assertTrue($result['alpineLoaded'] ?? false, 'Alpine.js should be loaded');
            $this->assertTrue($result['livewireLoaded'] ?? false, 'Livewire should be loaded');
            $this->assertGreaterThan(0, $result['livewireComponents'] ?? 0, 'Should have Livewire components');
        });
    }
}