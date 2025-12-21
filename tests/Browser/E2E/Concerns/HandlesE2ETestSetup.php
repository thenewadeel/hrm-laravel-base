<?php

namespace Tests\Browser\E2E\Concerns;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;

trait HandlesE2ETestSetup
{
    /**
     * Setup complete test environment with organizations and users.
     */
    protected function setupE2ETestEnvironment(): array
    {
        // Create multiple organizations for multi-tenant testing
        $organizations = [
            'primary' => Organization::factory()->create(['name' => 'Test Organization Primary']),
            'secondary' => Organization::factory()->create(['name' => 'Test Organization Secondary']),
        ];

        // Create users with different roles
        $users = [
            'admin' => $this->createUserWithRole($organizations['primary'], 'admin'),
            'manager' => $this->createUserWithRole($organizations['primary'], 'manager'),
            'member' => $this->createUserWithRole($organizations['primary'], 'member'),
            'employee' => $this->createUserWithRole($organizations['primary'], 'employee'),
        ];

        return [
            'organizations' => $organizations,
            'users' => $users,
        ];
    }

    /**
     * Create a user with specific role for organization.
     */
    protected function createUserWithRole(Organization $organization, string $role): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $organization->users()->attach($user->id, [
            'roles' => $role,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    /**
     * Login user and navigate to specific module.
     */
    protected function loginAndNavigateToModule(Browser $browser, User $user, string $module): void
    {
        $browser->loginAs($user)
            ->visit('/')
            ->waitForText('Dashboard', 10)
            ->clickLink($module)
            ->waitForLocationIn(['/dashboard', '/accounts', '/hrm', '/inventory'], 10);
    }

    /**
     * Setup test data for workflows.
     */
    protected function setupWorkflowTestData(): array
    {
        return [
            'chart_of_accounts' => $this->createChartOfAccounts(),
            'employees' => $this->createTestEmployees(),
            'inventory_items' => $this->createTestInventoryItems(),
            'fee_structures' => $this->createTestFeeStructures(),
        ];
    }

    /**
     * Create chart of accounts for testing.
     */
    protected function createChartOfAccounts(): array
    {
        return [
            'cash_account' => \App\Models\Accounting\ChartOfAccount::factory()->create([
                'account_type' => 'asset',
                'name' => 'Test Cash Account',
            ]),
            'bank_account' => \App\Models\Accounting\ChartOfAccount::factory()->create([
                'account_type' => 'asset',
                'name' => 'Test Bank Account',
            ]),
            'revenue_account' => \App\Models\Accounting\ChartOfAccount::factory()->create([
                'account_type' => 'revenue',
                'name' => 'Test Revenue Account',
            ]),
            'expense_account' => \App\Models\Accounting\ChartOfAccount::factory()->create([
                'account_type' => 'expense',
                'name' => 'Test Expense Account',
            ]),
        ];
    }

    /**
     * Create test employees.
     */
    protected function createTestEmployees(): array
    {
        return [
            'full_time' => \App\Models\Employee::factory()->create([
                'employment_type' => 'full_time',
                'salary' => 50000,
            ]),
            'part_time' => \App\Models\Employee::factory()->create([
                'employment_type' => 'part_time',
                'salary' => 25000,
            ]),
            'contract' => \App\Models\Employee::factory()->create([
                'employment_type' => 'contract',
                'salary' => 75000,
            ]),
        ];
    }

    /**
     * Create test inventory items.
     */
    protected function createTestInventoryItems(): array
    {
        return [
            'product_a' => \App\Models\Inventory\Item::factory()->create([
                'name' => 'Test Product A',
                'unit_price' => 100.00,
                'reorder_level' => 10,
            ]),
            'product_b' => \App\Models\Inventory\Item::factory()->create([
                'name' => 'Test Product B',
                'unit_price' => 200.00,
                'reorder_level' => 5,
            ]),
        ];
    }

    /**
     * Create test fee structures.
     */
    protected function createTestFeeStructures(): array
    {
        return [
            'membership_fee' => \App\Models\Membership\Fee::factory()->create([
                'name' => 'Annual Membership Fee',
                'amount' => 1000.00,
                'frequency' => 'annual',
            ]),
            'registration_fee' => \App\Models\Membership\Fee::factory()->create([
                'name' => 'Registration Fee',
                'amount' => 500.00,
                'frequency' => 'one_time',
            ]),
        ];
    }

    /**
     * Wait for page to fully load with all components.
     */
    protected function waitForPageLoad(Browser $browser): void
    {
        $browser->waitFor('[wire\\:id]', 15)
            ->waitUntilMissing('.loading', 10)
            ->pause(500); // Allow for any remaining animations
    }

    /**
     * Assert no JavaScript errors occurred.
     */
    protected function assertNoJavaScriptErrors(Browser $browser): void
    {
        $browser->script([
            "window.errors = [];",
            "window.onerror = function(msg, url, line) { window.errors.push(msg); return false; };",
        ]);

        // Check for accumulated errors
        $errors = $browser->script("return window.errors;");
        
        if (!empty($errors[0])) {
            throw new \Exception("JavaScript errors detected: " . implode(', ', $errors[0]));
        }
    }

    /**
     * Take screenshot on failure for debugging.
     */
    protected function takeScreenshotOnFailure(): void
    {
        $this->captureFailures();
    }

    /**
     * Clean up test data after test completion.
     */
    protected function cleanupE2ETestData(array $testData): void
    {
        // Clean up in proper order to respect foreign key constraints
        if (isset($testData['organizations'])) {
            foreach ($testData['organizations'] as $organization) {
                $organization->delete();
            }
        }
    }

    /**
     * Measure and assert page load performance.
     */
    protected function assertPageLoadPerformance(Browser $browser, int $maxLoadTimeMs = 3000): void
    {
        $startTime = microtime(true);
        
        $this->waitForPageLoad($browser);
        
        $loadTime = (microtime(true) - $startTime) * 1000;
        
        if ($loadTime > $maxLoadTimeMs) {
            throw new \Exception("Page load time {$loadTime}ms exceeds maximum allowed {$maxLoadTimeMs}ms");
        }
    }

    /**
     * Simulate mobile device viewport.
     */
    protected function useMobileViewport(Browser $browser): void
    {
        $browser->resize(375, 667); // iPhone 6/7/8 dimensions
    }

    /**
     * Simulate tablet device viewport.
     */
    protected function useTabletViewport(Browser $browser): void
    {
        $browser->resize(768, 1024); // iPad dimensions
    }

    /**
     * Simulate desktop viewport.
     */
    protected function useDesktopViewport(Browser $browser): void
    {
        $browser->resize(1920, 1080); // Full HD desktop
    }

    /**
     * Assert responsive design works across viewports.
     */
    protected function assertResponsiveDesign(Browser $browser, callable $testCallback): void
    {
        // Test mobile
        $this->useMobileViewport($browser);
        $testCallback($browser, 'mobile');

        // Test tablet
        $this->useTabletViewport($browser);
        $testCallback($browser, 'tablet');

        // Test desktop
        $this->useDesktopViewport($browser);
        $testCallback($browser, 'desktop');
    }
}