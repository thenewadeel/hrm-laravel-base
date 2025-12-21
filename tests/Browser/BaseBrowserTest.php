<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

abstract class BaseBrowserTest extends DuskTestCase
{
    /**
     * Create and login as an admin user.
     */
    protected function loginAsAdmin(Browser $browser, ?Organization $organization = null): User
    {
        $organization = $organization ?: Organization::factory()->create();

        $user = User::factory()->create(['email_verified_at' => now()]);

        $organization->users()->attach($user->id, [
            'roles' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $browser->loginAs($user)
            ->visit('/')
            ->waitForText($organization->name, 10);

        return $user;
    }

    /**
     * Create and login as a regular user.
     */
    protected function loginAsUser(Browser $browser, ?Organization $organization = null): User
    {
        $organization = $organization ?: Organization::factory()->create();

        $user = User::factory()->create(['email_verified_at' => now()]);

        $organization->users()->attach($user->id, [
            'roles' => 'member',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $browser->loginAs($user)
            ->visit('/')
            ->waitForText($organization->name, 10);

        return $user;
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
     * Wait for Livewire component to load.
     */
    protected function waitForLivewire(Browser $browser, ?string $component = null): void
    {
        if ($component) {
            $browser->waitFor("[wire\\:id*='{$component}']", 10);
        } else {
            $browser->waitFor('[wire\\:id]', 10);
        }
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
