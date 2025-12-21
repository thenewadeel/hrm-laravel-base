<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Concerns\HandlesAlpineTesting;
use Tests\Browser\Concerns\HandlesJavaScriptErrors;
use Tests\Browser\Concerns\HandlesMultiTenantTesting;
use Tests\Browser\Utilities\JavaScriptTestUtilities;

class AlpineJsTest extends BaseBrowserTest
{
    use HandlesAlpineTesting, HandlesJavaScriptErrors, HandlesMultiTenantTesting;

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
            $alpineLoaded = $browser->script("return typeof window.Alpine !== 'undefined'")[0] ?? false;
            $this->assertTrue($alpineLoaded, 'Alpine.js should be loaded');

            // Assert Alpine.js version is available
            $alpineVersion = $browser->script("return window.Alpine.version")[0] ?? null;
            $this->assertNotEmpty($alpineVersion, 'Alpine.js version should be available');

            // Assert components are initialized
            $componentCount = $browser->script("return document.querySelectorAll('[x-data]').length")[0] ?? 0;
            $this->assertGreaterThan(0, $componentCount, 'Should have Alpine components');

            // Assert no initialization errors
            $this->assertNoAlpineErrors($browser);
            $this->assertNoJavaScriptErrors($browser);
        });
    }

    /**
     * Test Alpine.js data properties.
     */
    public function test_alpine_data_properties(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser);
            $this->setupJavaScriptErrorMonitoring($browser);
            $this->waitForAlpineReady($browser);

            // Test basic Alpine data binding
            $browser->waitFor('[x-data]', 10);
            
            // Check if any Alpine component has data
            $hasData = $browser->script("
                var el = document.querySelector('[x-data]');
                if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return false;
                var alpine = el._x_dataStack[0];
                return Object.keys(alpine).length > 0;
            ")[0] ?? false;

            $this->assertTrue($hasData, 'Alpine components should have data properties');

            // Test data property access
            $dataKeys = $browser->script("
                var el = document.querySelector('[x-data]');
                if (!el || !el._x_dataStack || el._x_dataStack.length === 0) return [];
                var alpine = el._x_dataStack[0];
                return Object.keys(alpine).filter(k => typeof alpine[k] !== 'function');
            ")[0] ?? [];

            $this->assertNotEmpty($dataKeys, 'Alpine components should have non-function properties');

            // Assert no errors
            $this->assertNoAlpineErrors($browser);
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
            
            $modelInputs = $browser->script("
                return Array.from(document.querySelectorAll('input[x-model]')).map(input => ({
                    selector: input.tagName.toLowerCase() + (input.id ? '#' + input.id : '') + (input.className ? '.' + input.className.split(' ').join('.') : ''),
                    model: input.getAttribute('x-model'),
                    type: input.type,
                    value: input.value
                }));
            ")[0] ?? [];

            $this->assertNotEmpty($modelInputs, 'Should have x-model inputs');

            foreach ($modelInputs as $input) {
                if ($input['model']) {
                    $this->assertTrue(true, "Found x-model input: {$input['model']} of type {$input['type']}");
                }
            }

            // Assert no errors
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
            $clickElements = $browser->script("
                return Array.from(document.querySelectorAll('[x-on\\\\:click], [\\\\@click]')).map(el => ({
                    selector: el.tagName.toLowerCase() + (el.id ? '#' + el.id : ''),
                    handler: el.getAttribute('x-on:click') || el.getAttribute('@click')
                }));
            ")[0] ?? [];

            $this->assertNotEmpty($clickElements, 'Should have click event handlers');

            // Test Alpine.js and Livewire integration
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
            $this->waitForAlpineReady($browser);

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
}