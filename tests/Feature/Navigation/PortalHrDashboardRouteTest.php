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
        // RED PHASE: This should fail initially
        $this->assertFalse(
            Route::has('portal.hr.dashboard'),
            'Portal HR Dashboard route should NOT exist yet (TDD RED phase)'
        );

        // Additional validation
        try {
            route('portal.hr.dashboard');
            $this->fail('Route should not exist in RED phase');
        } catch (\Exception $e) {
            // Expected in RED phase - route should not exist
            $this->assertStringContains('not defined', $e->getMessage());
        }
    }

    #[Test]
    public function it_portal_hr_dashboard_route_should_exist()
    {
        // GREEN PHASE: This should pass after we add the route
        $this->assertTrue(
            Route::has('portal.hr.dashboard'),
            'Portal HR Dashboard route should exist (TDD GREEN phase)'
        );

        // Verify route can be generated
        $url = route('portal.hr.dashboard');
        $this->assertStringContains('/dashboard', $url);
    }
}
