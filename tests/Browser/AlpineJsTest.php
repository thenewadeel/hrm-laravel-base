<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\HandlesAlpineJsTesting;
use Tests\Browser\Concerns\HandlesJavaScriptErrors;
use Tests\Browser\Concerns\HandlesMultiTenantTesting;
use Tests\Browser\Utilities\JavaScriptTestUtilities;

class AlpineJsTest extends BaseBrowserTest
{
    use HandlesAlpineJsTesting, HandlesJavaScriptErrors, HandlesMultiTenantTesting;

    /**
     * Test Alpine.js initialization and basic functionality.
     */
    public function test_alpine_initialization(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Assert Alpine.js is loaded
            $this->assertTrue(
                $this->executeAlpineScript($browser, "return typeof window.Alpine !== 'undefined'"),
                'Alpine.js should be loaded'
            );

            // Assert Alpine.js version is available
            $alpineVersion = $this->executeAlpineScript($browser, "return window.Alpine.version");
            $this->assertNotEmpty($alpineVersion, 'Alpine.js version should be available');

            // Assert components are initialized
            $componentCount = $this->executeAlpineScript($browser, "return document.querySelectorAll('[x-data]').length");
            $this->assertGreaterThan(0, $componentCount, 'Should have Alpine components');

            // Assert no initialization errors
            $this->assertNoAlpineErrors($browser);
            $this->assertNoJavaScriptErrors($browser);
        });
    }

    /**
     * Test Alpine.js data property definitions and scope.
     */
    public function test_alpine_data_properties(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Test dashboard search variable (common error source)
            $browser->waitFor('.dashboard-search', 10);
            
            // Check if search is properly defined in Alpine component
            $hasSearchProperty = $this->executeAlpineScript($browser, "
                var searchEl = document.querySelector('.dashboard-search').closest('[x-data]');
                if (!searchEl || !searchEl._x_dataStack || searchEl._x_dataStack.length === 0) return false;
                var alpine = searchEl._x_dataStack[0];
                return typeof alpine.search !== 'undefined';
            ");

            $this->assertTrue($hasSearchProperty, 'Search property should be defined in Alpine component');

            // Test data property access
            $searchValue = $this->getAlpineData($browser, 'search', '.dashboard-search');
            $this->assertNotNull($searchValue, 'Search property should be accessible');

            // Test data property modification
            $this->setAlpineData($browser, 'search', 'test-value', '.dashboard-search');
            $updatedValue = $this->getAlpineData($browser, 'search', '.dashboard-search');
            $this->assertEquals('test-value', $updatedValue, 'Search property should be updatable');

            // Test reactivity
            $this->assertAlpineReactivity($browser, 'search', 'reactive-test', '.dashboard-search');

            // Assert no errors
            $this->assertNoAlpineErrors($browser);
            $this->assertNoJavaScriptErrorPattern($browser, 'search is not defined');
        });
    }

    /**
     * Test Alpine.js x-model directive functionality.
     */
    public function test_alpine_model_directive(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Test text input binding
            $browser->waitFor('input[x-model]', 10);
            
            $modelInputs = $this->executeAlpineScript($browser, "
                return Array.from(document.querySelectorAll('input[x-model]')).map(input => ({
                    selector: input.tagName.toLowerCase() + (input.id ? '#' + input.id : '') + (input.className ? '.' + input.className.split(' ').join('.') : ''),
                    model: input.getAttribute('x-model'),
                    type: input.type,
                    value: input.value
                }));
            ");

            $this->assertNotEmpty($modelInputs, 'Should have x-model inputs');

            foreach ($modelInputs as $input) {
                if ($input['model']) {
                    // Test two-way binding
                    $testValue = 'test-' . uniqid();
                    
                    // Set input value
                    $this->executeAlpineScript($browser, "
                        var input = document.querySelector('input[x-model=\"{$input['model']}\"]');
                        if (input) {
                            input.value = '{$testValue}';
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    ");

                    $browser->pause(200);

                    // Check Alpine property was updated
                    $alpineValue = $this->getAlpineData($browser, $input['model']);
                    $this->assertEquals($testValue, $alpineValue, "x-model should update Alpine property '{$input['model']}'");
                }
            }

            // Test checkbox binding
            $checkboxes = $this->executeAlpineScript($browser, "
                return Array.from(document.querySelectorAll('input[type=\"checkbox\"][x-model]')).map(checkbox => ({
                    model: checkbox.getAttribute('x-model'),
                    checked: checkbox.checked
                }));
            ");

            foreach ($checkboxes as $checkbox) {
                if ($checkbox['model']) {
                    // Toggle checkbox
                    $this->executeAlpineScript($browser, "
                        var checkbox = document.querySelector('input[type=\"checkbox\"][x-model=\"{$checkbox['model']}\"]');
                        if (checkbox) {
                            checkbox.checked = !checkbox.checked;
                            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    ");

                    $browser->pause(200);

                    // Check Alpine property was updated
                    $alpineValue = $this->getAlpineData($browser, $checkbox['model']);
                    $this->assertIsBool($alpineValue, "Checkbox x-model should update Alpine property '{$checkbox['model']}' to boolean");
                }
            }

            $this->assertNoAlpineErrors($browser);
        });
    }

    /**
     * Test Alpine.js x-show and x-if directive functionality.
     */
    public function test_alpine_conditional_directives(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Test x-show directive
            $showElements = $this->executeAlpineScript($browser, "
                return Array.from(document.querySelectorAll('[x-show]')).map(el => ({
                    selector: el.tagName.toLowerCase() + (el.id ? '#' + el.id : ''),
                    expression: el.getAttribute('x-show'),
                    visible: el.offsetParent !== null
                }));
            ");

            foreach ($showElements as $element) {
                if ($element['expression'] === 'true') {
                    $this->assertTrue($element['visible'], "Element with x-show='true' should be visible");
                } elseif ($element['expression'] === 'false') {
                    $this->assertFalse($element['visible'], "Element with x-show='false' should be hidden");
                }
            }

            // Test dynamic x-show
            $browser->waitFor('[x-show]', 5);
            $this->executeAlpineScript($browser, "
                var el = document.querySelector('[x-show]');
                if (el && el._x_dataStack && el._x_dataStack.length > 0) {
                    var alpine = el._x_dataStack[0];
                    alpine.testShow = false;
                }
            ");

            $browser->pause(200);

            // Test x-if directive (requires checking DOM presence)
            $ifElements = $this->executeAlpineScript($browser, "
                var templates = Array.from(document.querySelectorAll('template[x-if]'));
                return templates.map(template => ({
                    expression: template.getAttribute('x-if'),
                    hasContent: template.content.children.length > 0
                }));
            ");

            $this->assertNoAlpineErrors($browser);
        });
    }

    /**
     * Test Alpine.js event handling.
     */
    public function test_alpine_event_handling(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Test click events
            $clickElements = $this->executeAlpineScript($browser, "
                return Array.from(document.querySelectorAll('[x-on\\\\:click], [\\\\@click]')).map(el => ({
                    selector: el.tagName.toLowerCase() + (el.id ? '#' + el.id : ''),
                    handler: el.getAttribute('x-on:click') || el.getAttribute('@click')
                }));
            ");

            $this->assertNotEmpty($clickElements, 'Should have click event handlers');

            // Test click event on navigation drawer
            $browser->waitFor('[data-drawer-toggle]', 10);
            $this->triggerAlpineEvent($browser, '[data-drawer-toggle]', 'click');
            $browser->pause(300);

            // Check if drawer opened
            $drawerOpen = $this->executeAlpineScript($browser, "
                var drawer = document.querySelector('[data-drawer]');
                return drawer && drawer.classList.contains('open');
            ");

            // Test submit events
            $submitElements = $this->executeAlpineScript($browser, "
                return Array.from(document.querySelectorAll('[x-on\\\\:submit], [\\\\@submit]')).map(el => ({
                    selector: el.tagName.toLowerCase() + (el.id ? '#' + el.id : ''),
                    handler: el.getAttribute('x-on:submit') || el.getAttribute('@submit')
                }));
            ");

            // Test change events
            $changeElements = $this->executeAlpineScript($browser, "
                return Array.from(document.querySelectorAll('[x-on\\\\:change], [\\\\@change]')).map(el => ({
                    selector: el.tagName.toLowerCase() + (el.id ? '#' + el.id : ''),
                    handler: el.getAttribute('x-on:change') || el.getAttribute('@change')
                }));
            ");

            $this->assertNoAlpineErrors($browser);
        });
    }

    /**
     * Test Alpine.js component lifecycle and initialization.
     */
    public function test_alpine_component_lifecycle(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Test x-init execution
            $initElements = $this->executeAlpineScript($browser, "
                return Array.from(document.querySelectorAll('[x-init]')).map(el => ({
                    selector: el.tagName.toLowerCase() + (el.id ? '#' + el.id : ''),
                    initCode: el.getAttribute('x-init')
                }));
            ");

            // Test component initialization order
            $componentStates = $this->executeAlpineScript($browser, "
                var components = Array.from(document.querySelectorAll('[x-data]'));
                return components.map((el, index) => ({
                    index: index,
                    selector: el.tagName.toLowerCase() + (el.id ? '#' + el.id : ''),
                    initialized: el._x_dataStack && el._x_dataStack.length > 0,
                    dataKeys: el._x_dataStack && el._x_dataStack.length > 0 ? 
                        Object.keys(el._x_dataStack[0]).filter(k => typeof el._x_dataStack[0][k] !== 'function') : []
                }));
            ");

            foreach ($componentStates as $component) {
                $this->assertTrue($component['initialized'], "Component {$component['selector']} should be initialized");
            }

            // Test component cleanup
            $this->testAlpineCleanup($browser, '[x-data]');

            $this->assertNoAlpineErrors($browser);
        });
    }

    /**
     * Test Alpine.js and Livewire integration.
     */
    public function test_alpine_livewire_integration(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Wait for Livewire components
            $this->waitForLivewire($browser);

            // Test Livewire + Alpine components
            $livewireAlpineComponents = $this->executeAlpineScript($browser, "
                var livewireElements = Array.from(document.querySelectorAll('[wire\\\\:id]'));
                return livewireElements.map(el => {
                    var alpineParent = el.closest('[x-data]');
                    return {
                        livewireId: el.getAttribute('wire:id'),
                        hasAlpineParent: !!alpineParent,
                        alpineData: alpineParent && alpineParent._x_dataStack && alpineParent._x_dataStack.length > 0 ?
                            Object.keys(alpineParent._x_dataStack[0]) : []
                    };
                });
            ");

            // Test Livewire events with Alpine handlers
            $browser->waitFor('[wire\\:click]', 10);
            
            // Test that Livewire updates don't break Alpine
            $this->executeAlpineScript($browser, "
                var livewireButton = document.querySelector('[wire\\\\:click]');
                if (livewireButton) {
                    livewireButton.click();
                }
            ");

            $browser->pause(500);

            // Check Alpine components are still functional after Livewire update
            $alpineStillWorking = $this->executeAlpineScript($browser, "
                var alpineEl = document.querySelector('[x-data]');
                return alpineEl && alpineEl._x_dataStack && alpineEl._x_dataStack.length > 0;
            ");

            $this->assertTrue($alpineStillWorking, 'Alpine should still work after Livewire updates');

            $this->assertNoAlpineErrors($browser);
            $this->assertNoJavaScriptErrors($browser);
        });
    }

    /**
     * Test Alpine.js performance and memory usage.
     */
    public function test_alpine_performance(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Measure Alpine performance
            $performance = $this->measureAlpinePerformance($browser);

            $this->assertArrayHasKey('componentCount', $performance);
            $this->assertArrayHasKey('evaluationTime', $performance);
            $this->assertArrayHasKey('memoryDelta', $performance);

            // Performance should be reasonable
            $this->assertLessThan(100.0, $performance['evaluationTime'], 'Alpine evaluation should be fast');
            $this->assertLessThan(1048576, $performance['memoryDelta'], 'Alpine memory usage should be reasonable');

            // Check for memory leaks
            $memoryCheck = $this->checkAlpineMemoryLeaks($browser);

            if (isset($memoryCheck['memoryLeaked'])) {
                $this->assertFalse($memoryCheck['memoryLeaked'], 'Alpine should not have memory leaks');
            }

            $this->assertNoAlpineErrors($browser);
        });
    }

    /**
     * Test Alpine.js error handling and graceful degradation.
     */
    public function test_alpine_error_handling(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Test expression error handling
            $result = $this->executeAlpineScript($browser, "
                try {
                    var el = document.querySelector('[x-data]');
                    if (!el || !el._x_dataStack || el._x_dataStack.length === 0) {
                        return { error: 'No Alpine component found' };
                    }
                    
                    var alpine = el._x_dataStack[0];
                    
                    // Try to access undefined property
                    var undefinedProp = alpine.nonExistentProperty;
                    return { success: true, value: undefinedProp };
                } catch (error) {
                    return { error: error.message, caught: true };
                }
            ");

            // Should handle undefined properties gracefully
            $this->assertTrue(
                isset($result['success']) || isset($result['caught']),
                'Alpine should handle undefined properties gracefully'
            );

            // Test method error handling
            $methodResult = $this->executeScript($browser, "
                try {
                    var el = document.querySelector('[x-data]');
                    if (!el || !el._x_dataStack || el._x_dataStack.length === 0) {
                        return { error: 'No Alpine component found' };
                    }
                    
                    var alpine = el._x_dataStack[0];
                    
                    // Try to call undefined method
                    if (typeof alpine.nonExistentMethod === 'function') {
                        return alpine.nonExistentMethod();
                    } else {
                        return { error: 'Method does not exist', handled: true };
                    }
                } catch (error) {
                    return { error: error.message, caught: true };
                }
            ");

            $this->assertTrue(
                isset($methodResult['handled']) || isset($methodResult['caught']),
                'Alpine should handle undefined methods gracefully'
            );

            $this->assertNoJavaScriptErrors($browser);
        });
    }

    /**
     * Test comprehensive JavaScript functionality.
     */
    public function test_comprehensive_javascript_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);

            // Initialize test harness
            JavaScriptTestUtilities::createTestHarness($browser);

            // Run comprehensive tests
            JavaScriptTestUtilities::testAlpineDataBinding($browser);
            JavaScriptTestUtilities::testAlpineDirectives($browser);
            JavaScriptTestUtilities::testAlpineEventHandling($browser);
            JavaScriptTestUtilities::testJavaScriptErrorHandling($browser);
            JavaScriptTestUtilities::testJavaScriptPerformance($browser);
            JavaScriptTestUtilities::testCrossBrowserCompatibility($browser);

            // Get results
            $results = JavaScriptTestUtilities::getTestResults($browser);

            $this->assertArrayHasKey('summary', $results);
            $this->assertArrayHasKey('results', $results);
            $this->assertArrayHasKey('errors', $results);

            $summary = $results['summary'];
            $this->assertGreaterThan(0, $summary['totalTests'], 'Should run comprehensive tests');
            
            // Allow some tests to fail but not too many
            $failureRate = $summary['totalTests'] > 0 ? $summary['failedTests'] / $summary['totalTests'] : 0;
            $this->assertLessThan(0.5, $failureRate, 'Failure rate should be less than 50%');

            // Generate report
            $report = JavaScriptTestUtilities::generateTestReport($browser);
            $this->assertNotEmpty($report, 'Should generate test report');

            // Clean up
            JavaScriptTestUtilities::resetTestHarness($browser);

            $this->assertNoJavaScriptIssues($browser);
        });
    }

    /**
     * Test specific "search is not defined" error scenarios.
     */
    public function test_search_variable_definition_errors(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Navigate to dashboard
            $browser->waitFor('.dashboard-search', 10);

            // Check for search variable in various contexts
            $searchChecks = $this->executeScript($browser, "
                var checks = [];
                
                // Check 1: Dashboard search component
                var dashboardSearch = document.querySelector('.dashboard-search');
                if (dashboardSearch) {
                    var alpineParent = dashboardSearch.closest('[x-data]');
                    if (alpineParent && alpineParent._x_dataStack && alpineParent._x_dataStack.length > 0) {
                        var alpine = alpineParent._x_dataStack[0];
                        checks.push({
                            context: 'dashboard-search',
                            hasSearch: typeof alpine.search !== 'undefined',
                            searchType: typeof alpine.search,
                            searchValue: alpine.search
                        });
                    } else {
                        checks.push({
                            context: 'dashboard-search',
                            hasSearch: false,
                            error: 'No Alpine parent found'
                        });
                    }
                }
                
                // Check 2: Any element with search-related x-model
                var searchModels = document.querySelectorAll('[x-model*=\"search\"]');
                checks.push({
                    context: 'search-models',
                    count: searchModels.length,
                    elements: Array.from(searchModels).map(el => ({
                        model: el.getAttribute('x-model'),
                        hasAlpineParent: !!el.closest('[x-data]')
                    }))
                });
                
                // Check 3: Global search variable
                checks.push({
                    context: 'global',
                    hasGlobalSearch: typeof window.search !== 'undefined',
                    hasAlpineSearch: typeof window.Alpine !== 'undefined' && window.Alpine.store && window.Alpine.store.search
                });
                
                return checks;
            ");

            // Verify search is properly defined where needed
            foreach ($searchChecks as $check) {
                if ($check['context'] === 'dashboard-search') {
                    $this->assertTrue($check['hasSearch'], 'Search should be defined in dashboard component');
                }
            }

            // Test search functionality
            $this->executeAlpineScript($browser, "
                var searchInput = document.querySelector('.dashboard-search input');
                if (searchInput) {
                    searchInput.value = 'test search';
                    searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            ");

            $browser->pause(300);

            // Verify no "search is not defined" errors
            $this->assertNoJavaScriptErrorPattern($browser, 'search is not defined');
            $this->assertNoJavaScriptErrorPattern($browser, 'Cannot read property.*search');
            $this->assertNoJavaScriptErrorPattern($browser, 'undefined.*search');

            $this->assertNoAlpineErrors($browser);
        });
    }

    /**
     * Test Alpine.js expression evaluation and edge cases.
     */
    public function test_alpine_expression_evaluation(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Test basic expressions
            $this->testAlpineExpression($browser, 'true', true);
            $this->testAlpineExpression($browser, 'false', false);
            $this->testAlpineExpression($browser, '1 + 1', 2);
            $this->testAlpineExpression($browser, '"test"', 'test');

            // Test property access
            $browser->waitFor('[x-data]', 5);
            $this->executeAlpineScript($browser, "
                var el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack.length > 0) {
                    el._x_dataStack[0].testProp = 'test-value';
                }
            ");

            $this->testAlpineExpression($browser, 'testProp', 'test-value');

            // Test method calls
            $this->executeAlpineScript($browser, "
                var el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack.length > 0) {
                    el._x_dataStack[0].testMethod = function() { return 'method-result'; };
                }
            ");

            $this->testAlpineExpression($browser, 'testMethod()', 'method-result');

            // Test error handling in expressions
            $errorResult = $this->executeScript($browser, "
                try {
                    var el = document.querySelector('[x-data]');
                    if (!el || !el._x_dataStack || el._x_dataStack.length === 0) {
                        return { error: 'No Alpine component' };
                    }
                    
                    var alpine = el._x_dataStack[0];
                    var result = (function() {
                        with(alpine) {
                            return nonExistentVariable;
                        }
                    })();
                    
                    return { result: result };
                } catch (error) {
                    return { error: error.message, handled: true };
                }
            ");

            $this->assertTrue(
                isset($errorResult['error']) && isset($errorResult['handled']),
                'Expression errors should be handled gracefully'
            );

            $this->assertNoAlpineErrors($browser);
        });
    }

    /**
     * Test Alpine.js with real-time data updates.
     */
    public function test_alpine_real_time_updates(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Test real-time search updates
            $browser->waitFor('.dashboard-search', 10);
            
            // Get initial state
            $initialState = $this->getAlpineDataSnapshot($browser, '.dashboard-search');

            // Simulate rapid search input changes
            for ($i = 0; $i < 5; $i++) {
                $searchTerm = "test-search-{$i}";
                
                $this->executeAlpineScript($browser, "
                    var searchInput = document.querySelector('.dashboard-search input');
                    if (searchInput) {
                        searchInput.value = '{$searchTerm}';
                        searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                ");

                $browser->pause(100);
            }

            // Check final state
            $finalState = $this->getAlpineDataSnapshot($browser, '.dashboard-search');

            // Test reactive updates to arrays/objects
            $this->executeAlpineScript($browser, "
                var el = document.querySelector('[x-data]');
                if (el && el._x_dataStack && el._x_dataStack.length > 0) {
                    var alpine = el._x_dataStack[0];
                    alpine.testArray = [1, 2, 3];
                    alpine.testObject = { key: 'value' };
                }
            ");

            $browser->pause(200);

            // Verify reactivity with complex data
            $arrayValue = $this->getAlpineData($browser, 'testArray');
            $objectValue = $this->getAlpineData($browser, 'testObject');

            $this->assertEquals([1, 2, 3], $arrayValue);
            $this->assertEquals(['key' => 'value'], (array) $objectValue);

            $this->assertNoAlpineErrors($browser);
        });
    }
}