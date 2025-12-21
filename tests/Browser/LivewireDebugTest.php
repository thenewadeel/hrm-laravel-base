<?php

namespace Tests\Browser;

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LivewireDebugTest extends DuskTestCase
{
    protected ?Organization $organization = null;
    protected ?User $adminUser = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->adminUser = User::factory()->create(['email_verified_at' => now()]);

        $this->organization->users()->attach($this->adminUser->id, [
            'roles' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Simple test to debug page loading.
     */
    public function test_debug_fees_page_loading(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/membership/fees')
                ->pause(3000);

            // Get current URL
            $currentUrl = $browser->driver->getCurrentURL();
            echo "\n=== CURRENT URL: {$currentUrl} ===\n";

            // Check response status
            $statusCode = $browser->driver->executeScript("return window.performance.getEntriesByType('navigation')[0]?.responseStatus || 200;")[0] ?? 200;
            echo "\n=== RESPONSE STATUS: {$statusCode} ===\n";

            // Get page source
            $pageSource = $browser->driver->getPageSource();
            echo "\n=== PAGE SOURCE LENGTH: " . strlen($pageSource) . " ===\n";
            
            // Look for specific error patterns
            if (strpos($pageSource, 'Not Found') !== false) {
                echo "\n=== PAGE SHOWING 'Not Found' ===\n";
            }
            if (strpos($pageSource, '404') !== false) {
                echo "\n=== PAGE SHOWING '404' ===\n";
            }

            // Try alternative routes
            echo "\n=== TRYING ALTERNATIVE ROUTES ===\n";
            
            $browser->visit('/fees')->pause(1000);
            $feesUrl = $browser->driver->getCurrentURL();
            echo "\n=== VISITED /fees: {$feesUrl} ===\n";
            
            $feesSource = $browser->driver->getPageSource();
            if (strpos($feesSource, 'Not Found') === false) {
                echo "\n=== /fees ROUTE WORKS! ===\n";
            }

            // Take screenshot
            $browser->screenshot('debug_fees_page');

            // Check for any Livewire elements
            $livewireElements = $browser->elements('[wire\\:id]');
            echo "\n=== LIVEWIRE ELEMENTS COUNT: " . count($livewireElements) . " ===\n";

            // Check if Alpine is loaded
            $alpineLoaded = $browser->script("return typeof window.Alpine !== 'undefined'")[0] ?? false;
            echo "\n=== ALPINE LOADED: " . ($alpineLoaded ? 'YES' : 'NO') . " ===\n";

            // Check if Livewire is loaded
            $livewireLoaded = $browser->script("return typeof window.Livewire !== 'undefined'")[0] ?? false;
            echo "\n=== LIVEWIRE LOADED: " . ($livewireLoaded ? 'YES' : 'NO') . " ===\n";

            // Simple assertion that should pass
            $this->assertNotEmpty($currentUrl, 'URL should not be empty');
        });
    }

    /**
     * Test visiting fees page directly.
     */
    public function test_visit_fees_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/fees')
                ->pause(2000);

            $currentUrl = $browser->driver->getCurrentURL();
            echo "\n=== FEES PAGE URL: {$currentUrl} ===\n";

            $pageSource = $browser->driver->getPageSource();
            if (strpos($pageSource, 'livewire:fee-manager') !== false) {
                echo "\n=== FOUND FEE-MANAGER COMPONENT ===\n";
            }

            if (strpos($pageSource, 'Manage member fees and payments') !== false) {
                echo "\n=== FOUND PAGE CONTENT ===\n";
            }

            // Take screenshot
            $browser->screenshot('fees_page_visit');

            $this->assertNotEmpty($currentUrl, 'URL should not be empty');
        });
    }
}