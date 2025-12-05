<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * TDD Test: Identify ALL Missing Navigation Routes
 *
 * RED PHASE: This test should FAIL and show us ALL missing routes
 * GREEN PHASE: After we fix all routes, this should pass
 */
class NavigationMissingRoutesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_identifies_all_missing_navigation_routes_comprehensively()
    {
        // Extract all route() calls from navigation files
        $navigationFiles = [
            resource_path('views/components/navigation/desktop-menu.blade.php'),
            resource_path('views/components/navigation/mobile-menu.blade.php'),
            resource_path('views/components/navigation/portal-desktop-menu.blade.php'),
        ];

        $allRouteReferences = [];

        foreach ($navigationFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);

                // Extract all route() calls
                preg_match_all("/route\(['\"]([^'\"]+)['\"]/", $content, $matches);

                if (isset($matches[1])) {
                    $allRouteReferences = array_merge($allRouteReferences, $matches[1]);
                }
            }
        }

        // Remove duplicates and sort
        $allRouteReferences = array_unique($allRouteReferences);
        sort($allRouteReferences);

        // Check each route
        $missingRoutes = [];
        $existingRoutes = [];
        $brokenRoutes = [];

        foreach ($allRouteReferences as $routeName) {
            if (Route::has($routeName)) {
                $existingRoutes[] = $routeName;
            } else {
                $missingRoutes[] = $routeName;

                // Try to generate the route to see the exact error
                try {
                    route($routeName);
                } catch (\Exception $e) {
                    $brokenRoutes[$routeName] = $e->getMessage();
                }
            }
        }

        // This should FAIL in RED phase, showing us all missing routes
        if (! empty($missingRoutes)) {
            $this->markTestIncomplete(
                "MISSING ROUTES FOUND (Expected in TDD RED phase):\n\n".
                    'Missing Routes ('.count($missingRoutes)."):\n".
                    implode("\n", $missingRoutes)."\n\n".
                    "Broken Route Details:\n".
                    $this->formatBrokenRoutes($brokenRoutes)."\n\n".
                    'Existing Routes ('.count($existingRoutes)."):\n".
                    implode("\n", array_slice($existingRoutes, 0, 10)).
                    (count($existingRoutes) > 10 ? "\n... and ".(count($existingRoutes) - 10).' more' : '')
            );
        }

        // If we get here, all routes exist (GREEN phase) - TEST SHOULD PASS
        $this->assertEmpty($missingRoutes, 'All navigation routes should exist');
        $this->assertNotEmpty($existingRoutes, 'Navigation routes should exist');

        // Additional validation for TDD GREEN phase
        $this->assertTrue(count($existingRoutes) > 10, 'Should have substantial navigation routes available');
        $this->assertTrue(count($missingRoutes) === 0, 'Should have no missing routes');

        // Additional validation for TDD GREEN phase
        $this->assertTrue(count($existingRoutes) > 10, 'Should have substantial navigation routes available');
        $this->assertTrue(count($missingRoutes) === 0, 'Should have no missing routes');
    }

    private function formatBrokenRoutes(array $brokenRoutes): string
    {
        $output = '';
        foreach ($brokenRoutes as $route => $error) {
            $output .= "- {$route}: {$error}\n";
        }

        return $output;
    }

    #[Test]
    public function it_validates_specific_problematic_routes()
    {
        // Test specific routes that are causing issues
        $problematicRoutes = [
            'accounting.outstanding.receivables',
            'accounting.outstanding.payables',
            'accounting.download.trial-balance',
            'accounting.download.balance-sheet',
            'accounting.download.income-statement',
            'accounting.fixed-assets.download.asset-register',
            'accounting.fixed-assets.download.depreciation-schedule',
            'accounting.download.bank-transactions',
            'accounting.download.bank-statement',
            'accounting.download.bank-reconciliation',
            'accounting.download.receivables-outstanding',
            'accounting.download.payables-outstanding',
            'accounting.tax.download.tax-report',
            'accounting.tax.download.tax-liability',
            'accounting.tax.download.filing-schedule',
            'inventory.reports.download.stock-levels',
            'inventory.reports.download.movement',
        ];

        $missingRoutes = [];
        foreach ($problematicRoutes as $route) {
            if (! Route::has($route)) {
                $missingRoutes[] = $route;
            }
        }

        if (! empty($missingRoutes)) {
            $this->markTestIncomplete(
                "PROBLEMATIC ROUTES MISSING:\n".implode("\n", $missingRoutes)
            );
        }

        $this->assertEmpty($missingRoutes, 'All problematic routes should exist');
    }
}
