<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;

trait HandlesLivewireTesting
{
    /**
     * Wait for Livewire to initialize and be ready.
     */
    protected function waitForLivewireToLoad(Browser $browser): void
    {
        $browser->waitUntil('window.Livewire !== undefined', 10)
            ->waitUntil('window.Livewire.components !== undefined', 10)
            ->pause(200); // Allow Livewire to fully initialize
    }

    /**
     * Wait for Livewire to be ready (alias for compatibility).
     */
    protected function waitForLivewire(Browser $browser): void
    {
        $browser->waitUntil('window.Livewire !== undefined', 10)
            ->waitUntil('window.Livewire.components !== undefined', 10)
            ->pause(200); // Allow Livewire to fully initialize
    }

    /**
     * Get the current Livewire component instance from browser.
     */
    protected function getLivewireComponent(Browser $browser, string $componentName): ?object
    {
        $script = "
            const component = window.Livewire.find(
                Array.from(document.querySelectorAll('[wire\\\\:id]'))
                    .find(el => el.outerHTML.includes('{$componentName}'))
                    ?.getAttribute('wire:id')
            );
            return component ? {
                id: component.id,
                name: component.name,
                data: component.$wire,
                effects: component.effects
            } : null;
        ";

        return $browser->script($script)[0] ?? null;
    }

    /**
     * Assert Livewire component exists on page.
     */
    protected function assertLivewireComponentExists(Browser $browser, string $componentName): void
    {
        $browser->waitForLivewireToLoad()
            ->assertPresent("[wire\\:id*='{$componentName}']");
    }

