<?php

namespace Tests\Browser\Traits;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;

trait BrowserTestSetup
{
    /**
     * Setup complete organization with accounting data.
     */
    protected function setupOrganizationWithAccounting(Browser $browser): Organization
    {
        $organization = Organization::factory()->create();

        // Create chart of accounts
        ChartOfAccount::factory()->count(10)->create([
            'organization_id' => $organization->id,
        ]);

        $user = User::factory()->create();
        $organization->users()->attach($user->id, [
            'roles' => json_encode(['admin']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Set current organization for the user
        $user->current_organization_id = $organization->id;
        $user->save();

        $browser->loginAs($user)
            ->visit('/')
            ->pause(2000)
            ->visit('/dashboard')
            ->pause(2000);

        return $organization;
    }

    /**
     * Setup organization with inventory data.
     */
    protected function setupOrganizationWithInventory(Browser $browser): Organization
    {
        $organization = Organization::factory()->create();

        // Create stores
        $stores = Store::factory()->count(3)->create([
            'organization_id' => $organization->id,
        ]);

        // Create items
        Item::factory()->count(20)->create([
            'organization_id' => $organization->id,
            'store_id' => $stores->random()->id,
        ]);

        $user = User::factory()->create();
        $organization->users()->attach($user->id, [
            'roles' => json_encode(['admin']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Set current organization for the user
        $user->current_organization_id = $organization->id;
        $user->save();

        $browser->loginAs($user)
            ->visit('/')
            ->pause(2000)
            ->visit('/dashboard')
            ->pause(2000);

        return $organization;
    }

    /**
     * Setup multi-organization scenario.
     */
    protected function setupMultiOrganizationScenario(Browser $browser): array
    {
        $organizations = Organization::factory()->count(3)->create();

        $user = User::factory()->create();

        foreach ($organizations as $org) {
            $org->users()->attach($user->id, [
                'roles' => json_encode(['admin']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Set current organization for the user to first org
        $user->current_organization_id = $organizations->first()->id;
        $user->save();

        $browser->loginAs($user)
            ->visit('/')
            ->pause(2000)
            ->visit('/dashboard')
            ->pause(2000);

        return [
            'user' => $user,
            'organizations' => $organizations,
        ];
    }

    /**
     * Setup user with specific role.
     */
    protected function setupUserWithRole(Browser $browser, string $role = 'member'): array
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create();

        $organization->users()->attach($user->id, [
            'roles' => json_encode([$role]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Set current organization for the user
        $user->current_organization_id = $organization->id;
        $user->save();

        $browser->loginAs($user)
            ->visit('/')
            ->pause(2000)
            ->visit('/dashboard')
            ->pause(2000);

        return [
            'user' => $user,
            'organization' => $organization,
        ];
    }

    /**
     * Navigate to accounting module.
     */
    protected function navigateToAccounting(Browser $browser): void
    {
        $browser->clickLink('Accounting')
            ->waitForLocation('/accounts', 10)
            ->assertPathIs('/accounts');
    }

    /**
     * Navigate to HRM module.
     */
    protected function navigateToHRM(Browser $browser): void
    {
        $browser->clickLink('HRM')
            ->waitForLocation('/hrm', 10)
            ->assertPathIs('/hrm');
    }

    /**
     * Navigate to inventory module.
     */
    protected function navigateToInventory(Browser $browser): void
    {
        $browser->clickLink('Inventory')
            ->waitForLocation('/inventory', 10)
            ->assertPathIs('/inventory');
    }

    /**
     * Navigate to organization settings.
     */
    protected function navigateToOrganizationSettings(Browser $browser): void
    {
        $browser->clickLink('Organization')
            ->waitForLocation('/organization', 10)
            ->assertPathIs('/organization');
    }

    /**
     * Create test voucher via browser.
     */
    protected function createTestVoucher(Browser $browser, array $data = []): void
    {
        $this->navigateToAccounting($browser);

        $browser->clickLink('Create Voucher')
            ->waitFor('.voucher-form', 10)
            ->type('description', $data['description'] ?? 'Test Voucher')
            ->type('date', $data['date'] ?? now()->format('Y-m-d'))
            ->select('voucher_type', $data['type'] ?? 'sales');

        // Add debit and credit entries
        if (! empty($data['entries'])) {
            foreach ($data['entries'] as $index => $entry) {
                $browser->type("entries.{$index}.account_id", $entry['account_id'])
                    ->type("entries.{$index}.debit", $entry['debit'] ?? 0)
                    ->type("entries.{$index}.credit", $entry['credit'] ?? 0);
            }
        }

        $browser->click('button[type="submit"]')
            ->waitForText('Voucher created successfully', 10);
    }

    /**
     * Create test inventory item via browser.
     */
    protected function createTestInventoryItem(Browser $browser, array $data = []): void
    {
        $this->navigateToInventory($browser);

        $browser->clickLink('Add Item')
            ->waitFor('.item-form', 10)
            ->type('name', $data['name'] ?? 'Test Item')
            ->type('sku', $data['sku'] ?? 'TEST-SKU-001')
            ->type('description', $data['description'] ?? 'Test Description')
            ->type('purchase_price', $data['purchase_price'] ?? 100)
            ->type('selling_price', $data['selling_price'] ?? 150)
            ->select('store_id', $data['store_id'] ?? 1)
            ->type('quantity', $data['quantity'] ?? 10);

        $browser->click('button[type="submit"]')
            ->waitForText('Item created successfully', 10);
    }

    /**
     * Assert dashboard loads correctly.
     */
    protected function assertDashboardLoads(Browser $browser): void
    {
        $browser->assertPathIs('/')
            ->assertPresent('[data-dashboard]')
            ->assertSee('Dashboard')
            ->waitFor('.dashboard-content', 10);
    }

    /**
     * Assert user can access specific module.
     */
    protected function assertCanAccessModule(Browser $browser, string $module): void
    {
        $this->{"navigateTo{$module}"}($browser);
        $browser->assertSee($module)
            ->assertPresent('[data-module-content]');
    }

    /**
     * Assert user cannot access specific module.
     */
    protected function assertCannotAccessModule(Browser $browser, string $module): void
    {
        $path = strtolower($module);
        $browser->visit("/{$path}")
            ->assertStatus(403)
            ->assertSee('Forbidden');
    }

    /**
     * Switch between organizations and verify context.
     */
    protected function switchOrganizationAndVerify(Browser $browser, Organization $targetOrg): void
    {
        $browser->click('[data-organization-switcher]')
            ->waitFor('.organization-dropdown', 5)
            ->clickLink($targetOrg->name)
            ->waitForLocation('/', 10)
            ->assertSee($targetOrg->name)
            ->assertAttribute('[data-current-organization]', 'data-org-id', $targetOrg->id);
    }

    /**
     * Type with delay for better reliability in tests.
     */
    protected function typeSlowly(Browser $browser, string $selector, string $text, int $delay = 100): void
    {
        foreach (str_split($text) as $char) {
            $browser->keys($selector, $char)
                ->pause($delay);
        }
    }

    /**
     * Handle file upload with preview.
     */
    protected function uploadFileWithPreview(Browser $browser, string $selector, string $filePath): void
    {
        $browser->attach($selector, $filePath)
            ->waitFor('.file-preview', 5)
            ->assertPresent('.file-preview');
    }

    /**
     * Assert form validation errors.
     */
    protected function assertValidationErrors(Browser $browser, array $fields): void
    {
        foreach ($fields as $field => $message) {
            $browser->assertSeeIn("[data-error='{$field}']", $message);
        }
    }

    /**
     * Clear form fields.
     */
    protected function clearForm(Browser $browser, array $fields): void
    {
        foreach ($fields as $field) {
            $browser->clear($field);
        }
    }

    /**
     * Check responsive design at different screen sizes.
     */
    protected function testResponsiveDesign(Browser $browser, callable $testCallback): void
    {
        // Desktop
        $browser->resize(1920, 1080);
        $testCallback($browser, 'desktop');

        // Tablet
        $browser->resize(768, 1024);
        $testCallback($browser, 'tablet');

        // Mobile
        $browser->resize(375, 667);
        $testCallback($browser, 'mobile');

        // Reset to desktop
        $browser->resize(1920, 1080);
    }

    /**
     * Wait for Livewire component to load.
     */
    protected function waitForLegacyLivewireComponent(Browser $browser, string $componentName): void
    {
        $browser->waitFor("[wire\\:id*='{$componentName}']", 10);
    }

    /**
     * Navigate to a specific module.
     */
    protected function navigateToModule(Browser $browser, string $module): void
    {
        $browser->clickLink($module)
            ->waitForLocationIn(['/dashboard', '/accounts', '/hrm', '/inventory'], 10)
            ->assertPathIsNot('/login');
    }

    /**
     * Assert organization context is active.
     */
    protected function assertOrganizationContext(Browser $browser, Organization $organization): void
    {
        $browser->assertSee($organization->name)
            ->assertPresent('[data-organization-id]');
    }

    /**
     * Switch between organizations in browser context.
     */
    protected function switchOrganizationInBrowser(Browser $browser, Organization $organization): void
    {
        $browser->click('[data-organization-switcher]')
            ->waitFor('.organization-dropdown', 5)
            ->clickLink($organization->name)
            ->waitForLocation('/', 10)
            ->assertSee($organization->name);
    }

    /**
     * Fill form with data.
     */
    protected function fillForm(Browser $browser, array $data): void
    {
        foreach ($data as $field => $value) {
            $browser->type($field, $value);
        }
    }

    /**
     * Select dropdown option.
     */
    protected function selectOption(Browser $browser, string $selector, string $value): void
    {
        $browser->select($selector, $value);
    }

    /**
     * Assert flash message appears.
     */
    protected function assertFlashMessage(Browser $browser, string $message, string $type = 'success'): void
    {
        $browser->assertSeeIn("[data-flash-type='{$type}']", $message);
    }

    /**
     * Wait for and close any modal dialogs.
     */
    protected function closeModals(Browser $browser): void
    {
        $browser->whenAvailable('.modal', function ($modal) {
            $modal->click('[data-dismiss="modal"]')
                ->waitUntilMissing('.modal', 5);
        });
    }

    /**
     * Handle confirmation dialogs.
     */
    protected function confirmAction(Browser $browser): void
    {
        $browser->whenAvailable('.modal.show', function ($modal) {
            $modal->click('button[data-confirm]')
                ->waitUntilMissing('.modal.show', 5);
        });
    }

    /**
     * Assert table contains specific data.
     */
    protected function assertTableContains(Browser $browser, string $text): void
    {
        $browser->assertSeeIn('table', $text);
    }

    /**
     * Assert table does not contain specific data.
     */
    protected function assertTableDoesNotContain(Browser $browser, string $text): void
    {
        $browser->assertDontSeeIn('table', $text);
    }

    /**
     * Search in datatable or list.
     */
    protected function searchInTable(Browser $browser, string $searchTerm): void
    {
        $browser->whenAvailable('input[data-search]', function ($search) use ($searchTerm) {
            $search->type($searchTerm)
                ->pause(500); // Wait for debounce
        });
    }

    /**
     * Click action button in table row.
     */
    protected function clickTableRowAction(Browser $browser, string $rowText, string $action): void
    {
        $browser->with("table tr:contains('{$rowText}')", function ($row) use ($action) {
            $row->click("button[data-action='{$action}']")
                ->pause(200);
        });
    }

    /**
     * Assert page title contains expected text.
     */
    protected function assertPageTitle(Browser $browser, string $title): void
    {
        $browser->assertTitleContains($title);
    }

    /**
     * Assert breadcrumb navigation.
     */
    protected function assertBreadcrumb(Browser $browser, array $items): void
    {
        $browser->assertPresent('nav[aria-label="breadcrumb"]');

        foreach ($items as $item) {
            $browser->assertSeeIn('nav[aria-label="breadcrumb"]', $item);
        }
    }

    /**
     * Handle file uploads.
     */
    protected function uploadFile(Browser $browser, string $selector, string $filePath): void
    {
        $browser->attach($selector, $filePath);
    }

    /**
     * Wait for AJAX request to complete.
     */
    protected function waitForAjax(Browser $browser): void
    {
        $browser->waitUntilMissing('.loading', 10)
            ->pause(200); // Small delay for any remaining animations
    }

    /**
     * Debug helper - pause execution and take screenshot.
     */
    protected function debug(Browser $browser, string $name = 'debug'): void
    {
        $browser->pause(5000)
            ->screenshot($name);
    }
}
