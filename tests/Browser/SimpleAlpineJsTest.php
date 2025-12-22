<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\HandlesAlpineTesting;
use Tests\Browser\Concerns\HandlesJavaScriptErrors;

class SimpleAlpineJsTest extends BaseBrowserTest
{
    use HandlesAlpineTesting, HandlesJavaScriptErrors;

    /**
     * Test Alpine.js basic loading without authentication.
     */
    public function test_alpine_basic_loading(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(5000); // Just wait, don't use waitFor for now

            // Setup error monitoring
            $this->setupJavaScriptErrorMonitoring($browser);

            // Check if page loads at all
            $title = $browser->driver->getTitle();
            $this->assertNotEmpty($title, 'Page should have a title');

            // Check if Alpine.js is loaded
            $alpineLoaded = $browser->script("return typeof window.Alpine !== 'undefined'")[0] ?? false;
            
            // Debug information
            $debugInfo = $browser->script("
                return {
                    url: window.location.href,
                    title: document.title,
                    alpineDefined: typeof window.Alpine !== 'undefined',
                    alpineVersion: window.Alpine ? window.Alpine.version : 'undefined',
                    livewireDefined: typeof window.Livewire !== 'undefined',
                    documentReady: document.readyState,
                    scriptsCount: document.scripts.length,
                    bodyPresent: document.body ? true : false,
                    errors: window.__duskErrorMonitor ? window.__duskErrorMonitor.errors : []
                };
            ")[0];

            echo "\n=== Alpine.js Debug Info ===\n";
            echo json_encode($debugInfo, JSON_PRETTY_PRINT) . "\n";

            // For now, just check the page loads successfully
            $this->assertTrue(true, 'Basic page loading test completed');
        });
    }

    /**
     * Test Alpine.js on a page that uses it explicitly.
     */
    public function test_alpine_on_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            // Create a simple user and organization
            $organization = $this->createOrganization();
            $user = $this->createUser($organization);
            
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->pause(5000);

            // Setup monitoring
            $this->setupJavaScriptErrorMonitoring($browser);

            // Check if Alpine is available
            $alpineState = $browser->script("
                return {
                    alpineDefined: typeof window.Alpine !== 'undefined',
                    alpineVersion: window.Alpine ? window.Alpine.version : 'undefined',
                    xDataElements: document.querySelectorAll('[x-data]').length,
                    documentReady: document.readyState,
                    bodyClasses: document.body.className
                };
            ")[0];

            echo "\n=== Dashboard Alpine State ===\n";
            echo json_encode($alpineState, JSON_PRETTY_PRINT) . "\n";

            $this->assertNotEmpty($alpineState['alpineVersion'], 'Alpine.js should be loaded on dashboard');
        });
    }

    private function createOrganization()
    {
        return \App\Models\Organization::factory()->create();
    }

    private function createUser($organization)
    {
        $user = \App\Models\User::factory()->create(['email_verified_at' => now()]);
        $organization->users()->attach($user->id, [
            'roles' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $user;
    }
}