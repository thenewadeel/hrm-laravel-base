<?php

namespace Tests\Browser\E2E\Workflows;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\BaseBrowserTest;
use Tests\Browser\E2E\Concerns\HandlesE2ETestSetup;
use Tests\Browser\E2E\Concerns\HandlesWorkflowAssertions;
use Tests\Browser\E2E\Assertions\E2EAssertions;
use Tests\Browser\E2E\Fixtures\E2ETestFixtures;

class MemberRegistrationAndFeePaymentTest extends BaseBrowserTest
{
    use HandlesE2ETestSetup, HandlesWorkflowAssertions;

    protected $testData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testData = E2ETestFixtures::createOrganizationSetup();
    }

    protected function tearDown(): void
    {
        E2ETestFixtures::cleanup($this->testData);
        parent::tearDown();
    }

    /**
     * Test complete member registration and fee payment workflow.
     */
    public function test_complete_member_registration_and_fee_payment_workflow(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];
            $organization = $this->testData['organization'];

            // Step 1: Login as admin
            $browser->loginAs($admin)
                ->visit('/')
                ->waitForText($organization->name, 10);

            E2EAssertions::assertPageTitle($browser, 'Dashboard');
            E2EAssertions::assertDataIsolation($browser, $organization->id);

            // Step 2: Navigate to membership module
            $this->navigateToModule($browser, 'Membership');
            $this->waitForPageLoad($browser);

            // Step 3: Register new member
            $browser->clickLink('Add New Member')
                ->waitFor('.member-registration-form', 10)
                ->type('first_name', 'Test')
                ->type('last_name', 'Member')
                ->type('email', 'test.member@example.com')
                ->type('phone', '+1234567890')
                ->select('membership_type', 'premium')
                ->type('address', '123 Test Street')
                ->type('city', 'Test City')
                ->select('country', 'US')
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Member registered successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'member_registration');

            // Step 4: Verify member is created
            $browser->assertSeeIn('.members-table', 'Test Member')
                ->assertSeeIn('.members-table', 'test.member@example.com')
                ->assertSeeIn('.members-table', 'premium');

            // Step 5: Setup fee structure for member
            $browser->clickLink('Test Member')
                ->waitFor('.member-details', 10)
                ->clickLink('Manage Fees')
                ->waitFor('.fee-management', 10);

            // Add annual membership fee
            $browser->select('fee_id', $this->testData['fees']['annual_membership']->id)
                ->type('amount', 1200.00)
                ->type('due_date', now()->addMonth()->format('Y-m-d'))
                ->click('button[data-action="add_fee"]');

            E2EAssertions::assertSuccessMessage($browser, 'Fee added successfully');

            // Add one-time registration fee
            $browser->select('fee_id', $this->testData['fees']['registration_fee']->id)
                ->type('amount', 500.00)
                ->type('due_date', now()->format('Y-m-d'))
                ->click('button[data-action="add_fee"]');

            E2EAssertions::assertSuccessMessage($browser, 'Fee added successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'fee_setup');

            // Step 6: Process fee payment
            $browser->clickLink('Process Payment')
                ->waitFor('.payment-form', 10)
                ->check('fee_ids[]', 0) // Select annual fee
                ->check('fee_ids[]', 1) // Select registration fee
                ->select('payment_method', 'bank_transfer')
                ->type('payment_reference', 'PAY001')
                ->type('notes', 'Initial membership payment')
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Payment processed successfully');
            E2EAssertions::assertWorkflowStepCompleted($browser, 'fee_payment');

            // Step 7: Verify financial records
            $browser->clickLink('View Financial Records')
                ->waitFor('.financial-records', 10);

            E2EAssertions::assertFinancialTransaction($browser, [
                'id' => 'PAY001',
                'amount' => 1700.00,
                'type' => 'fee_payment',
                'date' => now()->format('Y-m-d'),
            ]);

            // Step 8: Verify double-entry bookkeeping
            E2EAssertions::assertDoubleEntryBalanced($browser, 'PAY001');

            // Step 9: Generate receipt
            $browser->clickLink('Generate Receipt')
                ->waitFor('.receipt-preview', 10)
                ->assertSee('Test Member')
                ->assertSee('1700.00')
                ->assertSee('Annual Membership Fee')
                ->assertSee('Registration Fee')
                ->click('button[data-action="download_receipt"]');

            E2EAssertions::assertExportGenerated($browser, 'pdf');

            // Step 10: Verify member status
            $browser->visit('/membership')
                ->waitFor('.members-table', 10)
                ->assertSeeIn('.member-status', 'Active')
                ->assertSeeIn('.payment-status', 'Paid');

            E2EAssertions::assertWorkflowCompletion($browser, 'member_registration_workflow', true);

            // Step 11: Test audit trail
            E2EAssertions::assertAuditTrailEntry($browser, 'member_registered', $admin->name);
            E2EAssertions::assertAuditTrailEntry($browser, 'fee_payment_processed', $admin->name);

            // Step 12: Test responsive design
            $this->assertResponsiveDesign($browser, function (Browser $browser, string $viewport) {
                $browser->visit('/membership')
                    ->waitFor('.members-table', 10)
                    ->assertPresent('.members-table');

                if ($viewport === 'mobile') {
                    $browser->assertPresent('.mobile-member-card')
                        ->assertMissing('.desktop-member-table');
                } else {
                    $browser->assertPresent('.desktop-member-table');
                }
            });

            // Step 13: Test search and filtering
            $browser->visit('/membership')
                ->waitFor('.members-table', 10);

            E2EAssertions::assertSearchResults($browser, 'Test Member', ['Test Member', 'test.member@example.com']);

            $browser->select('filter_status', 'active')
                ->pause(500)
                ->assertSeeIn('.members-table', 'Test Member')
                ->assertDontSeeIn('.members-table', 'Inactive');

            // Step 14: Test error handling
            $this->assertErrorHandling($browser, 'invalid_data', function (Browser $browser) {
                $browser->visit('/membership/create')
                    ->waitFor('.member-registration-form', 10)
                    ->type('email', 'invalid-email')
                    ->click('button[type="submit"]')
                    ->waitFor('.error-message', 5);
            });

            // Step 15: Verify data persistence
            $this->assertDataPersistence($browser, [
                '[data-member-count]' => '4', // Original 3 + 1 new
                '[data-total-revenue]' => '1700.00',
            ]);

            // Step 16: Test performance
            $this->assertPageLoadPerformance($browser, 3000);

            // Step 17: Test accessibility
            E2EAssertions::assertAccessibilityFeatures($browser);

            // Step 18: Test dark mode
            E2EAssertions::assertDarkModeToggle($browser);

            // Final verification
            E2EAssertions::assertWorkflowCompletion($browser, 'complete_member_onboarding', true);
        });
    }

    /**
     * Test member registration with validation errors.
     */
    public function test_member_registration_validation_errors(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];

            $browser->loginAs($admin)
                ->visit('/membership')
                ->clickLink('Add New Member')
                ->waitFor('.member-registration-form', 10)
                ->click('button[type="submit"]'); // Submit empty form

            E2EAssertions::assertFormValidation($browser, [
                'first_name' => 'The first name field is required.',
                'last_name' => 'The last name field is required.',
                'email' => 'The email field is required.',
                'membership_type' => 'The membership type field is required.',
            ]);

            // Test duplicate email
            $browser->type('first_name', 'Duplicate')
                ->type('last_name', 'Test')
                ->type('email', $this->testData['members']['alice_johnson']->email)
                ->select('membership_type', 'standard')
                ->click('button[type="submit"]');

            E2EAssertions::assertErrorMessage($browser, 'The email has already been taken.');
        });
    }

    /**
     * Test fee payment with insufficient funds scenario.
     */
    public function test_fee_payment_insufficient_funds(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];

            $browser->loginAs($admin)
                ->visit('/membership')
                ->clickLink($this->testData['members']['charlie_brown']->name)
                ->waitFor('.member-details', 10)
                ->clickLink('Manage Fees')
                ->waitFor('.fee-management', 10);

            // Try to process payment with insufficient funds
            $browser->select('fee_id', $this->testData['fees']['annual_membership']->id)
                ->type('amount', 999999.99) // Excessive amount
                ->click('button[data-action="process_payment"]');

            E2EAssertions::assertErrorMessage($browser, 'Insufficient funds for this transaction.');
        });
    }

    /**
     * Test member registration workflow across multiple organizations.
     */
    public function test_multi_organization_member_isolation(): void
    {
        // Create second organization
        $secondOrg = E2ETestFixtures::createOrganizationSetup();
        
        $this->browse(function (Browser $browser) use ($secondOrg) {
            $admin1 = $this->testData['users']['admin'];
            $admin2 = $secondOrg['users']['admin'];

            // Login to first organization and create member
            $browser->loginAs($admin1)
                ->visit('/')
                ->waitForText($this->testData['organization']->name, 10);

            E2EAssertions::assertDataIsolation($browser, $this->testData['organization']->id);

            // Switch to second organization
            $this->switchOrganizationInBrowser($browser, $secondOrg['organization']);

            E2EAssertions::assertDataIsolation($browser, $secondOrg['organization']->id);
            $browser->assertDontSee($this->testData['members']['alice_johnson']->name);

            // Create member in second organization
            $browser->visit('/membership')
                ->clickLink('Add New Member')
                ->waitFor('.member-registration-form', 10)
                ->type('first_name', 'Second')
                ->type('last_name', 'Org Member')
                ->type('email', 'second.org@example.com')
                ->select('membership_type', 'standard')
                ->click('button[type="submit"]');

            E2EAssertions::assertSuccessMessage($browser, 'Member registered successfully');

            // Switch back to first organization
            $this->switchOrganizationInBrowser($browser, $this->testData['organization']);

            // Verify second organization member is not visible
            $browser->visit('/membership')
                ->waitFor('.members-table', 10)
                ->assertDontSee('Second Org Member');
        });

        E2ETestFixtures::cleanup($secondOrg);
    }

    /**
     * Test member registration workflow performance under load.
     */
    public function test_member_registration_performance(): void
    {
        $this->browse(function (Browser $browser) {
            $admin = $this->testData['users']['admin'];

            $browser->loginAs($admin)
                ->visit('/membership');

            // Measure page load time
            $startTime = microtime(true);
            $browser->clickLink('Add New Member')
                ->waitFor('.member-registration-form', 10);
            $loadTime = (microtime(true) - $startTime) * 1000;

            $this->assertLessThan(2000, $loadTime, 'Member registration form should load within 2 seconds');

            // Test form submission performance
            $startTime = microtime(true);
            $browser->type('first_name', 'Performance')
                ->type('last_name', 'Test')
                ->type('email', 'perf.test@example.com')
                ->select('membership_type', 'premium')
                ->click('button[type="submit"]')
                ->waitFor('.success-message', 10);
            $submitTime = (microtime(true) - $startTime) * 1000;

            $this->assertLessThan(3000, $submitTime, 'Member registration should complete within 3 seconds');
        });
    }
}