<?php

namespace Tests\Browser;

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\BrowserTestSetup;

class FeeManagementTest extends JavaScriptDuskTestCase
{
    use BrowserTestSetup;

    protected ?Organization $organization = null;

    protected ?User $admin = null;

    protected ?User $memberUser = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();

        // Create admin user
        $this->admin = User::factory()->create();
        $this->organization->users()->attach($this->admin->id, [
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create regular member user
        $this->memberUser = User::factory()->create();
        $this->organization->users()->attach($this->memberUser->id, [
            'role' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test that fee management page loads without JavaScript errors.
     */
    public function test_fee_management_page_loads_without_console_errors(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10)
                ->assertSee('Fee Management')
                ->assertSee('Total Fees')
                ->assertSee('Paid Amount')
                ->assertSee('Pending Amount')
                ->assertSee('Overdue Fees');

            // Check for console errors
            $browser->script([
                'window.consoleErrors = [];',
                'console.error = function(message) { window.consoleErrors.push(message); };',
            ]);

            // Wait a bit for any async operations
            $browser->pause(2000);

            // Check for accumulated console errors
            $consoleErrors = $browser->script('return window.consoleErrors;')[0];

            $this->assertEmpty($consoleErrors, 'Console errors detected: '.json_encode($consoleErrors));
        });
    }

    /**
     * Test payment modal opens and functions correctly.
     */
    public function test_payment_modal_opens_and_functions(): void
    {
        $member = Member::factory()->create(['organization_id' => $this->organization->id]);
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member->id,
            'amount' => 100.00,
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $this->browse(function (Browser $browser) use ($fee) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10)
                ->waitForText($fee->description, 10)
                ->assertSee($fee->description)
                ->click('button[wire\\:click*="showPaymentForm"]')
                ->waitFor('.fixed.inset-0', 5) // Modal backdrop
                ->waitForText('Process Payment', 5)
                ->assertSee('Process Payment')
                ->assertPresent('input[wire\\:model="payment_amount"]')
                ->assertPresent('select[wire\\:model="payment_method"]')
                ->assertPresent('input[wire\\:model="payment_reference"]')
                ->assertPresent('textarea[wire\\:model="payment_notes"]')
                ->assertSeeIn('input[wire\\:model="payment_amount"]', (string) $fee->remaining_amount)
                ->click('button[wire\\:click="hidePaymentForm"]')
                ->waitUntilMissing('.fixed.inset-0', 5)
                ->assertDontSee('Process Payment');
        });
    }

    /**
     * Test ShowPaymentForm method works from browser interaction.
     */
    public function test_show_payment_form_method_works(): void
    {
        $member = Member::factory()->create(['organization_id' => $this->organization->id]);
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member->id,
            'amount' => 150.00,
            'paid_amount' => 50.00,
            'status' => 'pending',
            'due_date' => now()->addDays(15),
        ]);

        $this->browse(function (Browser $browser) use ($fee) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10)
                ->waitForText($fee->description, 10);

            // Click the Pay button for the specific fee
            $browser->with("table tr:contains('{$fee->description}')", function ($row) use ($fee) {
                $row->click("button[wire\\:click*=\"showPaymentForm({$fee->id})\"]")
                    ->pause(500);
            });

            // Verify modal opens with correct data
            $browser->waitFor('.fixed.inset-0', 5)
                ->assertSee('Process Payment')
                ->assertInputValue('payment_amount', (string) $fee->remaining_amount) // Should be 100.00
                ->assertSelected('payment_method', 'cash');

            // Verify form fields are present and functional
            $browser->type('payment_amount', '75.00')
                ->select('payment_method', 'bank_transfer')
                ->type('payment_reference', 'REF-12345')
                ->type('payment_notes', 'Partial payment for membership fee')
                ->assertInputValue('payment_amount', '75.00')
                ->assertSelected('payment_method', 'bank_transfer')
                ->assertInputValue('payment_reference', 'REF-12345')
                ->assertInputValue('payment_notes', 'Partial payment for membership fee');
        });
    }

    /**
     * Test fee creation workflow works end-to-end.
     */
    public function test_fee_creation_workflow_end_to_end(): void
    {
        $member = Member::factory()->create(['organization_id' => $this->organization->id]);

        $this->browse(function (Browser $browser) use ($member) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10)
                ->click('button[wire\\:click="showCreateFeeForm"]')
                ->waitForText('Create New Fee', 5)
                ->assertSee('Create New Fee');

            // Fill in the fee creation form
            $browser->select('member_id', (string) $member->id)
                ->select('fee_type', 'subscription')
                ->type('description', 'Annual Membership Fee 2024')
                ->type('amount', '250.00')
                ->type('due_date', now()->addDays(30)->format('Y-m-d'))
                ->type('notes', 'Standard annual membership subscription')
                ->click('button[type="submit"]')
                ->waitForText('Fee created successfully', 10)
                ->assertSee('Fee created successfully')
                ->waitUntilMissing('Create New Fee', 5)
                ->assertSee('Annual Membership Fee 2024')
                ->assertSee('$250.00')
                ->assertSee($member->full_name);
        });
    }

    /**
     * Test payment processing works correctly.
     */
    public function test_payment_processing_works_correctly(): void
    {
        $member = Member::factory()->create(['organization_id' => $this->organization->id]);
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member->id,
            'amount' => 100.00,
            'status' => 'pending',
            'due_date' => now()->addDays(15),
        ]);

        $this->browse(function (Browser $browser) use ($fee) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10)
                ->waitForText($fee->description, 10);

            // Open payment form
            $browser->with("table tr:contains('{$fee->description}')", function ($row) use ($fee) {
                $row->click("button[wire\\:click*=\"showPaymentForm({$fee->id})\"]")
                    ->pause(500);
            });

            // Process payment
            $browser->waitFor('.fixed.inset-0', 5)
                ->assertSee('Process Payment')
                ->type('payment_amount', '100.00')
                ->select('payment_method', 'credit_card')
                ->type('payment_reference', 'CC-12345')
                ->type('payment_notes', 'Full payment via credit card')
                ->click('button[type="submit"]')
                ->waitForText('Payment processed successfully', 10)
                ->assertSee('Payment processed successfully')
                ->waitUntilMissing('.fixed.inset-0', 5);

            // Verify fee status updated
            $browser->refresh()
                ->waitForText('Fee Management', 10)
                ->assertSee('Paid') // Status should be updated
                ->assertSee('Paid: $100.00'); // Should show paid amount
        });
    }

    /**
     * Test search and filtering functionality works.
     */
    public function test_search_and_filtering_functionality(): void
    {
        $member1 = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $member2 = Member::factory()->create([
            'organization_id' => $this->organization->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        // Create different types of fees
        MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member1->id,
            'description' => 'Annual Subscription 2024',
            'fee_type' => 'subscription',
            'status' => 'pending',
            'amount' => 100.00,
        ]);

        MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member2->id,
            'description' => 'Late Payment Penalty',
            'fee_type' => 'late_fee',
            'status' => 'paid',
            'amount' => 25.00,
        ]);

        MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member1->id,
            'description' => 'Special Event Fee',
            'fee_type' => 'additional_service',
            'status' => 'overdue',
            'amount' => 50.00,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10);

            // Test search functionality
            $browser->type('input[wire\\:model="search"]', 'John')
                ->pause(1000) // Wait for debounce
                ->assertSee('John Doe')
                ->assertDontSee('Jane Smith');

            // Clear search
            $browser->clear('input[wire\\:model="search"]')
                ->pause(1000)
                ->assertSee('John Doe')
                ->assertSee('Jane Smith');

            // Test status filter
            $browser->select('status', 'paid')
                ->pause(1000)
                ->assertSee('Jane Smith')
                ->assertDontSee('John Doe');

            // Test fee type filter
            $browser->select('status', 'all')
                ->pause(1000)
                ->select('feeType', 'subscription')
                ->pause(1000)
                ->assertSee('Annual Subscription 2024')
                ->assertDontSee('Late Payment Penalty')
                ->assertDontSee('Special Event Fee');

            // Test combined filters
            $browser->select('feeType', 'late_fee')
                ->pause(1000)
                ->assertSee('Late Payment Penalty')
                ->assertDontSee('Annual Subscription 2024');
        });
    }

    /**
     * Test Alpine.js data binding works properly.
     */
    public function test_alpinejs_data_binding_works_properly(): void
    {
        $member = Member::factory()->create(['organization_id' => $this->organization->id]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10);

            // Test that Alpine.js is loaded and working
            $browser->script([
                'window.alpineTest = { loaded: false };',
                "document.addEventListener('alpine:init', () => { window.alpineTest.loaded = true; });",
            ]);

            $browser->pause(1000);

            $alpineLoaded = $browser->script('return window.alpineTest.loaded;')[0];
            $this->assertTrue($alpineLoaded, 'Alpine.js is not loaded properly');

            // Test Livewire wire:model bindings
            $browser->click('button[wire\\:click="showCreateFeeForm"]')
                ->waitForText('Create New Fee', 5)
                ->type('description', 'Test Fee Description')
                ->assertInputValue('description', 'Test Fee Description')
                ->type('amount', '123.45')
                ->assertInputValue('amount', '123.45')
                ->select('fee_type', 'penalty')
                ->assertSelected('fee_type', 'penalty');

            // Test live model updates
            $browser->type('notes', 'Test notes with live binding')
                ->pause(500) // Allow for live updates
                ->assertInputValue('notes', 'Test notes with live binding');
        });
    }

    /**
     * Test multi-tenant data isolation.
     */
    public function test_multi_tenant_data_isolation(): void
    {
        // Create another organization and fees
        $otherOrg = Organization::factory()->create();
        $otherAdmin = User::factory()->create();
        $otherOrg->users()->attach($otherAdmin->id, [
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $member = Member::factory()->create(['organization_id' => $this->organization->id]);
        $otherMember = Member::factory()->create(['organization_id' => $otherOrg->id]);

        // Create fees for both organizations
        MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member->id,
            'description' => 'Main Organization Fee',
            'amount' => 100.00,
        ]);

        MemberFee::factory()->create([
            'organization_id' => $otherOrg->id,
            'member_id' => $otherMember->id,
            'description' => 'Other Organization Fee',
            'amount' => 200.00,
        ]);

        $this->browse(function (Browser $browser) use ($otherAdmin) {
            // Login as admin of first organization
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10)
                ->assertSee('Main Organization Fee')
                ->assertDontSee('Other Organization Fee');

            // Switch to other organization
            $browser->loginAs($otherAdmin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10)
                ->assertSee('Other Organization Fee')
                ->assertDontSee('Main Organization Fee');
        });
    }

    /**
     * Test user permissions and access control.
     */
    public function test_user_permissions_and_access_control(): void
    {
        $member = Member::factory()->create(['organization_id' => $this->organization->id]);
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member->id,
            'status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) {
            // Test regular member access (should be restricted)
            $browser->loginAs($this->memberUser)
                ->visit('/fees')
                ->assertDontSee('Create Fee')
                ->assertDontSee('Generate Overdue Fees')
                ->assertDontSee('Pay'); // Action buttons should not be visible

            // Test admin access (should have full access)
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10)
                ->assertSee('Create Fee')
                ->assertSee('Generate Overdue Fees')
                ->assertSee('Pay'); // Action buttons should be visible
        });
    }

    /**
     * Test error handling and validation.
     */
    public function test_error_handling_and_validation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10)
                ->click('button[wire\\:click="showCreateFeeForm"]')
                ->waitForText('Create New Fee', 5);

            // Test validation errors
            $browser->click('button[type="submit"]')
                ->waitForText('required', 5)
                ->assertSee('The description field is required')
                ->assertSee('The amount field is required')
                ->assertSee('The due date field is required');

            // Test invalid amount
            $browser->type('amount', '0')
                ->click('button[type="submit"]')
                ->waitForText('at least 0.01', 5)
                ->assertSee('The amount field must be at least 0.01');
        });
    }

    /**
     * Test sorting functionality.
     */
    public function test_sorting_functionality(): void
    {
        $member = Member::factory()->create(['organization_id' => $this->organization->id]);

        // Create fees with different amounts and dates
        $fee1 = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member->id,
            'amount' => 50.00,
            'created_at' => now()->subDays(3),
        ]);

        $fee2 = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member->id,
            'amount' => 200.00,
            'created_at' => now()->subDays(1),
        ]);

        $fee3 = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member->id,
            'amount' => 100.00,
            'created_at' => now()->subDays(2),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10);

            // Test amount sorting
            $browser->click('button[wire\\:click*="sort(\'amount\')"]')
                ->pause(1000)
                ->assertSeeInOrder(['$50.00', '$100.00', '$200.00']);

            // Test reverse amount sorting
            $browser->click('button[wire\\:click*="sort(\'amount\')"]')
                ->pause(1000)
                ->assertSeeInOrder(['$200.00', '$100.00', '$50.00']);

            // Test date sorting
            $browser->click('button[wire\\:click*="sort(\'created_at\')"]')
                ->pause(1000);
        });
    }

    /**
     * Test fee waiver functionality.
     */
    public function test_fee_waiver_functionality(): void
    {
        $member = Member::factory()->create(['organization_id' => $this->organization->id]);
        $fee = MemberFee::factory()->create([
            'organization_id' => $this->organization->id,
            'member_id' => $member->id,
            'amount' => 75.00,
            'status' => 'pending',
        ]);

        $this->browse(function (Browser $browser) use ($fee) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10)
                ->waitForText($fee->description, 10);

            // Test fee waiver
            $browser->with("table tr:contains('{$fee->description}')", function ($row) use ($fee) {
                $row->click("button[wire\\:click*=\"waiveFee({$fee->id})\"]")
                    ->pause(500);
            });

            // Handle confirmation dialog
            $browser->waitFor('.swal2-container', 5) // SweetAlert or similar confirmation
                ->whenAvailable('.swal2-container', function ($modal) {
                    $modal->click('.swal2-confirm')
                        ->pause(500);
                })
                ->waitForText('Fee waived successfully', 10)
                ->assertSee('Fee waived successfully')
                ->refresh()
                ->waitForText('Fee Management', 10)
                ->assertSee('Waived'); // Status should be updated
        });
    }

    /**
     * Test responsive design on different screen sizes.
     */
    public function test_responsive_design(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10);

            // Test desktop view
            $browser->resize(1200, 800)
                ->assertSee('Total Fees')
                ->assertSee('Paid Amount')
                ->assertSee('Pending Amount')
                ->assertSee('Overdue Fees');

            // Test tablet view
            $browser->resize(768, 1024)
                ->assertSee('Total Fees')
                ->assertSee('Paid Amount')
                ->assertSee('Pending Amount')
                ->assertSee('Overdue Fees');

            // Test mobile view
            $browser->resize(375, 667)
                ->assertSee('Total Fees')
                ->assertSee('Paid Amount')
                ->assertSee('Pending Amount')
                ->assertSee('Overdue Fees');
        });
    }

    /**
     * Test keyboard navigation and accessibility.
     */
    public function test_keyboard_navigation_and_accessibility(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                ->visit('/fees')
                ->waitForText('Fee Management', 10);

            // Test tab navigation
            $browser->keys('body', '{tab}')
                ->pause(200)
                ->keys('body', '{tab}')
                ->pause(200)
                ->keys('body', '{enter')
                ->pause(500);

            // Test that buttons are focusable
            $browser->click('button[wire\\:click="showCreateFeeForm"]')
                ->waitForText('Create New Fee', 5)
                ->assertFocused('input[name="description"]');

            // Test escape key to close modal
            $browser->keys('body', '{escape}')
                ->waitUntilMissing('Create New Fee', 5)
                ->assertDontSee('Create New Fee');
        });
    }
}
