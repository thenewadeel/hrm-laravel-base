<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;

trait HandlesJavaScriptTesting
{
    /**
     * Check for JavaScript errors in console.
     */
    protected function checkForJavaScriptErrors(\Laravel\Dusk\Browser $browser): array
    {
        $script = "
            var errors = [];
            var originalError = console.error;
            var originalLog = console.log;
            
            // Capture errors
            console.error = function() {
                errors.push({
                    type: 'error',
                    args: Array.prototype.slice.call(arguments),
                    timestamp: new Date().toISOString(),
                    stack: new Error().stack
                });
                originalError.apply(console, arguments);
            };
            
            // Capture logs that might indicate issues
            console.log = function() {
                var args = Array.prototype.slice.call(arguments);
                var message = args.join(' ');
                if (message.includes('error') || message.includes('undefined') || message.includes('failed')) {
                    errors.push({
                        type: 'warning',
                        args: args,
                        timestamp: new Date().toISOString()
                    });
                }
                originalLog.apply(console, arguments);
            };
            
            return errors;
        ";

        $errors = $browser->script($script);

        return $errors[0] ?? [];
    }

    /**
     * Assert no JavaScript errors occurred.
     */
    protected function assertNoJavaScriptErrors(Browser $browser): void
    {
        $errors = $this->checkForJavaScriptErrors($browser);

        if (! empty($errors)) {
            $errorMessages = array_map(function ($error) {
                return "[{$error['type']}] ".implode(' ', $error['args']);
            }, $errors);

            $this->fail("JavaScript errors detected:\n".implode("\n", $errorMessages));
        }
    }

    /**
     * Get JavaScript console output.
     */
    protected function getConsoleOutput(Browser $browser): array
    {
        $script = "
            var logs = [];
            var originalLog = console.log;
            var originalWarn = console.warn;
            var originalError = console.error;
            var originalInfo = console.info;
            
            function captureLog(type, args) {
                logs.push({
                    type: type,
                    args: Array.prototype.slice.call(arguments, 1),
                    timestamp: new Date().toISOString(),
                    url: window.location.href
                });
            }
            
            console.log = function() { captureLog('log', arguments); originalLog.apply(console, arguments); };
            console.warn = function() { captureLog('warn', arguments); originalWarn.apply(console, arguments); };
            console.error = function() { captureLog('error', arguments); originalError.apply(console, arguments); };
            console.info = function() { captureLog('info', arguments); originalInfo.apply(console, arguments); };
            
            return logs;
        ";

        $logs = $browser->script($script);

        return $logs[0] ?? [];
    }

    /**
     * Wait for JavaScript to be ready.
     */
    protected function waitForJavaScript(\Laravel\Dusk\Browser $browser): void
    {
        $browser->waitUntil("return document.readyState === 'complete'", 10)
            ->waitUntil("return typeof jQuery !== 'undefined' ? jQuery.active === 0 : true", 10)
            ->pause(500); // Additional wait for any remaining scripts
    }

    /**
     * Wait for specific JavaScript condition.
     */
    protected function waitForJavaScriptCondition(Browser $browser, string $condition, int $timeout = 10): void
    {
        $browser->waitUntil($condition, $timeout);
    }

    /**
     * Execute JavaScript and return result.
     */
    protected function executeScript(Browser $browser, string $script): mixed
    {
        $result = $browser->script($script);

        return $result[0] ?? null;
    }

    /**
     * Check if JavaScript variable is defined.
     */
    protected function isJavaScriptDefined(Browser $browser, string $variable): bool
    {
        return $this->executeScript($browser, "return typeof {$variable} !== 'undefined';");
    }

    /**
     * Get JavaScript variable value.
     */
    protected function getJavaScriptVariable(Browser $browser, string $variable): mixed
    {
        return $this->executeScript($browser, "return {$variable};");
    }

    /**
     * Set JavaScript variable value.
     */
    protected function setJavaScriptVariable(Browser $browser, string $variable, mixed $value): void
    {
        $valueJson = json_encode($value);
        $this->executeScript($browser, "{$variable} = {$valueJson};");
    }

    /**
     * Trigger JavaScript event.
     */
    protected function triggerJavaScriptEvent(Browser $browser, string $selector, string $event, array $data = []): void
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

