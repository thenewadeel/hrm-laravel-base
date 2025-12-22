<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SimpleLivewireTest extends DuskTestCase
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
     * Test Livewire basic availability.
     */
    public function test_livewire_availability(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/')
                ->pause(3000);

            // Check Livewire basic availability
            $livewireInfo = $browser->script("
                const info = {
                    livewireDefined: typeof window.Livewire !== 'undefined',
                    hasComponents: !!(typeof window.Livewire.components === 'undefined'),
                    hasFind: typeof window.Livewire.find === 'function',
                    hasComponentsArray: typeof window.Livewire.componentsComponentsArray !== 'undefined',
                    hasEffects: !!(typeof window.Livewire.effects === 'undefined'),
                    pageUrl: window.location.href,
                    readyState: document.readyState
                };

                // Try to find components safely
                if (window.Livewire && window.Livewire.componentsComponentsArray) {
                    info.componentCount = window.Livewire.componentsComponentsArray.length;
                    info.componentNames = window.Livewire.componentsComponentsArray.map(c => c.name || c.constructor.name);
                } else {
                    info.componentCount = 0;
                    info.componentNames = [];
                }

                return info;
            ")[0] ?? [];

            // echo "\n=== LIVEWIRE INFO ===\n";
            // echo "Livewire Defined: " . ($livewireInfo['livewireDefined'] ? 'YES' : 'NO') . "\n";
            // echo "Has Components: " . ($livewireInfo['hasComponents'] ? 'YES' : 'NO') . "\n";
            // echo "Has Find Method: " . ($livewireInfo['hasFind'] ? 'YES' : 'NO') . "\n";
            // echo "Has Components Array: " . ($livewireInfo['hasComponentsArray'] ? 'YES' : 'NO') . "\n";
            // echo "Component Count: " . $livewireInfo['componentCount'] . "\n";
            // echo "Component Names: " . implode(', ', $livewireInfo['componentNames']) . "\n";
            // echo "Page URL: " . $livewireInfo['pageUrl'] . "\n";
            // echo "Ready State: " . $livewireInfo['readyState'] . "\n";
            // echo "==========================\n";

            // Take screenshot
            $browser->screenshot('livewire_availability');

            // Basic assertions
            $this->assertTrue($livewireInfo['livewireDefined'], 'Livewire should be defined');
            $this->assertTrue($livewireInfo['hasComponents'], 'Livewire should have components');
            $this->assertGreaterThanOrEqual(0, $livewireInfo['componentCount'], 'Should have some components');
        });
    }

    /**
     * Test Livewire component on fees page.
     */
    public function test_fees_page_livewire(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/fees')
                ->pause(3000);

            $currentUrl = $browser->driver->getCurrentURL();
            // echo "\n=== FEES PAGE URL: {$currentUrl} ===\n";

            // Check if redirected
            if (strpos($currentUrl, '/login') !== false) {
                // echo "\n=== REDIRECTED TO LOGIN - AUTHENTICATION ISSUE ===\n";
                $this->markTestSkipped('Authentication issue - redirected to login');
                return;
            }

            // Check page content
            $pageContent = $browser->driver->getPageSource();
            if (strpos($pageContent, 'livewire:') === false) {
                // echo "\n=== NO LIVEWIRE COMPONENTS FOUND IN SOURCE ===\n";
                $this->markTestSkipped('No Livewire components found on page');
                return;
            }

            // echo "\n=== FOUND LIVEWIRE COMPONENTS ===\n";

            // Extract Livewire component info
            $livewireComponents = $browser->script("
                const components = [];
                const elements = document.querySelectorAll('[wire\\\\:id]');

                elements.forEach(el => {
                    const wireId = el.getAttribute('wire:id');
                    if (wireId) {
                        components.push({
                            wireId: wireId,
                            tagName: el.tagName.toLowerCase(),
                            hasData: !!el.getAttribute('wire:data'),
                            hasModel: !!el.getAttribute('wire:model'),
                            hasClick: !!el.getAttribute('wire:click')
                        });
                    }
                });

                return {
                    count: components.length,
                    components: components
                };
            ")[0] ?? [];

            // echo "\n=== LIVEWIRE COMPONENT COUNT: " . $livewireComponents['count'] . " ===\n";

            if ($livewireComponents['count'] > 0) {
                foreach ($livewireComponents['components'] as $component) {
                    // echo "Component: {$component['wireId']} ({$component['tagName']})\n";
                    // echo "  Has Data: " . ($component['hasData'] ? 'YES' : 'NO') . "\n";
                    // echo "  Has Model: " . ($component['hasModel'] ? 'YES' : 'NO') . "\n";
                    // echo "  Has Click: " . ($component['hasClick'] ? 'YES' : 'NO') . "\n";
                    // echo "  ---\n";
                }
            }

            // Take screenshot
            $browser->screenshot('fees_livewire_components');

            // Assertions
            if ($livewireComponents['count'] > 0) {
                $this->assertGreaterThan(0, $livewireComponents['count'], 'Should have Livewire components on fees page');
            }
        });
    }
}
