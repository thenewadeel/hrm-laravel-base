<?php

namespace Tests\Browser\Concerns;

use Laravel\Dusk\Browser;

trait HandlesJavaScriptErrors
{
    /**
     * Setup comprehensive JavaScript error monitoring.
     */
    protected function setupJavaScriptErrorMonitoring(Browser $browser): void
    {
        $this->executeScript($browser, "
            // Clear any existing error handlers
            if (window.__duskErrorMonitor) {
                window.__duskErrorMonitor.errors = [];
            } else {
                window.__duskErrorMonitor = {
                    errors: [],
                    warnings: [],
                    logs: [],
                    networkErrors: [],
                    unhandledRejections: []
                };
            }
            
            // Store original methods
            var originalError = console.error;
            var originalWarn = console.warn;
            var originalLog = console.log;
            var originalOnerror = window.onerror;
            var originalOnunhandledrejection = window.onunhandledrejection;
            
            // Enhanced error capture
            console.error = function() {
                var args = Array.prototype.slice.call(arguments);
                var message = args.join(' ');
                
                window.__duskErrorMonitor.errors.push({
                    type: 'console.error',
                    message: message,
                    args: args,
                    timestamp: new Date().toISOString(),
                    stack: new Error().stack,
                    url: window.location.href,
                    userAgent: navigator.userAgent
                });
                
                originalError.apply(console, arguments);
            };
            
            console.warn = function() {
                var args = Array.prototype.slice.call(arguments);
                var message = args.join(' ');
                
                window.__duskErrorMonitor.warnings.push({
                    type: 'console.warn',
                    message: message,
                    args: args,
                    timestamp: new Date().toISOString(),
                    url: window.location.href
                });
                
                originalWarn.apply(console, arguments);
            };
            
            console.log = function() {
                var args = Array.prototype.slice.call(arguments);
                var message = args.join(' ');
                
                // Only capture logs that might indicate issues
                if (message.includes('error') || 
                    message.includes('undefined') || 
                    message.includes('failed') ||
                    message.includes('null') ||
                    message.includes('NaN')) {
                    
                    window.__duskErrorMonitor.logs.push({
                        type: 'console.log',
                        message: message,
                        args: args,
                        timestamp: new Date().toISOString(),
                        url: window.location.href
                    });
                }
                
                originalLog.apply(console, arguments);
            };
            
            // Global error handler
            window.onerror = function(message, source, lineno, colno, error) {
                window.__duskErrorMonitor.errors.push({
                    type: 'window.onerror',
                    message: message,
                    source: source,
                    line: lineno,
                    column: colno,
                    error: error ? error.stack : null,
                    timestamp: new Date().toISOString(),
                    url: window.location.href
                });
                
                return false; // Don't prevent default error handling
            };
            
            // Unhandled promise rejection handler
            window.onunhandledrejection = function(event) {
                window.__duskErrorMonitor.unhandledRejections.push({
                    type: 'unhandledrejection',
                    reason: event.reason ? (event.reason.stack || event.reason.toString()) : 'Unknown reason',
                    timestamp: new Date().toISOString(),
                    url: window.location.href
                });
            };
            
            // Network error monitoring
            var originalFetch = window.fetch;
            window.fetch = function() {
                var url = arguments[0];
                var options = arguments[1] || {};
                var method = options.method || 'GET';
                
                return originalFetch.apply(this, arguments)
                    .catch(function(error) {
                        window.__duskErrorMonitor.networkErrors.push({
                            type: 'fetch.error',
                            url: url,
                            method: method,
                            error: error.toString(),
                            timestamp: new Date().toISOString(),
                            url: window.location.href
                        });
                        throw error;
                    });
            };
            
            // XMLHttpRequest error monitoring
            var originalXHROpen = XMLHttpRequest.prototype.open;
            var originalXHRSend = XMLHttpRequest.prototype.send;
            
            XMLHttpRequest.prototype.open = function(method, url) {
                this._duskUrl = url;
                this._duskMethod = method;
                return originalXHROpen.apply(this, arguments);
            };
            
            XMLHttpRequest.prototype.send = function() {
                var xhr = this;
                var originalOnError = this.onerror;
                var originalOnTimeout = this.ontimeout;
                
                this.onerror = function() {
                    window.__duskErrorMonitor.networkErrors.push({
                        type: 'xhr.error',
                        url: xhr._duskUrl,
                        method: xhr._duskMethod,
                        status: xhr.status,
                        timestamp: new Date().toISOString(),
                        url: window.location.href
                    });
                    
                    if (originalOnError) originalOnError.apply(this, arguments);
                };
                
                this.ontimeout = function() {
                    window.__duskErrorMonitor.networkErrors.push({
                        type: 'xhr.timeout',
                        url: xhr._duskUrl,
                        method: xhr._duskMethod,
                        timestamp: new Date().toISOString(),
                        url: window.location.href
                    });
                    
                    if (originalOnTimeout) originalOnTimeout.apply(this, arguments);
                };
                
                return originalXHRSend.apply(this, arguments);
            };
        ");
    }

    /**
     * Get all JavaScript errors.
     */
    protected function getJavaScriptErrors(Browser $browser): array
    {
        return $this->executeScript($browser, "
            return window.__duskErrorMonitor ? window.__duskErrorMonitor.errors : [];
        ");
    }

    /**
     * Get all JavaScript warnings.
     */
    protected function getJavaScriptWarnings(Browser $browser): array
    {
        return $this->executeScript($browser, "
            return window.__duskErrorMonitor ? window.__duskErrorMonitor.warnings : [];
        ");
    }

    /**
     * Get all network errors.
     */
    protected function getNetworkErrors(Browser $browser): array
    {
        return $this->executeScript($browser, "
            return window.__duskErrorMonitor ? window.__duskErrorMonitor.networkErrors : [];
        ");
    }

    /**
     * Get all unhandled promise rejections.
     */
    protected function getUnhandledRejections(Browser $browser): array
    {
        return $this->executeScript($browser, "
            return window.__duskErrorMonitor ? window.__duskErrorMonitor.unhandledRejections : [];
        ");
    }

    /**
     * Assert no JavaScript errors occurred.
     */
    protected function assertNoJavaScriptErrors(Browser $browser): void
    {
        $errors = $this->getJavaScriptErrors($browser);
        
        if (!empty($errors)) {
            $errorMessages = array_map(function ($error) {
                return "[{$error['type']}] {$error['message']} at {$error['source']}:{$error['line']}:{$error['column']}";
            }, $errors);

            $this->fail("JavaScript errors detected:\n".implode("\n", $errorMessages));
        }
    }

    /**
     * Assert no JavaScript warnings occurred.
     */
    protected function assertNoJavaScriptWarnings(Browser $browser): void
    {
        $warnings = $this->getJavaScriptWarnings($browser);
        
        if (!empty($warnings)) {
            $warningMessages = array_map(function ($warning) {
                return "[{$warning['type']}] {$warning['message']}";
            }, $warnings);

            $this->fail("JavaScript warnings detected:\n".implode("\n", $warningMessages));
        }
    }

    /**
     * Assert no network errors occurred.
     */
    protected function assertNoNetworkErrors(Browser $browser): void
    {
        $networkErrors = $this->getNetworkErrors($browser);
        
        if (!empty($networkErrors)) {
            $errorMessages = array_map(function ($error) {
                return "[{$error['type']}] {$error['method']} {$error['url']}: {$error['error']}";
            }, $networkErrors);

            $this->fail("Network errors detected:\n".implode("\n", $errorMessages));
        }
    }

    /**
     * Assert no unhandled promise rejections occurred.
     */
    protected function assertNoUnhandledRejections(Browser $browser): void
    {
        $rejections = $this->getUnhandledRejections($browser);
        
        if (!empty($rejections)) {
            $rejectionMessages = array_map(function ($rejection) {
                return "[{$rejection['type']}] {$rejection['reason']}";
            }, $rejections);

            $this->fail("Unhandled promise rejections detected:\n".implode("\n", $rejectionMessages));
        }
    }

    /**
     * Assert no JavaScript issues at all (errors, warnings, network errors, rejections).
     */
    protected function assertNoJavaScriptIssues(Browser $browser): void
    {
        $this->assertNoJavaScriptErrors($browser);
        $this->assertNoJavaScriptWarnings($browser);
        $this->assertNoNetworkErrors($browser);
        $this->assertNoUnhandledRejections($browser);
    }

    /**
     * Get specific JavaScript errors by type or message pattern.
     */
    protected function getJavaScriptErrorsByPattern(Browser $browser, string $pattern): array
    {
        $errors = $this->getJavaScriptErrors($browser);
        
        return array_filter($errors, function ($error) use ($pattern) {
            return preg_match('/' . preg_quote($pattern, '/') . '/i', $error['message']);
        });
    }

    /**
     * Assert specific JavaScript error pattern is not present.
     */
    protected function assertNoJavaScriptErrorPattern(Browser $browser, string $pattern): void
    {
        $errors = $this->getJavaScriptErrorsByPattern($browser, $pattern);
        
        if (!empty($errors)) {
            $errorMessages = array_map(function ($error) {
                return "[{$error['type']}] {$error['message']}";
            }, $errors);

            $this->fail("JavaScript errors matching pattern '{$pattern}' detected:\n".implode("\n", $errorMessages));
        }
    }

    /**
     * Test JavaScript error handling with try-catch.
     */
    protected function testJavaScriptErrorHandling(Browser $browser): void
    {
        $result = $this->executeScript($browser, "
            try {
                // Intentionally cause an error
                nonExistentFunction();
                return { success: false, message: 'Error should have been thrown' };
            } catch (error) {
                return { 
                    success: true, 
                    message: 'Error caught successfully',
                    errorName: error.name,
                    errorMessage: error.message,
                    errorStack: error.stack
                };
            }
        ");

        $this->assertTrue($result['success'], 'JavaScript error handling should work');
        $this->assertEquals('ReferenceError', $result['errorName'], 'Should catch ReferenceError');
    }

    /**
     * Test JavaScript promise error handling.
     */
    protected function testJavaScriptPromiseErrorHandling(Browser $browser): void
    {
        $result = $this->executeScript($browser, "
            return new Promise(function(resolve) {
                // Create a promise that rejects
                Promise.reject(new Error('Test promise rejection'))
                    .catch(function(error) {
                        resolve({
                            success: true,
                            message: 'Promise error caught',
                            errorMessage: error.message
                        });
                    })
                    .then(function() {
                        resolve({ success: false, message: 'Promise should have been caught' });
                    });
            });
        ");

        $this->assertTrue($result['success'], 'JavaScript promise error handling should work');
        $this->assertEquals('Test promise rejection', $result['errorMessage'], 'Should catch promise rejection');
    }

    /**
     * Test JavaScript async/await error handling.
     */
    protected function testJavaScriptAsyncErrorHandling(Browser $browser): void
    {
        $result = $this->executeScript($browser, "
            return (async function() {
                try {
                    await Promise.reject(new Error('Test async error'));
                    return { success: false, message: 'Async error should have been thrown' };
                } catch (error) {
                    return {
                        success: true,
                        message: 'Async error caught',
                        errorMessage: error.message
                    };
                }
            })();
        ");

        $this->assertTrue($result['success'], 'JavaScript async/await error handling should work');
        $this->assertEquals('Test async error', $result['errorMessage'], 'Should catch async error');
    }

    /**
     * Monitor JavaScript performance and detect potential issues.
     */
    protected function monitorJavaScriptPerformance(Browser $browser): array
    {
        return $this->executeScript($browser, "
            if (!window.performance || !window.performance.memory) {
                return { error: 'Performance APIs not available' };
            }
            
            var start = performance.now();
            var startMemory = performance.memory.usedJSHeapSize;
            
            // Monitor long-running tasks
            var longTasks = [];
            var observer = new PerformanceObserver(function(list) {
                list.getEntries().forEach(function(entry) {
                    if (entry.duration > 50) { // Tasks longer than 50ms
                        longTasks.push({
                            name: entry.name,
                            duration: entry.duration,
                            startTime: entry.startTime
                        });
                    }
                });
            });
            
            try {
                observer.observe({ entryTypes: ['longtask'] });
            } catch (e) {
                // longtask might not be supported
            }
            
            // Force some JavaScript execution
            var testArray = new Array(10000).fill(0).map((_, i) => i * i);
            var testResult = testArray.reduce((sum, val) => sum + val, 0);
            
            var end = performance.now();
            var endMemory = performance.memory.usedJSHeapSize;
            
            // Disconnect observer
            if (observer.disconnect) {
                observer.disconnect();
            }
            
            return {
                executionTime: end - start,
                memoryDelta: endMemory - startMemory,
                memoryUsed: endMemory,
                memoryLimit: performance.memory.jsHeapSizeLimit,
                longTasks: longTasks,
                testResult: testResult,
                performanceAPIs: {
                    memory: !!window.performance.memory,
                    observer: !!window.PerformanceObserver,
                    now: !!window.performance.now
                }
            };
        ");
    }

    /**
     * Test JavaScript memory leak detection.
     */
    protected function testJavaScriptMemoryLeakDetection(Browser $browser): array
    {
        return $this->executeScript($browser, "
            if (!window.performance || !window.performance.memory) {
                return { error: 'Memory API not available' };
            }
            
            var initialMemory = performance.memory.usedJSHeapSize;
            var objects = [];
            
            // Create objects that could potentially leak
            for (var i = 0; i < 1000; i++) {
                objects.push({
                    id: i,
                    data: new Array(100).fill(Math.random()),
                    callback: function() { return i; }
                });
            }
            
            var afterCreationMemory = performance.memory.usedJSHeapSize;
            
            // Clear references
            objects = null;
            
            // Force garbage collection if available
            if (window.gc) {
                window.gc();
            }
            
            // Wait a bit for GC
            setTimeout(function() {
                var afterCleanupMemory = performance.memory.usedJSHeapSize;
                
                return {
                    initialMemory: initialMemory,
                    afterCreationMemory: afterCreationMemory,
                    afterCleanupMemory: afterCleanupMemory,
                    memoryLeaked: afterCleanupMemory > initialMemory + 50000, // 50KB tolerance
                    memoryDifference: afterCleanupMemory - initialMemory,
                    objectsCreated: 1000
                };
            }, 100);
            
            return {
                initialMemory: initialMemory,
                afterCreationMemory: afterCreationMemory,
                message: 'Memory check in progress...'
            };
        ");
    }

    /**
     * Test JavaScript event system error handling.
     */
    protected function testJavaScriptEventErrorHandling(Browser $browser): void
    {
        $result = $this->executeScript($browser, "
            var eventErrors = [];
            var testElement = document.createElement('div');
            testElement.id = 'event-error-test';
            document.body.appendChild(testElement);
            
            // Add event listener that throws an error
            testElement.addEventListener('test-error', function() {
                throw new Error('Test event error');
            });
            
            // Add error handler to capture the error
            testElement.addEventListener('test-error', function(event) {
                // This should still execute even if previous listener threw error
                eventErrors.push('Error handled gracefully');
            });
            
            try {
                var event = new Event('test-error');
                testElement.dispatchEvent(event);
            } catch (e) {
                eventErrors.push('Event error caught: ' + e.message);
            }
            
            // Clean up
            if (testElement.parentNode) {
                testElement.parentNode.removeChild(testElement);
            }
            
            return {
                eventErrors: eventErrors,
                success: eventErrors.length > 0
            };
        ");

        $this->assertTrue($result['success'], 'JavaScript event error handling should work');
    }

    /**
     * Get JavaScript console summary.
     */
    protected function getJavaScriptConsoleSummary(Browser $browser): array
    {
        return $this->executeScript($browser, "
            if (!window.__duskErrorMonitor) {
                return { error: 'Error monitoring not setup' };
            }
            
            return {
                errors: window.__duskErrorMonitor.errors.length,
                warnings: window.__duskErrorMonitor.warnings.length,
                logs: window.__duskErrorMonitor.logs.length,
                networkErrors: window.__duskErrorMonitor.networkErrors.length,
                unhandledRejections: window.__duskErrorMonitor.unhandledRejections.length,
                totalIssues: window.__duskErrorMonitor.errors.length + 
                           window.__duskErrorMonitor.warnings.length + 
                           window.__duskErrorMonitor.networkErrors.length + 
                           window.__duskErrorMonitor.unhandledRejections.length
            };
        ");
    }

    /**
     * Clear JavaScript error monitoring.
     */
    protected function clearJavaScriptErrorMonitoring(Browser $browser): void
    {
        $this->executeScript($browser, "
            if (window.__duskErrorMonitor) {
                window.__duskErrorMonitor.errors = [];
                window.__duskErrorMonitor.warnings = [];
                window.__duskErrorMonitor.logs = [];
                window.__duskErrorMonitor.networkErrors = [];
                window.__duskErrorMonitor.unhandledRejections = [];
            }
        ");
    }

    /**
     * Execute JavaScript safely with error handling.
     */
    protected function executeScript(Browser $browser, string $script): mixed
    {
        try {
            $result = $browser->script($script);
            return $result[0] ?? null;
        } catch (\Exception $e) {
            $this->fail("JavaScript execution failed: " . $e->getMessage());
            return null;
        }
    }
}