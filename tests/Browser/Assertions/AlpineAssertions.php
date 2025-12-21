<?php

namespace Tests\Browser\Assertions;

use Laravel\Dusk\Browser;

trait AlpineAssertions
{
    /**
     * Assert Alpine.js component exists and is initialized.
     */
    public function assertAlpineComponentExists(Browser $browser, string $selector): self
    {
        $browser->assertPresent($selector)
            ->waitUntil("return document.querySelector('{$selector}')._x_dataStack && document.querySelector('{$selector}')._x_dataStack.length > 0", 10);

        return $this;
    }

    /**
     * Assert Alpine.js component does not exist.
     */
    public function assertAlpineComponentMissing(Browser $browser, string $selector): self
    {
        $browser->assertMissing($selector);

        return $this;
    }

    /**
     * Assert Alpine.js data property has expected value.
     */
    public function assertAlpineData(Browser $browser, string $property, mixed $expectedValue, string $selector = '[x-data]'): self
    {
        $actualValue = $this->getAlpineData($browser, $property, $selector);
        
        if ($actualValue !== $expectedValue) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine data property '{$property}' expected to be " . var_export($expectedValue, true) . 
                " but got " . var_export($actualValue, true)
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js data property does not have specific value.
     */
    public function assertAlpineDataNot(Browser $browser, string $property, mixed $unexpectedValue, string $selector = '[x-data]'): self
    {
        $actualValue = $this->getAlpineData($browser, $property, $selector);
        
        if ($actualValue === $unexpectedValue) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine data property '{$property}' should not be " . var_export($unexpectedValue, true)
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js data property is truthy.
     */
    public function assertAlpineDataTruthy(Browser $browser, string $property, string $selector = '[x-data]'): self
    {
        $actualValue = $this->getAlpineData($browser, $property, $selector);
        
        if (!$actualValue) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine data property '{$property}' should be truthy but is " . var_export($actualValue, true)
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js data property is falsy.
     */
    public function assertAlpineDataFalsy(Browser $browser, string $property, string $selector = '[x-data]'): self
    {
        $actualValue = $this->getAlpineData($browser, $property, $selector);
        
        if ($actualValue) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine data property '{$property}' should be falsy but is " . var_export($actualValue, true)
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js method exists.
     */
    public function assertAlpineMethodExists(Browser $browser, string $method, string $selector = '[x-data]'): self
    {
        $exists = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return false;
            var alpine = el._x_dataStack[0];
            return typeof alpine.{$method} === 'function';
        ");

        if (!$exists) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine method '{$method}' should exist in component '{$selector}'"
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js method does not exist.
     */
    public function assertAlpineMethodMissing(Browser $browser, string $method, string $selector = '[x-data]'): self
    {
        $exists = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return false;
            var alpine = el._x_dataStack[0];
            return typeof alpine.{$method} === 'function';
        ");

        if ($exists) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine method '{$method}' should not exist in component '{$selector}'"
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js x-show directive is working (element is visible).
     */
    public function assertAlpineShown(Browser $browser, string $selector): self
    {
        $isVisible = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el) return false;
            
            var computedStyle = window.getComputedStyle(el);
            return el.offsetParent !== null && 
                   computedStyle.display !== 'none' && 
                   computedStyle.visibility !== 'hidden' &&
                   computedStyle.opacity !== '0';
        ");

        if (!$isVisible) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Element '{$selector}' should be visible (x-show=true)"
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js x-show directive is working (element is hidden).
     */
    public function assertAlpineHidden(Browser $browser, string $selector): self
    {
        $isHidden = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el) return true; // Missing element is considered hidden
            
            var computedStyle = window.getComputedStyle(el);
            return el.offsetParent === null || 
                   computedStyle.display === 'none' || 
                   computedStyle.visibility === 'hidden' ||
                   computedStyle.opacity === '0';
        ");

        if (!$isHidden) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Element '{$selector}' should be hidden (x-show=false)"
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js x-if directive rendered element.
     */
    public function assertAlpineIfExists(Browser $browser, string $selector): self
    {
        $browser->assertPresent($selector);

        return $this;
    }

    /**
     * Assert Alpine.js x-if directive did not render element.
     */
    public function assertAlpineIfMissing(Browser $browser, string $selector): self
    {
        $browser->assertMissing($selector);

        return $this;
    }

    /**
     * Assert Alpine.js x-model binding is working.
     */
    public function assertAlpineModel(Browser $browser, string $inputSelector, string $property, mixed $expectedValue, string $componentSelector = '[x-data]'): self
    {
        // Check input value
        $inputValue = $this->executeScript($browser, "
            var input = document.querySelector('{$inputSelector}');
            if (!input) return null;
            return input.type === 'checkbox' ? input.checked : input.value;
        ");

        if ($inputValue !== $expectedValue) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Input '{$inputSelector}' value should be " . var_export($expectedValue, true) . 
                " but is " . var_export($inputValue, true)
            );
        }

        // Check Alpine property
        $alpineValue = $this->getAlpineData($browser, $property, $componentSelector);
        
        if ($alpineValue !== $expectedValue) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine property '{$property}' should be " . var_export($expectedValue, true) . 
                " but is " . var_export($alpineValue, true)
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js x-text directive content.
     */
    public function assertAlpineText(Browser $browser, string $selector, string $expectedText): self
    {
        $actualText = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            return el ? el.textContent.trim() : null;
        ");

        if ($actualText !== $expectedText) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Element '{$selector}' text should be '{$expectedText}' but is '{$actualText}'"
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js x-html directive content.
     */
    public function assertAlpineHtml(Browser $browser, string $selector, string $expectedHtml): self
    {
        $actualHtml = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            return el ? el.innerHTML.trim() : null;
        ");

        if ($actualHtml !== $expectedHtml) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Element '{$selector}' HTML should be '{$expectedHtml}' but is '{$actualHtml}'"
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js x-bind:class directive.
     */
    public function assertAlpineClass(Browser $browser, string $selector, string $expectedClass): self
    {
        $actualClass = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            return el ? el.className : null;
        ");

        if ($actualClass !== $expectedClass) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Element '{$selector}' class should be '{$expectedClass}' but is '{$actualClass}'"
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js x-bind:style directive.
     */
    public function assertAlpineStyle(Browser $browser, string $selector, string $expectedStyle): self
    {
        $actualStyle = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            return el ? el.getAttribute('style') : null;
        ");

        if ($actualStyle !== $expectedStyle) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Element '{$selector}' style should be '{$expectedStyle}' but is '{$actualStyle}'"
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js component has been initialized with x-init.
     */
    public function assertAlpineInitialized(Browser $browser, string $selector, string $testProperty = 'initialized'): self
    {
        $isInitialized = $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return false;
            var alpine = el._x_dataStack[0];
            return alpine.{$testProperty} === true;
        ");

        if (!$isInitialized) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine component '{$selector}' should be initialized (x-init executed)"
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js expression evaluates to expected result.
     */
    public function assertAlpineExpression(Browser $browser, string $expression, mixed $expectedResult, string $selector = '[x-data]'): self
    {
        $result = $this->executeScript($browser, "
            try {
                var el = document.querySelector('{$selector}');
                if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return null;
                var alpine = el._x_dataStack[0];
                
                return (function() {
                    with(alpine) {
                        return {$expression};
                    }
                })();
            } catch (error) {
                return { error: error.message };
            }
        ");

        if ($result !== $expectedResult) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine expression '{$expression}' should evaluate to " . var_export($expectedResult, true) . 
                " but got " . var_export($result, true)
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js reactivity is working.
     */
    public function assertAlpineReactivity(Browser $browser, string $property, mixed $newValue, string $selector = '[x-data]'): self
    {
        // Get initial value
        $initialValue = $this->getAlpineData($browser, $property, $selector);

        // Set new value
        $this->setAlpineData($browser, $property, $newValue, $selector);

        // Wait for reactivity
        $browser->pause(200);

        // Check value was set
        $currentValue = $this->getAlpineData($browser, $property, $selector);
        
        if ($currentValue !== $newValue) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine reactivity should update '{$property}' to " . var_export($newValue, true) . 
                " but it is " . var_export($currentValue, true)
            );
        }

        return $this;
    }

    /**
     * Assert no Alpine.js errors occurred.
     */
    public function assertNoAlpineErrors(Browser $browser): self
    {
        $errors = $this->checkAlpineErrors($browser);
        
        if (!empty($errors)) {
            $errorMessages = array_map(function ($error) {
                return "[{$error['type']}] {$error['message']}";
            }, $errors);

            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine.js errors detected:\n" . implode("\n", $errorMessages)
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js performance is within acceptable limits.
     */
    public function assertAlpinePerformance(Browser $browser, float $maxEvaluationTime = 100.0, int $maxMemoryDelta = 1048576): self
    {
        $performance = $this->measureAlpinePerformance($browser);
        
        if (isset($performance['error'])) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Cannot measure Alpine performance: {$performance['error']}"
            );
        }

        if ($performance['evaluationTime'] > $maxEvaluationTime) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine evaluation time {$performance['evaluationTime']}ms exceeds maximum {$maxEvaluationTime}ms"
            );
        }

        if ($performance['memoryDelta'] > $maxMemoryDelta) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine memory delta {$performance['memoryDelta']} bytes exceeds maximum {$maxMemoryDelta} bytes"
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js component count is as expected.
     */
    public function assertAlpineComponentCount(Browser $browser, int $expectedCount): self
    {
        $actualCount = $this->executeScript($browser, "
            return document.querySelectorAll('[x-data]').length;
        ");

        if ($actualCount !== $expectedCount) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Expected {$expectedCount} Alpine components but found {$actualCount}"
            );
        }

        return $this;
    }

    /**
     * Assert Alpine.js version is loaded.
     */
    public function assertAlpineLoaded(Browser $browser): self
    {
        $isLoaded = $this->executeScript($browser, "
            return typeof window.Alpine !== 'undefined' && window.Alpine.version;
        ");

        if (!$isLoaded) {
            throw new \PHPUnit\Framework\ExpectationFailedException(
                "Alpine.js should be loaded and initialized"
            );
        }

        return $this;
    }

    // Helper methods for assertions

    private function getAlpineData(Browser $browser, string $property, string $selector): mixed
    {
        return $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return null;
            var alpine = el._x_dataStack[0];
            return alpine.{$property};
        ");
    }

    private function setAlpineData(Browser $browser, string $property, mixed $value, string $selector): void
    {
        $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return;
            var alpine = el._x_dataStack[0];
            alpine.{$property} = ".json_encode($value).';
        ');
    }

    private function executeScript(Browser $browser, string $script): mixed
    {
        $result = $browser->script($script);
        return $result[0] ?? null;
    }

    private function checkAlpineErrors(Browser $browser): array
    {
        return $this->executeScript($browser, "
            var errors = [];
            var originalError = console.error;
            
            console.error = function() {
                var args = Array.prototype.slice.call(arguments);
                var message = args.join(' ');
                if (message.includes('Alpine') || 
                    message.includes('x-data') || 
                    message.includes('x-init') ||
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
            
            return errors;
        ");
    }

    private function measureAlpinePerformance(Browser $browser): array
    {
        return $this->executeScript($browser, "
            if (!window.performance) {
                return { error: 'Performance API not available' };
            }
            
            var start = performance.now();
            var startMemory = performance.memory ? performance.memory.usedJSHeapSize : 0;
            
            // Force Alpine reactivity check
            var elements = document.querySelectorAll('[x-data]');
            elements.forEach(function(el) {
                if (el._x_dataStack && el._x_dataStack.length > 0) {
                    Object.keys(el._x_dataStack[0]);
                }
            });
            
            var end = performance.now();
            var endMemory = performance.memory ? performance.memory.usedJSHeapSize : 0;
            
            return {
                evaluationTime: end - start,
                memoryDelta: endMemory - startMemory,
                componentCount: elements.length
            };
        ");
    }
}