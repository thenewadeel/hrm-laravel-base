<?php

namespace Tests\Browser;

use App\Models\Organization;
use Laravel\Dusk\Browser;
use Tests\Browser\Traits\BrowserTestSetup;

class DuskInstallationTest extends JavaScriptDuskTestCase
{
    use BrowserTestSetup;

    /**
     * Test basic Dusk installation and configuration.
     */
    public function test_dusk_installation_works(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(3000) // Give time for page to load
                ->screenshot('debug-page-content')
                ->assertSourceHas('<html') // Check if page has HTML
                ->assertTitleContains('HRM-Base');
        });
    }

    /**
     * Test multi-tenant authentication works.
     */
    public function test_multi_tenant_authentication(): void
    {
        $this->browse(function (Browser $browser) {
            $organization = $this->setupOrganizationWithAccounting($browser);

            $browser->screenshot('debug-multi-tenant-auth')
                ->pause(2000)
                ->dump('Current URL: ' . $browser->driver->getCurrentURL())
                ->dump('Page title: ' . $browser->driver->getTitle())
                ->dump('Looking for org name: ' . $organization->name);

            $browser->assertSee($organization->name)
                ->assertPresent('[data-organization-id]')
                ->assertAuthenticated()
                ->assertPathIs('/');
        });
    }

    /**
     * Test organization switching functionality.
     */
    public function test_organization_switching(): void
    {
        $this->browse(function (Browser $browser) {
            $scenario = $this->setupMultiOrganizationScenario($browser);
            $organizations = $scenario['organizations'];

            // Start with first organization
            $browser->assertSee($organizations->first()->name);

            // Switch to second organization
            $this->switchOrganizationInBrowser($browser, $organizations->skip(1)->first());

            // Verify switch was successful
            $browser->assertSee($organizations->skip(1)->first()->name);
        });
    }

    /**
     * Test module navigation works correctly.
     */
    public function test_module_navigation(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupOrganizationWithAccounting($browser);

            // Test Accounting module
            $this->assertCanAccessModule($browser, 'Accounting');

            // Test HRM module
            $this->assertCanAccessModule($browser, 'HRM');

            // Test Inventory module
            $this->assertCanAccessModule($browser, 'Inventory');

            // Test Organization module
            $this->assertCanAccessModule($browser, 'Organization');
        });
    }

    /**
     * test Livewire components load correctly.
     */
    public function test_livewire_components_load(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupOrganizationWithAccounting($browser);

            // Navigate to dashboard
            $browser->visit('/')
                ->waitForLivewireComponent('dashboard', 5);

            // Navigate to accounting
            $this->navigateToAccounting($browser);
            $this->waitForLivewireComponent($browser, 'voucher-list', 5);
        });
    }

    /**
     * Test responsive design works.
     */
    public function test_responsive_design(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupOrganizationWithAccounting($browser);

            $this->testResponsiveDesign($browser, function (Browser $browser, string $device) {
                $browser->assertPresent('header')
                    ->assertPresent('main')
                    ->assertPresent('nav');

                if ($device === 'mobile') {
                    $browser->assertPresent('.mobile-menu');
                } else {
                    $browser->assertPresent('.desktop-menu');
                }
            });
        });
    }

    /**
     * Test form interactions work correctly.
     */
    public function test_form_interactions(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupOrganizationWithAccounting($browser);

            // Navigate to create voucher form
            $this->navigateToAccounting($browser);
            $browser->clickLink('Create Voucher')
                ->waitFor('.voucher-form', 10);

            // Test form validation
            $browser->click('button[type="submit"]')
                ->waitFor('.validation-error', 5)
                ->assertSee('Description is required');

            // Fill form correctly
            $browser->type('description', 'Test Voucher')
                ->type('date', now()->format('Y-m-d'))
                ->select('voucher_type', 'sales')
                ->click('button[type="submit"]')
                ->waitForText('Voucher created successfully', 10);
        });
    }

    /**
     * Test database transactions work correctly.
     */
    public function test_database_transactions(): void
    {
        $this->browse(function (Browser $browser) {
            $organization = $this->setupOrganizationWithAccounting($browser);

            // Create a voucher
            $this->createTestVoucher($browser, [
                'description' => 'Transaction Test Voucher',
                'type' => 'sales',
                'entries' => [
                    ['account_id' => 1, 'debit' => 1000, 'credit' => 0],
                    ['account_id' => 2, 'debit' => 0, 'credit' => 1000],
                ],
            ]);

            // Verify voucher exists in database
            $this->assertDatabaseHas('journal_entries', [
                'organization_id' => $organization->id,
                'description' => 'Transaction Test Voucher',
            ]);
        });
    }

    /**
     * Test error handling and screenshots.
     */
    public function test_error_handling_and_screenshots(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupOrganizationWithAccounting($browser);

            // Try to access non-existent route
            $browser->visit('/non-existent-route')
                ->assertStatus(404)
                ->assertSee('Not Found');

            // Take screenshot for debugging
            $browser->screenshot('404-error-page');
        });
    }

    /**
     * test JavaScript execution works.
     */
    public function test_javascript_execution(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupOrganizationWithAccounting($browser);

            // Test JavaScript execution
            $result = $browser->script('return document.title;');
            $this->assertIsArray($result);
            $this->assertNotEmpty($result[0]);

            // Test JavaScript interaction
            $browser->script('window.testVariable = "test-value";');
            $testValue = $browser->script('return window.testVariable;');
            $this->assertEquals('test-value', $testValue[0]);
        });
    }

    /**
     * Test console output capture.
     */
    public function test_console_output_capture(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupOrganizationWithAccounting($browser);

            // Log to console
            $browser->script('console.log("Dusk test log message");');

            // Console output will be captured automatically on test failure
            $browser->assertSee('Dashboard');
        });
    }

    /**
     * Test file upload functionality.
     */
    public function test_file_upload_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupOrganizationWithAccounting($browser);

            // Create a test file
            $testFilePath = storage_path('app/test-upload.txt');
            file_put_contents($testFilePath, 'Test file content');

            // Navigate to a page with file upload (if available)
            $this->navigateToOrganizationSettings($browser);

            // Look for file upload input
            $browser->whenAvailable('input[type="file"]', function ($input) use ($testFilePath) {
                $input->attach($testFilePath);
            });

            // Clean up test file
            if (file_exists($testFilePath)) {
                unlink($testFilePath);
            }
        });
    }

    /**
     * Test headless browser operation.
     */
    public function test_headless_browser_operation(): void
    {
        // This test verifies that headless mode works
        // It will run in CI/CD environments without display
        $this->browse(function (Browser $browser) {
            $this->setupOrganizationWithAccounting($browser);

            $browser->visit('/')
                ->assertTitleContains('HRM')
                ->assertSee('Dashboard');
        });
    }

    /**
     * Test browser cleanup between tests.
     */
    public function test_browser_cleanup(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupOrganizationWithAccounting($browser);

            // Set some state
            $browser->script('localStorage.setItem("test", "value");');

            // Verify state is set
            $value = $browser->script('return localStorage.getItem("test");');
            $this->assertEquals('value', $value[0]);
        });

        // New browser instance should have clean state
        $this->browse(function (Browser $browser) {
            $browser->visit('/');

            // LocalStorage should be clean in new instance
            $value = $browser->script('return localStorage.getItem("test");');
            $this->assertNull($value[0]);
        });
    }
}
