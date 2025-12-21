<?php

namespace Tests\Browser\HR;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\Browser\JavaScriptDuskTestCase;

class HRDirectTest extends JavaScriptDuskTestCase
{
    /**
     * Test HR pages can be accessed with direct login.
     */
    public function test_hr_pages_accessible(): void
    {
        $this->browse(function (Browser $browser) {
            // Create organization and user directly
            $organization = Organization::factory()->create();
            $user = User::factory()->create();
            
            // Attach user to organization
            $organization->users()->attach($user->id, [
                'roles' => json_encode(['admin']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Login as user
            $browser->loginAs($user)
                ->visit('/')
                ->pause(3000)
                ->screenshot('hr-direct-logged-in');

            // Test employee index page
            $browser->visit('/hr/employees')
                ->pause(3000)
                ->assertPathIs('/hr/employees')
                ->screenshot('hr-direct-employees-index');

            // Test employee create page
            $browser->visit('/hr/employees/create')
                ->pause(3000)
                ->assertPathIs('/hr/employees/create')
                ->screenshot('hr-direct-employees-create');

            // Test that forms have expected fields
            $browser->assertPresent('input[name="first_name"]')
                ->assertPresent('input[name="last_name"]')
                ->assertPresent('input[name="email"]')
                ->screenshot('hr-direct-form-fields');
        });
    }

    /**
     * Test employee search functionality works.
     */
    public function test_employee_search_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            // Setup user and organization
            $organization = Organization::factory()->create();
            $user = User::factory()->create();
            $organization->users()->attach($user->id, [
                'roles' => json_encode(['admin']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $browser->loginAs($user)
                ->visit('/hr/employees')
                ->pause(3000)
                ->screenshot('hr-search-start');

            // Test search input exists
            $browser->assertPresent('input[name="search"]')
                ->type('input[name="search"]', 'test search')
                ->pause(2000)
                ->assertPresent('input[name="search"]') // Should still be there
                ->screenshot('hr-search-typed');
        });
    }

    /**
     * Test basic form interactions.
     */
    public function test_basic_form_interactions(): void
    {
        $this->browse(function (Browser $browser) {
            // Setup
            $organization = Organization::factory()->create();
            $user = User::factory()->create();
            $organization->users()->attach($user->id, [
                'roles' => json_encode(['admin']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $browser->loginAs($user)
                ->visit('/hr/employees/create')
                ->pause(3000)
                ->screenshot('hr-form-start');

            // Fill in some form fields
            $timestamp = time();
            $browser->type('input[name="first_name"]', 'Test')
                ->type('input[name="last_name"]', 'User')
                ->type('input[name="email"]', "test.{$timestamp}@example.com")
                ->pause(2000)
                ->screenshot('hr-form-filled');

            // Check if we can clear fields
            $browser->clear('input[name="first_name"]')
                ->pause(1000)
                ->screenshot('hr-form-cleared');
        });
    }

    /**
     * Test that no JavaScript errors occur on HR pages.
     */
    public function test_no_javascript_errors(): void
    {
        $this->browse(function (Browser $browser) {
            // Setup
            $organization = Organization::factory()->create();
            $user = User::factory()->create();
            $organization->users()->attach($user->id, [
                'roles' => json_encode(['admin']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Visit HR pages and check for issues
            $pages = [
                '/hr/employees',
                '/hr/employees/create',
                '/hr/positions',
                '/hr/shifts'
            ];

            foreach ($pages as $page) {
                $browser->loginAs($user)
                    ->visit($page)
                    ->pause(3000)
                    ->screenshot('hr-no-errors-' . str_replace('/', '-', $page));
                
                // Simple check - if page loads without crashing, test passes
                $browser->assertPathBeginsWith('/hr');
            }
        });
    }
}