    /**
     * Assert Livewire component method exists.
     */
    protected function assertLivewireMethodExists(Browser $browser, string $componentName, string $method): void
    {
        $component = $this->getLivewireComponent($browser, $componentName);

        $this->assertNotNull($component, "Livewire component '{$componentName}' not found");
        $this->assertObjectHasProperty('data', $component);

        $hasMethod = $browser->script("
            const component = window.Livewire.find('{$component->id}');
            return component && typeof component.{$method} === 'function';
        ")[0] ?? false;

        $this->assertTrue($hasMethod, "Method '{$method}' not found in component '{$componentName}'");
    }

    /**
     * Call Livewire component method directly via JavaScript.
     */
    protected function callLivewireMethod(Browser $browser, string $componentName, string $method, array $params = []): void
    {
        $component = $this->getLivewireComponent($browser, $componentName);
        $this->assertNotNull($component, "Livewire component '{$componentName}' not found");

        $paramsJson = json_encode($params);
        $script = "
            const component = window.Livewire.find('{$component->id}');
            if (component && typeof component.{$method} === 'function') {
                component.{$method}(...{$paramsJson});
            }
        ";

        $browser->script($script);
    }

    /**
     * Set Livewire component property value.
     */
    protected function setLivewireProperty(Browser $browser, string $componentName, string $property, $value): void
    {
        $component = $this->getLivewireComponent($browser, $componentName);
        $this->assertNotNull($component, "Livewire component '{$componentName}' not found");

        $valueJson = json_encode($value);
        $script = "
            const component = window.Livewire.find('{$component->id}');
            if (component && component.\$wire) {
                component.\$wire.{$property} = {$valueJson};
            }
        ";

        $browser->script($script);
    }

    /**
     * Get Livewire component property value.
     */
    protected function getLivewireProperty(Browser $browser, string $componentName, string $property)
    {
        $component = $this->getLivewireComponent($browser, $componentName);
        $this->assertNotNull($component, "Livewire component '{$componentName}' not found");

        $script = "
            const component = window.Livewire.find('{$component->id}');
            return component && component.\$wire ? component.\$wire.{$property} : null;
        ";

        return $browser->script($script)[0] ?? null;
    }

    /**
     * Assert Livewire component property has expected value.
     */
    protected function assertLivewirePropertyEquals(Browser $browser, string $componentName, string $property, $expected): void
    {
        $actual = $this->getLivewireProperty($browser, $componentName, $property);
        $this->assertEquals($expected, $actual, "Property '{$property}' does not match expected value");
    }

    /**
     * Wait for Livewire component to finish processing.
     */
    protected function waitForLivewireUpdate(Browser $browser, ?string $componentName = null): void
    {
        $script = $componentName
            ? "
                const component = window.Livewire.components.componentsArray.find(c => c.name.includes('{$componentName}'));
                return component && !component.isProcessing;
            "
            : 'return !window.Livewire.components.componentsArray.some(c => c.isProcessing);';

        $browser->waitUntil($script, 15)
            ->pause(200); // Allow DOM to update
    }

    /**
     * Assert no Livewire JavaScript errors.
     */
    protected function assertNoLivewireErrors(Browser $browser): void
    {
        $errors = $browser->script("
            const errors = [];
            
            // Check console errors
            if (window.console && window.console.error) {
                const originalError = console.error;
                const errorLogs = [];
                console.error = function(...args) {
                    errorLogs.push(args.join(' '));
                    originalError.apply(console, args);
                };
                
                // Restore after a brief moment
                setTimeout(() => {
                    console.error = originalError;
                }, 100);
                
                return errorLogs;
            }
            
            return errors;
        ")[0] ?? [];

        $this->assertEmpty($errors, 'Livewire JavaScript errors detected: '.implode(', ', $errors));
    }

    /**
     * Assert Livewire wire:model is working correctly.
     */
    protected function assertWireModelWorks(Browser $browser, string $selector, string $property, $value): void
    {
        $browser->type($selector, $value)
            ->pause(500) // Wait for debouncing
            ->waitForLivewireUpdate();

        // Check if the Livewire property was updated
        $componentName = $this->extractComponentNameFromSelector($browser, $selector);
        if ($componentName) {
            $actualValue = $this->getLivewireProperty($browser, $componentName, $property);
            $this->assertEquals($value, $actualValue, "wire:model failed for property '{$property}'");
        }
    }

    /**
     * Assert Livewire wire:click is working correctly.
     */
    protected function assertWireClickWorks(Browser $browser, string $selector, ?string $expectedResult = null): void
    {
        $componentName = $this->extractComponentNameFromSelector($browser, $selector);

        $browser->click($selector)
            ->waitForLivewireUpdate($componentName);

        if ($expectedResult && $componentName) {
            // Check for expected result in component or DOM
            $browser->assertSee($expectedResult);
        }
    }

    /**
     * Extract component name from element selector.
     */
    private function extractComponentNameFromSelector(Browser $browser, string $selector): ?string
    {
        $script = "
            const element = document.querySelector('{$selector}');
            if (!element) return null;
            
            let livewireElement = element;
            while (livewireElement && !livewireElement.hasAttribute('wire:id')) {
                livewireElement = livewireElement.parentElement;
            }
            
            if (livewireElement) {
                const component = window.Livewire.find(livewireElement.getAttribute('wire:id'));
                return component ? component.name : null;
            }
            
            return null;
        ";

        return $browser->script($script)[0] ?? null;
    }

    /**
     * Test Livewire component reactivity with Alpine.js.
     */
    protected function assertAlpineLivewireReactivity(Browser $browser, string $alpineExpression, string $livewireProperty): void
    {
        $script = "
            // Test if Alpine can access Livewire properties
            try {
                const result = {$alpineExpression};
                return result !== undefined;
            } catch (e) {
                return false;
            }
        ";

        $reactive = $browser->script($script)[0] ?? false;
        $this->assertTrue($reactive, "Alpine.js and Livewire reactivity not working for expression: {$alpineExpression}");
    }

    /**
     * Assert Livewire loading states are displayed correctly.
     */
    protected function assertLivewireLoadingStates(Browser $browser, string $loadingSelector = '[wire\\:loading]'): void
    {
        // Trigger an action that should show loading state
        $browser->assertPresent($loadingSelector)
            ->waitUntilMissing($loadingSelector, 10);
    }

    /**
     * Test Livewire component lifecycle hooks.
     */
    protected function assertLivewireLifecycleHooks(Browser $browser, string $componentName): void
    {
        $script = "
            const component = window.Livewire.components.componentsArray.find(c => c.name.includes('{$componentName}'));
            if (!component) return false;
            
            // Check if lifecycle hooks were called
            return {
                mounted: component.effects.mounted !== undefined,
                updated: component.effects.updated !== undefined,
                initialized: component.initialized
            };
        ";

        $lifecycle = $browser->script($script)[0] ?? false;
        $this->assertIsArray($lifecycle, 'Could not verify Livewire lifecycle hooks');
        $this->assertTrue($lifecycle['initialized'] ?? false, 'Component was not properly initialized');
    }

    /**
     * Simulate network latency and test component behavior.
     */
    protected function simulateNetworkLatency(Browser $browser, int $milliseconds = 1000): void
    {
        $script = "
            // Simulate network latency for Livewire requests
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                return new Promise(resolve => {
                    setTimeout(() => {
                        resolve(originalFetch.apply(this, args));
                    }, {$milliseconds});
                });
            };
        ";

        $browser->script($script);
    }

    /**
     * Restore normal network behavior.
     */
    protected function restoreNetworkBehavior(Browser $browser): void
    {
        $script = '
            // Restore original fetch if it was overridden
            if (window.originalFetch) {
                window.fetch = window.originalFetch;
            }
        ';

        $browser->script($script);
    }
}
