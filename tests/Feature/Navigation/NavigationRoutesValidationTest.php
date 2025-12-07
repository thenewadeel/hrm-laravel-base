<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * TDD Test: Navigation Routes Validation
 *
 * This test ensures ALL navigation routes exist before fixing navigation.
 * Following RED-GREEN-REFACTOR TDD approach.
 */
class NavigationRoutesValidationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_validates_all_accounting_navigation_routes_exist()
    {
        // Accounting Routes - RED PHASE: These should fail initially
        $accountingRoutes = [
            'accounting.index',
            'accounting.bank-accounts.index',
            'accounting.bank-statements.index',
            'accounting.bank-reconciliation.index',
            'accounting.bank-transactions.index',
            'accounting.cash-receipts.index',
            'accounting.cash-payments.index',
            'accounting.outstanding.receivables',
            'accounting.outstanding.payables',
            'accounting.fixed-assets.index',
            'accounting.financial-years.index',
            'accounting.tax.reporting.dashboard',
            'accounting.vouchers.sales.create',
            'accounting.vouchers.purchase.create',
            'accounting.vouchers.expense.create',
            'accounting.vouchers.salary.create',
        ];

        foreach ($accountingRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Accounting route [{$route}] should exist"
            );
        }
    }

    #[Test]
    public function it_validates_all_hr_navigation_routes_exist()
    {
        // HR Routes - RED PHASE: These should fail initially
        $hrRoutes = [
            'hr.employees.index',
            'hr.positions.index',
            'hr.shifts.index',
            'attendance.dashboard',
            'payroll.dashboard',
            'payroll.processing',
            'payroll.advances',
            'payroll.loans',
            'payroll.increments',
            'payroll.tax',
        ];

        foreach ($hrRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "HR route [{$route}] should exist"
            );
        }
    }

    #[Test]
    public function it_validates_all_inventory_navigation_routes_exist()
    {
        // Inventory Routes - RED PHASE: These should fail initially
        $inventoryRoutes = [
            'inventory.items.index',
            'inventory.stores.index',
            'inventory.transactions.index',
            'inventory.stock.adjustment',
            'inventory.stock.count',
            'inventory.stock.transfer',
            'inventory.reports.stock-levels',
            'inventory.reports.low-stock',
            'inventory.reports.movement',
        ];

        foreach ($inventoryRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Inventory route [{$route}] should exist"
            );
        }
    }

    #[Test]
    public function it_validates_all_organization_navigation_routes_exist()
    {
        // Organization Routes - RED PHASE: These should fail initially
        $organizationRoutes = [
            'organization.index',
            'organization.dashboard',
            'organization.analytics',
            'organization.structure',
        ];

        foreach ($organizationRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Organization route [{$route}] should exist"
            );
        }
    }

    #[Test]
    public function it_validates_all_membership_navigation_routes_exist()
    {
        // Membership Routes - RED PHASE: These should fail initially
        $membershipRoutes = [
            'members.index',
            'membership.dashboard',
            'cards.index',
            'cards.templates',
            'cards.settings',
            'fees.index',
            'fees.statistics',
            'subscriptions.index',
            'subscriptions.statistics',
            'subscriptions.expiring',
        ];

        foreach ($membershipRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Membership route [{$route}] should exist"
            );
        }
    }

    #[Test]
    public function it_validates_all_admin_navigation_routes_exist()
    {
        // Admin Routes - RED PHASE: These should fail initially
        $adminRoutes = [
            'admin.dashboard',
            'admin.attach-user',
            'admin.detach-user',
        ];

        foreach ($adminRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Admin route [{$route}] should exist"
            );
        }
    }

    #[Test]
    public function it_validates_all_setup_navigation_routes_exist()
    {
        // Setup Routes - RED PHASE: These should fail initially
        $setupRoutes = [
            'setup.organization',
            'setup.accounts',
            'setup.stores',
        ];

        foreach ($setupRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Setup route [{$route}] should exist"
            );
        }
    }

    #[Test]
    public function it_validates_all_portal_navigation_routes_exist()
    {
        // Portal Routes - RED PHASE: These should fail initially
        $portalRoutes = [
            'portal.employee.dashboard',
            'portal.employee.attendance',
            'portal.employee.leave',
            'portal.employee.payslips',
            'portal.employee.setup',
            'portal.manager.dashboard',
            'portal.manager.team-attendance',
            'portal.manager.reports',
        ];

        foreach ($portalRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Portal route [{$route}] should exist"
            );
        }
    }

    #[Test]
    public function it_validates_all_download_routes_exist()
    {
        // Download Routes - RED PHASE: These should fail initially
        $downloadRoutes = [
            'accounting.download.trial-balance',
            'accounting.download.balance-sheet',
            'accounting.download.income-statement',
            'accounting.download.receivables-outstanding',
            'accounting.download.payables-outstanding',
            'accounting.fixed-assets.download.asset-register',
            'accounting.fixed-assets.download.depreciation-schedule',
            'accounting.download.bank-transactions',
            'accounting.download.bank-statement',
            'accounting.download.bank-reconciliation',
            'accounting.tax.download.tax-report',
            'accounting.tax.download.tax-liability',
            'accounting.tax.download.filing-schedule',
            'inventory.reports.download.stock-levels',
            'inventory.reports.download.movement',
        ];

        foreach ($downloadRoutes as $route) {
            $this->assertTrue(
                Route::has($route),
                "Download route [{$route}] should exist"
            );
        }
    }

    #[Test]
    public function it_provides_comprehensive_route_validation_report()
    {
        // This test provides a detailed report of all missing routes
        $allNavigationRoutes = [
            // Core
            'dashboard',

            // Accounting
            'accounting.index',
            'accounting.bank-accounts.index',
            'accounting.bank-statements.index',
            'accounting.bank-reconciliation.index',
            'accounting.bank-transactions.index',
            'accounting.cash-receipts.index',
            'accounting.cash-payments.index',
            'accounting.outstanding.receivables',
            'accounting.outstanding.payables',
            'accounting.fixed-assets.index',
            'accounting.financial-years.index',
            'accounting.tax.reporting.dashboard',
            'accounting.vouchers.sales.create',
            'accounting.vouchers.purchase.create',
            'accounting.vouchers.expense.create',
            'accounting.vouchers.salary.create',

            // HR
            'hr.employees.index',
            'hr.positions.index',
            'hr.shifts.index',
            'attendance.dashboard',
            'payroll.dashboard',
            'payroll.processing',
            'payroll.advances',
            'payroll.loans',
            'payroll.increments',
            'payroll.tax',

            // Inventory
            'inventory.items.index',
            'inventory.stores.index',
            'inventory.transactions.index',
            'inventory.stock.adjustment',
            'inventory.stock.count',
            'inventory.stock.transfer',
            'inventory.reports.stock-levels',
            'inventory.reports.low-stock',
            'inventory.reports.movement',

            // Organization
            'organization.index',
            'organization.dashboard',
            'organization.analytics',
            'organization.structure',

            // Membership
            'members.index',
            'membership.dashboard',
            'cards.index',
            'cards.templates',
            'cards.settings',
            'fees.index',
            'fees.statistics',
            'subscriptions.index',
            'subscriptions.statistics',
            'subscriptions.expiring',

            // Admin
            'admin.dashboard',
            'admin.attach-user',
            'admin.detach-user',

            // Setup
            'setup.organization',
            'setup.accounts',
            'setup.stores',

            // Portal
            'portal.employee.dashboard',
            'portal.employee.attendance',
            'portal.employee.leave',
            'portal.employee.payslips',
            'portal.employee.setup',
            'portal.manager.dashboard',
            'portal.manager.team-attendance',
            'portal.manager.reports',
        ];

        $missingRoutes = [];
        $existingRoutes = [];

        foreach ($allNavigationRoutes as $route) {
            if (Route::has($route)) {
                $existingRoutes[] = $route;
            } else {
                $missingRoutes[] = $route;
            }
        }

        // Provide detailed output for debugging
        if (! empty($missingRoutes)) {
            $this->markTestIncomplete(
                'Missing routes found: '.implode(', ', $missingRoutes).
                    '. Existing routes: '.implode(', ', $existingRoutes)
            );
        }

        // If we get here, all routes exist
        $this->assertEmpty($missingRoutes, 'No missing routes should exist');
        $this->assertNotEmpty($existingRoutes, 'Navigation routes should exist');
    }
}
