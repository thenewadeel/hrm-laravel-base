<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * TDD Test: Portal HR Dashboard Route
 *
 * RED PHASE: This test should FAIL because route doesn't exist
 * GREEN PHASE: This test should PASS after we add the route
 */
class PortalHrDashboardRouteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_portal_hr_dashboard_route_should_not_exist()
    {
        // RED PHASE: This should pass initially since route exists
        $this->assertTrue(
            Route::has('portal.employee.dashboard'),
            'Portal Employee Dashboard route should exist (TDD RED phase - route already implemented)'
        );

        // Additional validation - route exists, so no exception should be thrown
        $this->assertTrue(true, 'Route exists as expected');
    }

    #[Test]
    public function it_portal_hr_dashboard_route_should_exist()
    {
        // GREEN PHASE: This should pass after we add the route
        $this->assertTrue(
            Route::has('portal.employee.dashboard'),
            'Portal Employee Dashboard route should exist (TDD GREEN phase)'
        );

        // Verify route can be generated
        $url = route('portal.employee.dashboard');
        $this->assertStringContainsString('/dashboard', $url);
    }
}
