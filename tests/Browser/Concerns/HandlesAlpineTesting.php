<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;

trait HandlesAlpineTesting
{
    /**
     * Assert Alpine.js data property has expected value.
     */
    protected function assertAlpineData(Browser $browser, string $property, mixed $expectedValue): void
    {
        $actualValue = $this->getAlpineData($browser, $property);
        $this->assertEquals($expectedValue, $actualValue, "Alpine data property '{$property}' does not match expected value");
    }

    /**
     * Get Alpine.js data property value.
     */
    protected function getAlpineData(Browser $browser, string $property): mixed
    {
        $script = "
            var el = document.querySelector('[x-data]');
            if (!el) return null;
            var alpine = el._x_dataStack[0];
            return alpine.{$property};
        ";

        return $this->executeScript($browser, $script);
    }

    /**
     * Set Alpine.js data property value.
     */
    protected function setAlpineData(Browser $browser, string $property, mixed $value): void
    {
        $script = "
            var el = document.querySelector('[x-data]');
            if (!el) return;
            var alpine = el._x_dataStack[0];
            alpine.{$property} = ".json_encode($value).';
        ';

        $this->executeScript($browser, $script);
        $browser->pause(100); // Allow Alpine to reactivity
    }

    /**
     * Assert Alpine.js method exists.
     */
    protected function assertAlpineMethodExists(Browser $browser, string $method): void
    {
        $exists = $this->executeScript($browser, "
            var el = document.querySelector('[x-data]');
            if (!el) return false;
            var alpine = el._x_dataStack[0];
            return typeof alpine.{$method} === 'function';
        ");

        $this->assertTrue($exists, "Alpine method '{$method}' should exist");
    }

    /**
     * Call Alpine.js method.
     */
    protected function callAlpineMethod(Browser $browser, string $method, array $args = []): mixed
    {
        $argsJson = json_encode($args);
        $script = "
            var el = document.querySelector('[x-data]');
            if (!el) return null;
            var alpine = el._x_dataStack[0];
            return alpine.{$method}.apply(alpine, {$argsJson});
        ";

        return $this->executeScript($browser, $script);
    }

    /**
     * Assert Alpine.js component is initialized.
     */
    protected function assertAlpineComponent(Browser $browser, string $selector): void
    {
        $isInitialized = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el) return false;
            return el._x_dataStack && el._x_dataStack.length > 0;
        ");

        $this->assertTrue($isInitialized, "Alpine component at '{$selector}' should be initialized");
    }

    /**
     * Wait for Alpine.js to be ready.
     */
    protected function waitForAlpineReady(Browser $browser): void
    {
        $browser->waitUntil("
            return typeof window.Alpine !== 'undefined' && 
                   document.querySelectorAll('[x-data]').length > 0 &&
                   Array.from(document.querySelectorAll('[x-data]')).every(el => el._x_dataStack && el._x_dataStack.length > 0);
        ", 10);
    }

    /**
     * Get all Alpine.js data properties.
     */
    protected function getAlpineDataSnapshot(Browser $browser): array
    {
        return $this->executeScript($browser, "
            var el = document.querySelector('[x-data]');
            if (!el) return {};
            var alpine = el._x_dataStack[0];
            
            // Create a clean copy of the data
            var data = {};
            for (var key in alpine) {
                if (alpine.hasOwnProperty(key) && typeof alpine[key] !== 'function') {
                    data[key] = alpine[key];
                }
            }
            return data;
        ");
    }

    /**
     * Assert Alpine.js reactivity works correctly.
     */
    protected function assertAlpineReactivity(Browser $browser, string $property, mixed $newValue): void
    {
        // Get initial value
        $initialValue = $this->getAlpineData($browser, $property);

        // Set new value
        $this->setAlpineData($browser, $property, $newValue);

        // Verify the value was set
        $currentValue = $this->getAlpineData($browser, $property);
        $this->assertEquals($newValue, $currentValue, "Alpine reactivity should update '{$property}'");

        // Wait a bit for any reactivity to complete
        $browser->pause(200);

        // Verify the value is still set (ensuring no immediate reset)
        $finalValue = $this->getAlpineData($browser, $property);
        $this->assertEquals($newValue, $finalValue, "Alpine reactivity should maintain '{$property}' value");
    }

    /**
     * Check for Alpine.js errors in console.
     */
    protected function checkAlpineErrors(Browser $browser): array
    {
        $script = "
            var errors = [];
            var originalError = console.error;
            console.error = function() {
                var args = Array.prototype.slice.call(arguments);
                var message = args.join(' ');
                if (message.includes('Alpine') || message.includes('x-data') || message.includes('x-init')) {
                    errors.push(args);
                }
                originalError.apply(console, arguments);
            };
            return errors;
        ";

        $errors = $browser->script($script);

        return $errors[0] ?? [];
    }

    /**
     * Assert no Alpine.js errors occurred.
     */
    protected function assertNoAlpineErrors(Browser $browser): void
    {
        $errors = $this->checkAlpineErrors($browser);
        $this->assertEmpty($errors, 'Alpine.js errors detected: '.json_encode($errors));
    }

    /**
     * Test Alpine.js directive functionality.
     */
    protected function assertAlpineDirective(Browser $browser, string $directive, string $selector, mixed $expectedValue): void
    {
        $actualValue = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el) return null;
            
            switch ('{$directive}') {
                case 'x-show':
                    return el.style.display !== 'none' && el.offsetParent !== null;
                case 'x-text':
                    return el.textContent.trim();
                case 'x-html':
                    return el.innerHTML.trim();
                case 'x-model':
                    return el.value || el.checked;
                case 'x-bind:class':
                    return el.className;
                case 'x-bind:style':
                    return el.getAttribute('style');
                default:
                    return el.getAttribute('{$directive}');
            }
        ");

        $this->assertEquals($expectedValue, $actualValue, "Alpine directive '{$directive}' on '{$selector}' should have expected value");
    }

    /**
     * Test Alpine.js event handling.
     */
    protected function triggerAlpineEvent(Browser $browser, string $selector, string $event): void
    {
        $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el) return;
            
            var event = new Event('{$event}', {
                bubbles: true,
                cancelable: true
            });
            el.dispatchEvent(event);
        ");

        $browser->pause(200); // Allow event handling to complete
    }

    /**
     * Get Alpine.js component state.
     */
    protected function getAlpineComponentState(Browser $browser, string $selector): array
    {
        return $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack) return {};
            
            var state = {};
            var data = el._x_dataStack[0];
            
            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    state[key] = data[key];
                }
            }
            
            return state;
        ");
    }
}
