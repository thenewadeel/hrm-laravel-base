<?php

namespace Tests\Browser\Assertions;

use PHPUnit\Framework\Assert as PHPUnit;

trait LivewireAssertions
{
    /**
     * Assert that a Livewire component is present on the page.
     */
    public function assertLivewireComponentPresent(string $componentName): self
    {
        $this->assertPresent("[wire\\:id*='{$componentName}']");

        return $this;
    }

    /**
     * Assert that a Livewire component is not present on the page.
     */
    public function assertLivewireComponentNotPresent(string $componentName): self
    {
        $this->assertMissing("[wire\\:id*='{$componentName}']");

        return $this;
    }

    /**
     * Assert that a Livewire component contains specific text.
     */
    public function assertLivewireComponentContains(string $componentName, string $text): self
    {
        $this->with("[wire\\:id*='{$componentName}']", function ($browser) use ($text) {
            $browser->assertSee($text);
        });

        return $this;
    }

    /**
     * Assert that a Livewire component does not contain specific text.
     */
    public function assertLivewireComponentDoesNotContain(string $componentName, string $text): self
    {
        $this->with("[wire\\:id*='{$componentName}']", function ($browser) use ($text) {
            $browser->assertDontSee($text);
        });

        return $this;
    }

    /**
     * Assert that a Livewire component property has a specific value.
     */
    public function assertLivewirePropertyEquals(string $componentName, string $property, $expected): self
    {
        $script = "
            const component = window.Livewire.components.componentsArray.find(c => c.name.includes('{$componentName}'));
            if (!component || !component.\$wire) return null;
            return component.\$wire.{$property};
        ";

        $actual = $this->script($script)[0] ?? null;

        PHPUnit::assertEquals(
            $expected,
            $actual,
            "Livewire property '{$property}' in component '{$componentName}' does not equal expected value"
        );

        return $this;
    }

    /**
     * Assert that a Livewire component property exists.
     */
    public function assertLivewirePropertyExists(string $componentName, string $property): self
    {
        $script = "
            const component = window.Livewire.components.componentsArray.find(c => c.name.includes('{$componentName}'));
            if (!component || !component.\$wire) return false;
            return component.\$wire.hasOwnProperty('{$property}');
        ";

        $exists = $this->script($script)[0] ?? false;

        PHPUnit::assertTrue(
            $exists,
            "Livewire property '{$property}' does not exist in component '{$componentName}'"
        );

        return $this;
    }

    /**
     * Assert that a Livewire component method exists.
     */
    public function assertLivewireMethodExists(string $componentName, string $method): self
    {
        $script = "
            const component = window.Livewire.components.componentsArray.find(c => c.name.includes('{$componentName}'));
            if (!component) return false;
            return typeof component.{$method} === 'function';
        ";

        $exists = $this->script($script)[0] ?? false;

        PHPUnit::assertTrue(
            $exists,
            "Livewire method '{$method}' does not exist in component '{$componentName}'"
        );

        return $this;
    }

    /**
     * Assert that a Livewire component is currently loading.
     */
    public function assertLivewireIsLoading(?string $componentName = null): self
    {
        $script = $componentName
            ? "
                const component = window.Livewire.components.componentsArray.find(c => c.name.includes('{$componentName}'));
                return component && component.isProcessing;
            "
            : 'return window.Livewire.components.componentsArray.some(c => c.isProcessing);';

        $isLoading = $this->script($script)[0] ?? false;

        PHPUnit::assertTrue(
            $isLoading,
            'Livewire component is not loading'
        );

        return $this;
    }

    /**
     * Assert that a Livewire component is not loading.
     */
    public function assertLivewireIsNotLoading(?string $componentName = null): self
    {
        $script = $componentName
            ? "
                const component = window.Livewire.components.componentsArray.find(c => c.name.includes('{$componentName}'));
                return !component || !component.isProcessing;
            "
            : 'return !window.Livewire.components.componentsArray.some(c => c.isProcessing);';

        $isNotLoading = $this->script($script)[0] ?? false;

        PHPUnit::assertTrue(
            $isNotLoading,
            'Livewire component is still loading'
        );

        return $this;
    }

    /**
     * Assert that a wire:model input is working correctly.
     */
    public function assertWireModelWorks(string $selector, string $property, $value): self
    {
        $this->type($selector, $value)
            ->pause(500); // Wait for debouncing

        // Check if the Livewire property was updated
        $componentName = $this->extractComponentNameFromSelector($selector);

        if ($componentName) {
            $script = "
                const component = window.Livewire.components.componentsArray.find(c => c.name.includes('{$componentName}'));
                return component && component.\$wire ? component.\$wire.{$property} : null;
            ";

            $actualValue = $this->script($script)[0] ?? null;

            PHPUnit::assertEquals(
                $value,
                $actualValue,
                "wire:model failed for property '{$property}' with selector '{$selector}'"
            );
        }

        return $this;
    }

