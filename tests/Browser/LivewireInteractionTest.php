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
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager')
                ->assertNoLivewireErrors();

            // Test component initialization
            $browser->assertLivewirePropertyExists('fee-manager', 'search')
                ->assertLivewirePropertyEquals('fee-manager', 'search', '')
                ->assertLivewirePropertyEquals('fee-manager', 'status', 'all')
                ->assertLivewirePropertyEquals('fee-manager', 'feeType', 'all')
                ->assertLivewirePropertyExists('fee-manager', 'showCreateForm')
                ->assertLivewirePropertyEquals('fee-manager', 'showCreateForm', false);
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
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test search functionality
            $browser->type('[wire\\:model="search"]', 'Test Fee')
                ->pause(500) // Wait for debouncing
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'search', 'Test Fee')
                ->assertSee('Test Fee Description');

            // Test search clearing
            $browser->clear('[wire\\:model="search"]')
                ->pause(500)
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'search', '');
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
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test opening create form
            $browser->click('[wire\\:click="showCreateFeeForm"]')
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'showCreateForm', true)
                ->assertSee('Create New Fee');

            // Test form field interactions
            $browser->select('[wire\\:model="fee_type"]', 'subscription')
                ->assertLivewirePropertyEquals('fee-manager', 'fee_type', 'subscription')
                ->type('[wire\\:model="description"]', 'Test Subscription Fee')
                ->assertLivewirePropertyEquals('fee-manager', 'description', 'Test Subscription Fee')
                ->type('[wire\\:model="amount"]', '100.50')
                ->assertLivewirePropertyEquals('fee-manager', 'amount', 100.50)
                ->type('[wire\\:model="due_date"]', now()->addDays(30)->format('Y-m-d'))
                ->assertLivewirePropertyEquals('fee-manager', 'due_date', now()->addDays(30)->format('Y-m-d'));

            // Test member selection
            $browser->select('[wire\\:model="member_id"]', $member->id)
                ->assertLivewirePropertyEquals('fee-manager', 'member_id', $member->id);

            // Test form submission
            $browser->click('[wire\\:click="createFee"]')
                ->waitForLivewireUpdate('fee-manager')
                ->pause(1000); // Wait for processing

            // Check for success notification
            $browser->assertSee('Fee created successfully')
                ->assertLivewirePropertyEquals('fee-manager', 'showCreateForm', false);
        });
    }

    /**
     * Test FeeManager payment form.
     */
    public function test_fee_manager_payment_form(): void
    {
        $member = Member::factory()->create(['organization_id' => $this->organization->id]);
        $fee = MemberFee::factory()->create([
            'member_id' => $member->id,
            'organization_id' => $this->organization->id,
            'amount' => 100.00,
            'paid_amount' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($fee) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test opening payment form
            $browser->click("[wire\\:click=\"showPaymentForm({$fee->id})\"]")
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'showPaymentForm', true)
                ->assertLivewirePropertyEquals('fee-manager', 'selectedFeeId', $fee->id)
                ->assertLivewirePropertyEquals('fee-manager', 'payment_amount', $fee->remaining_amount);

            // Test payment form fields
            $browser->type('[wire\\:model="payment_amount"]', '50.00')
                ->assertLivewirePropertyEquals('fee-manager', 'payment_amount', 50.00)
                ->select('[wire\\:model="payment_method"]', 'bank_transfer')
                ->assertLivewirePropertyEquals('fee-manager', 'payment_method', 'bank_transfer')
                ->type('[wire\\:model="payment_reference"]', 'REF123')
                ->assertLivewirePropertyEquals('fee-manager', 'payment_reference', 'REF123');

            // Test payment processing
            $browser->click('[wire\\:click="processPayment"]')
                ->waitForLivewireUpdate('fee-manager')
                ->pause(1000); // Wait for processing

            // Check for success notification
            $browser->assertSee('Payment processed successfully')
                ->assertLivewirePropertyEquals('fee-manager', 'showPaymentForm', false);
        });
    }

    /**
     * Test FeeManager filtering and sorting.
     */
    public function test_fee_manager_filtering_and_sorting(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test status filtering
            $browser->select('[wire\\:model="status"]', 'pending')
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'status', 'pending');

            // Test fee type filtering
            $browser->select('[wire\\:model="feeType"]', 'subscription')
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'feeType', 'subscription');

            // Test sorting
            $browser->click('[wire\\:click="sort(\'amount\')"]')
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'sortBy', 'amount')
                ->assertLivewirePropertyEquals('fee-manager', 'sortDirection', 'asc');

            // Test per page selection
            $browser->select('[wire\\:model="perPage"]', '25')
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'perPage', 25);
        });
    }

    /**
     * Assert Livewire component is present (browser-less version).
     */
    public function assertLivewireComponentPresent(string $componentName): self
    {
        $this->assertPresent("[wire\\:id*='{$componentName}']");
        return $this;
    }

    /**
     * Assert Livewire component contains text (browser-less version).
     */
    public function assertLivewireComponentContains(string $componentName, string $text): self
    {
        $this->assertSeeIn("[wire\\:id*='{$componentName}']", $text);
        return $this;
    }

    /**
     * Assert Livewire property equals (browser-less version).
     */
    public function assertLivewirePropertyEquals(string $componentName, string $property, $expected): self
    {
        $script = "
            var component = Livewire.find('{$componentName}');
            return component ? component.get('{$property}') : null;
        ";
        
        $actual = $this->script($script)[0];
        $this->assertEquals($expected, $actual, "Livewire property '{$property}' does not match expected value");
        
        return $this;
    }

    /**
     * Assert Livewire property exists (browser-less version).
     */
    public function assertLivewirePropertyExists(string $componentName, string $property): self
    {
        $script = "
            var component = Livewire.find('{$componentName}');
            return component ? component.hasOwnProperty('{$property}') : false;
        ";
        
        $exists = $this->script($script)[0];
        $this->assertTrue($exists, "Livewire property '{$property}' does not exist");
        
        return $this;
    }

    /**
     * Assert no Livewire errors (browser-less version).
     */
    public function assertNoLivewireErrors(): self
    {
        $script = "
            var errors = [];
            Livewire.components.components.forEach(function(component) {
                if (component.errors && Object.keys(component.errors).length > 0) {
                    errors.push(component.id + ': ' + JSON.stringify(component.errors));
                }
            });
            return errors;
        ";
        
        $errors = $this->script($script)[0];
        $this->assertEmpty($errors, 'Livewire errors found: ' . implode(', ', $errors));
        
        return $this;
    }

    /**
     * Wait for Livewire to load (browser-less version).
     */
    public function waitForLivewireToLoad(): self
    {
        $this->waitForLivewire();
        return $this;
    }

    /**
     * Test FeeManager component basic functionality.
     */
    public function test_member_manager_component_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/members')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('member-manager')
                ->assertNoLivewireErrors();

            // Test search functionality
            $browser->type('[wire\\:model="search"]', $this->regularUser->name)
                ->pause(500)
                ->waitForLivewireUpdate('member-manager')
                ->assertLivewirePropertyEquals('member-manager', 'search', $this->regularUser->name)
                ->assertSee($this->regularUser->name);

            // Test organization filtering
            $browser->select('[wire\\:model="organizationId"]', $this->organization->id)
                ->waitForLivewireUpdate('member-manager')
                ->assertLivewirePropertyEquals('member-manager', 'organizationId', $this->organization->id);
        });
    }

    /**
     * Test Accounting Dashboard component.
     */
    public function test_accounting_dashboard_component(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/accounts/dashboard')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('dashboard')
                ->assertNoLivewireErrors();

            // Test dashboard data loading
            $browser->assertLivewirePropertyExists('dashboard', 'summary')
                ->waitForLivewireUpdate('dashboard');

            // Test refresh functionality
            $browser->click('[wire\\:click="generateSummary"]')
                ->waitForLivewireUpdate('dashboard')
                ->pause(1000); // Wait for data refresh
        });
    }

    /**
     * Test Navigation component interactions.
     */
    public function test_navigation_component_interactions(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('navigation-main')
                ->assertNoLivewireErrors();

            // Test navigation menu items
            $browser->assertSee('Dashboard')
                ->assertSee('Accounts')
                ->assertSee('HRM')
                ->assertSee('Inventory')
                ->assertSee('Organization');

            // Test navigation clicks
            $browser->clickLink('Accounts')
                ->waitForLocation('/accounts')
                ->assertPathIs('/accounts');
        });
    }

    /**
     * Test Alpine.js and Livewire integration.
     */
    public function test_alpine_livewire_integration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertAlpineLivewireIntegration();

            // Test Alpine data binding with Livewire
            $script = '
                // Test if Alpine can access Livewire component data
                const livewireComponent = window.Livewire.components.componentsArray[0];
                return livewireComponent && livewireComponent.$wire !== undefined;
            ';

            $hasAccess = $browser->script($script)[0] ?? false;
            $this->assertTrue($hasAccess, 'Alpine.js cannot access Livewire component data');
        });
    }

    /**
     * Test Livewire component lifecycle hooks.
     */
    public function test_livewire_component_lifecycle(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test component initialization
            $this->assertLivewireLifecycleHooks($browser, 'fee-manager');

            // Test component updates
            $browser->type('[wire\\:model="search"]', 'test')
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewireStateSynchronized('fee-manager');
        });
    }

    /**
     * Test Livewire error handling.
     */
    public function test_livewire_error_handling(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test validation errors
            $browser->click('[wire\\:click="showCreateFeeForm"]')
                ->waitForLivewireUpdate('fee-manager')
                ->click('[wire\\:click="createFee"]') // Submit empty form
                ->waitForLivewireUpdate('fee-manager')
                ->pause(500);

            // Check for validation error messages
            $browser->assertSee('required');
        });
    }

    /**
     * Test Livewire loading states.
     */
    public function test_livewire_loading_states(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Simulate network latency to test loading states
            $this->simulateNetworkLatency($browser, 2000);

            // Trigger an action that should show loading state
            $browser->type('[wire\\:model="search"]', 'test search')
                ->assertLivewireIsLoading('fee-manager')
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewireIsNotLoading('fee-manager');

            // Restore normal network behavior
            $this->restoreNetworkBehavior($browser);
        });
    }

    /**
     * Test Livewire real-time updates and polling.
     */
    public function test_livewire_real_time_updates(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test event dispatching
            $browser->script("
                window.addEventListener('fee-created', function(e) {
                    window.testEventReceived = true;
                    window.testEventData = e.detail;
                });
            ");

            // Trigger event
            $browser->click('[wire\\:click="showCreateFeeForm"]')
                ->waitForLivewireUpdate('fee-manager');

            // Check if event system is working
            $eventSystemWorking = $browser->script('
                return window.Livewire.components.componentsArray.some(c => c.effects.dispatched);
            ')[0] ?? false;

            $this->assertTrue($eventSystemWorking, 'Livewire event system is not working');
        });
    }

    /**
     * Test Livewire component method calls from frontend.
     */
    public function test_livewire_method_calls_from_frontend(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test method existence
            $this->assertLivewireMethodExists($browser, 'fee-manager', 'showCreateFeeForm');
            $this->assertLivewireMethodExists($browser, 'fee-manager', 'hideCreateFeeForm');
            $this->assertLivewireMethodExists($browser, 'fee-manager', 'createFee');
            $this->assertLivewireMethodExists($browser, 'fee-manager', 'processPayment');

            // Test method calls
            $this->callLivewireMethod($browser, 'fee-manager', 'showCreateFeeForm');
            $browser->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'showCreateForm', true);

            $this->callLivewireMethod($browser, 'fee-manager', 'hideCreateFeeForm');
            $browser->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'showCreateForm', false);
        });
    }

    /**
     * Test Livewire component state management.
     */
    public function test_livewire_state_management(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test property setting and getting
            $this->setLivewireProperty($browser, 'fee-manager', 'search', 'test value');
            $browser->waitForLivewireUpdate('fee-manager');

            $searchValue = $this->getLivewireProperty($browser, 'fee-manager', 'search');
            $this->assertEquals('test value', $searchValue);

            // Test multiple property updates
            $this->setLivewireProperty($browser, 'fee-manager', 'status', 'pending');
            $this->setLivewireProperty($browser, 'fee-manager', 'feeType', 'subscription');
            $browser->waitForLivewireUpdate('fee-manager');

            $this->protectedAssertLivewirePropertyEquals($browser, 'fee-manager', 'status', 'pending');
            $this->protectedAssertLivewirePropertyEquals($browser, 'fee-manager', 'feeType', 'subscription');
        });
    }

    /**
     * Test Livewire modal interactions.
     */
    public function test_livewire_modal_interactions(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test modal opening
            $browser->click('[wire\\:click="showCreateFeeForm"]')
                ->waitForLivewireUpdate('fee-manager')
                ->assertPresent('.modal') // Assuming modal has this class
                ->assertSee('Create New Fee');

            // Test modal closing
            $browser->click('[wire\\:click="hideCreateFeeForm"]')
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'showCreateForm', false)
                ->waitUntilMissing('.modal', 5);
        });
    }

    /**
     * Test Livewire component reactivity.
     */
    public function test_livewire_component_reactivity(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test reactive updates
            $browser->type('[wire\\:model="search"]', 'reactive test')
                ->pause(500)
                ->assertLivewirePropertyEquals('fee-manager', 'search', 'reactive test');

            // Test computed properties reactivity
            $browser->select('[wire\\:model="status"]', 'paid')
                ->waitForLivewireUpdate('fee-manager')
                ->assertLivewirePropertyEquals('fee-manager', 'status', 'paid');

            // Test that UI updates reactively
            $browser->assertSee('paid'); // Assuming filtered results show status
        });
    }

    /**
     * Test Livewire component performance.
     */
    public function test_livewire_component_performance(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Measure response time for search
            $startTime = microtime(true);

            $browser->type('[wire\\:model="search"]', 'performance test')
                ->waitForLivewireUpdate('fee-manager');

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            // Assert response time is reasonable (less than 2 seconds)
            $this->assertLessThan(2000, $responseTime, 'Livewire component response time is too slow');
        });
    }

    /**
     * Test Livewire component accessibility.
     */
    public function test_livewire_component_accessibility(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad()
                ->assertLivewireComponentPresent('fee-manager');

            // Test ARIA attributes
            $browser->assertPresent('[wire\\:model][aria-label], [wire\\:model][aria-describedby]')
                ->assertPresent('button[wire\\:click][aria-label], button[wire\\:click][title]');

            // Test keyboard navigation
            $browser->keys('[wire\\:model="search"]', ['tab'])
                ->assertFocused('[wire\\:model="status"]') // Should focus next form element
                ->keys('[wire\\:model="status"]', ['tab'])
                ->assertFocused('[wire\\:model="feeType"]');
        });
    }

    /**
     * Test Livewire component security.
     */
    public function test_livewire_component_security(): void
    {
        $this->browse(function (Browser $browser) {
            // Test with regular user (should not have admin access)
            $browser->loginAs($this->regularUser)
                ->visit('/membership/fees')
                ->waitForLivewireToLoad();

            // Check if unauthorized actions are properly blocked
            $canAccessAdminFeatures = $browser->script("
                const component = window.Livewire.components.componentsArray.find(c => c.name.includes('fee-manager'));
                if (!component) return false;
                
                // Try to access admin-only methods
                try {
                    component.createFee();
                    return true; // If no error, security issue
                } catch (e) {
                    return false; // Expected behavior
                }
            ")[0] ?? true;

            $this->assertFalse($canAccessAdminFeatures, 'Security issue: Regular user can access admin features');
        });
    }
}
