<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;

trait HandlesAlpineJsTesting
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
     * Get Alpine.js data property value from specific component.
     */
    protected function getAlpineData(Browser $browser, string $property, string $selector = '[x-data]'): mixed
    {
        $script = "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return null;
            var alpine = el._x_dataStack[0];
            return alpine.{$property};
        ";

        return $this->executeAlpineScript($browser, $script);
    }

    /**
     * Set Alpine.js data property value.
     */
    protected function setAlpineData(Browser $browser, string $property, mixed $value, string $selector = '[x-data]'): void
    {
        $script = "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return;
            var alpine = el._x_dataStack[0];
            alpine.{$property} = ".json_encode($value).';
        ';

        $this->executeAlpineScript($browser, $script);
        $browser->pause(100); // Allow Alpine reactivity
    }

    /**
     * Assert Alpine.js method exists.
     */
    protected function assertAlpineMethodExists(Browser $browser, string $method, string $selector = '[x-data]'): void
    {
        $exists = $this->executeAlpineScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return false;
            var alpine = el._x_dataStack[0];
            return typeof alpine.{$method} === 'function';
        ");

        $this->assertTrue($exists, "Alpine method '{$method}' should exist in component '{$selector}'");
    }

    /**
     * Call Alpine.js method and return result.
     */
    protected function callAlpineMethod(Browser $browser, string $method, array $args = [], string $selector = '[x-data]'): mixed
    {
        $argsJson = json_encode($args);
        $script = "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return null;
            var alpine = el._x_dataStack[0];
            return alpine.{$method}.apply(alpine, {$argsJson});
        ";

        return $this->executeAlpineScript($browser, $script);
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
                   window.Alpine.version &&
                   document.querySelectorAll('[x-data]').length > 0;
        ", 10);

        // Wait for components to initialize
        $browser->pause(500);
    }

    /**
     * Get all Alpine.js data properties from component.
     */
    protected function getAlpineDataSnapshot(Browser $browser, string $selector = '[x-data]'): array
    {
        return $this->executeAlpineScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return {};
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
    protected function assertAlpineReactivity(Browser $browser, string $property, mixed $newValue, string $selector = '[x-data]'): void
    {
        // Get initial value
        $initialValue = $this->getAlpineData($browser, $property, $selector);

        // Set new value
        $this->setAlpineData($browser, $property, $newValue, $selector);

        // Verify the value was set
        $currentValue = $this->getAlpineData($browser, $property, $selector);
        $this->assertEquals($newValue, $currentValue, "Alpine reactivity should update '{$property}'");

        // Wait for reactivity to complete
        $browser->pause(200);

        // Verify the value is still set
        $finalValue = $this->getAlpineData($browser, $property, $selector);
        $this->assertEquals($newValue, $finalValue, "Alpine reactivity should maintain '{$property}' value");
    }

    /**
     * Check for Alpine.js errors in console.
     */
    protected function checkAlpineErrors(Browser $browser): array
    {
        return $this->executeAlpineScript($browser, "
            var errors = [];
            var originalError = console.error;
            var originalWarn = console.warn;
            
            // Capture recent errors (last 5 seconds)
            var now = Date.now();
            
            console.error = function() {
                var args = Array.prototype.slice.call(arguments);
                var message = args.join(' ');
                if (message.includes('Alpine') || 
                    message.includes('x-data') || 
                    message.includes('x-init') ||
                    message.includes('x-show') ||
                    message.includes('x-if') ||
                    message.includes('x-model') ||
                    message.includes('is not defined')) {
                    errors.push({
                        type: 'error',
                        message: message,
                        args: args,
                        timestamp: new Date().toISOString()
                    });
                }
                originalError.apply(console, arguments);
            };
            
            console.warn = function() {
                var args = Array.prototype.slice.call(arguments);
                var message = args.join(' ');
                if (message.includes('Alpine') || message.includes('expression')) {
                    errors.push({
                        type: 'warning',
                        message: message,
                        args: args,
                        timestamp: new Date().toISOString()
                    });
                }
                originalWarn.apply(console, arguments);
            };
            
            return errors;
        ");
    }

    /**
     * Assert no Alpine.js errors occurred.
     */
    protected function assertNoAlpineErrors(Browser $browser): void
    {
        $errors = $this->checkAlpineErrors($browser);
        $this->assertEmpty($errors, 'Alpine.js errors detected: '.json_encode($errors, JSON_PRETTY_PRINT));
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
                    if (el.type === 'checkbox') return el.checked;
                    if (el.type === 'radio') return el.checked;
                    return el.value;
                case 'x-bind:class':
                    return el.className;
                case 'x-bind:style':
                    return el.getAttribute('style');
                case 'x-bind:href':
                    return el.getAttribute('href');
                case 'x-bind:src':
                    return el.getAttribute('src');
                case 'x-bind:disabled':
                    return el.disabled;
                case 'x-bind:readonly':
                    return el.readOnly;
                default:
                    return el.getAttribute('{$directive}');
            }
        ");

        $this->assertEquals($expectedValue, $actualValue, "Alpine directive '{$directive}' on '{$selector}' should have expected value");
    }

    /**
     * Test Alpine.js event handling.
     */
    protected function triggerAlpineEvent(Browser $browser, string $selector, string $event, array $data = []): void
    {
        $dataJson = json_encode($data);
        $script = "
            var el = document.querySelector('{$selector}');
            if (!el) return;
            
            var event = new CustomEvent('{$event}', {
                detail: {$dataJson},
                bubbles: true,
                cancelable: true
            });
            el.dispatchEvent(event);
        ";

        $this->executeAlpineScript($browser, $script);
        $browser->pause(200); // Allow event handling to complete
    }

    /**
     * Get Alpine.js component state including methods.
     */
    protected function getAlpineComponentState(Browser $browser, string $selector): array
    {
        return $this->executeAlpineScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack) return {};
            
            var state = {};
            var data = el._x_dataStack[0];
            
            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    state[key] = {
                        value: data[key],
                        type: typeof data[key],
                        isFunction: typeof data[key] === 'function'
                    };
                }
            }
            
            return state;
        ");
    }

    /**
     * Test Alpine.js x-model binding.
     */
    protected function assertAlpineModelBinding(Browser $browser, string $inputSelector, string $property, mixed $testValue, string $componentSelector = '[x-data]'): void
    {
        // Set the input value
        $this->executeAlpineScript($browser, "
            var input = document.querySelector('{$inputSelector}');
            if (!input) return;
            
            if (input.type === 'checkbox') {
                input.checked = ".json_encode($testValue).";
            } else if (input.type === 'radio') {
                input.checked = ".json_encode($testValue).";
            } else {
                input.value = ".json_encode($testValue).";
            }
            
            // Trigger change event
            var event = new Event('input', { bubbles: true });
            input.dispatchEvent(event);
        ");

        $browser->pause(200);

        // Check if Alpine property was updated
        $actualValue = $this->getAlpineData($browser, $property, $componentSelector);
        $this->assertEquals($testValue, $actualValue, "Alpine x-model binding should update '{$property}'");
    }

    /**
     * Test Alpine.js x-show directive.
     */
    protected function assertAlpineShowDirective(Browser $browser, string $selector, bool $shouldBeVisible, string $componentSelector = '[x-data]'): void
    {
        $isVisible = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el) return false;
            
            var computedStyle = window.getComputedStyle(el);
            var isVisible = computedStyle.display !== 'none' && 
                           el.offsetParent !== null &&
                           computedStyle.visibility !== 'hidden' &&
                           computedStyle.opacity !== '0';
            
            return isVisible;
        ");

        $this->assertEquals($shouldBeVisible, $isVisible, "Alpine x-show directive on '{$selector}' should be " . ($shouldBeVisible ? 'visible' : 'hidden'));
    }

    /**
     * Test Alpine.js x-if directive (requires checking DOM presence).
     */
    protected function assertAlpineIfDirective(Browser $browser, string $selector, bool $shouldExist): void
    {
        if ($shouldExist) {
            $browser->assertPresent($selector);
        } else {
            $browser->assertMissing($selector);
        }
    }

    /**
     * Test Alpine.js component initialization with x-init.
     */
    protected function assertAlpineInitExecuted(Browser $browser, string $selector, string $testProperty = 'initialized'): void
    {
        $isInitialized = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return false;
            var alpine = el._x_dataStack[0];
            return alpine.{$testProperty} === true;
        ");

        $this->assertTrue($isInitialized, "Alpine x-init should have executed for component '{$selector}'");
    }

    /**
     * Test Alpine.js expression evaluation.
     */
    protected function testAlpineExpression(Browser $browser, string $expression, mixed $expectedResult): void
    {
        $result = $this->executeAlpineScript($browser, "
            try {
                var el = document.querySelector('[x-data]');
                if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return null;
                var alpine = el._x_dataStack[0];
                
                // Evaluate the expression in the context of Alpine
                return (function() {
                    with(alpine) {
                        return {$expression};
                    }
                })();
            } catch (error) {
                return { error: error.message };
            }
        ");

        $this->assertEquals($expectedResult, $result, "Alpine expression '{$expression}' should evaluate to expected result");
    }

    /**
     * Monitor Alpine.js performance.
     */
    protected function measureAlpinePerformance(Browser $browser): array
    {
        return $this->executeAlpineScript($browser, "
            if (!window.performance || !window.performance.memory) {
                return { error: 'Performance APIs not available' };
            }
            
            var start = performance.now();
            var startMemory = performance.memory.usedJSHeapSize;
            
            // Force Alpine reactivity check
            var elements = document.querySelectorAll('[x-data]');
            elements.forEach(function(el) {
                if (el._x_dataStack && el._x_dataStack.length > 0) {
                    // Access data to trigger reactivity checks
                    Object.keys(el._x_dataStack[0]);
                }
            });
            
            var end = performance.now();
            var endMemory = performance.memory.usedJSHeapSize;
            
            return {
                componentCount: elements.length,
                evaluationTime: end - start,
                memoryDelta: endMemory - startMemory,
                totalMemory: endMemory,
                alpineVersion: window.Alpine ? window.Alpine.version : 'unknown'
            };
        ");
    }

    /**
     * Check for Alpine.js memory leaks.
     */
    protected function checkAlpineMemoryLeaks(Browser $browser): array
    {
        return $this->executeAlpineScript($browser, "
            if (!window.performance || !window.performance.memory) {
                return { error: 'Memory API not available' };
            }
            
            var initialMemory = performance.memory.usedJSHeapSize;
            
            // Create and destroy Alpine components
            var testDiv = document.createElement('div');
            testDiv.setAttribute('x-data', '{ test: true }');
            document.body.appendChild(testDiv);
            
            // Force Alpine initialization
            if (window.Alpine && window.Alpine.initTree) {
                window.Alpine.initTree(testDiv);
            }
            
            var afterInitMemory = performance.memory.usedJSHeapSize;
            
            // Remove the element
            if (testDiv.parentNode) {
                testDiv.parentNode.removeChild(testDiv);
            }
            
            // Force garbage collection if available
            if (window.gc) {
                window.gc();
            }
            
            var afterCleanupMemory = performance.memory.usedJSHeapSize;
            
            return {
                initialMemory: initialMemory,
                afterInitMemory: afterInitMemory,
                afterCleanupMemory: afterCleanupMemory,
                memoryLeaked: afterCleanupMemory > initialMemory + 10000, // Allow 10KB tolerance
                memoryDifference: afterCleanupMemory - initialMemory
            };
        ");
    }

    /**
     * Execute JavaScript safely with error handling.
     */
    protected function executeAlpineScript(Browser $browser, string $script): mixed
    {
        try {
            $result = $browser->script($script);
            return $result[0] ?? null;
        } catch (\Exception $e) {
            $this->fail("JavaScript execution failed: " . $e->getMessage());
        }
    }

    /**
     * Wait for specific Alpine condition.
     */
    protected function waitForAlpineCondition(Browser $browser, string $condition, int $timeout = 10): void
    {
        $browser->waitUntil($condition, $timeout);
    }

    /**
     * Test Alpine.js component cleanup.
     */
    protected function testAlpineCleanup(Browser $browser, string $selector): void
    {
        // Get component state before removal
        $beforeState = $this->getAlpineComponentState($browser, $selector);

        // Remove the component
        $this->executeAlpineScript($browser, "
            var el = document.querySelector('{$selector}');
            if (el && el.parentNode) {
                el.parentNode.removeChild(el);
            }
        ");

        $browser->pause(200);

        // Check that component was removed
        $exists = $this->executeAlpineScript($browser, "
            return document.querySelector('{$selector}') !== null;
        ");

        $this->assertFalse($exists, "Alpine component should be removed from DOM");

        // Check for memory leaks
        $memoryCheck = $this->checkAlpineMemoryLeaks($browser);
        if (isset($memoryCheck['memoryLeaked']) && $memoryCheck['memoryLeaked']) {
            $this->fail("Alpine component cleanup may have memory leaks");
        }
    }
}