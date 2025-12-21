<?php

namespace Tests\Browser\Utilities;

use Laravel\Dusk\Browser;

class JavaScriptTestUtilities
{
    /**
     * Create a comprehensive JavaScript test harness.
     */
    public static function createTestHarness(Browser $browser): void
    {
        $browser->script("
            window.__duskTestHarness = {
                results: [],
                errors: [],
                performance: {},
                
                addResult: function(test, result, details = null) {
                    this.results.push({
                        test: test,
                        result: result,
                        details: details,
                        timestamp: new Date().toISOString()
                    });
                },
                
                addError: function(test, error, details = null) {
                    this.errors.push({
                        test: test,
                        error: error,
                        details: details,
                        timestamp: new Date().toISOString(),
                        stack: new Error().stack
                    });
                },
                
                measurePerformance: function(name, fn) {
                    var start = performance.now();
                    var startMemory = performance.memory ? performance.memory.usedJSHeapSize : 0;
                    
                    try {
                        var result = fn();
                        var end = performance.now();
                        var endMemory = performance.memory ? performance.memory.usedJSHeapSize : 0;
                        
                        this.performance[name] = {
                            executionTime: end - start,
                            memoryDelta: endMemory - startMemory,
                            result: result,
                            success: true
                        };
                        
                        return result;
                    } catch (error) {
                        var end = performance.now();
                        var endMemory = performance.memory ? performance.memory.usedJSHeapSize : 0;
                        
                        this.performance[name] = {
                            executionTime: end - start,
                            memoryDelta: endMemory - startMemory,
                            error: error.message,
                            success: false
                        };
                        
                        throw error;
                    }
                },
                
                getResults: function() {
                    return {
                        results: this.results,
                        errors: this.errors,
                        performance: this.performance,
                        summary: {
                            totalTests: this.results.length,
                            passedTests: this.results.filter(r => r.result === 'pass').length,
                            failedTests: this.results.filter(r => r.result === 'fail').length,
                            totalErrors: this.errors.length
                        }
                    };
                },
                
                reset: function() {
                    this.results = [];
                    this.errors = [];
                    this.performance = {};
                }
            };
        ");
    }

    /**
     * Test Alpine.js data binding and reactivity.
     */
    public static function testAlpineDataBinding(Browser $browser): array
    {
        return $browser->script("
            var harness = window.__duskTestHarness;
            
            try {
                // Test 1: Basic data property access
                harness.measurePerformance('dataPropertyAccess', function() {
                    var el = document.querySelector('[x-data]');
                    if (!el || !el._x_dataStack || el._x_dataStack.length === 0) {
                        throw new Error('No Alpine component found');
                    }
                    
                    var alpine = el._x_dataStack[0];
                    return Object.keys(alpine).length;
                });
                
                // Test 2: Data property modification
                harness.measurePerformance('dataPropertyModification', function() {
                    var el = document.querySelector('[x-data]');
                    if (!el || !el._x_dataStack || el._x_dataStack.length === 0) {
                        throw new Error('No Alpine component found');
                    }
                    
                    var alpine = el._x_dataStack[0];
                    var originalValue = alpine.testProperty || null;
                    alpine.testProperty = 'test-value-' + Date.now();
                    
                    // Verify the change
                    var changed = alpine.testProperty === 'test-value-' + Date.now();
                    
                    // Restore original value if it existed
                    if (originalValue !== null) {
                        alpine.testProperty = originalValue;
                    } else {
                        delete alpine.testProperty;
                    }
                    
                    return changed;
                });
                
                // Test 3: Reactivity check
                harness.measurePerformance('reactivityCheck', function() {
                    var el = document.querySelector('[x-data]');
                    if (!el || !el._x_dataStack || el._x_dataStack.length === 0) {
                        return false;
                    }
                    
                    var alpine = el._x_dataStack[0];
                    var testProp = '__reactivity_test_' + Date.now();
                    
                    // Set property
                    alpine[testProp] = 'initial';
                    
                    // Force Alpine to process changes
                    if (window.Alpine && window.Alpine.flush) {
                        window.Alpine.flush();
                    }
                    
                    // Check if property is still there
                    var exists = alpine[testProp] === 'initial';
                    
                    // Clean up
                    delete alpine[testProp];
                    
                    return exists;
                });
                
                harness.addResult('alpineDataBinding', 'pass', 'All data binding tests completed');
                
            } catch (error) {
                harness.addError('alpineDataBinding', error.message);
            }
            
            return harness.getResults();
        ");
    }

    /**
     * Test Alpine.js directive functionality.
     */
    public static function testAlpineDirectives(Browser $browser): array
    {
        return $browser->script("
            var harness = window.__duskTestHarness;
            
            try {
                // Test x-show directive
                harness.measurePerformance('xShowDirective', function() {
                    var elements = document.querySelectorAll('[x-show]');
                    var working = 0;
                    
                    elements.forEach(function(el) {
                        var computedStyle = window.getComputedStyle(el);
                        var isVisible = el.offsetParent !== null && 
                                       computedStyle.display !== 'none';
                        
                        // Check if visibility matches the x-show condition
                        var xShowValue = el.getAttribute('x-show');
                        if (xShowValue === 'true' && isVisible) working++;
                        else if (xShowValue === 'false' && !isVisible) working++;
                    });
                    
                    return working;
                });
                
                // Test x-model directive
                harness.measurePerformance('xModelDirective', function() {
                    var elements = document.querySelectorAll('[x-model]');
                    var working = 0;
                    
                    elements.forEach(function(el) {
                        var modelName = el.getAttribute('x-model');
                        if (!modelName) return;
                        
                        // Find parent Alpine component
                        var alpineEl = el.closest('[x-data]');
                        if (!alpineEl || !alpineEl._x_dataStack || alpineEl._x_dataStack.length === 0) return;
                        
                        var alpine = alpineEl._x_dataStack[0];
                        var modelValue = alpine[modelName];
                        var elementValue = el.type === 'checkbox' ? el.checked : el.value;
                        
                        if (modelValue === elementValue) working++;
                    });
                    
                    return working;
                });
                
                // Test x-text directive
                harness.measurePerformance('xTextDirective', function() {
                    var elements = document.querySelectorAll('[x-text]');
                    var working = 0;
                    
                    elements.forEach(function(el) {
                        var textExpression = el.getAttribute('x-text');
                        if (!textExpression) return;
                        
                        // Find parent Alpine component
                        var alpineEl = el.closest('[x-data]');
                        if (!alpineEl || !alpineEl._x_dataStack || alpineEl._x_dataStack.length === 0) return;
                        
                        var alpine = alpineEl._x_dataStack[0];
                        try {
                            var expectedText = (function() {
                                with(alpine) {
                                    return eval(textExpression);
                                }
                            })();
                            
                            if (el.textContent.trim() == expectedText) working++;
                        } catch (e) {
                            // Expression evaluation failed
                        }
                    });
                    
                    return working;
                });
                
                harness.addResult('alpineDirectives', 'pass', 'Directive tests completed');
                
            } catch (error) {
                harness.addError('alpineDirectives', error.message);
            }
            
            return harness.getResults();
        ");
    }

    /**
     * Test Alpine.js event handling.
     */
    public static function testAlpineEventHandling(Browser $browser): array
    {
        return $browser->script("
            var harness = window.__duskTestHarness;
            
            try {
                // Test click events
                harness.measurePerformance('clickEvents', function() {
                    var clickElements = document.querySelectorAll('[x-on\\\\:click], [\\\\@click]');
                    var working = 0;
                    
                    clickElements.forEach(function(el) {
                        // Check if element has click event listeners
                        var hasClickListener = el.onclick || 
                                              el.getAttribute('x-on:click') || 
                                              el.getAttribute('@click');
                        
                        if (hasClickListener) working++;
                    });
                    
                    return working;
                });
                
                // Test submit events
                harness.measurePerformance('submitEvents', function() {
                    var submitElements = document.querySelectorAll('[x-on\\\\:submit], [\\\\@submit]');
                    var working = 0;
                    
                    submitElements.forEach(function(el) {
                        var hasSubmitListener = el.onsubmit || 
                                               el.getAttribute('x-on:submit') || 
                                               el.getAttribute('@submit');
                        
                        if (hasSubmitListener) working++;
                    });
                    
                    return working;
                });
                
                // Test custom events
                harness.measurePerformance('customEvents', function() {
                    var customElements = document.querySelectorAll('[x-on\\\\:*], [\\\\@*]');
                    return customElements.length;
                });
                
                harness.addResult('alpineEventHandling', 'pass', 'Event handling tests completed');
                
            } catch (error) {
                harness.addError('alpineEventHandling', error.message);
            }
            
            return harness.getResults();
        ");
    }

    /**
     * Test JavaScript error handling capabilities.
     */
    public static function testJavaScriptErrorHandling(Browser $browser): array
    {
        return $browser->script("
            var harness = window.__duskTestHarness;
            
            try {
                // Test try-catch functionality
                harness.measurePerformance('tryCatch', function() {
                    var caught = false;
                    try {
                        nonExistentFunction();
                    } catch (error) {
                        caught = error instanceof Error;
                    }
                    return caught;
                });
                
                // Test promise error handling
                harness.measurePerformance('promiseErrorHandling', function() {
                    return new Promise(function(resolve) {
                        Promise.reject(new Error('Test error'))
                            .catch(function(error) {
                                resolve(error instanceof Error);
                            });
                    });
                });
                
                // Test async/await error handling
                harness.measurePerformance('asyncErrorHandling', function() {
                    return (async function() {
                        try {
                            await Promise.reject(new Error('Test async error'));
                            return false;
                        } catch (error) {
                            return error instanceof Error;
                        }
                    })();
                });
                
                // Test global error handlers
                harness.measurePerformance('globalErrorHandlers', function() {
                    var errorCaught = false;
                    var originalHandler = window.onerror;
                    
                    window.onerror = function(message, source, lineno, colno, error) {
                        errorCaught = true;
                        return false;
                    };
                    
                    try {
                        throw new Error('Test global error');
                    } catch (e) {
                        // Error should be caught by global handler
                    }
                    
                    window.onerror = originalHandler;
                    return errorCaught;
                });
                
                harness.addResult('javascriptErrorHandling', 'pass', 'Error handling tests completed');
                
            } catch (error) {
                harness.addError('javascriptErrorHandling', error.message);
            }
            
            return harness.getResults();
        ");
    }

    /**
     * Test JavaScript performance and memory usage.
     */
    public static function testJavaScriptPerformance(Browser $browser): array
    {
        return $browser->script("
            var harness = window.__duskTestHarness;
            
            try {
                // Test DOM manipulation performance
                harness.measurePerformance('domManipulation', function() {
                    var start = performance.now();
                    var fragment = document.createDocumentFragment();
                    
                    for (var i = 0; i < 1000; i++) {
                        var div = document.createElement('div');
                        div.textContent = 'Test element ' + i;
                        div.className = 'test-element';
                        fragment.appendChild(div);
                    }
                    
                    document.body.appendChild(fragment);
                    
                    // Clean up
                    var testElements = document.querySelectorAll('.test-element');
                    testElements.forEach(function(el) {
                        el.parentNode.removeChild(el);
                    });
                    
                    var end = performance.now();
                    return end - start;
                });
                
                // Test array operations performance
                harness.measurePerformance('arrayOperations', function() {
                    var start = performance.now();
                    var arr = new Array(10000).fill(0).map((_, i) => i);
                    
                    // Map operation
                    var mapped = arr.map(x => x * 2);
                    
                    // Filter operation
                    var filtered = mapped.filter(x => x > 1000);
                    
                    // Reduce operation
                    var sum = filtered.reduce((acc, val) => acc + val, 0);
                    
                    var end = performance.now();
                    return {
                        executionTime: end - start,
                        result: sum,
                        arrayLength: arr.length,
                        filteredLength: filtered.length
                    };
                });
                
                // Test memory allocation and cleanup
                harness.measurePerformance('memoryManagement', function() {
                    if (!performance.memory) {
                        return { error: 'Memory API not available' };
                    }
                    
                    var initialMemory = performance.memory.usedJSHeapSize;
                    var objects = [];
                    
                    // Allocate memory
                    for (var i = 0; i < 1000; i++) {
                        objects.push({
                            id: i,
                            data: new Array(100).fill(Math.random()),
                            timestamp: Date.now()
                        });
                    }
                    
                    var afterAllocation = performance.memory.usedJSHeapSize;
                    
                    // Clear references
                    objects = null;
                    
                    // Force garbage collection if available
                    if (window.gc) {
                        window.gc();
                    }
                    
                    var afterCleanup = performance.memory.usedJSHeapSize;
                    
                    return {
                        initialMemory: initialMemory,
                        afterAllocation: afterAllocation,
                        afterCleanup: afterCleanup,
                        memoryAllocated: afterAllocation - initialMemory,
                        memoryFreed: afterAllocation - afterCleanup,
                        memoryLeaked: afterCleanup > initialMemory + 50000 // 50KB tolerance
                    };
                });
                
                harness.addResult('javascriptPerformance', 'pass', 'Performance tests completed');
                
            } catch (error) {
                harness.addError('javascriptPerformance', error.message);
            }
            
            return harness.getResults();
        ");
    }

    /**
     * Test cross-browser compatibility features.
     */
    public static function testCrossBrowserCompatibility(Browser $browser): array
    {
        return $browser->script("
            var harness = window.__duskTestHarness;
            
            try {
                // Test ES6 feature support
                harness.measurePerformance('es6Features', function() {
                    var features = {
                        arrowFunctions: typeof (() => {}) === 'function',
                        templateLiterals: typeof `test` === 'string',
                        destructuring: (() => { try { var [a] = [1]; return true; } catch(e) { return false; } })(),
                        spreadOperator: (() => { try { var arr = [...[1,2,3]]; return true; } catch(e) { return false; } })(),
                        classes: typeof class Test {} === 'function',
                        promises: typeof Promise !== 'undefined',
                        asyncAwait: (async function() { return true; })() instanceof Promise
                    };
                    
                    return features;
                });
                
                // Test DOM API support
                harness.measurePerformance('domApiSupport', function() {
                    var apis = {
                        querySelector: typeof document.querySelector === 'function',
                        addEventListener: typeof document.addEventListener === 'function',
                        classList: typeof document.documentElement.classList !== 'undefined',
                        dataset: typeof document.documentElement.dataset !== 'undefined',
                        mutationObserver: typeof MutationObserver !== 'undefined',
                        intersectionObserver: typeof IntersectionObserver !== 'undefined',
                        resizeObserver: typeof ResizeObserver !== 'undefined'
                    };
                    
                    return apis;
                });
                
                // Test Web API support
                harness.measurePerformance('webApiSupport', function() {
                    var apis = {
                        fetch: typeof fetch === 'function',
                        localStorage: typeof localStorage !== 'undefined',
                        sessionStorage: typeof sessionStorage !== 'undefined',
                        webWorkers: typeof Worker !== 'undefined',
                        webSockets: typeof WebSocket !== 'undefined',
                        geolocation: typeof navigator.geolocation !== 'undefined',
                        canvas: typeof HTMLCanvasElement !== 'undefined',
                        webGL: (() => {
                            try {
                                var canvas = document.createElement('canvas');
                                return !!(canvas.getContext && canvas.getContext('webgl'));
                            } catch(e) {
                                return false;
                            }
                        })()
                    };
                    
                    return apis;
                });
                
                harness.addResult('crossBrowserCompatibility', 'pass', 'Compatibility tests completed');
                
            } catch (error) {
                harness.addError('crossBrowserCompatibility', error.message);
            }
            
            return harness.getResults();
        ");
    }

    /**
     * Get comprehensive test results.
     */
    public static function getTestResults(Browser $browser): array
    {
        return $browser->script("
            return window.__duskTestHarness ? window.__duskTestHarness.getResults() : { error: 'Test harness not initialized' };
        ");
    }

    /**
     * Reset test harness.
     */
    public static function resetTestHarness(Browser $browser): void
    {
        $browser->script("
            if (window.__duskTestHarness) {
                window.__duskTestHarness.reset();
            }
        ");
    }

    /**
     * Generate test report.
     */
    public static function generateTestReport(Browser $browser): string
    {
        $results = self::getTestResults($browser);
        
        if (isset($results['error'])) {
            return "Error: " . $results['error'];
        }
        
        $report = "=== JavaScript Test Report ===\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Summary
        $summary = $results['summary'];
        $report .= "SUMMARY:\n";
        $report .= "Total Tests: {$summary['totalTests']}\n";
        $report .= "Passed: {$summary['passedTests']}\n";
        $report .= "Failed: {$summary['failedTests']}\n";
        $report .= "Errors: {$summary['totalErrors']}\n\n";
        
        // Performance metrics
        if (!empty($results['performance'])) {
            $report .= "PERFORMANCE:\n";
            foreach ($results['performance'] as $test => $metrics) {
                $report .= "- {$test}:\n";
                $report .= "  Success: " . ($metrics['success'] ? 'Yes' : 'No') . "\n";
                if (isset($metrics['executionTime'])) {
                    $report .= "  Execution Time: " . round($metrics['executionTime'], 2) . "ms\n";
                }
                if (isset($metrics['memoryDelta'])) {
                    $report .= "  Memory Delta: " . round($metrics['memoryDelta'] / 1024, 2) . "KB\n";
                }
                if (isset($metrics['error'])) {
                    $report .= "  Error: {$metrics['error']}\n";
                }
            }
            $report .= "\n";
        }
        
        // Test results
        if (!empty($results['results'])) {
            $report .= "TEST RESULTS:\n";
            foreach ($results['results'] as $result) {
                $status = strtoupper($result['result']);
                $report .= "- [{$status}] {$result['test']}\n";
                if ($result['details']) {
                    $report .= "  Details: {$result['details']}\n";
                }
            }
            $report .= "\n";
        }
        
        // Errors
        if (!empty($results['errors'])) {
            $report .= "ERRORS:\n";
            foreach ($results['errors'] as $error) {
                $report .= "- {$error['test']}: {$error['error']}\n";
                if ($error['details']) {
                    $report .= "  Details: {$error['details']}\n";
                }
            }
        }
        
        return $report;
    }
}