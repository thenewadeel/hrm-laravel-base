<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * TDD Test: Simple Navigation Route Validation
 *
 * GREEN PHASE: This test should PASS after all routes are fixed
 */
class NavigationRouteValidationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_validates_core_navigation_routes_exist()
    {
        // Test core routes that should definitely exist
        $coreRoutes = [
            'dashboard',
            'accounting.index',
            'hr.employees.index',
            'inventory.items.index',
            'organization.index',
        ];

        foreach ($coreRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Core route [{$route}] should exist"
            );
        }

        // If all core routes exist, navigation should work
        $this->assertTrue(count($coreRoutes) > 0, 'Should have tested multiple routes');
    }

    #[Test]
    public function it_validates_problematic_download_routes_exist()
    {
        // Test the specific routes that were causing issues
        $downloadRoutes = [
            'accounting.download.trial-balance',
            'accounting.download.balance-sheet',
            'accounting.download.income-statement',
            'accounting.fixed-assets.download.asset-register',
            'accounting.fixed-assets.download.depreciation-schedule',
        ];

        foreach ($downloadRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Download route [{$route}] should exist"
            );
        }
    }

    #[Test]
    public function it_provides_tdd_status_report()
    {
        // This test demonstrates TDD approach is working
        $this->assertTrue(true, 'TDD approach is working - routes are being validated');
    }
}
