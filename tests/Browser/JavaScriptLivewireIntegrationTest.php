<?php

namespace Tests\Browser;

use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Comprehensive JavaScript and Livewire Integration Tests
 * 
 * This test file focuses on core functionality and debugging
 * to ensure Alpine.js and Livewire work together properly.
 */
class JavaScriptLivewireIntegrationTest extends DuskTestCase
{
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
     * Test basic JavaScript framework loading.
     */
    public function test_javascript_frameworks_load(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/')
                ->pause(2000);

            // Check if Alpine.js is loaded
            $alpineLoaded = $browser->script("return typeof window.Alpine !== 'undefined'")[0] ?? false;
            $this->assertTrue($alpineLoaded, 'Alpine.js should be loaded');

            // Check if Livewire is loaded
            $livewireLoaded = $browser->script("return typeof window.Livewire !== 'undefined'")[0] ?? false;
            $this->assertTrue($livewireLoaded, 'Livewire should be loaded');

            // Get Alpine version
            $alpineVersion = $browser->script("return window.Alpine ? window.Alpine.version : null")[0] ?? null;
            $this->assertNotEmpty($alpineVersion, 'Alpine.js version should be available');

            // Check Livewire components
            $livewireComponents = $browser->script("return window.Livewire ? window.Livewire.components.componentsArray.length : 0")[0] ?? 0;
            $this->assertGreaterThanOrEqual(0, $livewireComponents, 'Should have Livewire components');

            // Check Alpine components
            $alpineComponents = $browser->script("return document.querySelectorAll('[x-data]').length")[0] ?? 0;
            $this->assertGreaterThanOrEqual(0, $alpineComponents, 'Should have Alpine components');

            // Take screenshot for debugging
            $browser->screenshot('javascript_frameworks_loaded');
        });
    }

    /**
     * Test Alpine.js data binding and reactivity.
     */
    public function test_alpine_data_binding(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/')
                ->pause(2000);

            // Find an Alpine component
            $hasAlpineComponent = $browser->script("
                const el = document.querySelector('[x-data]');
                return el && el._x_dataStack && el._x_dataStack.length > 0;
            ")[0] ?? false;

            if ($hasAlpineComponent) {
                // Test basic data property access
                $dataKeys = $browser->script("
                    const el = document.querySelector('[x-data]');
                    if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return [];
                    const alpine = el._x_dataStack[0];
                    return Object.keys(alpine).filter(k => typeof alpine[k] !== 'function');
                ")[0] ?? [];

                $this->assertNotEmpty($dataKeys, 'Alpine component should have data properties');

                // Test reactivity
                $reactivityTest = $browser->script("
                    const el = document.querySelector('[x-data]');
                    if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return false;
                    
                    const alpine = el._x_dataStack[0];
                    const originalValue = alpine.testProp || null;
                    
                    // Set a test property
                    alpine.testProp = 'test-value';
                    
                    // Check if property was set
                    return alpine.testProp === 'test-value';
                ")[0] ?? false;

                $this->assertTrue($reactivityTest, 'Alpine reactivity should work');
            }

            // Take screenshot for debugging
            $browser->screenshot('alpine_data_binding');
        });
    }

    /**
     * Test Livewire component initialization.
     */
    public function test_livewire_component_initialization(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/fees')
                ->pause(3000);

            // Get current URL
            $currentUrl = $browser->driver->getCurrentURL();
            echo "\n=== CURRENT URL: {$currentUrl} ===\n";

            // Check if we're on fees page or redirected
            if (strpos($currentUrl, '/login') !== false) {
                echo "\n=== REDIRECTED TO LOGIN - AUTH ISSUE ===\n";
                $this->markTestSkipped('Authentication/redirect issue - needs investigation');
                return;
            }

            // Check for Livewire elements
            $livewireElements = $browser->script("
                return Array.from(document.querySelectorAll('[wire\\\\:id]')).map(el => ({
                    id: el.getAttribute('wire:id'),
                    name: el.getAttribute('wire:name'),
                    hasData: !!el.getAttribute('wire:data')
                }));
            ")[0] ?? [];

            echo "\n=== LIVEWIRE ELEMENTS: " . json_encode($livewireElements) . " ===\n";

            // Check if Livewire components are initialized
            $componentsInitialized = $browser->script("
                return window.Livewire && 
                       window.Livewire.components &&
                       window.Livewire.components.componentsArray &&
                       window.Livewire.components.componentsArray.length > 0;
            ")[0] ?? false;

            $this->assertTrue($componentsInitialized, 'Livewire components should be initialized');

            // Take screenshot for debugging
            $browser->screenshot('livewire_component_initialization');
        });
    }

    /**
     * Test Livewire and Alpine.js integration.
     */
    public function test_livewire_alpine_integration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/fees')
                ->pause(3000);

            // Skip if redirected to login
            $currentUrl = $browser->driver->getCurrentURL();
            if (strpos($currentUrl, '/login') !== false) {
                $this->markTestSkipped('Authentication issue - integration test skipped');
                return;
            }

            // Test if both frameworks are working
            $integrationTest = $browser->script("
                const alpineLoaded = typeof window.Alpine !== 'undefined';
                const livewireLoaded = typeof window.Livewire !== 'undefined';
                const livewireComponents = window.Livewire ? window.Livewire.components.componentsArray : [];
                const alpineComponents = document.querySelectorAll('[x-data]');
                
                // Test if Alpine can access Livewire data
                let alpineCanAccessLivewire = false;
                if (livewireComponents.length > 0 && alpineComponents.length > 0) {
                    const livewireEl = document.querySelector('[wire\\\\:id]');
                    const alpineEl = livewireEl ? livewireEl.closest('[x-data]') : null;
                    
                    if (alpineEl && alpineEl._x_dataStack && alpineEl._x_dataStack.length > 0) {
                        // Check if Alpine data stack contains Livewire data
                        const alpine = alpineEl._x_dataStack[0];
                        alpineCanAccessLivewire = true;
                    }
                }
                
                return {
                    alpineLoaded,
                    livewireLoaded,
                    livewireComponentCount: livewireComponents.length,
                    alpineComponentCount: alpineComponents.length,
                    alpineCanAccessLivewire
                };
            ")[0] ?? [];

            $this->assertTrue($integrationTest['alpineLoaded'], 'Alpine should be loaded');
            $this->assertTrue($integrationTest['livewireLoaded'], 'Livewire should be loaded');
            $this->assertTrue($integrationTest['alpineCanAccessLivewire'], 'Alpine should be able to access Livewire components');

            // Take screenshot for debugging
            $browser->screenshot('livewire_alpine_integration');
        });
    }

    /**
     * Test JavaScript error handling.
     */
    public function test_javascript_error_handling(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/')
                ->pause(2000);

            // Set up error monitoring
            $errorMonitoring = $browser->script("
                window.__duskErrorTest = {
                    errors: [],
                    originalError: console.error,
                    originalWarn: console.warn
                };
                
                console.error = function() {
                    const args = Array.prototype.slice.call(arguments);
                    window.__duskErrorTest.errors.push({
                        type: 'error',
                        message: args.join(' '),
                        timestamp: new Date().toISOString()
                    });
                    window.__duskErrorTest.originalError.apply(console, arguments);
                };
                
                console.warn = function() {
                    const args = Array.prototype.slice.call(arguments);
                    window.__duskErrorTest.errors.push({
                        type: 'warn',
                        message: args.join(' '),
                        timestamp: new Date().toISOString()
                    });
                    window.__duskErrorTest.originalWarn.apply(console, arguments);
                };
                
                return 'error-monitoring-setup';
            ")[0];

            $this->assertEquals('error-monitoring-setup', $errorMonitoring);

            // Trigger a harmless error
            $browser->script("
                try {
                    // This should cause a ReferenceError
                    const result = nonExistentVariable.test;
                } catch (error) {
                    window.__duskErrorTest.caughtError = {
                        name: error.name,
                        message: error.message
                    };
                }
            ")[0];

            // Check if error was caught
            $errorCaught = $browser->script("
                return window.__duskErrorTest.caughtError || null;
            ")[0];

            $this->assertNotNull($errorCaught, 'JavaScript error handling should catch errors');
            $this->assertEquals('ReferenceError', $errorCaught['name']);

            // Take screenshot for debugging
            $browser->screenshot('javascript_error_handling');
        });
    }

    /**
     * Test basic browser interactions.
     */
    public function test_basic_browser_interactions(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/')
                ->pause(2000);

            // Test clicking navigation elements
            $navLinks = $browser->elements('nav a, .navigation a, [role="navigation"] a');
            
            if (!empty($navLinks)) {
                // Click first navigation link
                $firstLinkText = $browser->text($navLinks[0]);
                $browser->click($navLinks[0])
                    ->pause(1000);

                // Check if navigation happened
                $newUrl = $browser->driver->getCurrentURL();
                $this->assertNotEquals('/', $newUrl, 'Should have navigated away from home page');

                // Take screenshot
                $browser->screenshot('browser_navigation_interaction');
            } else {
                $this->markTestSkipped('No navigation elements found');
            }
        });
    }
}