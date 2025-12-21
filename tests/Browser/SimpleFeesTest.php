<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SimpleFeesTest extends DuskTestCase
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
     * Test visiting fees page loads properly.
     */
    public function test_fees_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/fees')
                ->pause(2000)
                ->assertSee('Manage member fees and payments');

            // Check if Livewire is loaded
            $livewireLoaded = $browser->script("
                return typeof window.Livewire !== 'undefined' && 
                       window.Livewire.components &&
                       window.Livewire.components.componentsArray &&
                       window.Livewire.components.componentsArray.length > 0;
            ")[0] ?? false;

            $this->assertTrue($livewireLoaded, 'Livewire should be loaded on page');

            // Debug what's actually on the page
            $pageTitle = $browser->driver->getTitle();
            echo "\n=== PAGE TITLE: {$pageTitle} ===\n";

            $pageSource = $browser->driver->getPageSource();
            if (strpos($pageSource, 'Manage member fees') !== false) {
                echo "\n=== FOUND EXPECTED TEXT ===\n";
            } else {
                echo "\n=== DID NOT FIND EXPECTED TEXT ===\n";
                echo "\n=== FIRST 2000 CHARS ===\n" . substr($pageSource, 0, 2000) . "\n=== END ===\n";
            }

            // Check if Alpine is loaded
            $alpineLoaded = $browser->script("return typeof window.Alpine !== 'undefined'")[0] ?? false;
            echo "\n=== ALPINE LOADED: " . ($alpineLoaded ? 'YES' : 'NO') . " ===\n";

            // Check if Livewire is loaded
            $livewireLoaded = $browser->script("
                return typeof window.Livewire !== 'undefined' && 
                       window.Livewire.components &&
                       window.Livewire.components.componentsArray;
            ")[0] ?? false;
            echo "\n=== LIVEWIRE LOADED: " . ($livewireLoaded ? 'YES' : 'NO') . " ===\n";

            if ($livewireLoaded) {
                $componentCount = $browser->script("return window.Livewire.components.componentsArray.length")[0] ?? 0;
                echo "\n=== LIVEWIRE COMPONENTS: {$componentCount} ===\n";
            }

            // Take screenshot for debugging
            $browser->screenshot('fees_page_loaded');
        });
    }

    /**
     * Test search functionality works.
     */
    public function test_search_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/fees')
                ->pause(2000)
                ->waitFor('[wire\\:model.live="search"]', 10);

            // Type in search box
            $browser->type('[wire\\:model.live="search"]', 'test search')
                ->pause(1000);

            // Check if search value was set
            $searchValue = $browser->script("
                const component = window.Livewire.components.componentsArray.find(c => 
                    c.name && c.name.includes('fee-manager')
                );
                return component ? component.search : null;
            ")[0];

            $this->assertEquals('test search', $searchValue, 'Search value should be updated');

            // Clear search
            $browser->clear('[wire\\:model.live="search"]')
                ->pause(500);

            // Take screenshot
            $browser->screenshot('search_functionality');
        });
    }

    /**
     * Test button interactions work.
     */
    public function test_button_interactions(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->adminUser)
                ->visit('/fees')
                ->pause(2000)
                ->waitFor('[wire\\:click]', 10);

            // Test clicking create fee button
            $browser->click('[wire\\:click="showCreateFeeForm"]')
                ->pause(1000);

            // Check if form appeared
            $showCreateForm = $browser->script("
                const component = window.Livewire.components.componentsArray.find(c => 
                    c.name && c.name.includes('fee-manager')
                );
                return component ? component.showCreateForm : false;
            ")[0];

            $this->assertTrue($showCreateForm, 'Create form should be visible after clicking button');

            // Test closing form
            $browser->click('[wire\\:click="hideCreateFeeForm"]')
                ->pause(500);

            // Take screenshot
            $browser->screenshot('button_interactions');
        });
    }
}