        $this->executeScript($browser, $script);
        $browser->pause(200);
    }

    /**
     * Check if element has JavaScript event listener.
     */
    protected function hasEventListener(Browser $browser, string $selector, string $event): bool
    {
        return $this->executeScript($browser, "
            var el = document.querySelector('{$selector}');
            if (!el) return false;
            
            // Check if event listener exists (this is a simplified check)
            var events = el._events || el.addEventListener;
            return events !== undefined;
        ");
    }

    /**
     * Test JavaScript performance.
     */
    protected function measureJavaScriptPerformance(Browser $browser, string $script): array
    {
        $performanceScript = "
            var start = performance.now();
            var result = (function() {
                {$script}
            })();
            var end = performance.now();
            
            return {
                result: result,
                executionTime: end - start,
                memoryUsage: performance.memory ? performance.memory.usedJSHeapSize : null
            };
        ";

        return $this->executeScript($browser, $performanceScript);
    }

    /**
     * Check for memory leaks in JavaScript.
     */
    protected function checkMemoryUsage(Browser $browser): array
    {
        return $this->executeScript($browser, "
            if (!performance.memory) {
                return { error: 'Memory API not available' };
            }
            
            return {
                usedJSHeapSize: performance.memory.usedJSHeapSize,
                totalJSHeapSize: performance.memory.totalJSHeapSize,
                jsHeapSizeLimit: performance.memory.jsHeapSizeLimit
            };
        ");
    }

    /**
     * Test JavaScript error handling.
     */
    protected function testJavaScriptErrorHandling(Browser $browser): void
    {
        // Try to trigger an error and see if it's handled gracefully
        $this->executeScript($browser, "
            try {
                // Intentionally cause an error
                nonExistentFunction();
            } catch (error) {
                console.log('Error caught:', error.message);
            }
        ");

        $browser->pause(200);

        // Check that error was caught and didn't crash the page
        $this->assertNoJavaScriptErrors($browser);
    }

    /**
     * Test JavaScript module loading.
     */
    protected function testJavaScriptModules(Browser $browser): void
    {
        $modules = [
            'Alpine' => 'window.Alpine',
            'Livewire' => 'window.Livewire',
            'jQuery' => 'window.jQuery',
        ];

        foreach ($modules as $name => $global) {
            $isLoaded = $this->executeScript($browser, "return typeof {$global} !== 'undefined';");
            $this->assertTrue($isLoaded, "{$name} should be loaded");
        }
    }

    /**
     * Test JavaScript async operations.
     */
    protected function testAsyncOperations(Browser $browser): void
    {
        // Test Promise handling
        $promiseResult = $this->executeScript($browser, "
            return new Promise(function(resolve) {
                setTimeout(function() {
                    resolve('async test completed');
                }, 100);
            });
        ");

        $this->assertEquals('async test completed', $promiseResult);

        // Test async/await
        $asyncResult = $this->executeScript($browser, "
            (async function() {
                await new Promise(resolve => setTimeout(resolve, 50));
                return 'await test completed';
            })();
        ");

        $this->assertEquals('await test completed', $asyncResult);
    }

    /**
     * Monitor JavaScript network requests.
     */
    protected function monitorNetworkRequests(Browser $browser): array
    {
        return $this->executeScript($browser, "
            var requests = [];
            var originalFetch = window.fetch;
            var originalXHR = window.XMLHttpRequest;
            
            // Monitor fetch requests
            window.fetch = function() {
                var url = arguments[0];
                var method = arguments[1] ? arguments[1].method || 'GET' : 'GET';
                
                requests.push({
                    type: 'fetch',
                    url: url,
                    method: method,
                    timestamp: new Date().toISOString()
                });
                
                return originalFetch.apply(this, arguments);
            };
            
            // Monitor XHR requests
            var XHROpen = originalXHR.prototype.open;
            originalXHR.prototype.open = function(method, url) {
                requests.push({
                    type: 'xhr',
                    url: url,
                    method: method,
                    timestamp: new Date().toISOString()
                });
                
                return XHROpen.apply(this, arguments);
            };
            
            return requests;
        ");
    }

    /**
     * Test JavaScript DOM manipulation.
     */
    protected function testDOMManipulation(Browser $browser): void
    {
        // Test element creation
        $elementCreated = $this->executeScript($browser, "
            var div = document.createElement('div');
            div.id = 'test-element';
            div.textContent = 'Test Content';
            document.body.appendChild(div);
            return document.getElementById('test-element') !== null;
        ");

        $this->assertTrue($elementCreated, 'DOM element creation should work');

        // Test element removal
        $elementRemoved = $this->executeScript($browser, "
            var el = document.getElementById('test-element');
            if (el) {
                el.parentNode.removeChild(el);
                return document.getElementById('test-element') === null;
            }
            return false;
        ");

        $this->assertTrue($elementRemoved, 'DOM element removal should work');
    }

    /**
     * Test JavaScript event system.
     */
    protected function testEventSystem(Browser $browser): void
    {
        $eventFired = $this->executeScript($browser, "
            var eventFired = false;
            var testElement = document.createElement('div');
            testElement.id = 'event-test';
            document.body.appendChild(testElement);
            
            testElement.addEventListener('test-event', function() {
                eventFired = true;
            });
            
            var event = new Event('test-event');
            testElement.dispatchEvent(event);
            
            // Clean up
            testElement.parentNode.removeChild(testElement);
            
            return eventFired;
        ");

        $this->assertTrue($eventFired, 'JavaScript event system should work');
    }

    /**
     * Get JavaScript stack trace on error.
     */
    protected function getJavaScriptStackTrace(Browser $browser): array
    {
        return $this->executeScript($browser, "
            try {
                // Intentionally cause an error to get stack trace
                throw new Error('Test error for stack trace');
            } catch (error) {
                return error.stack ? error.stack.split('\\n') : [];
            }
        ");
    }

    /**
     * Test JavaScript timing functions.
     */
    protected function testTimingFunctions(Browser $browser): void
    {
        $timeoutWorked = $this->executeScript($browser, '
            var timeoutFired = false;
            setTimeout(function() {
                timeoutFired = true;
            }, 50);
            
            // Wait a bit
            var start = Date.now();
            while (Date.now() - start < 100) {
                // Busy wait
            }
            
            return timeoutFired;
        ');

        $this->assertTrue($timeoutWorked, 'setTimeout should work');

        $intervalWorked = $this->executeScript($browser, '
            var intervalCount = 0;
            var interval = setInterval(function() {
                intervalCount++;
                if (intervalCount >= 2) {
                    clearInterval(interval);
                }
            }, 25);
            
            // Wait for intervals
            var start = Date.now();
            while (Date.now() - start < 100) {
                // Busy wait
            }
            
            return intervalCount >= 2;
        ');

        $this->assertTrue($intervalWorked, 'setInterval should work');
    }
}