    /**
     * Assert that a wire:click action is working correctly.
     */
    public function assertWireClickWorks(string $selector, $expectedResult = null): self
    {
        $componentName = $this->extractComponentNameFromSelector($selector);

        $this->click($selector)
            ->pause(500); // Wait for Livewire update

        if ($expectedResult) {
            $this->assertSee($expectedResult);
        }

        return $this;
    }

    /**
     * Assert that Livewire validation errors are displayed.
     */
    public function assertLivewireValidationErrors(array $fields): self
    {
        foreach ($fields as $field) {
            $this->assertPresent("[wire\\:target='{$field}']")
                ->assertSeeIn("[wire\\:target='{$field}']", 'error');
        }

        return $this;
    }

    /**
     * Assert that no Livewire validation errors are displayed.
     */
    public function assertNoLivewireValidationErrors(array $fields = []): self
    {
        if (empty($fields)) {
            $this->assertMissing('[wire\\:target]');
        } else {
            foreach ($fields as $field) {
                $this->assertMissing("[wire\\:target='{$field}']");
            }
        }

        return $this;
    }

    /**
     * Assert that a Livewire event was dispatched.
     */
    public function assertLivewireEventDispatched(string $eventName, array $params = []): self
    {
        $script = "
            const dispatchedEvents = window.Livewire.components.componentsArray
                .flatMap(c => c.effects.dispatched || [])
                .filter(e => e.event === '{$eventName}');
            
            return dispatchedEvents.length > 0;
        ";

        $wasDispatched = $this->script($script)[0] ?? false;

        PHPUnit::assertTrue(
            $wasDispatched,
            "Livewire event '{$eventName}' was not dispatched"
        );

        return $this;
    }

    /**
     * Assert that a Livewire listener is registered.
     */
    public function assertLivewireListenerRegistered(string $eventName): self
    {
        $script = "
            const hasListener = window.Livewire.components.componentsArray
                .some(c => c.listeners && c.listeners.hasOwnProperty('{$eventName}'));
            
            return hasListener;
        ";

        $hasListener = $this->script($script)[0] ?? false;

        PHPUnit::assertTrue(
            $hasListener,
            "Livewire listener for event '{$eventName}' is not registered"
        );

        return $this;
    }

    /**
     * Assert that Alpine.js and Livewire are working together.
     */
    public function assertAlpineLivewireIntegration(): self
    {
        $script = "
            // Check if Alpine.js is available
            if (!window.Alpine) return false;
            
            // Check if Livewire is available
            if (!window.Livewire) return false;
            
            // Check if Alpine can access Livewire
            try {
                const testComponent = window.Alpine.$data(document.querySelector('[x-data]'));
                return testComponent !== undefined;
            } catch (e) {
                return false;
            }
        ";

        $isIntegrated = $this->script($script)[0] ?? false;

        PHPUnit::assertTrue(
            $isIntegrated,
            'Alpine.js and Livewire integration is not working'
        );

        return $this;
    }

    /**
     * Assert that Livewire polling is working.
     */
    public function assertLivewirePolling(string $componentName, int $intervalMs): self
    {
        $script = "
            const component = window.Livewire.components.componentsArray.find(c => c.name.includes('{$componentName}'));
            if (!component) return false;
            
            return component.effects.poll && component.effects.poll.interval === {$intervalMs};
        ";

        $isPolling = $this->script($script)[0] ?? false;

        PHPUnit::assertTrue(
            $isPolling,
            "Livewire component '{$componentName}' is not polling with interval {$intervalMs}ms"
        );

        return $this;
    }

    /**
     * Assert that Livewire component state is synchronized.
     */
    public function assertLivewireStateSynchronized(string $componentName): self
    {
        $script = "
            const component = window.Livewire.components.componentsArray.find(c => c.name.includes('{$componentName}'));
            if (!component) return false;
            
            // Check if component data is in sync with server
            return component.dataChecksum === component.serverMemo.dataChecksum;
        ";

        $isSynchronized = $this->script($script)[0] ?? false;

        PHPUnit::assertTrue(
            $isSynchronized,
            "Livewire component '{$componentName}' state is not synchronized"
        );

        return $this;
    }

    /**
     * Extract component name from element selector.
     */
    private function extractComponentNameFromSelector(string $selector): ?string
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

        return $this->script($script)[0] ?? null;
    }
}
